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

use \App\Models\MRAFClientes;
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

class MRAFClientesController extends Controller
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

    public function index(Request $request)
    {
        try {

            $user          = Auth::user();

            $MRAFClientes  = MRAFClientes::getAll(500,$request);

            if($user){
                Logs::cadastrar($user->id, ($user->name . ' visualizou a lista do módulo mra_f_clientes'));
            }

            return view('mra_f_clientes.index', [
                'exibe_filtros'    => 0,
                'MRAFClientes'     => $MRAFClientes,
            ]);

        }catch(\Exception $e){
            Log::error($e->getMessage());
            Session::flash('flash_error', "Ocorreu um erro! Tente novamente.");
            return Redirect::to('/');
        }
    }

    public function filter(Request $request)
    {
        return $this->index($request);
    }

    public function create($id = null)
    {
        try {

            $user = Auth::user();

            $MRAFClientes     = null;
            if(!is_null($id)){
                $MRAFClientes = MRAFClientes::find($id);
                if(!$MRAFClientes){
                    \Session::flash('flash_error', 'Registro não encontrado!');
                    return Redirect::to('mra_fluxo_financeiro/mra_f_clientes');
                }
            }

            if(!Permissions::permissaoModerador($user) && $MRAFClientes->r_auth != 0 && $MRAFClientes->r_auth != $user->id){
                Session::flash('flash_error', 'Você não tem permissão para executar esta ação!');
                return Redirect::to('mra_fluxo_financeiro/mra_f_clientes');
            }

            if($user){
                // Edição
                if(!is_null($MRAFClientes)){
                    Logs::cadastrar($user->id, ($user->name . ' visualizou a tela de edição ID #' . $id . ' do módulo mra_f_clientes'));
                    // Criação
                }else {
                    Logs::cadastrar($user->id, ($user->name . ' visualizou a tela de criação do módulo mra_f_clientes'));
                }
            }

            return view('mra_f_clientes.add_edit', [
                'exibe_filtros'            => 0,
                'MRAFClientes'     => $MRAFClientes
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

            $MRAFClientes                       = null;
            if(isset($store['id'])){
                $MRAFClientes                   = MRAFClientes::find($store['id']);
                if(!$MRAFClientes){
                    \Session::flash('flash_error', 'Registro não encontrado!');
                    return Redirect::to('mra_fluxo_financeiro/mra_f_clientes');
                }
            }
            $acao                                   = 'edit';
            if(is_null($MRAFClientes)){
                $MRAFClientes                      = new MRAFClientes();
                $acao                               = 'add';
            }

            $MRAFClientes->tipo                    = (isset($store['tipo'])?$store['tipo']:null);
            $MRAFClientes->cpf                     = (isset($store['cpf'])?str_replace(['_'],'',$store['cpf']):null);
            $MRAFClientes->cnpj                    = (isset($store['cnpj'])?str_replace(['_'],'',$store['cnpj']):null);
            $MRAFClientes->inscricao_estadual      = (isset($store['inscricao_estadual'])?$store['inscricao_estadual']:null);
            $MRAFClientes->inscricao_municipal     = (isset($store['inscricao_municipal'])?$store['inscricao_municipal']:null);

            // :: Física
            if($MRAFClientes->tipo == 'F'){
                $MRAFClientes->cnpj                = null;
                $MRAFClientes->inscricao_estadual  = null;
                $MRAFClientes->inscricao_municipal = null;

            // :: Jurídica
            }elseif($MRAFClientes->tipo == 'J'){
                $MRAFClientes->cpf                 = null;

            // :: Estrangeiro
            }elseif($MRAFClientes->tipo == 'E'){
                $MRAFClientes->cpf                 = null;
                $MRAFClientes->cnpj                = null;
                $MRAFClientes->inscricao_estadual  = null;
            }else {
                $MRAFClientes->cpf                 = null;
                $MRAFClientes->cnpj                = null;
                $MRAFClientes->inscricao_estadual  = null;
                $MRAFClientes->inscricao_municipal = null;
            }
            $MRAFClientes->nome                    = (isset($store['nome'])?$store['nome']:null);
            $MRAFClientes->cont_telefone           = (isset($store['cont_telefone'])?str_replace(['_'],'',$store['cont_telefone']):null);
            $MRAFClientes->cont_email              = (isset($store['cont_email'])?$store['cont_email']:null);
            $MRAFClientes->end_cep                 = (isset($store['end_cep'])?str_replace(['_'],'',$store['end_cep']):null);
            $MRAFClientes->end_rua                 = (isset($store['end_rua'])?$store['end_rua']:null);
            $MRAFClientes->end_numero              = (isset($store['end_numero'])?$store['end_numero']:null);
            $MRAFClientes->end_bairro              = (isset($store['end_bairro'])?$store['end_bairro']:null);
            $MRAFClientes->end_complemento         = (isset($store['end_complemento'])?$store['end_complemento']:null);
            $MRAFClientes->end_estado              = (isset($store['end_estado'])?$store['end_estado']:null);
            $MRAFClientes->end_cidade              = (isset($store['end_cidade'])?$store['end_cidade']:null);
            $MRAFClientes->end_pais                = (isset($store['end_pais'])?$store['end_pais']:null);
            $MRAFClientes->status                  = (isset($store['status'])?$store['status']:null);
            $MRAFClientes->r_auth                  = $r_auth;

            $MRAFClientes->save();

            DB::commit();

            // :: Edição
            if($acao=='edit'){
                Session::flash('flash_success', "Cliente atualizado com sucesso!");
                if($user){
                    Logs::cadastrar($user->id, ($user->name . ' mra_f_clientes|'.$acao.': atualizou ID: ' . $MRAFClientes->id));
                }

            // :: Criação
            }else {
                Session::flash('flash_success', "Cliente cadastrado com sucesso!");
                if($user){
                    Logs::cadastrar($user->id, ($user->name . ' mra_f_clientes|'.$acao.': cadastrou ID: ' . $MRAFClientes->id));
                }
            }

        }catch(Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            Session::flash('flash_error', "Erro ao realizar atualização mra_f_clientes|'.$acao.': " . $e->getMessage());
            return back()->withInput()->with([],400);
        }

        if($redirect){
            return Redirect::to($redirect);
        }

        return Redirect::to('/mra_fluxo_financeiro/mra_f_clientes');
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
                $MRAFClientes = MRAFClientes::where($subForm, $value)->get();
            }else {
                $MRAFClientes = MRAFClientes::where($subForm, $value)->where(function($q) use ($user){
                    $q->whereNull('r_auth')->orWhere('r_auth', $user->id)->orWhere('r_auth', 0);
                })->get();
            }
        }else {
            if(Permissions::permissaoModerador($user)){
                $MRAFClientes = MRAFClientes::find($value);
            }else {
                $MRAFClientes = MRAFClientes::where(function($q) use ($user){
                    $q->whereNull('r_auth')->orWhere('r_auth', $user->id)->orWhere('r_auth', 0);
                })->find($value);
            }
        }
        return Response::json($MRAFClientes);
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
        $MRAFClientes = MRAFClientes::find($id);
        return $this->controllerRepository::destroy(new MRAFClientes(), $id, 'mra_fluxo_financeiro/mra_f_clientes');
    }
}
