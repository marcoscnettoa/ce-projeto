<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Auth;
use Redirect;
use Session;
use Validator;
use Exception;
use Response;
use DB;
use PDF;
use Log;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

use \App\Models\MRANfClientes;
use \App\Models\Logs;
use \App\Models\Permissions;
use \GuzzleHttp\Client;
use setasign\Fpdi\Fpdi;
use Rap2hpoutre\FastExcel\FastExcel;
use Symfony\Component\Filesystem\Filesystem;
use Xthiago\PDFVersionConverter\Converter\GhostscriptConverterCommand;
use Xthiago\PDFVersionConverter\Converter\GhostscriptConverter;

use App\Repositories\UploadRepository;
use App\Repositories\ControllerRepository;
use App\Repositories\TemplateRepository;

class MRANfClientesController extends Controller
{
    public function __construct(
        Client $client,
        UploadRepository $uploadRepository,
        ControllerRepository $controllerRepository,
        TemplateRepository $templateRepository
    ) {
        $this->client               = $client;
        $this->upload               = $controllerRepository->upload;
        $this->maxSize              = $controllerRepository->maxSize;

        $this->uploadRepository     = $uploadRepository;
        $this->controllerRepository = $controllerRepository;
        $this->templateRepository   = $templateRepository;
    }

    public function index()
    {
        try {

            $user           = Auth::user();

            $MRANfClientes  = MRANfClientes::getAll(500);

            if($user){
                Logs::cadastrar($user->id, ($user->name . ' visualizou a lista do módulo mra_clientes'));
            }

            return view('mra_clientes.index', [
                'exibe_filtros'            => 0,
                'MRANfClientes'     => $MRANfClientes,
            ]);

        }catch(\Exception $e){
            Log::error($e->getMessage());
            Session::flash('flash_error', "Ocorreu um erro! Tente novamente.");
            return Redirect::to('/');
        }
    }

    public function filter(Request $request)
    {
        /* ... */
    }

    public function create($id = null)
    {
        try {

            $user = Auth::user();

            $MRANfClientes     = null;
            if(!is_null($id)){
                $MRANfClientes = MRANfClientes::find($id);
                if(!$MRANfClientes){
                    \Session::flash('flash_error', 'Registro não encontrado!');
                    return Redirect::to('mra_nota_fiscal/mra_clientes');
                }
            }

            if(!Permissions::permissaoModerador($user) && $MRANfClientes->r_auth != 0 && $MRANfClientes->r_auth != $user->id){
                Session::flash('flash_error', 'Você não tem permissão para executar esta ação!');
                return Redirect::to('mra_nota_fiscal/mra_clientes');
            }

            if($user){
                // Edição
                if(!is_null($MRANfClientes)){
                    Logs::cadastrar($user->id, ($user->name . ' visualizou a tela de edição ID #' . $id . ' do módulo mra_clientes'));
                    // Criação
                }else {
                    Logs::cadastrar($user->id, ($user->name . ' visualizou a tela de criação do módulo mra_clientes'));
                }
            }

            return view('mra_clientes.add_edit', [
                'exibe_filtros'            => 0,
                'MRANfClientes'     => $MRANfClientes
            ]);

        }catch(\Exception $e){
            Log::error($e->getMessage());
            Session::flash('flash_error', "Ocorreu um erro! Tente novamente.");
            return Redirect::to('/');
        }
    }

    protected function validator(array $data, $id = ''){
        return Validator::make($data, [
            'tipo'     => 'required',
            'nome'     => 'required',
            'cpf'      => ($data['tipo']=='F'?'required':''),
            'cnpj'     => ($data['tipo']=='J'?'required':'')
        ],[
            'tipo'     => 'O campo "Tipo de Pessoa" é obrigatório.',
            'nome'     => 'O campo "Nome" é obrigatório.',
            'cpf'      => 'O campo "CPF" é obrigatório.',
            'cnpj'     => 'O campo "CNPJ" é obrigatório.'
        ]);
    }

    public function store(Request $request)
    {
        try{

            $user       = Auth::user();
            $r_auth     = NULL;
            $redirect   = false;

            if($user) {
                $r_auth = $user->id;
            }

            $store = $request->all();

            if(isset($store['redirect']) && $store['redirect']){
                if (isset($_SERVER["QUERY_STRING"]) && $_SERVER["QUERY_STRING"]) {
                    $redirect = str_replace('redirect=', '', $_SERVER["QUERY_STRING"]);
                }
            }

            if(isset($store['r_auth'])){
                $r_auth = $store['r_auth'];
            }

            $validator  = $this->validator($store);
            if($validator->fails()){
                return back()->withInput()->with(array('errors' => $validator->errors()), 400);
            }

            DB::beginTransaction();

            $MRANfClientes                       = null;
            if(isset($store['id'])){
                $MRANfClientes                   = MRANfClientes::find($store['id']);
                if(!$MRANfClientes){
                    \Session::flash('flash_error', 'Registro não encontrado!');
                    return Redirect::to('mra_nota_fiscal/mra_clientes');
                }
            }
            $acao                                   = 'edit';
            if(is_null($MRANfClientes)){
                $MRANfClientes                      = new MRANfClientes();
                $acao                               = 'add';
            }

            $MRANfClientes->tipo                    = (isset($store['tipo'])?$store['tipo']:null);
            $MRANfClientes->cpf                     = (isset($store['cpf'])?str_replace(['_'],'',$store['cpf']):null);
            $MRANfClientes->cnpj                    = (isset($store['cnpj'])?str_replace(['_'],'',$store['cnpj']):null);
            $MRANfClientes->inscricao_estadual      = (isset($store['inscricao_estadual'])?$store['inscricao_estadual']:null);
            $MRANfClientes->inscricao_municipal     = (isset($store['inscricao_municipal'])?$store['inscricao_municipal']:null);

            // :: Física
            if($MRANfClientes->tipo == 'F'){
                $MRANfClientes->cnpj                = null;
                $MRANfClientes->inscricao_estadual  = null;
                $MRANfClientes->inscricao_municipal = null;

            // :: Jurídica
            }elseif($MRANfClientes->tipo == 'J'){
                $MRANfClientes->cpf                 = null;

            // :: Estrangeiro
            }elseif($MRANfClientes->tipo == 'E'){
                $MRANfClientes->cpf                 = null;
                $MRANfClientes->cnpj                = null;
                $MRANfClientes->inscricao_estadual  = null;
            }else {
                $MRANfClientes->cpf                 = null;
                $MRANfClientes->cnpj                = null;
                $MRANfClientes->inscricao_estadual  = null;
                $MRANfClientes->inscricao_municipal = null;
            }
            $MRANfClientes->nome                    = (isset($store['nome'])?$store['nome']:null);
            $MRANfClientes->cont_telefone           = (isset($store['cont_telefone'])?str_replace(['_'],'',$store['cont_telefone']):null);
            $MRANfClientes->cont_email              = (isset($store['cont_email'])?$store['cont_email']:null);
            $MRANfClientes->enviar_nf_email         = (isset($store['enviar_nf_email'])?$store['enviar_nf_email']:null);
            $MRANfClientes->end_cep                 = (isset($store['end_cep'])?str_replace(['_'],'',$store['end_cep']):null);
            $MRANfClientes->end_rua                 = (isset($store['end_rua'])?$store['end_rua']:null);
            $MRANfClientes->end_numero              = (isset($store['end_numero'])?$store['end_numero']:null);
            $MRANfClientes->end_bairro              = (isset($store['end_bairro'])?$store['end_bairro']:null);
            $MRANfClientes->end_complemento         = (isset($store['end_complemento'])?$store['end_complemento']:null);
            $MRANfClientes->end_estado              = (isset($store['end_estado'])?$store['end_estado']:null);
            $MRANfClientes->end_cidade              = (isset($store['end_cidade'])?$store['end_cidade']:null);
            $MRANfClientes->end_pais                = (isset($store['end_pais'])?$store['end_pais']:null);
            $MRANfClientes->status                  = (isset($store['status'])?$store['status']:null);
            $MRANfClientes->r_auth                  = $r_auth;

            $MRANfClientes->save();

            DB::commit();

            // :: Edição
            if($acao=='edit'){
                Session::flash('flash_success', "Cliente atualizado com sucesso!");
                if($user){
                    Logs::cadastrar($user->id, ($user->name . ' mra_clientes|'.$acao.': atualizou ID: ' . $MRANfClientes->id));
                }

            // :: Criação
            }else {
                Session::flash('flash_success', "Cliente cadastrado com sucesso!");
                if($user){
                    Logs::cadastrar($user->id, ($user->name . ' mra_clientes|'.$acao.': cadastrou ID: ' . $MRANfClientes->id));
                }
            }

        }catch(Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            Session::flash('flash_error', "Erro ao realizar atualização mra_clientes|'.$acao.': " . $e->getMessage());
            return back()->withInput()->with([],400);
        }

        if($redirect){
            return Redirect::to($redirect);
        }

        return Redirect::to('/mra_nota_fiscal/mra_clientes');
    }

    public function copy($id)
    {
        /* ... */
    }

    public function show($id)
    {
        /* ... */
    }

    public function modal($id)
    {
        /* ... */
    }

    public function ajax($value, Request $request)
    {
        $value   = str_replace('__H2F__', '/', $value);
        $subForm = $request->get('subForm');
        $user    = Auth::user();
        if($subForm){
            if(Permissions::permissaoModerador($user)){
                $MRANfClientes = MRANfClientes::where($subForm, $value)->get();
            }else {
                $MRANfClientes = MRANfClientes::where($subForm, $value)->where(function($q) use ($user){
                    $q->whereNull('r_auth')->orWhere('r_auth', $user->id)->orWhere('r_auth', 0);
                })->get();
            }
        }else {
            if(Permissions::permissaoModerador($user)){
                $MRANfClientes = MRANfClientes::find($value);
            }else {
                $MRANfClientes = MRANfClientes::where(function($q) use ($user){
                    $q->whereNull('r_auth')->orWhere('r_auth', $user->id)->orWhere('r_auth', 0);
                })->find($value);
            }
        }
        return Response::json($MRANfClientes);
    }

    public function pdf($id)
    {
        /* ... */
    }

    public function importar(Request $request)
    {
        /* ... */
    }

    public function edit($id)
    {
        return $this->create($id);
    }

    public function update(Request $request)
    {
        return $this->store($request);
    }

    public function destroy($id)
    {
        $MRANfClientes = MRANfClientes::find($id);
        return $this->controllerRepository::destroy(new MRANfClientes(), $id, 'mra_nota_fiscal/mra_clientes');
    }
}
