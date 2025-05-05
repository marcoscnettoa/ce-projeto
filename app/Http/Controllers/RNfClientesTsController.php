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

use \App\Models\RNfClientesTs;
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

class RNfClientesTsController extends Controller
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

            $RNfClientes  = RNfClientesTs::getAll(500);

            if($user){
                Logs::cadastrar($user->id, ($user->name . ' visualizou a lista do módulo r_nf_clientes_ts'));
            }

            return view('r_nf_clientes_ts.index', [
                'exibe_filtros'            => 0,
                'RNfClientes'     => $RNfClientes,
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

            $RNfClientes     = null;
            if(!is_null($id)){
                $RNfClientes = RNfClientesTs::find($id);
                if(!$RNfClientes){
                    \Session::flash('flash_error', 'Registro não encontrado!');
                    return Redirect::to('/nota_fiscal/clientes/ts');
                }
            }

            if(!Permissions::permissaoModerador($user) && $RNfClientes->r_auth != 0 && $RNfClientes->r_auth != $user->id){
                Session::flash('flash_error', 'Você não tem permissão para executar esta ação!');
                return Redirect::to('/nota_fiscal/clientes/ts');
            }

            if($user){
                // Edição
                if(!is_null($RNfClientes)){
                    Logs::cadastrar($user->id, ($user->name . ' visualizou a tela de edição ID #' . $id . ' do módulo r_nf_clientes_ts'));
                    // Criação
                }else {
                    Logs::cadastrar($user->id, ($user->name . ' visualizou a tela de criação do módulo r_nf_clientes_ts'));
                }
            }

            return view('r_nf_clientes_ts.add_edit', [
                'exibe_filtros'            => 0,
                'RNfClientes'     => $RNfClientes
            ]);

        }catch(\Exception $e){
            Log::error($e->getMessage());
            Session::flash('flash_error', "Ocorreu um erro! Tente novamente.");
            return Redirect::to('/');
        }
    }

    protected function validator(array $data, $id = ''){
        return Validator::make($data, [
            'tipo'                  => 'required',
            'nome'                  => 'required',
            'cpf'                   => ($data['tipo']=='F'?'required|unique:r_nf_clientes_ts,cpf,'.$id.',id,deleted_at,NULL':''),
            'cnpj'                  => ($data['tipo']=='J'?'required|unique:r_nf_clientes_ts,cnpj,'.$id.',id,deleted_at,NULL':''),
            'end_cep'               => 'required',
            'end_tipo_logradouro'   => 'required',
            'end_rua'               => 'required',
            'end_numero'            => 'required',
            'end_bairro'            => 'required',
            'end_estado'            => 'required',
            'end_cidade'            => 'required',
        ],[
            'tipo'                  => 'O campo "Tipo de Pessoa" é obrigatório.',
            'nome'                  => 'O campo "Nome" é obrigatório.',
            'cpf.required'          => 'O campo "CPF" é obrigatório.',
            'cpf.unique'            => 'O "CPF" informado já está em uso.',
            'cnpj.required'         => 'O campo "CNPJ" é obrigatório.',
            'cnpj.unique'           => 'O "CNPJ" informado já está em uso.',
            'end_cep'               => 'O campo "CEP" é obrigatório.',
            'end_tipo_logradouro'   => 'O campo "Tipo de Logradouro" é obrigatório.',
            'end_rua'               => 'O campo "Logradouro / Rua" é obrigatório.',
            'end_numero'            => 'O campo "Número" é obrigatório.',
            'end_bairro'            => 'O campo "Bairro" é obrigatório.',
            'end_estado'            => 'O campo "Estado" é obrigatório.',
            'end_cidade'            => 'O campo "Cidade" é obrigatório.',
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

            $validator  = $this->validator($store, (isset($store['id'])?$store['id']:null));
            if($validator->fails()){
                return back()->withInput()->with(array('errors' => $validator->errors()), 400);
            }

            DB::beginTransaction();

            $RNfClientes                       = null;
            if(isset($store['id'])){
                $RNfClientes                   = RNfClientesTs::find($store['id']);
                if(!$RNfClientes){
                    \Session::flash('flash_error', 'Registro não encontrado!');
                    return Redirect::to('/nota_fiscal/clientes/ts');
                }
            }
            $acao                                   = 'edit';
            if(is_null($RNfClientes)){
                $RNfClientes                      = new RNfClientesTs();
                $acao                               = 'add';
            }

            $RNfClientes->tipo                    = (isset($store['tipo'])?$store['tipo']:null);
            $RNfClientes->cpf                     = (isset($store['cpf'])?str_replace(['_'],'',$store['cpf']):null);
            $RNfClientes->cnpj                    = (isset($store['cnpj'])?str_replace(['_'],'',$store['cnpj']):null);
            $RNfClientes->inscricao_estadual      = (isset($store['inscricao_estadual'])?$store['inscricao_estadual']:null);
            $RNfClientes->inscricao_municipal     = (isset($store['inscricao_municipal'])?$store['inscricao_municipal']:null);

            // :: Física
            if($RNfClientes->tipo == 'F'){
                $RNfClientes->cnpj                = null;
                $RNfClientes->inscricao_estadual  = null;
                $RNfClientes->inscricao_municipal = null;

            // :: Jurídica
            }elseif($RNfClientes->tipo == 'J'){
                $RNfClientes->cpf                 = null;

            // :: Estrangeiro
            }elseif($RNfClientes->tipo == 'E'){
                $RNfClientes->cpf                 = null;
                $RNfClientes->cnpj                = null;
                $RNfClientes->inscricao_estadual  = null;
            }else {
                $RNfClientes->cpf                 = null;
                $RNfClientes->cnpj                = null;
                $RNfClientes->inscricao_estadual  = null;
                $RNfClientes->inscricao_municipal = null;
            }
            $RNfClientes->nome                    = (isset($store['nome'])?$store['nome']:null);
            $RNfClientes->cont_telefone           = (isset($store['cont_telefone'])?str_replace(['_'],'',$store['cont_telefone']):null);
            $RNfClientes->cont_email              = (isset($store['cont_email'])?$store['cont_email']:null);
            $RNfClientes->enviar_nf_email         = (isset($store['enviar_nf_email'])?$store['enviar_nf_email']:null);
            $RNfClientes->end_cep                 = (isset($store['end_cep'])?str_replace(['_'],'',$store['end_cep']):null);
            $RNfClientes->end_tipo_logradouro     = (isset($store['end_tipo_logradouro'])?$store['end_tipo_logradouro']:null);
            $RNfClientes->end_rua                 = (isset($store['end_rua'])?$store['end_rua']:null);
            $RNfClientes->end_numero              = (isset($store['end_numero'])?$store['end_numero']:null);
            $RNfClientes->end_bairro              = (isset($store['end_bairro'])?$store['end_bairro']:null);
            $RNfClientes->end_complemento         = (isset($store['end_complemento'])?$store['end_complemento']:null);
            $RNfClientes->end_estado              = (isset($store['end_estado'])?$store['end_estado']:null);
            $RNfClientes->end_cidade              = (isset($store['end_cidade'])?$store['end_cidade']:null);
            $RNfClientes->end_pais                = (isset($store['end_pais'])?$store['end_pais']:null);
            $RNfClientes->status                  = (isset($store['status'])?$store['status']:null);
            $RNfClientes->r_auth                  = $r_auth;
            $RNfClientes->save();

            DB::commit();

            // :: Edição
            if($acao=='edit'){
                Session::flash('flash_success', "Cliente atualizado com sucesso!");
                if($user){
                    Logs::cadastrar($user->id, ($user->name . ' r_nf_clientes_ts|'.$acao.': atualizou ID: ' . $RNfClientes->id));
                }

            // :: Criação
            }else {
                Session::flash('flash_success', "Cliente cadastrado com sucesso!");
                if($user){
                    Logs::cadastrar($user->id, ($user->name . ' r_nf_clientes_ts|'.$acao.': cadastrou ID: ' . $RNfClientes->id));
                }
            }

        }catch(Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            Session::flash('flash_error', "Erro ao realizar atualização r_nf_clientes_ts|'.$acao.': " . $e->getMessage());
            return back()->withInput()->with([],400);
        }

        if($redirect){
            return Redirect::to($redirect);
        }

        return Redirect::to('/nota_fiscal/clientes/ts');
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
                $RNfClientes = RNfClientesTs::where($subForm, $value)->get();
            }else {
                $RNfClientes = RNfClientesTs::where($subForm, $value)->where(function($q) use ($user){
                    $q->whereNull('r_auth')->orWhere('r_auth', $user->id)->orWhere('r_auth', 0);
                })->get();
            }
        }else {
            if(Permissions::permissaoModerador($user)){
                $RNfClientes = RNfClientesTs::find($value);
            }else {
                $RNfClientes = RNfClientesTs::where(function($q) use ($user){
                    $q->whereNull('r_auth')->orWhere('r_auth', $user->id)->orWhere('r_auth', 0);
                })->find($value);
            }
        }
        return Response::json($RNfClientes);
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
        $RNfClientes = RNfClientesTs::find($id);
        return $this->controllerRepository::destroy(new RNfClientesTs(), $id, 'nota_fiscal/clientes/ts');
    }
}
