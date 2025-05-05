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

use \App\Models\RNfConfiguracoesTs;
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
use App\Models\REstados;
use App\Models\RCidades;
use App\Http\Controllers\RTecnoSpeedController as RTecnoSpeed;

class RNfConfiguracoesTsController extends Controller
{
    public function __construct(
        Client $client,
        UploadRepository $uploadRepository,
        ControllerRepository $controllerRepository,
        TemplateRepository $templateRepository,
        RTecnoSpeed $tecnospeed
    ) {
        $this->client               = $client;
        $this->upload               = $controllerRepository->upload;
        $this->maxSize              = $controllerRepository->maxSize;

        $this->uploadRepository     = $uploadRepository;
        $this->controllerRepository = $controllerRepository;
        $this->templateRepository   = $templateRepository;
        $this->tecnospeed           = $tecnospeed;
    }

    public function index()
    {
        try {

            $user       = Auth::user();

            $MRANfConfiguracoes  = RNfConfiguracoesTs::find(1);

            $estados = REstados::selectRaw('*,CONCAT(sigla," - ",nome) as sigla_nome')
                ->pluck('sigla_nome', 'id')
                ->prepend("---", "");

            $cidades = RCidades::selectRaw('*,CONCAT(uf," - ",nome) as uf_nome')
                ->pluck('uf_nome', 'id')
                ->prepend("---", "");

            if($user){
                Logs::cadastrar($user->id, ($user->name . ' visualizou a tela de atualização do módulo r_nf_configuracoes_ts'));
            }

            return view('r_nf_configuracoes_ts.add_edit', [
                'exibe_filtros' => 0,
                'MRANfConfiguracoes' => $MRANfConfiguracoes,
                'estados' => $estados,
                'cidades' => $cidades,
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
            'cnpj'                       => 'required',
            'razao_social'               => 'required',
            'nome_fantasia'              => 'required',
            'inscricao_municipal'        => env('MODULO_NF_SERVICO') ? 'required' : '',
            'inscricao_estadual'         => env('MODULO_NF_PRODUTO') ? 'required' : '',
            'cont_email'                 => 'required',
            'cont_telefone'              => 'required',
            'optante_simples_nacional'   => 'required',
            'regime_tributario'          => 'required',
            'regime_tributario_especial' => 'required',
            'end_cep'                    => 'required',
            'end_tipo_logradouro'        => 'required',
            'end_rua'                    => 'required',
            'end_numero'                 => 'required',
            'end_bairro'                 => 'required',
            'end_complemento'            => 'required',
            'end_estado'                 => 'required',
            'end_cidade'                 => 'required',
            'certificado_digital'        => (!$data['certificado_id'] && !isset($data['certificado_digital'])) ? 'required' : '',
            'senha_certificado'          => (!$data['certificado_id'] && (!isset($data['certificado_digital']) || isset($data['certificado_digital']))) ? 'required' : '',
        ],[
            'cnpj'                       => 'O campo "CNPJ" é obrigatório.',
            'razao_social'               => 'O campo "Razão Social" é obrigatório.',
            'nome_fantasia'              => 'O campo "Nome Fantasia" é obrigatório.',
            'inscricao_municipal'        => 'O campo "Inscrição Municipal" é obrigatório.',
            'inscricao_estadual'         => 'O campo "Inscrição Estadual" é obrigatório.',
            'cont_email'                 => 'O campo "E-mail" é obrigatório.',
            'cont_telefone'              => 'O campo "Telefone" é obrigatório.',
            'optante_simples_nacional'   => 'O campo "Optante Simples Nacional" é obrigatório.',
            'regime_tributario'          => 'O campo "Regime Tributário" é obrigatório.',
            'regime_tributario_especial' => 'O campo "Regime Tributário Especial" é obrigatório.',
            'end_cep'                    => 'O campo "CEP" é obrigatório.',
            'end_tipo_logradouro'        => 'O campo "Tipo de Logradouro" é obrigatório.',
            'end_rua'                    => 'O campo "Logradouro / Rua" é obrigatório.',
            'end_numero'                 => 'O campo "Número" é obrigatório.',
            'end_bairro'                 => 'O campo "Bairro" é obrigatório.',
            'end_complemento'            => 'O campo "Complemento" é obrigatório.',
            'end_estado'                 => 'O campo "Estado" é obrigatório.',
            'end_cidade'                 => 'O campo "Cidade" é obrigatório.',
            'certificado_digital'        => 'O campo "Certificado Digital" é obrigatório.',
            'senha_certificado'          => 'O campo "Senha do Certificado" é obrigatório.',
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
            $acao  = 'N/A';

            if(isset($store['redirect']) && $store['redirect']){
                if (isset($_SERVER["QUERY_STRING"]) && $_SERVER["QUERY_STRING"]) {
                    $redirect = str_replace('redirect=', '', $_SERVER["QUERY_STRING"]);
                }
            }

            if(isset($store['r_auth'])){
                $r_auth = $store['r_auth'];
            }

            DB::beginTransaction();

            $MRANfConfiguracoes                      = RNfConfiguracoesTs::find(1); // ! Fixo - No momento Liberado apenas para 1 Empresa*
            $acao                                    = 'edit';
            if(!$MRANfConfiguracoes){
                $MRANfConfiguracoes                  = new RNfConfiguracoesTs();
                $MRANfConfiguracoes->id              = 1;
                $acao                                = 'add';
            }

            // Verificando e impedinco alteração do CNPJ para empresa cadastrada
            if ($acao == 'edit' && $MRANfConfiguracoes->cnpj && $MRANfConfiguracoes->cnpj != $store['cnpj']) {
                Session::flash('flash_error', "Desculpe, não é possível alterar o CNPJ da empresa cadastrada! Para mais informações entre em contato com o suporte.");
                return back()->withInput()->with([],401);
            }

            // Verificando se existe certificado cadastrado
            $store['certificado_id'] = $MRANfConfiguracoes->certificado_id;

            $validator  = $this->validator($store);
            if($validator->fails()){
                return back()->withInput()->with(array('errors' => $validator->errors()), 400);
            }

            // Cadastrar Certificado Digital
            if (isset($store['certificado_digital']) && $store['certificado_digital']) {

                $cadastrarCertificadoDigital_response = $this->tecnospeed->cadastrarCertificadoDigital($store['certificado_digital'], $store['senha_certificado'], $MRANfConfiguracoes->certificado_id);

                if ($cadastrarCertificadoDigital_response['status'] == '200' || $cadastrarCertificadoDigital_response['status'] == '201') {

                    $MRANfConfiguracoes->certificado_id = isset($cadastrarCertificadoDigital_response['response']['data']['id']) ?
                    $cadastrarCertificadoDigital_response['response']['data']['id'] : null;
                    $MRANfConfiguracoes->save();
        
                    $cadastrarCertificadoDigital_response['nf_log']->response_mensagem = $MRANfConfiguracoes->certificado_id ?
                        "Certificado Digital alterado com sucesso!" : "Certificado Digital cadastrado com sucesso!";
                    $cadastrarCertificadoDigital_response['nf_log']->save();

                    DB::commit();
                
                }else {
                    $cadastrarCertificadoDigital_response['nf_log']->response_mensagem = isset($cadastrarCertificadoDigital_response['response']['error']['message']) ? $cadastrarCertificadoDigital_response['response']['error']['message'] : 'Ocorreu um erro ao cadastrar o certificado digital!';

                    // Exibindo erros de campos
                    if (isset($cadastrarCertificadoDigital_response['response']['error']['data']['fields'])) {
                        $error = '';
                        foreach ($cadastrarCertificadoDigital_response['response']['error']['data']['fields'] as $key => $value) {
                            $error = $error.nl2br($key.' - '.$value."\n");
                        }
                        $cadastrarCertificadoDigital_response['nf_log']->response_mensagem = $error;
                    }

                    $cadastrarCertificadoDigital_response['nf_log']->save();
                    DB::commit();

                    Session::flash('flash_error', "Erro ao cadastrar Certificado Digital: ".$cadastrarCertificadoDigital_response['nf_log']->response_mensagem);
                    return back()->withInput()->with([],400);
                }
            }

            if (!$MRANfConfiguracoes->certificado_id) {
                $cadastrarCertificadoDigital_response['nf_log']->response_mensagem = "Certificado Digital não cadastrado!";

                $cadastrarCertificadoDigital_response['nf_log']->save();
                DB::commit();

                Session::flash('flash_error', "Desculpe, certificado digital não encontrado!");
                return back()->withInput()->with([],400);
            }

            // Cadastrar Empresa e Configurações
            $acao_empresa = !$MRANfConfiguracoes->cnpj ? 'add' : 'edit';
            $cadastrarEmpresa_response = $this->tecnospeed->cadastrarEmpresa($store, $MRANfConfiguracoes->certificado_id, $acao_empresa);

            // Sucesso
            if ($cadastrarEmpresa_response['status'] == '200' || $cadastrarEmpresa_response['status'] == '201') {
                $cadastrarEmpresa_response['nf_log']->response_mensagem = isset($cadastrarEmpresa_response['response']['message']) ?
                    $cadastrarEmpresa_response['response']['message'] : null;

            $cadastrarEmpresa_response['nf_log']->save();
            DB::commit();

            // Erro
            }else {
                $cadastrarEmpresa_response['nf_log']->response_mensagem = isset($cadastrarEmpresa_response['response']['error']['message']) ?
                    $cadastrarEmpresa_response['response']['error']['message'] : null;

                // Exibindo erros de campos
                if (isset($cadastrarEmpresa_response['response']['error']['data']['fields'])) {
                    $error = '';
                    foreach ($cadastrarEmpresa_response['response']['error']['data']['fields'] as $key => $value) {
                        $error = $error.nl2br($key.' - '.$value."\n");
                    }
                    $cadastrarEmpresa_response['nf_log']->response_mensagem = $error;
                }

                $cadastrarEmpresa_response['nf_log']->save();
                DB::commit();

                Session::flash('flash_error', "Erro ao cadastrar Empresa: ".$cadastrarEmpresa_response['nf_log']->response_mensagem);
                return back()->withInput()->with([],400);
            }

            // Cadastrar Webhook
            $cadastrarWebhook_response = $this->tecnospeed->cadastrarWebhook(
                env('APP_HASH_ID'), $store['cnpj'], $acao = $MRANfConfiguracoes->token_webhook ? 'edit' : 'add'
            );

            // Sucesso
            if ($cadastrarWebhook_response['status'] == '200') {
                $cadastrarWebhook_response['nf_log']->response_mensagem = isset($cadastrarWebhook_response['response']['message']) ?
                    $cadastrarWebhook_response['response']['message'] : null;

                $MRANfConfiguracoes->token_webhook = true;

                $cadastrarWebhook_response['nf_log']->save();
                DB::commit();

            // Erro
            }else {
                $cadastrarWebhook_response['nf_log']->response_mensagem = isset($cadastrarWebhook_response['response']['error']['message']) ?
                    $cadastrarWebhook_response['response']['error']['message'] : null;

                    // Exibindo erros de campos
                if (isset($cadastrarWebhook_response['response']['error']['data']['fields'])) {
                    $error = '';
                    foreach ($cadastrarWebhook_response['response']['error']['data']['fields'] as $key => $value) {
                        $error = $error.nl2br($key.' - '.$value."\n");
                    }
                    $cadastrarWebhook_response['nf_log']->response_mensagem = $error;
                }

                $cadastrarWebhook_response['nf_log']->save();
                DB::commit();

                Session::flash('flash_error', "Erro ao cadastrar Empresa: ".$cadastrarWebhook_response['nf_log']->response_mensagem);
                return back()->withInput()->with([],400);
            }

            $MRANfConfiguracoes->cnpj                       = (isset($store['cnpj'])?str_replace(['_'],'',$store['cnpj']):null);
            $MRANfConfiguracoes->inscricao_municipal        = (isset($store['inscricao_municipal'])?$store['inscricao_municipal']:null);
            $MRANfConfiguracoes->inscricao_estadual         = (isset($store['inscricao_estadual'])?$store['inscricao_estadual']:null);
            $MRANfConfiguracoes->razao_social               = (isset($store['razao_social'])?$store['razao_social']:null);
            $MRANfConfiguracoes->nome_fantasia              = (isset($store['nome_fantasia'])?$store['nome_fantasia']:null);
            $MRANfConfiguracoes->apelido_empresa            = (isset($store['apelido_empresa'])?$store['apelido_empresa']:null);
            $MRANfConfiguracoes->cont_email                 = (isset($store['cont_email'])?$store['cont_email']:null);
            $MRANfConfiguracoes->cont_telefone              = (isset($store['cont_telefone'])?str_replace(['_'],'',$store['cont_telefone']):null);
            $MRANfConfiguracoes->cnae_fiscal                = (isset($store['cnae_fiscal'])?$store['cnae_fiscal']:null);
            $MRANfConfiguracoes->optante_simples_nacional   = $store['optante_simples_nacional'] == 1 ? true : false;
            $MRANfConfiguracoes->regime_tributario          = $store['regime_tributario'];
            $MRANfConfiguracoes->regime_tributario_especial = $store['regime_tributario_especial'];
            $MRANfConfiguracoes->status                     = (isset($store['status'])?$store['status']:null);
            $MRANfConfiguracoes->end_cep                    = (isset($store['end_cep'])?str_replace(['_'],'',$store['end_cep']):null);
            $MRANfConfiguracoes->end_tipo_logradouro        = $store['end_tipo_logradouro'];
            $MRANfConfiguracoes->end_rua                    = (isset($store['end_rua'])?$store['end_rua']:null);
            $MRANfConfiguracoes->end_numero                 = (isset($store['end_numero'])?$store['end_numero']:null);
            $MRANfConfiguracoes->end_bairro                 = (isset($store['end_bairro'])?$store['end_bairro']:null);
            $MRANfConfiguracoes->end_complemento            = (isset($store['end_complemento'])?$store['end_complemento']:null);
            $MRANfConfiguracoes->end_estado                 = (isset($store['end_estado'])?$store['end_estado']:null);
            $MRANfConfiguracoes->end_cidade                 = (isset($store['end_cidade'])?$store['end_cidade']:null);
            $MRANfConfiguracoes->senha_certificado          = $MRANfConfiguracoes->certificado_id ? 1 : 0;
            $MRANfConfiguracoes->producao                   = $store['producao'];
            $MRANfConfiguracoes->token_api                  = (isset($store['token_api'])?$store['token_api']:null);
            $MRANfConfiguracoes->r_auth                     = $r_auth;

            $MRANfConfiguracoes->save();

            DB::commit();

            Session::flash('flash_success', "Configurações realizadas com sucesso!");

            if($user) {
                Logs::cadastrar($user->id, ($user->name . ' r_nf_configuracoes_ts|'.$acao.': atualizou ID: ' . $MRANfConfiguracoes->id));
            }

        }catch(Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            Session::flash('flash_error', "Erro ao realizar atualização r_nf_configuracoes_ts|'.$acao.': " . $e->getMessage());
            return back()->withInput()->with([],400);
        }

        if($redirect){
            return Redirect::to($redirect);
        }

        return Redirect::to('/nota_fiscal/configuracoes/ts');
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
                $MRANfConfiguracoes = RNfConfiguracoesTs::where($subForm, $value)->get();
            }else {
                $MRANfConfiguracoes = RNfConfiguracoesTs::where($subForm, $value)->where(function($q) use ($user){
                    $q->whereNull('r_auth')->orWhere('r_auth', $user->id)->orWhere('r_auth', 0);
                })->get();
            }
        }else {
            if(Permissions::permissaoModerador($user)){
                $MRANfConfiguracoes = RNfConfiguracoesTs::find($value);
            }else {
                $MRANfConfiguracoes = RNfConfiguracoesTs::where(function($q) use ($user){
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
