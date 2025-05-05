<?php

namespace App\Http\Controllers;

use App\Helper\Mask;
use App\Models\MRAGIuguClientes;
use App\Models\MRAGIuguConfiguracoes;
use App\Models\MRAGIuguFaturasItens;
use App\Models\MRAGIuguPlanos;
use Carbon\Carbon;
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

use \App\Http\Controllers\MRA\MRAGIugu;

use \App\Models\MRAGIuguFaturas;
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

class MRAGIuguFaturasController extends Controller
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
            $user                   = Auth::user();

            $MRAGIuguFaturas        = MRAGIuguFaturas::getAll(500,$request);

            if($user){
                Logs::cadastrar($user->id, ($user->name . ' visualizou a lista do módulo mra_g_iugu_faturas'));
            }

            return view('mra_g_iugu_faturas.index', [
                'exibe_filtros'     => 0,
                'MRAGIuguFaturas'   => $MRAGIuguFaturas,
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

            $user                   = Auth::user();

            $MRAGIuguFaturas       = null;
            if(!is_null($id)){
                $MRAGIuguFaturas   = MRAGIuguFaturas::find($id);
                if(!$MRAGIuguFaturas){
                    \Session::flash('flash_error', 'Registro não encontrado!');
                    return Redirect::to('mra_g_iugu/mra_g_iugu_faturas');
                }
            }

            if(!Permissions::permissaoModerador($user) && $MRAGIuguFaturas->r_auth != 0 && $MRAGIuguFaturas->r_auth != $user->id){
                Session::flash('flash_error', 'Você não tem permissão para executar esta ação!');
                return Redirect::to('mra_g_iugu/mra_g_iugu_faturas');
            }

            if($user){
                // Edição
                if(!is_null($MRAGIuguFaturas)){
                    Logs::cadastrar($user->id, ($user->name . ' visualizou a tela de edição ID #' . $id . ' do módulo mra_g_iugu_faturas'));
                    // Criação
                }else {
                    Logs::cadastrar($user->id, ($user->name . ' visualizou a tela de criação do módulo mra_g_iugu_faturas'));
                }
            }

            return view('mra_g_iugu_faturas.add_edit', [
                'exibe_filtros'    => 0,
                'MRAGIuguFaturas'  => $MRAGIuguFaturas
            ]);

        }catch(\Exception $e){
            Log::error($e->getMessage());
            Session::flash('flash_error', "Ocorreu um erro! Tente novamente.");
            return Redirect::to('/');
        }
    }

    protected function validator(array $data, $id = ''){

        return Validator::make($data, [
            //'iugu_expires_at'           => 'required',
            //'mra_g_iugu_clientes_id'    => (isset($data['id'])?'':'required'),
            //'mra_g_iugu_planos_id'      => 'required',
        ],[
            //'iugu_expires_at'           => 'O campo "Data de Expiração" é obrigatório.',
            //'mra_g_iugu_clientes_id'    => 'O campo "Cliente" é obrigatório.',
            //'mra_g_iugu_planos_id'      => 'O campo "Plano" é obrigatório.',
        ]);

    }

    public function store(Request $request)
    {
        try{

            $user                   = Auth::user();
            $r_auth                 = NULL;
            $redirect               = false;

            if($user) {
                $r_auth             = $user->id;
            }

            $store                  = $request->all();
            //print_r($store); exit;
            if(isset($store['redirect']) && $store['redirect']){
                if (isset($_SERVER["QUERY_STRING"]) && $_SERVER["QUERY_STRING"]) {
                    $redirect       = str_replace('redirect=', '', $_SERVER["QUERY_STRING"]);
                }
            }

            if(isset($store['r_auth'])){
                $r_auth             = $store['r_auth'];
            }

            $validator              = $this->validator($store);
            if($validator->fails()){
                return back()->withInput()->with(array('errors' => $validator->errors()), 400);
            }

            // :: Add
            $MRAGIuguClientes   = null;
            if(isset($store['mra_g_iugu_clientes_id'])){
                // :: Buscar Cliente
                $MRAGIuguClientes                                       = MRAGIuguClientes::find($store['mra_g_iugu_clientes_id']);
                if(!$MRAGIuguClientes){
                    \Session::flash('flash_error', 'Cliente não encontrado!');
                    return back()->withInput()->with([],400);
                }
            }

            // :: Buscar Plano
            /*$MRAGIuguPlanos                                             = MRAGIuguPlanos::find($store['mra_g_iugu_planos_id']);
            if(!$MRAGIuguPlanos){
                \Session::flash('flash_error', 'Plano não encontrado!');
                return back()->withInput()->with([],400);
            }*/

            $MRAGIuguFaturas                                        = null;
            if(isset($store['id'])){
                $MRAGIuguFaturas                                    = MRAGIuguFaturas::find($store['id']);
                if(!$MRAGIuguFaturas){
                    \Session::flash('flash_error', 'Registro não encontrado!');
                    return Redirect::to('mra_g_iugu/mra_g_iugu_faturas');
                }
            }

            // ! :: Edit | ! Cliente não pode ser Editado, caso não encontrado por exclusão, não é possível dá continuidade
            // tem que carregar novamente a lista de clietnes disponíveis IUGU, ou remover Assinatura e criar novamente
            /*if(!isset($store['mra_g_iugu_clientes_id'])){
                if(!$MRAGIuguFaturas->MRAGIuguClientes){
                    \Session::flash('flash_error', 'Cliente não encontrado!');
                    return back()->withInput()->with([],400);
                }
                $MRAGIuguClientes                                       = $MRAGIuguFaturas->MRAGIuguClientes;
            }*/

            // :: Excluir Plano Forçado
            if(isset($store['destroy_forcado'])){ return $this->destroy($MRAGIuguFaturas); }

            DB::beginTransaction();

            $acao                                                   = 'edit';
            if(is_null($MRAGIuguFaturas)){
                $MRAGIuguFaturas                                    = new MRAGIuguFaturas();
                $acao                                               = 'add';

                $MRAGIuguFaturas->mra_g_iugu_clientes_id            = ($MRAGIuguClientes?$MRAGIuguClientes->id:null);
                $MRAGIuguFaturas->iugu_customer_id                  = ($MRAGIuguClientes?$MRAGIuguClientes->iugu_customer_id:null);
            }

            $MRAGIuguFaturas->iugu_due_date                         = (!empty($store['iugu_due_date'])?\App\Helper\Helper::H_Data_ptBR_DB($store['iugu_due_date']):$MRAGIuguFaturas->iugu_due_date);

            $MRAGIuguFaturas->fp_todos                              = (isset($store['fp_todos'])?1:$MRAGIuguFaturas->fp_todos);
            $MRAGIuguFaturas->fp_cartao_credito                     = (isset($store['fp_cartao_credito'])?1:$MRAGIuguFaturas->fp_cartao_credito);
            $MRAGIuguFaturas->fp_boleto                             = (isset($store['fp_boleto'])?1:$MRAGIuguFaturas->fp_boleto);
            $MRAGIuguFaturas->fp_pix                                = (isset($store['fp_pix'])?1:$MRAGIuguFaturas->fp_pix);

            if($MRAGIuguClientes){
                $store['tipo']                                      =  $MRAGIuguClientes->tipo;
                $store['cpf']                                       =  $MRAGIuguClientes->cpf;
                $store['cnpj']                                      =  $MRAGIuguClientes->cnpj;
                $store['inscricao_estadual']                        =  $MRAGIuguClientes->inscricao_estadual;
                $store['inscricao_municipal']                       =  $MRAGIuguClientes->inscricao_municipal;
                $store['nome']                                      =  $MRAGIuguClientes->nome;
                $store['cont_telefone']                             =  $MRAGIuguClientes->cont_telefone;
                $store['cont_email']                                =  $MRAGIuguClientes->cont_email;
                $store['end_cep']                                   =  $MRAGIuguClientes->end_cep;
                $store['end_rua']                                   =  $MRAGIuguClientes->end_rua;
                $store['end_numero']                                =  $MRAGIuguClientes->end_numero;
                $store['end_bairro']                                =  $MRAGIuguClientes->end_bairro;
                $store['end_complemento']                           =  $MRAGIuguClientes->end_complemento;
                $store['end_estado']                                =  $MRAGIuguClientes->end_estado;
                $store['end_cidade']                                =  $MRAGIuguClientes->end_cidade;
                $store['end_pais']                                  =  $MRAGIuguClientes->end_pais;
            }

            $MRAGIuguFaturas->tipo                                  = (isset($store['tipo'])?$store['tipo']:$MRAGIuguFaturas->tipo);
            $MRAGIuguFaturas->cpf                                   = (isset($store['cpf'])?str_replace(['_'],'',$store['cpf']):$MRAGIuguFaturas->cpf);
            $MRAGIuguFaturas->cnpj                                  = (isset($store['cnpj'])?str_replace(['_'],'',$store['cnpj']):$MRAGIuguFaturas->cnpj);
            $MRAGIuguFaturas->inscricao_estadual                    = (isset($store['inscricao_estadual'])?$store['inscricao_estadual']:$MRAGIuguFaturas->inscricao_estadual);
            $MRAGIuguFaturas->inscricao_municipal                   = (isset($store['inscricao_municipal'])?$store['inscricao_municipal']:$MRAGIuguFaturas->inscricao_municipal);

            // :: Física
            if($MRAGIuguFaturas->tipo == 'F'){
                $MRAGIuguFaturas->cnpj                              = null;
                $MRAGIuguFaturas->inscricao_estadual                = null;
                $MRAGIuguFaturas->inscricao_municipal               = null;

            // :: Jurídica
            }elseif($MRAGIuguFaturas->tipo == 'J'){
                $MRAGIuguFaturas->cpf                               = null;

            // :: Estrangeiro
            /*}elseif($MRAGIuguFaturas->tipo == 'E'){
                $MRAGIuguFaturas->cpf                               = null;
                $MRAGIuguFaturas->cnpj                              = null;
                $MRAGIuguFaturas->inscricao_estadual                = null;*/
            }else {
                $MRAGIuguFaturas->cpf                               = null;
                $MRAGIuguFaturas->cnpj                              = null;
                $MRAGIuguFaturas->inscricao_estadual                = null;
                $MRAGIuguFaturas->inscricao_municipal               = null;
            }
            $MRAGIuguFaturas->nome                                  = (isset($store['nome'])?$store['nome']:$MRAGIuguFaturas->nome);
            $MRAGIuguFaturas->cont_telefone                         = (isset($store['cont_telefone'])?str_replace(['_'],'',$store['cont_telefone']):$MRAGIuguFaturas->cont_telefone);
            $MRAGIuguFaturas->cont_email                            = (!empty($store['cont_email'])?$store['cont_email']:$MRAGIuguFaturas->cont_email);
            $MRAGIuguFaturas->end_cep                               = (isset($store['end_cep'])?str_replace(['_'],'',$store['end_cep']):$MRAGIuguFaturas->end_cep);
            $MRAGIuguFaturas->end_rua                               = (isset($store['end_rua'])?$store['end_rua']:$MRAGIuguFaturas->end_rua);
            $MRAGIuguFaturas->end_numero                            = (isset($store['end_numero'])?$store['end_numero']:$MRAGIuguFaturas->end_numero);
            $MRAGIuguFaturas->end_bairro                            = (isset($store['end_bairro'])?$store['end_bairro']:$MRAGIuguFaturas->end_bairro);
            $MRAGIuguFaturas->end_complemento                       = (isset($store['end_complemento'])?$store['end_complemento']:$MRAGIuguFaturas->end_complemento);
            $MRAGIuguFaturas->end_estado                            = (isset($store['end_estado'])?$store['end_estado']:$MRAGIuguFaturas->end_estado);
            $MRAGIuguFaturas->end_cidade                            = (isset($store['end_cidade'])?$store['end_cidade']:$MRAGIuguFaturas->end_cidade);
            $MRAGIuguFaturas->end_pais                              = (isset($store['end_pais'])?$store['end_pais']:$MRAGIuguFaturas->end_pais);


            $MRAGIuguFaturas->iugu_notification_url                     = "https://webhook.site/a729bf6e-e7a6-43c5-821a-923623e86fb6"; #URL('mra_g_iugu/mra_g_iugu_faturas/webhooks');
            $MRAGIuguFaturas->r_auth                                    = $r_auth;
            $MRAGIuguFaturas->plataforma                                = 1;

            $MRAGIuguFaturas->save();

            $MRAGIuguConfiguracoes                                      = MRAGIuguConfiguracoes::find(1);

            $MRAGIuguFaturas->iugu_order_id                             = ($MRAGIuguConfiguracoes?$MRAGIuguConfiguracoes->prefix_order_id:'').str_pad($MRAGIuguFaturas->id, 5, '0', STR_PAD_LEFT);
            $MRAGIuguFaturas->save();


            // Itens - ! Add - | ! Não existe Edição só Adição
            $MRAGIuguFaturas_items                                      = [];
            if($acao=="add" and isset($store['mra_g_iugu_faturas_i_id']) and count($store['mra_g_iugu_faturas_i_id'])){
                // :: Lista Itens
                foreach($store['mra_g_iugu_faturas_i_id'] as $K => $Prod_i){
                    $MRAGIuguFaturasItens                               = new MRAGIuguFaturasItens();
                    $MRAGIuguFaturasItens->mra_g_iugu_faturas_id        = $MRAGIuguFaturas->id;
                    $MRAGIuguFaturasItens->quantidade                   = (isset($store['item_quantidade'][$K])?$store['item_quantidade'][$K]:null);
                    $MRAGIuguFaturasItens->valor                        = (isset($store['item_valor'][$K])?\App\Helper\Helper::H_Decimal_ptBR_DB($store['item_valor'][$K]):null);
                    $MRAGIuguFaturasItens->descricao                    = (isset($store['item_descricao'][$K])?$store['item_descricao'][$K]:null);
                    $MRAGIuguFaturasItens->r_auth                       = $r_auth;
                    $MRAGIuguFaturasItens->save();
                    $MRAGIuguFaturas_items[$K]['quantity']              = $MRAGIuguFaturasItens->quantidade;
                    $MRAGIuguFaturas_items[$K]['price_cents']           = \App\Helper\Helper::H_Decimal_DB_ValueCents($MRAGIuguFaturasItens->valor,2);
                    $MRAGIuguFaturas_items[$K]['description']           = $MRAGIuguFaturasItens->descricao;
                }
            }

            //DB::commit();
            //print_r($store);
            //print_r($MRAGIuguClientes);
            //print_r($MRAGIuguFaturas);
            //print_r($MRAGIuguFaturas_items);
            //exit;

            // # Verificando e Add na IUGU
            $MRAGIugu                                   = new MRAGIugu();

            // # Formas de Pagamento
            $payable_with = [];
            if($MRAGIuguFaturas->fp_todos){
                $payable_with[] = 'all';
            }else {
                if($MRAGIuguFaturas->fp_cartao_credito){
                    $payable_with[] = 'credit_card';
                }
                if($MRAGIuguFaturas->fp_boleto){
                    $payable_with[] = 'bank_slip';
                }
                if($MRAGIuguFaturas->fp_pix){
                    $payable_with[] = 'pix';
                }
            }
            // - #

            $JSON_pr                                    = preg_replace("/[^0-9]/", "", $MRAGIuguFaturas->cont_telefone);
            $JSON_prefix                                = '0'.substr($JSON_pr, 0, 2);
            $JSON_phone                                 = substr($JSON_pr, 2);
            $JSON   = [
                'notification_url'  => $MRAGIuguFaturas->iugu_notification_url,
                'due_date'          => $MRAGIuguFaturas->iugu_due_date,
                'email'             => $MRAGIuguFaturas->cont_email,
                'items'             => $MRAGIuguFaturas_items,
                'payable_with'      => $payable_with,
                'payer'             => [
                        'email'         => $MRAGIuguFaturas->cont_email,
                        'name'          => $MRAGIuguFaturas->nome,
                        'phone'         => (!empty($JSON_phone)?$JSON_phone:null),
                        'phone_prefix'  => (!empty($JSON_prefix)?$JSON_prefix:null),
                        'cpf_cnpj'      => (($MRAGIuguFaturas->tipo=='F')?$MRAGIuguFaturas->cpf:(($MRAGIuguFaturas->tipo=='J')?$MRAGIuguFaturas->cnpj:null)),
                        'address'       => [
                            'zip_code'          => $MRAGIuguFaturas->end_cep,
                            'number'            => $MRAGIuguFaturas->end_numero,
                            'street'            => $MRAGIuguFaturas->end_rua,
                            'city'              => $MRAGIuguFaturas->end_cidade,
                            'state'             => $MRAGIuguFaturas->end_estado,
                            'district'          => $MRAGIuguFaturas->end_bairro,
                            'complement'        => $MRAGIuguFaturas->end_complemento
                        ]
                ]
            ];

            //print_r($JSON);
            //print_r($store);
            //print_r($MRAGIuguClientes);
            //print_r($MRAGIuguFaturas);
            //print_r($MRAGIuguFaturas_items);
            //exit;
            //exit;
            if($acao=='edit' and isset($store['enviar_email'])) {
                $JSON                                               = [];
                $MRAGIugu_resp                                      = $MRAGIugu->invoices_enviar_email($JSON,$MRAGIuguFaturas->iugu_invoices_id);
            }elseif($acao=='edit' and isset($store['fatura_paga_externamente'])) {
                $JSON                                               = [
                    'external_payment_id' => $MRAGIuguFaturas->iugu_invoices_id
                ];
                $MRAGIugu_resp                                      = $MRAGIugu->invoices_fatura_paga_externamente($JSON,$MRAGIuguFaturas->iugu_invoices_id);
            }elseif($acao=='add') {
                $MRAGIugu_resp                                      = $MRAGIugu->invoices_criar($JSON);
            }else {
                Session::flash('flash_error', 'Ocorreu um erro. Tente novamente!');
                return back()->withInput()->with([],400);
            }

            $MRAGIugu_resp['MRAGIuguLog']->mra_g_iugu_faturas_id    = $MRAGIuguFaturas->id;
            $MRAGIugu_resp['MRAGIuguLog']->save();

            if($MRAGIugu_resp['status'] != 200){
                if(isset($MRAGIugu_resp['iugu_resp']) and $MRAGIugu_resp['iugu_resp']['errors']){
                    $iugu_errors = 'Ocorreu um erro no serviço Iugu:<br/>';
                    if(is_array($MRAGIugu_resp['iugu_resp']['errors'])){
                        foreach($MRAGIugu_resp['iugu_resp']['errors'] as $key => $ire){
                            $iugu_errors .= '<span style="font-weight:normal;">- '.'<span style="font-weight:bold;">'.MRAGIugu::Get_giugu_api_erros_fields($key).'</span>'.' - '.MRAGIugu::Get_erros_fix_msg($ire[0]).'</span><br/>';
                        }
                    }else {
                        $iugu_errors .= '<span style="font-weight:normal;">- '.MRAGIugu::Get_erros_fix_msg($MRAGIugu_resp['iugu_resp']['errors']).'</span><br/>';
                    }
                    Session::flash('flash_error', $iugu_errors);
                }else {
                    Session::flash('flash_error', 'Ocorreu um erro. Tente novamente!');
                }
                return back()->withInput()->with([],400);
            }

            // - Iugu - Status - 200 - Ok !
            if($acao=='add') {
                $MRAGIuguFaturas->iugu_invoices_id                      = $MRAGIugu_resp['iugu_resp']['id'];
            }

            // ! Reforço Dados vindo da IUGU
            $MRAGIuguFaturas->iugu_status                               = (isset($MRAGIugu_resp['iugu_resp']['status'])?$MRAGIugu_resp['iugu_resp']['status']:$MRAGIuguFaturas->iugu_status);
            $MRAGIuguFaturas->iugu_total_cents                          = (isset($MRAGIugu_resp['iugu_resp']['total'])?\App\Helper\Helper::H_Decimal_ValueCents_DB($MRAGIugu_resp['iugu_resp']['total_cents']):$MRAGIuguFaturas->iugu_total_cents);
            $MRAGIuguFaturas->iugu_account_id                           = (isset($MRAGIugu_resp['iugu_resp']['account_id'])?$MRAGIugu_resp['iugu_resp']['account_id']:$MRAGIuguFaturas->iugu_account_id);
            $MRAGIuguFaturas->iugu_secure_id                            = (isset($MRAGIugu_resp['iugu_resp']['secure_id'])?$MRAGIugu_resp['iugu_resp']['secure_id']:$MRAGIuguFaturas->iugu_secure_id);
            $MRAGIuguFaturas->iugu_secure_url                           = (isset($MRAGIugu_resp['iugu_resp']['secure_url'])?$MRAGIugu_resp['iugu_resp']['secure_url']:$MRAGIuguFaturas->iugu_secure_url);
            $MRAGIuguFaturas->iugu_pix_qrcode                           = (isset($MRAGIugu_resp['iugu_resp']['pix']['qrcode'])?$MRAGIugu_resp['iugu_resp']['pix']['qrcode']:$MRAGIuguFaturas->iugu_pix_qrcode);
            $MRAGIuguFaturas->iugu_pix_qrcode_text                      = (isset($MRAGIugu_resp['iugu_resp']['pix']['qrcode_text'])?$MRAGIugu_resp['iugu_resp']['pix']['qrcode_text']:$MRAGIuguFaturas->iugu_pix_qrcode_text);

            $MRAGIugu_resp['MRAGIuguLog']->mra_g_iugu_clientes_id       = $MRAGIuguFaturas->mra_g_iugu_clientes_id;
            $MRAGIugu_resp['MRAGIuguLog']->iugu_customer_id             = $MRAGIuguFaturas->iugu_customer_id;
            $MRAGIugu_resp['MRAGIuguLog']->save();

            $MRAGIuguFaturas->iugu_resq                                 = $MRAGIugu_resp['MRAGIuguLog']->resq;
            $MRAGIuguFaturas->iugu_resp                                 = $MRAGIugu_resp['MRAGIuguLog']->resp;
            $MRAGIuguFaturas->save();
            // - #

            DB::commit();

            //print_r($MRAGIugu_resp); exit;

            if(isset($store['enviar_email'])){
                Session::flash('flash_success', "E-mail da Fatura enviada com sucesso!");
            }elseif(isset($store['fatura_paga_externamente'])){
                Session::flash('flash_success', "Fatura Paga Externamente com sucesso!");
            }else {
                Session::flash('flash_success', "Fatura criada com sucesso!");
            }
            if($user){
                Logs::cadastrar($user->id, ($user->name . ' mra_g_iugu_faturas | '.$acao.': cadastrou ID: ' . $MRAGIuguFaturas->id));
            }

        }catch(Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            Session::flash('flash_error', "Erro ao realizar atualização mra_g_iugu_faturas | '.$acao.': " . $e->getMessage());
            return back()->withInput()->with([],400);
        }

        if($redirect){
            return Redirect::to($redirect);
        }

        return Redirect::to('/mra_g_iugu/mra_g_iugu_faturas/'.$MRAGIuguFaturas->id.'/edit');
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
        $value                      = str_replace('__H2F__', '/', $value);
        $subForm                    = $request->get('subForm');
        $user                       = Auth::user();
        if($subForm){
            if(Permissions::permissaoModerador($user)){
                $MRAGIuguFaturas   = MRAGIuguFaturas::where($subForm, $value)->get();
            }else {
                $MRAGIuguFaturas   = MRAGIuguFaturas::where($subForm, $value)->where(function($q) use ($user){
                    $q->whereNull('r_auth')->orWhere('r_auth', $user->id)->orWhere('r_auth', 0);
                })->get();
            }
        }else {
            if(Permissions::permissaoModerador($user)){
                $MRAGIuguFaturas   = MRAGIuguFaturas::find($value);
            }else {
                $MRAGIuguFaturas   = MRAGIuguFaturas::where(function($q) use ($user){
                    $q->whereNull('r_auth')->orWhere('r_auth', $user->id)->orWhere('r_auth', 0);
                })->find($value);
            }
        }
        return Response::json($MRAGIuguFaturas);
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

    public function destroy($id_object)
    {
        $user                           = Auth::user();
        $r_auth                         = NULL;
        if ($user) {
            $r_auth = $user->id;
        }

        $request                        = Request::capture();
        $store                          = $request->all();

        if(is_object($id_object)){
            $MRAGIuguFaturas             = $id_object;
        }else {
            $MRAGIuguFaturas             = MRAGIuguFaturas::find($id_object);
        }
        $id                             = $MRAGIuguFaturas->id;

        // ! Se não existe
        if(!$MRAGIuguFaturas){
            \Session::flash('flash_error', 'Registro não encontrado!');
            return Redirect::to('mra_g_iugu/mra_g_iugu_faturas');
        }

        if(!Permissions::permissaoModerador($user) && $MRAGIuguFaturas->r_auth != $r_auth)
        {
            Session::flash('flash_error', 'Você não tem permissão para executar esta ação!');
            return back();
        }

        //print_r($MRAGIuguFaturas); exit;
        if(!isset($store['destroy_forcado'])) {
            // # - Iugu Excluir/Remover
            $MRAGIugu                                                   = new MRAGIugu();
            $MRAGIugu_resp                                              = $MRAGIugu->invoices_cancelar([],$MRAGIuguFaturas->iugu_invoices_id);
            //print_r($MRAGIugu_resp); exit;
            $MRAGIugu_resp['MRAGIuguLog']->iugu_invoices_id             = $MRAGIuguFaturas->iugu_invoices_id;
            $MRAGIugu_resp['MRAGIuguLog']->mra_g_iugu_clientes_id       = $MRAGIuguFaturas->mra_g_iugu_clientes_id;
            $MRAGIugu_resp['MRAGIuguLog']->mra_g_iugu_faturas_id        = $MRAGIuguFaturas->id;
            $MRAGIugu_resp['MRAGIuguLog']->iugu_customer_id             = $MRAGIuguFaturas->iugu_customer_id;
            $MRAGIugu_resp['MRAGIuguLog']->iugu_account_id              = $MRAGIuguFaturas->iugu_account_id;
            $MRAGIugu_resp['MRAGIuguLog']->save();

            //print_r($MRAGIugu_resp); exit;
            if($MRAGIugu_resp['status'] != 200){
                if(isset($MRAGIugu_resp['iugu_resp']) and $MRAGIugu_resp['iugu_resp']['errors']){
                    $iugu_errors  = 'Ocorreu um erro no serviço Iugu:<br/>';
                    if(isset($MRAGIugu_resp['iugu_resp']['errors']['base'])){
                        $iugu_errors .= '<span style="font-weight:normal;">- '.$MRAGIugu_resp['iugu_resp']['errors']['base'][0].'</span><br/>';
                    }else {
                        $iugu_errors .= '<span style="font-weight:normal;">- '.MRAGIugu::Get_erros_fix_msg($MRAGIugu_resp['iugu_resp']['errors']).'</span><br/>';
                    }
                    Session::flash('flash_error', $iugu_errors);
                }else {
                    Session::flash('flash_error', 'Ocorreu um erro. Tente novamente!');
                }
                return back()->withInput()->with([],400);
            }
            // - #

            $MRAGIuguFaturas->iugu_status                               = (isset($MRAGIugu_resp['iugu_resp']['status'])?$MRAGIugu_resp['iugu_resp']['status']:$MRAGIuguFaturas->iugu_status);
            $MRAGIuguFaturas->iugu_resq                                 = $MRAGIugu_resp['MRAGIuguLog']->resq;
            $MRAGIuguFaturas->iugu_resp                                 = $MRAGIugu_resp['MRAGIuguLog']->resp;
            $MRAGIuguFaturas->save();

            Session::flash('flash_success', "Fatura cancelada com sucesso!");

        }else {

            $MRAGIuguFaturas->delete();
            Session::flash('flash_success', "Fatura excluída com sucesso!");

            if($user){
                Logs::cadastrar($user->id, ($user->name . ' excluiu ID #' . $id . ' do módulo ' . get_class($MRAGIuguFaturas)));
            }
        }

        return Redirect::to('mra_g_iugu/mra_g_iugu_faturas');

    }

    public function iugu_load(Request $request){
        try{

            $user                               = Auth::user();
            $r_auth                             = NULL;
            if($user){
                $r_auth                         = $user->id;
            }
            $store                              = $request->all();

            if(isset($store['iugu_load']) and $store['iugu_load']){

                $MRAGIugu                       = new MRAGIugu();
                $cl_limit                       = 1; // 100
                $cl_start                       = 0;
                $qt_novos                       = 0;
                $qt_atualizados                 = 0;
                do {
                    $JSON                                       = [
                        'limit'             => $cl_limit,
                        'start'             => (!$cl_start?0:$cl_start*100),
                        //'created_at_from'   => '', // AAAA-MM-DDThh:mm:ss-03:00
                        //'created_at_to'     => '',  // AAAA-MM-DDThh:mm:ss-03:00
                        //'query'             => '', // pesquisa como e-mail, nome, anotações e variáveis customizadas
                        //'updated_since'     => '', // AAAA-MM-DDThh:mm:ss-03:00
                    ];
                    $MRAGIugu_resp              = $MRAGIugu->invoices_listar($JSON);

                    if($MRAGIugu_resp['status'] != 200){
                        if(isset($MRAGIugu_resp['iugu_resp']) and $MRAGIugu_resp['iugu_resp']['errors']){
                            $iugu_errors = 'Ocorreu um erro no serviço Iugu:<br/>';
                            if(is_array($MRAGIugu_resp['iugu_resp']['errors'])){
                                foreach($MRAGIugu_resp['iugu_resp']['errors'] as $key => $ire){
                                    $iugu_errors .= '<span style="font-weight:normal;">- '.'<span style="font-weight:bold;">'.MRAGIugu::Get_giugu_api_erros_fields($key).'</span>'.' - '.MRAGIugu::Get_erros_fix_msg($ire[0]).'</span><br/>';
                                }
                            }else {
                                $iugu_errors .= '<span style="font-weight:normal;">- '.MRAGIugu::Get_erros_fix_msg($MRAGIugu_resp['iugu_resp']['errors']).'</span><br/>';
                            }
                            Session::flash('flash_error', $iugu_errors);
                        }else {
                            Session::flash('flash_error', 'Ocorreu um erro. Tente novamente!');
                        }
                        return back()->withInput()->with([],400);
                    }
                    //print_r($MRAGIugu_resp); exit;

                    if(count($MRAGIugu_resp['iugu_resp']['items'])){
                        foreach($MRAGIugu_resp['iugu_resp']['items'] as $IRI){
                            //print_r($IRI); exit;

                            // :: Plano Sistema
                            $MRAGIuguFaturas                                    = MRAGIuguFaturas::where('iugu_subscriptions_id',$IRI['id'])->first();
                            if(!$MRAGIuguFaturas){
                                $MRAGIuguFaturas = new MRAGIuguFaturas();
                                $MRAGIuguFaturas->iugu_subscriptions_id         = $IRI['id'];
                                $qt_novos++;
                            }else {
                                $qt_atualizados++;
                            }
                            $MRAGIuguFaturas->iugu_resp                         = json_encode($IRI);
                            // :: Plano
                            /*$MRAGIuguPlanos                                     = MRAGIuguPlanos::where('iugu_plan_identifier',$IRI['plan_identifier'])->first();
                            $MRAGIuguFaturas->mra_g_iugu_planos_id              = null;
                            $MRAGIuguFaturas->iugu_plan_id                      = null;
                            if($MRAGIuguPlanos){
                                $MRAGIuguFaturas->mra_g_iugu_planos_id          = $MRAGIuguPlanos->id;
                                $MRAGIuguFaturas->iugu_plan_id                  = $MRAGIuguPlanos->iugu_plan_id;
                            }*/
                            // :: Cliente
                            /*$MRAGIuguClientes                                   = MRAGIuguClientes::where('iugu_customer_id',$IRI['customer_id'])->first();
                            $MRAGIuguFaturas->mra_g_iugu_clientes_id            = null;
                            $MRAGIuguFaturas->iugu_customer_id                  = null;
                            if($MRAGIuguClientes){
                                $MRAGIuguFaturas->mra_g_iugu_clientes_id        = $MRAGIuguClientes->id;
                                $MRAGIuguFaturas->iugu_customer_id              = $MRAGIuguClientes->iugu_customer_id;
                            }*/

                            /*$MRAGIuguFaturas->iugu_plan_identifier              = (isset($IRI['plan_identifier'])?$IRI['plan_identifier']:$MRAGIuguFaturas->iugu_plan_identifier);
                            $MRAGIuguFaturas->iugu_suspended                    = (isset($IRI['suspended'])?$IRI['suspended']:$MRAGIuguFaturas->iugu_suspended);
                            $MRAGIuguFaturas->iugu_cycled_at                    = (isset($IRI['cycled_at'])?Carbon::parse($IRI['cycled_at']):$MRAGIuguFaturas->iugu_cycled_at);
                            $MRAGIuguFaturas->iugu_expires_at                   = (isset($IRI['expires_at'])?Carbon::parse($IRI['expires_at']):$MRAGIuguFaturas->iugu_expires_at);
                            $MRAGIuguFaturas->iugu_active                       = (isset($IRI['active'])?$IRI['active']:$MRAGIuguFaturas->iugu_active);
                            $MRAGIuguFaturas->iugu_two_step                     = (isset($IRI['two_step'])?$IRI['two_step']:$MRAGIuguFaturas->iugu_two_step);
                            $MRAGIuguFaturas->iugu_suspend_on_invoice_expired   = (isset($IRI['suspend_on_invoice_expired'])?$IRI['suspend_on_invoice_expired']:$MRAGIuguFaturas->iugu_suspend_on_invoice_expired);
                            $MRAGIuguFaturas->iugu_in_trial                     = (isset($IRI['in_trial'])?$IRI['in_trial']:$MRAGIuguFaturas->iugu_in_trial);
                            $MRAGIuguFaturas->iugu_credits                      = (isset($IRI['credits'])?$IRI['credits']:$MRAGIuguFaturas->iugu_credits);
                            $MRAGIuguFaturas->iugu_credits_based                = (isset($IRI['credits_based'])?$IRI['credits_based']:$MRAGIuguFaturas->iugu_credits_based);*/
                            /*$MRAGIuguFaturas->iugu_plan_identifier           = (!empty($IRI['identifier'])?$IRI['identifier']:$MRAGIuguFaturas->iugu_plan_identifier);
                            $MRAGIuguFaturas->nome                           = (!empty($IRI['name'])?$IRI['name']:$MRAGIuguFaturas->nome);
                            $MRAGIuguFaturas->valor                          = \App\Helper\Helper::H_Decimal_ValueCents_DB($IRI['prices'][0]['value_cents']);
                            $MRAGIuguFaturas->intervalo                      = (!empty($IRI['interval'])?$IRI['interval']:$MRAGIuguFaturas->intervalo);
                            $MRAGIuguFaturas->intervalo_tipo                 = (!empty($IRI['interval_type'])?$IRI['interval_type']:$MRAGIuguFaturas->intervalo_tipo);
                            $MRAGIuguFaturas->dias_ger_faturamento           = (!empty($IRI['billing_days'])?$IRI['billing_days']:$MRAGIuguFaturas->dias_ger_faturamento);
                            // ! Fix *
                            $payable_with                                   = [];
                            if(is_array($IRI['payable_with'])){
                                $payable_with                               = $IRI['payable_with'];
                            }else {
                                $payable_with[]                             = $IRI['payable_with'];
                            }
                            $MRAGIuguFaturas->fp_todos                       = null;
                            $MRAGIuguFaturas->fp_cartao_credito              = null;
                            $MRAGIuguFaturas->fp_boleto                      = null;
                            $MRAGIuguFaturas->fp_pix                         = null;
                            if(in_array('all',$payable_with)){
                                $MRAGIuguFaturas->fp_todos                   = 1;
                                $MRAGIuguFaturas->fp_cartao_credito          = 1;
                                $MRAGIuguFaturas->fp_boleto                  = 1;
                                $MRAGIuguFaturas->fp_pix                     = 1;
                            }else {
                                $MRAGIuguFaturas->fp_cartao_credito          = (in_array('credit_card', $payable_with)?1:$MRAGIuguFaturas->fp_cartao_credito);
                                $MRAGIuguFaturas->fp_boleto                  = (in_array('bank_slip', $payable_with)?1:$MRAGIuguFaturas->fp_boleto);
                                $MRAGIuguFaturas->fp_pix                     = (in_array('pix', $payable_with)?1:$MRAGIuguFaturas->fp_pix);
                                if($MRAGIuguFaturas->fp_cartao_credito and $MRAGIuguFaturas->fp_boleto and $MRAGIuguFaturas->fp_pix){
                                    $MRAGIuguFaturas->fp_todos               = 1;
                                }
                            }*/
                            $MRAGIuguFaturas->r_auth                         = $r_auth;

                            $MRAGIuguFaturas->save();

                        }
                    }

                    $cl_start++;
                }while(isset($MRAGIugu_resp['iugu_resp']['items']) and count($MRAGIugu_resp['iugu_resp']['items']));

                $flash_success = 'Carregamento Faturas Iugu realizado com sucesso!<br/>';
                $flash_success .= '<span style="font-weight:normal">- </span>'.$qt_atualizados.' <span style="font-weight:normal">atualizados.</span><br/>';
                $flash_success .= '<span style="font-weight:normal">- </span>'.$qt_novos.' <span style="font-weight:normal">novos registrados.</span><br/>';
                Session::flash('flash_success', $flash_success);

            }else {
                return Redirect::to('/');
            }

            return Redirect::to('/mra_g_iugu/mra_g_iugu_faturas');

        }catch(Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            Session::flash('flash_error', "Erro ao realizar carregamento Iugu mra_g_iugu_faturas | iugu_load: " . $e->getMessage());
            return back()->withInput()->with([],400);
        }
    }

    public function consultar_fatura($id, Request $request){
        try{

            $user                               = Auth::user();
            $r_auth                             = NULL;
            if($user){
                $r_auth                         = $user->id;
            }
            $store                              = $request->all();


            $MRAGIuguFaturas                    = MRAGIuguFaturas::find($id);
            if(!$MRAGIuguFaturas){
                \Session::flash('flash_error', 'Registro não encontrado!');
                return Redirect::to('mra_g_iugu/mra_g_iugu_faturas');
            }

            $MRAGIugu                       = new MRAGIugu();
            $JSON                           = [];
            $MRAGIugu_resp                  = $MRAGIugu->invoices_buscar($JSON,$MRAGIuguFaturas->iugu_invoices_id);

            if($MRAGIugu_resp['status'] != 200){
                if(isset($MRAGIugu_resp['iugu_resp']) and $MRAGIugu_resp['iugu_resp']['errors']){
                    $iugu_errors = 'Ocorreu um erro no serviço Iugu:<br/>';
                    if(is_array($MRAGIugu_resp['iugu_resp']['errors'])){
                        foreach($MRAGIugu_resp['iugu_resp']['errors'] as $key => $ire){
                            $iugu_errors .= '<span style="font-weight:normal;">- '.'<span style="font-weight:bold;">'.MRAGIugu::Get_giugu_api_erros_fields($key).'</span>'.' - '.MRAGIugu::Get_erros_fix_msg($ire[0]).'</span><br/>';
                        }
                    }else {
                        $iugu_errors .= '<span style="font-weight:normal;">- '.MRAGIugu::Get_erros_fix_msg($MRAGIugu_resp['iugu_resp']['errors']).'</span><br/>';
                    }
                    Session::flash('flash_error', $iugu_errors);
                }else {
                    Session::flash('flash_error', 'Ocorreu um erro. Tente novamente!');
                }
                return back()->withInput()->with([],400);
            }

            $MRAGIuguFaturas->iugu_resq     = $MRAGIugu_resp['MRAGIuguLog']->resq;
            $MRAGIuguFaturas->iugu_resp     = $MRAGIugu_resp['MRAGIuguLog']->resp;
            $MRAGIuguFaturas->iugu_status   = (isset($MRAGIugu_resp['iugu_resp']['status'])?$MRAGIugu_resp['iugu_resp']['status']:$MRAGIuguFaturas->iugu_status);
            $MRAGIuguFaturas->save();

            $flash_success = 'Carregamento Fatura Iugu realizada com sucesso!';
            Session::flash('flash_success', $flash_success);

        }catch(Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            Session::flash('flash_error', "Erro ao realizar consulta Iugu mra_g_iugu_faturas | consultar_fatura: " . $e->getMessage());
        }

        return Redirect::to('/mra_g_iugu/mra_g_iugu_faturas/'.$id.'/edit');
    }

}
