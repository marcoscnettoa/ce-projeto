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

use \App\Models\MRAFFornecedores;
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

class MRAFFornecedoresController extends Controller
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

            $user           = Auth::user();

            $MRAFFornecedores  = MRAFFornecedores::getAll(500,$request);

            if($user){
                Logs::cadastrar($user->id, ($user->name . ' visualizou a lista do módulo mra_f_fornecedores'));
            }

            return view('mra_f_fornecedores.index', [
                'exibe_filtros'        => 0,
                'MRAFFornecedores'     => $MRAFFornecedores,
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

            $MRAFFornecedores     = null;
            if(!is_null($id)){
                $MRAFFornecedores = MRAFFornecedores::find($id);
                if(!$MRAFFornecedores){
                    \Session::flash('flash_error', 'Registro não encontrado!');
                    return Redirect::to('mra_fluxo_financeiro/mra_f_fornecedores');
                }
            }

            if(!Permissions::permissaoModerador($user) && $MRAFFornecedores->r_auth != 0 && $MRAFFornecedores->r_auth != $user->id){
                Session::flash('flash_error', 'Você não tem permissão para executar esta ação!');
                return Redirect::to('mra_fluxo_financeiro/mra_f_fornecedores');
            }

            if($user){
                // Edição
                if(!is_null($MRAFFornecedores)){
                    Logs::cadastrar($user->id, ($user->name . ' visualizou a tela de edição ID #' . $id . ' do módulo mra_f_fornecedores'));
                    // Criação
                }else {
                    Logs::cadastrar($user->id, ($user->name . ' visualizou a tela de criação do módulo mra_f_fornecedores'));
                }
            }

            return view('mra_f_fornecedores.add_edit', [
                'exibe_filtros'            => 0,
                'MRAFFornecedores'     => $MRAFFornecedores
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

            $MRAFFornecedores                       = null;
            if(isset($store['id'])){
                $MRAFFornecedores                   = MRAFFornecedores::find($store['id']);
                if(!$MRAFFornecedores){
                    \Session::flash('flash_error', 'Registro não encontrado!');
                    return Redirect::to('mra_fluxo_financeiro/mra_f_fornecedores');
                }
            }
            $acao                                   = 'edit';
            if(is_null($MRAFFornecedores)){
                $MRAFFornecedores                      = new MRAFFornecedores();
                $acao                               = 'add';
            }

            $MRAFFornecedores->tipo                    = (isset($store['tipo'])?$store['tipo']:null);
            $MRAFFornecedores->cpf                     = (isset($store['cpf'])?str_replace(['_'],'',$store['cpf']):null);
            $MRAFFornecedores->cnpj                    = (isset($store['cnpj'])?str_replace(['_'],'',$store['cnpj']):null);
            $MRAFFornecedores->inscricao_estadual      = (isset($store['inscricao_estadual'])?$store['inscricao_estadual']:null);
            $MRAFFornecedores->inscricao_municipal     = (isset($store['inscricao_municipal'])?$store['inscricao_municipal']:null);

            // :: Física
            if($MRAFFornecedores->tipo == 'F'){
                $MRAFFornecedores->cnpj                = null;
                $MRAFFornecedores->inscricao_estadual  = null;
                $MRAFFornecedores->inscricao_municipal = null;

            // :: Jurídica
            }elseif($MRAFFornecedores->tipo == 'J'){
                $MRAFFornecedores->cpf                 = null;

            // :: Estrangeiro
            }elseif($MRAFFornecedores->tipo == 'E'){
                $MRAFFornecedores->cpf                 = null;
                $MRAFFornecedores->cnpj                = null;
                $MRAFFornecedores->inscricao_estadual  = null;
            }else {
                $MRAFFornecedores->cpf                 = null;
                $MRAFFornecedores->cnpj                = null;
                $MRAFFornecedores->inscricao_estadual  = null;
                $MRAFFornecedores->inscricao_municipal = null;
            }
            $MRAFFornecedores->nome                    = (isset($store['nome'])?$store['nome']:null);
            $MRAFFornecedores->cont_telefone           = (isset($store['cont_telefone'])?str_replace(['_'],'',$store['cont_telefone']):null);
            $MRAFFornecedores->cont_email              = (isset($store['cont_email'])?$store['cont_email']:null);
            $MRAFFornecedores->end_cep                 = (isset($store['end_cep'])?str_replace(['_'],'',$store['end_cep']):null);
            $MRAFFornecedores->end_rua                 = (isset($store['end_rua'])?$store['end_rua']:null);
            $MRAFFornecedores->end_numero              = (isset($store['end_numero'])?$store['end_numero']:null);
            $MRAFFornecedores->end_bairro              = (isset($store['end_bairro'])?$store['end_bairro']:null);
            $MRAFFornecedores->end_complemento         = (isset($store['end_complemento'])?$store['end_complemento']:null);
            $MRAFFornecedores->end_estado              = (isset($store['end_estado'])?$store['end_estado']:null);
            $MRAFFornecedores->end_cidade              = (isset($store['end_cidade'])?$store['end_cidade']:null);
            $MRAFFornecedores->end_pais                = (isset($store['end_pais'])?$store['end_pais']:null);
            $MRAFFornecedores->status                  = (isset($store['status'])?$store['status']:null);
            $MRAFFornecedores->r_auth                  = $r_auth;

            $MRAFFornecedores->save();

            DB::commit();

            // :: Edição
            if($acao=='edit'){
                Session::flash('flash_success', "Fornecedor atualizado com sucesso!");
                if($user){
                    Logs::cadastrar($user->id, ($user->name . ' mra_f_fornecedores|'.$acao.': atualizou ID: ' . $MRAFFornecedores->id));
                }

            // :: Criação
            }else {
                Session::flash('flash_success', "Fornecedor cadastrado com sucesso!");
                if($user){
                    Logs::cadastrar($user->id, ($user->name . ' mra_f_fornecedores|'.$acao.': cadastrou ID: ' . $MRAFFornecedores->id));
                }
            }

        }catch(Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            Session::flash('flash_error', "Erro ao realizar atualização mra_f_fornecedores|'.$acao.': " . $e->getMessage());
            return back()->withInput()->with([],400);
        }

        if($redirect){
            return Redirect::to($redirect);
        }

        return Redirect::to('/mra_fluxo_financeiro/mra_f_fornecedores');
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
                $MRAFFornecedores = MRAFFornecedores::where($subForm, $value)->get();
            }else {
                $MRAFFornecedores = MRAFFornecedores::where($subForm, $value)->where(function($q) use ($user){
                    $q->whereNull('r_auth')->orWhere('r_auth', $user->id)->orWhere('r_auth', 0);
                })->get();
            }
        }else {
            if(Permissions::permissaoModerador($user)){
                $MRAFFornecedores = MRAFFornecedores::find($value);
            }else {
                $MRAFFornecedores = MRAFFornecedores::where(function($q) use ($user){
                    $q->whereNull('r_auth')->orWhere('r_auth', $user->id)->orWhere('r_auth', 0);
                })->find($value);
            }
        }
        return Response::json($MRAFFornecedores);
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
        $MRAFFornecedores = MRAFFornecedores::find($id);
        return $this->controllerRepository::destroy(new MRAFFornecedores(), $id, 'mra_fluxo_financeiro/mra_f_fornecedores');
    }
}
