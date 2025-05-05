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

use \App\Models\MRANfConfiguracoes;
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

class MRANfConfiguracoesController extends Controller
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

            $user       = Auth::user();

            $MRANfConfiguracoes  = MRANfConfiguracoes::find(1);

            if($user){
                Logs::cadastrar($user->id, ($user->name . ' visualizou a tela de atualização do módulo mra_configuracoes'));
            }

            return view('mra_configuracoes.add_edit', [
                'exibe_filtros' => 0,
                'MRANfConfiguracoes'     => $MRANfConfiguracoes,
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

    public function create()
    {
        /* ... */
    }

    protected function validator(array $data, $id = ''){
        return Validator::make($data, [
            'cnpj'              => 'required',
            'razao_social'      => 'required',
            'nome_fantasia'     => 'required',
            'cont_email'        => 'required',
            'cont_telefone'     => 'required',
            'regime_tributario' => 'required',
            'status'            => 'required',
            'end_cep'           => 'required',
            'end_rua'           => 'required',
            'end_numero'        => 'required',
            'end_bairro'        => 'required',
            'end_complemento'   => 'required',
            'end_estado'        => 'required',
            'end_cidade'        => 'required',
            //'token_api'         => 'required',
            //'token_webhook'     => 'required',
        ],[
            'cnpj'              => 'O campo "CNPJ" é obrigatório.',
            'razao_social'      => 'O campo "Razão Social" é obrigatório.',
            'nome_fantasia'     => 'O campo "Nome Fantasia" é obrigatório.',
            'cont_email'        => 'O campo "E-mail" é obrigatório.',
            'cont_telefone'     => 'O campo "Telefone" é obrigatório.',
            'regime_tributario' => 'O campo "Regime Tributário" é obrigatório.',
            'status'            => 'O campo "Status" é obrigatório.',
            'end_cep'           => 'O campo "CEP" é obrigatório.',
            'end_rua'           => 'O campo "Logradouro / Rua" é obrigatório.',
            'end_numero'        => 'O campo "Número" é obrigatório.',
            'end_bairro'        => 'O campo "Bairro" é obrigatório.',
            'end_complemento'   => 'O campo "Complemento" é obrigatório.',
            'end_estado'        => 'O campo "Estado" é obrigatório.',
            'end_cidade'        => 'O campo "Cidade" é obrigatório.',
            //'token_api'         => 'O campo "Token API" é obrigatório.',
            //'token_webhook'     => 'O campo "Webhook Token" é obrigatório.'
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

            $MRANfConfiguracoes                      = MRANfConfiguracoes::find(1); // ! Fixo - No momento Liberado apenas para 1 Empresa*
            $acao                           = 'edit';
            if(!$MRANfConfiguracoes){
                $MRANfConfiguracoes                  = new MRANfConfiguracoes();
                $MRANfConfiguracoes->id              = 1;
                $acao                       = 'add';
            }

            $MRANfConfiguracoes->cnpj                = (isset($store['cnpj'])?str_replace(['_'],'',$store['cnpj']):null);
            $MRANfConfiguracoes->inscricao_municipal = (isset($store['inscricao_municipal'])?$store['inscricao_municipal']:null);
            $MRANfConfiguracoes->inscricao_estadual  = (isset($store['inscricao_estadual'])?$store['inscricao_estadual']:null);
            $MRANfConfiguracoes->razao_social        = (isset($store['razao_social'])?$store['razao_social']:null);
            $MRANfConfiguracoes->nome_fantasia       = (isset($store['nome_fantasia'])?$store['nome_fantasia']:null);
            $MRANfConfiguracoes->apelido_empresa     = (isset($store['apelido_empresa'])?$store['apelido_empresa']:null);
            $MRANfConfiguracoes->cont_email          = (isset($store['cont_email'])?$store['cont_email']:null);
            $MRANfConfiguracoes->cont_telefone       = (isset($store['cont_telefone'])?str_replace(['_'],'',$store['cont_telefone']):null);
            $MRANfConfiguracoes->regime_tributario   = (isset($store['regime_tributario'])?$store['regime_tributario']:null);
            $MRANfConfiguracoes->status              = (isset($store['status'])?$store['status']:null);
            $MRANfConfiguracoes->end_cep             = (isset($store['end_cep'])?str_replace(['_'],'',$store['end_cep']):null);
            $MRANfConfiguracoes->end_rua             = (isset($store['end_rua'])?$store['end_rua']:null);
            $MRANfConfiguracoes->end_numero          = (isset($store['end_numero'])?$store['end_numero']:null);
            $MRANfConfiguracoes->end_bairro          = (isset($store['end_bairro'])?$store['end_bairro']:null);
            $MRANfConfiguracoes->end_complemento     = (isset($store['end_complemento'])?$store['end_complemento']:null);
            $MRANfConfiguracoes->end_estado          = (isset($store['end_estado'])?$store['end_estado']:null);
            $MRANfConfiguracoes->end_cidade          = (isset($store['end_cidade'])?$store['end_cidade']:null);
            $MRANfConfiguracoes->token_api           = (isset($store['token_api'])?$store['token_api']:null);
            $MRANfConfiguracoes->token_webhook       = (isset($store['token_webhook'])?$store['token_webhook']:null);
            $MRANfConfiguracoes->r_auth              = $r_auth;

            $MRANfConfiguracoes->save();

            DB::commit();

            Session::flash('flash_success', "Configurações realizada com sucesso!");

            if($user) {
                Logs::cadastrar($user->id, ($user->name . ' mra_configuracoes|'.$acao.': atualizou ID: ' . $MRANfConfiguracoes->id));
            }

        }catch(Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            Session::flash('flash_error', "Erro ao realizar atualização mra_configuracoes|'.$acao.': " . $e->getMessage());
            return back()->withInput()->with([],400);
        }

        if($redirect){
            return Redirect::to($redirect);
        }

        return Redirect::to('/mra_nota_fiscal/mra_configuracoes');
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
                $MRANfConfiguracoes = MRANfConfiguracoes::where($subForm, $value)->get();
            }else {
                $MRANfConfiguracoes = MRANfConfiguracoes::where($subForm, $value)->where(function($q) use ($user){
                    $q->whereNull('r_auth')->orWhere('r_auth', $user->id)->orWhere('r_auth', 0);
                })->get();
            }
        }else {
            if(Permissions::permissaoModerador($user)){
                $MRANfConfiguracoes = MRANfConfiguracoes::find($value);
            }else {
                $MRANfConfiguracoes = MRANfConfiguracoes::where(function($q) use ($user){
                    $q->whereNull('r_auth')->orWhere('r_auth', $user->id)->orWhere('r_auth', 0);
                })->find($value);
            }
        }
        return Response::json($MRANfConfiguracoes);
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
        /* ... */
    }

    public function update(Request $request)
    {
        return $this->store($request);
    }

    public function destroy($id)
    {
        /* ... */
    }
}
