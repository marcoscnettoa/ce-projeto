<?php

namespace App\Http\Controllers;

use App\Helper\Mask;
use App\Models\MRAGIuguClientes;
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

use \App\Models\MRAGIuguAssinaturas;
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

class MRAGIuguAssinaturasController extends Controller
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
            $user               = Auth::user();

            $MRAGIuguAssinaturas   = MRAGIuguAssinaturas::getAll(500,$request);

            if($user){
                Logs::cadastrar($user->id, ($user->name . ' visualizou a lista do módulo mra_g_iugu_assinaturas'));
            }

            return view('mra_g_iugu_assinaturas.index', [
                'exibe_filtros'     => 0,
                'MRAGIuguAssinaturas'  => $MRAGIuguAssinaturas,
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

            $MRAGIuguAssinaturas       = null;
            if(!is_null($id)){
                $MRAGIuguAssinaturas   = MRAGIuguAssinaturas::find($id);
                if(!$MRAGIuguAssinaturas){
                    \Session::flash('flash_error', 'Registro não encontrado!');
                    return Redirect::to('mra_g_iugu/mra_g_iugu_assinaturas');
                }
            }

            if(!Permissions::permissaoModerador($user) && $MRAGIuguAssinaturas->r_auth != 0 && $MRAGIuguAssinaturas->r_auth != $user->id){
                Session::flash('flash_error', 'Você não tem permissão para executar esta ação!');
                return Redirect::to('mra_g_iugu/mra_g_iugu_assinaturas');
            }

            if($user){
                // Edição
                if(!is_null($MRAGIuguAssinaturas)){
                    Logs::cadastrar($user->id, ($user->name . ' visualizou a tela de edição ID #' . $id . ' do módulo mra_g_iugu_assinaturas'));
                    // Criação
                }else {
                    Logs::cadastrar($user->id, ($user->name . ' visualizou a tela de criação do módulo mra_g_iugu_assinaturas'));
                }
            }

            return view('mra_g_iugu_assinaturas.add_edit', [
                'exibe_filtros'     => 0,
                'MRAGIuguAssinaturas'  => $MRAGIuguAssinaturas
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
            'mra_g_iugu_clientes_id'    => (isset($data['id'])?'':'required'),
            'mra_g_iugu_planos_id'      => 'required',
        ],[
            //'iugu_expires_at'           => 'O campo "Data de Expiração" é obrigatório.',
            'mra_g_iugu_clientes_id'    => 'O campo "Cliente" é obrigatório.',
            'mra_g_iugu_planos_id'      => 'O campo "Plano" é obrigatório.',
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

            $store      = $request->all();

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

            // :: Add
            if(isset($store['mra_g_iugu_clientes_id'])){
                // :: Buscar Cliente
                $MRAGIuguClientes                                       = MRAGIuguClientes::find($store['mra_g_iugu_clientes_id']);
                if(!$MRAGIuguClientes){
                    \Session::flash('flash_error', 'Cliente não encontrado!');
                    return back()->withInput()->with([],400);
                }
            }

            // :: Buscar Plano
            $MRAGIuguPlanos                                             = MRAGIuguPlanos::find($store['mra_g_iugu_planos_id']);
            if(!$MRAGIuguPlanos){
                \Session::flash('flash_error', 'Plano não encontrado!');
                return back()->withInput()->with([],400);
            }

            $MRAGIuguAssinaturas                                        = null;
            if(isset($store['id'])){
                $MRAGIuguAssinaturas                                    = MRAGIuguAssinaturas::find($store['id']);
                if(!$MRAGIuguAssinaturas){
                    \Session::flash('flash_error', 'Registro não encontrado!');
                    return Redirect::to('mra_g_iugu/mra_g_iugu_assinaturas');
                }
            }

            // ! :: Edit | ! Cliente não pode ser Editado, caso não encontrado por exclusão, não é possível dá continuidade
            // tem que carregar novamente a lista de clietnes disponíveis IUGU, ou remover Assinatura e criar novamente
            if(!isset($store['mra_g_iugu_clientes_id'])){
                if(!$MRAGIuguAssinaturas->MRAGIuguClientes){
                    \Session::flash('flash_error', 'Cliente não encontrado!');
                    return back()->withInput()->with([],400);
                }
                $MRAGIuguClientes                                       = $MRAGIuguAssinaturas->MRAGIuguClientes;
            }

            // :: Excluir Plano Forçado
            if(isset($store['destroy_forcado'])){ return $this->destroy($MRAGIuguAssinaturas); }

            DB::beginTransaction();

            $acao                                                       = 'edit';
            if(is_null($MRAGIuguAssinaturas)){
                $MRAGIuguAssinaturas                                    = new MRAGIuguAssinaturas();
                $acao                                                   = 'add';
            }

            $MRAGIuguAssinaturas->iugu_expires_at                       = (!empty($store['iugu_expires_at'])?\App\Helper\Helper::H_Data_ptBR_DB($store['iugu_expires_at']):null);
            //$MRAGIuguAssinaturas->iugu_suspended                        = (!empty($store['iugu_suspended'])?$store['iugu_suspended']:false);
            $MRAGIuguAssinaturas->mra_g_iugu_planos_id                  = $MRAGIuguPlanos->id;
            $MRAGIuguAssinaturas->iugu_plan_identifier                  = $MRAGIuguPlanos->iugu_plan_identifier;
            $MRAGIuguAssinaturas->mra_g_iugu_clientes_id                = $MRAGIuguClientes->id;
            $MRAGIuguAssinaturas->iugu_customer_id                      = $MRAGIuguClientes->iugu_customer_id;

            //$MRAGIuguAssinaturas->iugu_subscriptions_id             = (isset($store['iugu_plan_identifier'])?$store['iugu_plan_identifier']:null);
            //$MRAGIuguAssinaturas->iugu_plan_id                      = (isset($store['iugu_plan_identifier'])?$store['iugu_plan_identifier']:null);
            //$MRAGIuguAssinaturas->iugu_plan_identifier              = (isset($store['iugu_plan_identifier'])?$store['iugu_plan_identifier']:null);
            //$MRAGIuguAssinaturas->iugu_cycled_at                    = (isset($store['iugu_plan_identifier'])?$store['iugu_plan_identifier']:null);
            //$MRAGIuguAssinaturas->iugu_suspended                    = (isset($store['iugu_plan_identifier'])?$store['iugu_plan_identifier']:null);
            //$MRAGIuguAssinaturas->iugu_active                       = (isset($store['iugu_plan_identifier'])?$store['iugu_plan_identifier']:null);
            //$MRAGIuguAssinaturas->iugu_two_step                     = (isset($store['iugu_plan_identifier'])?$store['iugu_plan_identifier']:null);
            //$MRAGIuguAssinaturas->iugu_suspend_on_invoice_expired   = (isset($store['iugu_plan_identifier'])?$store['iugu_plan_identifier']:null);
            //$MRAGIuguAssinaturas->iugu_in_trial                     = (isset($store['iugu_plan_identifier'])?$store['iugu_plan_identifier']:null);
            //$MRAGIuguAssinaturas->iugu_credits                      = (isset($store['iugu_plan_identifier'])?$store['iugu_plan_identifier']:null);
            //$MRAGIuguAssinaturas->iugu_credits_based                = (isset($store['iugu_plan_identifier'])?$store['iugu_plan_identifier']:null);
            //$MRAGIuguAssinaturas->iugu_skip_charge                  = (isset($store['iugu_plan_identifier'])?$store['iugu_plan_identifier']:null);
            //$MRAGIuguAssinaturas->iugu_ignore_due_email             = (isset($store['iugu_plan_identifier'])?$store['iugu_plan_identifier']:null);

            $MRAGIuguAssinaturas->r_auth                                = $r_auth;

            $MRAGIuguAssinaturas->save();

            // # Verificando e Add na IUGU
            $MRAGIugu                                                   = new MRAGIugu();

            $JSON   = [
                'plan_identifier'   => $MRAGIuguAssinaturas->iugu_plan_identifier,
                'expires_at'        => $MRAGIuguAssinaturas->iugu_expires_at
            ];

            if($acao=='edit' and isset($store['suspender_assinatura'])) {
                $JSON['suspended']                                      = false;
                $MRAGIugu_resp                                          = $MRAGIugu->subscriptions_suspender($JSON,$MRAGIuguAssinaturas->iugu_subscriptions_id);
            }elseif($acao=='edit' and isset($store['ativar_assinatura'])) {
                $JSON['suspended']                                      = false;
                $MRAGIugu_resp                                          = $MRAGIugu->subscriptions_ativar($JSON,$MRAGIuguAssinaturas->iugu_subscriptions_id);
            }elseif($acao=='add') {
                $JSON['customer_id']                                    = $MRAGIuguAssinaturas->iugu_customer_id;
                $MRAGIugu_resp                                          = $MRAGIugu->subscriptions_criar($JSON);
            }elseif($acao=='edit') {
                //$JSON['suspended']                                      = $MRAGIuguAssinaturas->iugu_suspended;
                $MRAGIugu_resp                                          = $MRAGIugu->subscriptions_alterar($JSON,$MRAGIuguAssinaturas->iugu_subscriptions_id);
            }else {
                Session::flash('flash_error', 'Ocorreu um erro. Tente novamente!');
                return back()->withInput()->with([],400);
            }

            //print_r($MRAGIugu_resp); exit;

            $MRAGIugu_resp['MRAGIuguLog']->mra_g_iugu_assinaturas_id     = $MRAGIuguAssinaturas->id;
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
                $MRAGIuguAssinaturas->iugu_subscriptions_id             = $MRAGIugu_resp['iugu_resp']['id'];
            }

            $MRAGIuguAssinaturas->iugu_subscriptions_id                 = $MRAGIugu_resp['iugu_resp']['id'];

            // ! Reforço Dados vindo da IUGU
            $MRAGIuguAssinaturas->iugu_plan_identifier                  = (isset($MRAGIugu_resp['iugu_resp']['plan_identifier'])?$MRAGIugu_resp['iugu_resp']['plan_identifier']:$MRAGIuguAssinaturas->iugu_plan_identifier);
            $MRAGIuguAssinaturas->iugu_suspended                        = (isset($MRAGIugu_resp['iugu_resp']['suspended'])?$MRAGIugu_resp['iugu_resp']['suspended']:$MRAGIuguAssinaturas->iugu_suspended);
            $MRAGIuguAssinaturas->iugu_cycled_at                        = (isset($MRAGIugu_resp['iugu_resp']['cycled_at'])?Carbon::parse($MRAGIugu_resp['iugu_resp']['cycled_at']):$MRAGIuguAssinaturas->iugu_cycled_at);
            $MRAGIuguAssinaturas->iugu_expires_at                       = (isset($MRAGIugu_resp['iugu_resp']['expires_at'])?Carbon::parse($MRAGIugu_resp['iugu_resp']['expires_at']):$MRAGIuguAssinaturas->iugu_expires_at);
            $MRAGIuguAssinaturas->iugu_active                           = (isset($MRAGIugu_resp['iugu_resp']['active'])?$MRAGIugu_resp['iugu_resp']['active']:$MRAGIuguAssinaturas->iugu_active);
            $MRAGIuguAssinaturas->iugu_two_step                         = (isset($MRAGIugu_resp['iugu_resp']['two_step'])?$MRAGIugu_resp['iugu_resp']['two_step']:$MRAGIuguAssinaturas->iugu_two_step);
            $MRAGIuguAssinaturas->iugu_suspend_on_invoice_expired       = (isset($MRAGIugu_resp['iugu_resp']['suspend_on_invoice_expired'])?$MRAGIugu_resp['iugu_resp']['suspend_on_invoice_expired']:$MRAGIuguAssinaturas->iugu_suspend_on_invoice_expired);
            $MRAGIuguAssinaturas->iugu_in_trial                         = (isset($MRAGIugu_resp['iugu_resp']['in_trial'])?$MRAGIugu_resp['iugu_resp']['in_trial']:$MRAGIuguAssinaturas->iugu_in_trial);
            $MRAGIuguAssinaturas->iugu_credits                          = (isset($MRAGIugu_resp['iugu_resp']['credits'])?$MRAGIugu_resp['iugu_resp']['credits']:$MRAGIuguAssinaturas->iugu_credits);
            $MRAGIuguAssinaturas->iugu_credits_based                    = (isset($MRAGIugu_resp['iugu_resp']['credits_based'])?$MRAGIugu_resp['iugu_resp']['credits_based']:$MRAGIuguAssinaturas->iugu_credits_based);

            $MRAGIugu_resp['MRAGIuguLog']->mra_g_iugu_clientes_id       = $MRAGIuguAssinaturas->mra_g_iugu_clientes_id;
            $MRAGIugu_resp['MRAGIuguLog']->mra_g_iugu_planos_id         = $MRAGIuguAssinaturas->mra_g_iugu_planos_id;
            $MRAGIugu_resp['MRAGIuguLog']->mra_g_iugu_assinaturas_id    = $MRAGIuguAssinaturas->id;
            $MRAGIugu_resp['MRAGIuguLog']->iugu_customer_id             = $MRAGIuguAssinaturas->iugu_customer_id;
            $MRAGIugu_resp['MRAGIuguLog']->iugu_subscriptions_id        = $MRAGIuguAssinaturas->iugu_subscriptions_id;
            $MRAGIugu_resp['MRAGIuguLog']->iugu_plan_id                 = $MRAGIuguAssinaturas->iugu_plan_id;
            $MRAGIugu_resp['MRAGIuguLog']->iugu_plan_identifier         = $MRAGIuguAssinaturas->iugu_plan_identifier;
            $MRAGIugu_resp['MRAGIuguLog']->save();

            $MRAGIuguAssinaturas->save();
            // - #

            DB::commit();

            // :: Edição
            if($acao=='edit'){
                Session::flash('flash_success', "Plano atualizado com sucesso!");
                if($user){
                    Logs::cadastrar($user->id, ($user->name . ' mra_g_iugu_assinaturas | '.$acao.': atualizou ID: ' . $MRAGIuguAssinaturas->id));
                }

                // :: Criação
            }else {
                Session::flash('flash_success', "Plano cadastrado com sucesso!");
                if($user){
                    Logs::cadastrar($user->id, ($user->name . ' mra_g_iugu_assinaturas | '.$acao.': cadastrou ID: ' . $MRAGIuguAssinaturas->id));
                }
            }

        }catch(Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            Session::flash('flash_error', "Erro ao realizar atualização mra_g_iugu_assinaturas | '.$acao.': " . $e->getMessage());
            return back()->withInput()->with([],400);
        }

        if($redirect){
            return Redirect::to($redirect);
        }

        return Redirect::to('/mra_g_iugu/mra_g_iugu_assinaturas/'.$MRAGIuguAssinaturas->id.'/edit');
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
                $MRAGIuguAssinaturas   = MRAGIuguAssinaturas::where($subForm, $value)->get();
            }else {
                $MRAGIuguAssinaturas   = MRAGIuguAssinaturas::where($subForm, $value)->where(function($q) use ($user){
                    $q->whereNull('r_auth')->orWhere('r_auth', $user->id)->orWhere('r_auth', 0);
                })->get();
            }
        }else {
            if(Permissions::permissaoModerador($user)){
                $MRAGIuguAssinaturas   = MRAGIuguAssinaturas::find($value);
            }else {
                $MRAGIuguAssinaturas   = MRAGIuguAssinaturas::where(function($q) use ($user){
                    $q->whereNull('r_auth')->orWhere('r_auth', $user->id)->orWhere('r_auth', 0);
                })->find($value);
            }
        }
        return Response::json($MRAGIuguAssinaturas);
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
        $request                        = Request::capture();
        $store                          = $request->all();

        if(is_object($id_object)){
            $MRAGIuguAssinaturas             = $id_object;
        }else {
            $MRAGIuguAssinaturas             = MRAGIuguAssinaturas::find($id_object);
        }
        $id                             = $MRAGIuguAssinaturas->id;

        // ! Se não existe
        if(!$MRAGIuguAssinaturas){
            \Session::flash('flash_error', 'Registro não encontrado!');
            return Redirect::to('mra_g_iugu/mra_g_iugu_assinaturas');
        }

        if(!isset($store['destroy_forcado'])) {
            // # - Iugu Excluir/Remover
            $MRAGIugu                                                   = new MRAGIugu();
            $MRAGIugu_resp                                              = $MRAGIugu->subscriptions_remover([],$MRAGIuguAssinaturas->iugu_subscriptions_id);

            $MRAGIugu_resp['MRAGIuguLog']->mra_g_iugu_clientes_id       = $MRAGIuguAssinaturas->mra_g_iugu_clientes_id;
            $MRAGIugu_resp['MRAGIuguLog']->mra_g_iugu_planos_id         = $MRAGIuguAssinaturas->mra_g_iugu_planos_id;
            $MRAGIugu_resp['MRAGIuguLog']->mra_g_iugu_assinaturas_id    = $MRAGIuguAssinaturas->id;
            $MRAGIugu_resp['MRAGIuguLog']->iugu_customer_id             = $MRAGIuguAssinaturas->iugu_customer_id;
            $MRAGIugu_resp['MRAGIuguLog']->iugu_subscriptions_id        = $MRAGIuguAssinaturas->iugu_subscriptions_id;
            $MRAGIugu_resp['MRAGIuguLog']->iugu_plan_id                 = $MRAGIuguAssinaturas->iugu_plan_id;
            $MRAGIugu_resp['MRAGIuguLog']->iugu_plan_identifier         = $MRAGIuguAssinaturas->iugu_plan_identifier;
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
        }

        return $this->controllerRepository::destroy(new MRAGIuguAssinaturas(), $id, 'mra_g_iugu/mra_g_iugu_assinaturas');
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
                $cl_limit                       = 100;
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
                    $MRAGIugu_resp              = $MRAGIugu->subscriptions_listar($JSON);

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
                            $MRAGIuguAssinaturas                                    = MRAGIuguAssinaturas::where('iugu_subscriptions_id',$IRI['id'])->first();
                            if(!$MRAGIuguAssinaturas){
                                $MRAGIuguAssinaturas = new MRAGIuguAssinaturas();
                                $MRAGIuguAssinaturas->iugu_subscriptions_id         = $IRI['id'];
                                $qt_novos++;
                            }else {
                                $qt_atualizados++;
                            }
                            $MRAGIuguAssinaturas->iugu_resp                         = json_encode($IRI);
                            // :: Plano
                            $MRAGIuguPlanos                                         = MRAGIuguPlanos::where('iugu_plan_identifier',$IRI['plan_identifier'])->first();
                            $MRAGIuguAssinaturas->mra_g_iugu_planos_id              = null;
                            $MRAGIuguAssinaturas->iugu_plan_id                      = null;
                            if($MRAGIuguPlanos){
                                $MRAGIuguAssinaturas->mra_g_iugu_planos_id          = $MRAGIuguPlanos->id;
                                $MRAGIuguAssinaturas->iugu_plan_id                  = $MRAGIuguPlanos->iugu_plan_id;
                            }
                            // :: Cliente
                            $MRAGIuguClientes                                       = MRAGIuguClientes::where('iugu_customer_id',$IRI['customer_id'])->first();
                            $MRAGIuguAssinaturas->mra_g_iugu_clientes_id            = null;
                            $MRAGIuguAssinaturas->iugu_customer_id                  = null;
                            if($MRAGIuguClientes){
                                $MRAGIuguAssinaturas->mra_g_iugu_clientes_id        = $MRAGIuguClientes->id;
                                $MRAGIuguAssinaturas->iugu_customer_id              = $MRAGIuguClientes->iugu_customer_id;
                            }

                            $MRAGIuguAssinaturas->iugu_plan_identifier              = (isset($IRI['plan_identifier'])?$IRI['plan_identifier']:$MRAGIuguAssinaturas->iugu_plan_identifier);
                            $MRAGIuguAssinaturas->iugu_suspended                    = (isset($IRI['suspended'])?$IRI['suspended']:$MRAGIuguAssinaturas->iugu_suspended);
                            $MRAGIuguAssinaturas->iugu_cycled_at                    = (isset($IRI['cycled_at'])?Carbon::parse($IRI['cycled_at']):$MRAGIuguAssinaturas->iugu_cycled_at);
                            $MRAGIuguAssinaturas->iugu_expires_at                   = (isset($IRI['expires_at'])?Carbon::parse($IRI['expires_at']):$MRAGIuguAssinaturas->iugu_expires_at);
                            $MRAGIuguAssinaturas->iugu_active                       = (isset($IRI['active'])?$IRI['active']:$MRAGIuguAssinaturas->iugu_active);
                            $MRAGIuguAssinaturas->iugu_two_step                     = (isset($IRI['two_step'])?$IRI['two_step']:$MRAGIuguAssinaturas->iugu_two_step);
                            $MRAGIuguAssinaturas->iugu_suspend_on_invoice_expired   = (isset($IRI['suspend_on_invoice_expired'])?$IRI['suspend_on_invoice_expired']:$MRAGIuguAssinaturas->iugu_suspend_on_invoice_expired);
                            $MRAGIuguAssinaturas->iugu_in_trial                     = (isset($IRI['in_trial'])?$IRI['in_trial']:$MRAGIuguAssinaturas->iugu_in_trial);
                            $MRAGIuguAssinaturas->iugu_credits                      = (isset($IRI['credits'])?$IRI['credits']:$MRAGIuguAssinaturas->iugu_credits);
                            $MRAGIuguAssinaturas->iugu_credits_based                = (isset($IRI['credits_based'])?$IRI['credits_based']:$MRAGIuguAssinaturas->iugu_credits_based);

                            $MRAGIuguAssinaturas->r_auth                         = $r_auth;

                            $MRAGIuguAssinaturas->save();

                        }
                    }

                    $cl_start++;
                }while(isset($MRAGIugu_resp['iugu_resp']['items']) and count($MRAGIugu_resp['iugu_resp']['items']));

                $flash_success = 'Carregamento Assinaturas Iugu realizado com sucesso!<br/>';
                $flash_success .= '<span style="font-weight:normal">- </span>'.$qt_atualizados.' <span style="font-weight:normal">atualizados.</span><br/>';
                $flash_success .= '<span style="font-weight:normal">- </span>'.$qt_novos.' <span style="font-weight:normal">novos registrados.</span><br/>';
                Session::flash('flash_success', $flash_success);

            }else {
                return Redirect::to('/');
            }

            return Redirect::to('/mra_g_iugu/mra_g_iugu_assinaturas');

        }catch(Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            Session::flash('flash_error', "Erro ao realizar carregamento Iugu mra_g_iugu_assinaturas | iugu_load: " . $e->getMessage());
            return back()->withInput()->with([],400);
        }
    }

    public function consultar_assinatura($id, Request $request){
        try{

            $user                               = Auth::user();
            $r_auth                             = NULL;
            if($user){
                $r_auth                         = $user->id;
            }
            $store                              = $request->all();


            $MRAGIuguAssinaturas                = MRAGIuguAssinaturas::find($id);
            if(!$MRAGIuguAssinaturas){
                \Session::flash('flash_error', 'Registro não encontrado!');
                return Redirect::to('mra_g_iugu/mra_g_iugu_assinaturas');
            }

            $MRAGIugu                       = new MRAGIugu();
            $JSON                           = [];
            $MRAGIugu_resp                  = $MRAGIugu->subscriptions_buscar($JSON,$MRAGIuguAssinaturas->iugu_subscriptions_id);

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

            // :: Plano
            $MRAGIuguPlanos                                         = MRAGIuguPlanos::where('iugu_plan_identifier',$MRAGIugu_resp['iugu_resp']['plan_identifier'])->first();
            $MRAGIuguAssinaturas->mra_g_iugu_planos_id              = null;
            $MRAGIuguAssinaturas->iugu_plan_id                      = null;
            if($MRAGIuguPlanos){
                $MRAGIuguAssinaturas->mra_g_iugu_planos_id          = $MRAGIuguPlanos->id;
                $MRAGIuguAssinaturas->iugu_plan_id                  = $MRAGIuguPlanos->iugu_plan_id;
            }
            // :: Cliente
            $MRAGIuguClientes                                       = MRAGIuguClientes::where('iugu_customer_id',$MRAGIugu_resp['iugu_resp']['customer_id'])->first();
            $MRAGIuguAssinaturas->mra_g_iugu_clientes_id            = null;
            $MRAGIuguAssinaturas->iugu_customer_id                  = null;
            if($MRAGIuguClientes){
                $MRAGIuguAssinaturas->mra_g_iugu_clientes_id        = $MRAGIuguClientes->id;
                $MRAGIuguAssinaturas->iugu_customer_id              = $MRAGIuguClientes->iugu_customer_id;
            }

            $MRAGIuguAssinaturas->iugu_plan_identifier              = (isset($MRAGIugu_resp['iugu_resp']['plan_identifier'])?$MRAGIugu_resp['iugu_resp']['plan_identifier']:$MRAGIuguAssinaturas->iugu_plan_identifier);
            $MRAGIuguAssinaturas->iugu_suspended                    = (isset($MRAGIugu_resp['iugu_resp']['suspended'])?$MRAGIugu_resp['iugu_resp']['suspended']:$MRAGIuguAssinaturas->iugu_suspended);
            $MRAGIuguAssinaturas->iugu_cycled_at                    = (isset($MRAGIugu_resp['iugu_resp']['cycled_at'])?Carbon::parse($MRAGIugu_resp['iugu_resp']['cycled_at']):$MRAGIuguAssinaturas->iugu_cycled_at);
            $MRAGIuguAssinaturas->iugu_expires_at                   = (isset($MRAGIugu_resp['iugu_resp']['expires_at'])?Carbon::parse($MRAGIugu_resp['iugu_resp']['expires_at']):$MRAGIuguAssinaturas->iugu_expires_at);
            $MRAGIuguAssinaturas->iugu_active                       = (isset($MRAGIugu_resp['iugu_resp']['active'])?$MRAGIugu_resp['iugu_resp']['active']:$MRAGIuguAssinaturas->iugu_active);
            $MRAGIuguAssinaturas->iugu_two_step                     = (isset($MRAGIugu_resp['iugu_resp']['two_step'])?$MRAGIugu_resp['iugu_resp']['two_step']:$MRAGIuguAssinaturas->iugu_two_step);
            $MRAGIuguAssinaturas->iugu_suspend_on_invoice_expired   = (isset($MRAGIugu_resp['iugu_resp']['suspend_on_invoice_expired'])?$MRAGIugu_resp['iugu_resp']['suspend_on_invoice_expired']:$MRAGIuguAssinaturas->iugu_suspend_on_invoice_expired);
            $MRAGIuguAssinaturas->iugu_in_trial                     = (isset($MRAGIugu_resp['iugu_resp']['in_trial'])?$MRAGIugu_resp['iugu_resp']['in_trial']:$MRAGIuguAssinaturas->iugu_in_trial);
            $MRAGIuguAssinaturas->iugu_credits                      = (isset($MRAGIugu_resp['iugu_resp']['credits'])?$MRAGIugu_resp['iugu_resp']['credits']:$MRAGIuguAssinaturas->iugu_credits);
            $MRAGIuguAssinaturas->iugu_credits_based                = (isset($MRAGIugu_resp['iugu_resp']['credits_based'])?$MRAGIugu_resp['iugu_resp']['credits_based']:$MRAGIuguAssinaturas->iugu_credits_based);

            $MRAGIuguAssinaturas->r_auth                            = $r_auth;

            $MRAGIuguAssinaturas->iugu_resq     = $MRAGIugu_resp['MRAGIuguLog']->resq;
            $MRAGIuguAssinaturas->iugu_resp     = $MRAGIugu_resp['MRAGIuguLog']->resp;
            $MRAGIuguAssinaturas->save();

            $flash_success = 'Carregamento Assinatura Iugu realizada com sucesso!';
            Session::flash('flash_success', $flash_success);

        }catch(Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            Session::flash('flash_error', "Erro ao realizar consulta Iugu mra_g_iugu_assinaturas | consultar_assinatura: " . $e->getMessage());
        }

        return Redirect::to('/mra_g_iugu/mra_g_iugu_assinaturas/'.$id.'/edit');
    }

}
