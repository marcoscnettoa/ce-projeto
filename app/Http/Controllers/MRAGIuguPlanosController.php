<?php

namespace App\Http\Controllers;

use App\Helper\Mask;
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

use \App\Models\MRAGIuguPlanos;
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

class MRAGIuguPlanosController extends Controller
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

            $MRAGIuguPlanos   = MRAGIuguPlanos::getAll(500,$request);

            if($user){
                Logs::cadastrar($user->id, ($user->name . ' visualizou a lista do módulo mra_g_iugu_planos'));
            }

            return view('mra_g_iugu_planos.index', [
                'exibe_filtros'     => 0,
                'MRAGIuguPlanos'  => $MRAGIuguPlanos,
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

            $MRAGIuguPlanos       = null;
            if(!is_null($id)){
                $MRAGIuguPlanos   = MRAGIuguPlanos::find($id);
                if(!$MRAGIuguPlanos){
                    \Session::flash('flash_error', 'Registro não encontrado!');
                    return Redirect::to('mra_g_iugu/mra_g_iugu_planos');
                }
            }

            if(!Permissions::permissaoModerador($user) && $MRAGIuguPlanos->r_auth != 0 && $MRAGIuguPlanos->r_auth != $user->id){
                Session::flash('flash_error', 'Você não tem permissão para executar esta ação!');
                return Redirect::to('mra_g_iugu/mra_g_iugu_planos');
            }

            if($user){
                // Edição
                if(!is_null($MRAGIuguPlanos)){
                    Logs::cadastrar($user->id, ($user->name . ' visualizou a tela de edição ID #' . $id . ' do módulo mra_g_iugu_planos'));
                    // Criação
                }else {
                    Logs::cadastrar($user->id, ($user->name . ' visualizou a tela de criação do módulo mra_g_iugu_planos'));
                }
            }

            return view('mra_g_iugu_planos.add_edit', [
                'exibe_filtros'     => 0,
                'MRAGIuguPlanos'  => $MRAGIuguPlanos
            ]);

        }catch(\Exception $e){
            Log::error($e->getMessage());
            Session::flash('flash_error', "Ocorreu um erro! Tente novamente.");
            return Redirect::to('/');
        }
    }

    protected function validator(array $data, $id = ''){

        $iugu_plan_identifier_required      = false;
        $forma_pagamento_required           = false;

        if(!isset($data['fp_todos']) && !isset($data['fp_cartao_credito']) && !isset($data['fp_boleto']) && !isset($data['fp_pix'])){
            $forma_pagamento_required       = true;
        }

        if(isset($data['iugu_plan_identifier']) and empty($data['iugu_plan_identifier'])){
            $iugu_plan_identifier_required  = true;
        }

        return Validator::make($data, [
            'iugu_plan_identifier'  => ($iugu_plan_identifier_required?'required':''),
            'nome'                  => 'required',
            'valor'                 => 'required',
            'intervalo'             => 'required',
            'intervalo_tipo'        => 'required',
            'dias_ger_faturamento'  => 'required',
            'forma_pagamento'       => ($forma_pagamento_required?'required':''),
        ],[
            'iugu_plan_identifier'  => 'O campo "Identificador Iugu" é obrigatório.',
            'nome'                  => 'O campo "Nome" é obrigatório.',
            'valor'                 => 'O campo "Valor" é obrigatório.',
            'intervalo'             => 'O campo "Intervalo" é obrigatório.',
            'intervalo_tipo'        => 'O campo "Tipo de Intervalo" é obrigatório.',
            'dias_ger_faturamento'  => 'O campo "Dias de Faturamento" é obrigatório.',
            'forma_pagamento'       => 'O campo "Forma de Pagamento" é obrigatório.'
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

            //print_r($store); exit;

            if(isset($store['redirect']) && $store['redirect']){
                if (isset($_SERVER["QUERY_STRING"]) && $_SERVER["QUERY_STRING"]) {
                    $redirect = str_replace('redirect=', '', $_SERVER["QUERY_STRING"]);
                }
            }

            if(isset($store['r_auth'])){
                $r_auth = $store['r_auth'];
            }

            $MRAGIuguPlanos                           = null;
            if(isset($store['id'])){
                $MRAGIuguPlanos                       = MRAGIuguPlanos::find($store['id']);
                if(!$MRAGIuguPlanos){
                    \Session::flash('flash_error', 'Registro não encontrado!');
                    return Redirect::to('mra_g_iugu/mra_g_iugu_planos');
                }
            }

            // :: Excluir Plano Forçado
            if(isset($store['destroy_forcado'])){ return $this->destroy($MRAGIuguPlanos); }

            $validator  = $this->validator($store);
            if($validator->fails()){
                return back()->withInput()->with(array('errors' => $validator->errors()), 400);
            }

            DB::beginTransaction();

            $acao                                       = 'edit';
            if(is_null($MRAGIuguPlanos)){
                $MRAGIuguPlanos                         = new MRAGIuguPlanos();
                $MRAGIuguPlanos->iugu_plan_identifier   = (isset($store['iugu_plan_identifier'])?$store['iugu_plan_identifier']:null);
                $acao                                   = 'add';
            }

            $MRAGIuguPlanos->nome                       = (isset($store['nome'])?$store['nome']:null);
            $MRAGIuguPlanos->valor                      = (isset($store['valor'])?\App\Helper\Helper::H_Decimal_ptBR_DB($store['valor']):null);
            $MRAGIuguPlanos->intervalo                  = (isset($store['intervalo'])?$store['intervalo']:null);
            $MRAGIuguPlanos->intervalo_tipo             = (isset($store['intervalo_tipo'])?$store['intervalo_tipo']:null);
            $MRAGIuguPlanos->dias_ger_faturamento       = (isset($store['dias_ger_faturamento'])?$store['dias_ger_faturamento']:null);
            $MRAGIuguPlanos->fp_todos                   = (isset($store['fp_todos'])?1:null);
            $MRAGIuguPlanos->fp_cartao_credito          = (isset($store['fp_cartao_credito'])?1:null);
            $MRAGIuguPlanos->fp_boleto                  = (isset($store['fp_boleto'])?1:null);
            $MRAGIuguPlanos->fp_pix                     = (isset($store['fp_pix'])?1:null);
            //$MRAGIuguPlanos->status                   = (isset($store['status'])?$store['status']:null);
            $MRAGIuguPlanos->r_auth                     = $r_auth;

            $MRAGIuguPlanos->save();

            // # Verificando e Add na IUGU
            $MRAGIugu                                   = new MRAGIugu();

            // # Formas de Pagamento
            $payable_with = [];
            if($MRAGIuguPlanos->fp_todos){
                $payable_with[] = 'all';
            }else {
                if($MRAGIuguPlanos->fp_cartao_credito){
                    $payable_with[] = 'credit_card';
                }
                if($MRAGIuguPlanos->fp_boleto){
                    $payable_with[] = 'bank_slip';
                }
                if($MRAGIuguPlanos->fp_pix){
                    $payable_with[] = 'pix';
                }
            }
            // - #

            $JSON                                       = [
                'name'              => $MRAGIuguPlanos->nome,
                'interval'          => $MRAGIuguPlanos->intervalo,
                'interval_type'     => $MRAGIuguPlanos->intervalo_tipo,
                'value_cents'       => \App\Helper\Helper::H_Decimal_DB_ValueCents($MRAGIuguPlanos->valor,2),
                'payable_with'      => $payable_with,
                'billing_days'      => $MRAGIuguPlanos->dias_ger_faturamento,
                'identifier'        => $MRAGIuguPlanos->iugu_plan_identifier,
            ];

            if($acao=='add') {
                $MRAGIugu_resp                                      = $MRAGIugu->plans_criar($JSON);
            }elseif($acao=='edit') {
                $MRAGIugu_resp                                      = $MRAGIugu->plans_alterar($JSON,$MRAGIuguPlanos->iugu_plan_id);
            }else {
                Session::flash('flash_error', 'Ocorreu um erro. Tente novamente!');
                return back()->withInput()->with([],400);
            }

            $MRAGIugu_resp['MRAGIuguLog']->mra_g_iugu_planos_id     = $MRAGIuguPlanos->id;
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
                $MRAGIuguPlanos->iugu_plan_id                       = $MRAGIugu_resp['iugu_resp']['id'];
            }

            $MRAGIuguPlanos->iugu_plan_identifier                   = $MRAGIugu_resp['iugu_resp']['identifier'];
            $MRAGIugu_resp['MRAGIuguLog']->iugu_plan_id             = $MRAGIuguPlanos->iugu_plan_id;
            $MRAGIugu_resp['MRAGIuguLog']->iugu_plan_identifier     = $MRAGIuguPlanos->iugu_plan_identifier;
            $MRAGIugu_resp['MRAGIuguLog']->save();

            $MRAGIuguPlanos->save();
            // - #

            DB::commit();

            // :: Edição
            if($acao=='edit'){
                Session::flash('flash_success', "Plano atualizado com sucesso!");
                if($user){
                    Logs::cadastrar($user->id, ($user->name . ' mra_g_iugu_planos | '.$acao.': atualizou ID: ' . $MRAGIuguPlanos->id));
                }

            // :: Criação
            }else {
                Session::flash('flash_success', "Plano cadastrado com sucesso!");
                if($user){
                    Logs::cadastrar($user->id, ($user->name . ' mra_g_iugu_planos | '.$acao.': cadastrou ID: ' . $MRAGIuguPlanos->id));
                }
            }

        }catch(Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            Session::flash('flash_error', "Erro ao realizar atualização mra_g_iugu_planos | '.$acao.': " . $e->getMessage());
            return back()->withInput()->with([],400);
        }

        if($redirect){
            return Redirect::to($redirect);
        }

        return Redirect::to('/mra_g_iugu/mra_g_iugu_planos/'.$MRAGIuguPlanos->id.'/edit');
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
                $MRAGIuguPlanos   = MRAGIuguPlanos::where($subForm, $value)->get();
            }else {
                $MRAGIuguPlanos   = MRAGIuguPlanos::where($subForm, $value)->where(function($q) use ($user){
                    $q->whereNull('r_auth')->orWhere('r_auth', $user->id)->orWhere('r_auth', 0);
                })->get();
            }
        }else {
            if(Permissions::permissaoModerador($user)){
                $MRAGIuguPlanos   = MRAGIuguPlanos::find($value);
            }else {
                $MRAGIuguPlanos   = MRAGIuguPlanos::where(function($q) use ($user){
                    $q->whereNull('r_auth')->orWhere('r_auth', $user->id)->orWhere('r_auth', 0);
                })->find($value);
            }
        }
        return Response::json($MRAGIuguPlanos);
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
            $MRAGIuguPlanos             = $id_object;
        }else {
            $MRAGIuguPlanos             = MRAGIuguPlanos::find($id_object);
        }
        $id                             = $MRAGIuguPlanos->id;

        // ! Se não existe
        if(!$MRAGIuguPlanos){
            \Session::flash('flash_error', 'Registro não encontrado!');
            return Redirect::to('mra_g_iugu/mra_g_iugu_planos');
        }

        if(!isset($store['destroy_forcado'])) {
            // # - Iugu Excluir/Remover
            $MRAGIugu                                               = new MRAGIugu();
            $MRAGIugu_resp                                          = $MRAGIugu->plans_remover([],$MRAGIuguPlanos->iugu_plan_id);

            $MRAGIugu_resp['MRAGIuguLog']->mra_g_iugu_planos_id     = $MRAGIuguPlanos->id;
            $MRAGIugu_resp['MRAGIuguLog']->iugu_plan_id             = $MRAGIuguPlanos->iugu_plan_id;
            $MRAGIugu_resp['MRAGIuguLog']->iugu_plan_identifier     = (isset($MRAGIugu_resp['iugu_resp']['identifier'])?$MRAGIugu_resp['iugu_resp']['identifier']:$MRAGIuguPlanos->iugu_plan_identifier);
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

        return $this->controllerRepository::destroy(new MRAGIuguPlanos(), $id, 'mra_g_iugu/mra_g_iugu_planos');
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
                    $MRAGIugu_resp                                          = $MRAGIugu->plans_listar($JSON);

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
                            // /!\ Permite apenas Valores Únicos vinculado ao Plano
                            if(!isset($IRI['prices']) || count($IRI['prices']) != 1){ continue; }

                            // :: Plano Sistema
                            $MRAGIuguPlanos   = MRAGIuguPlanos::where('iugu_plan_id',$IRI['id'])->first();
                            if(!$MRAGIuguPlanos){
                                $MRAGIuguPlanos = new MRAGIuguPlanos();
                                $MRAGIuguPlanos->iugu_plan_id               = $IRI['id'];
                                $qt_novos++;
                            }else {
                                $qt_atualizados++;
                            }
                            $MRAGIuguPlanos->iugu_resp                      = json_encode($IRI);
                            $MRAGIuguPlanos->iugu_plan_identifier           = (!empty($IRI['identifier'])?$IRI['identifier']:$MRAGIuguPlanos->iugu_plan_identifier);
                            $MRAGIuguPlanos->nome                           = (!empty($IRI['name'])?$IRI['name']:$MRAGIuguPlanos->nome);
                            $MRAGIuguPlanos->valor                          = \App\Helper\Helper::H_Decimal_ValueCents_DB($IRI['prices'][0]['value_cents']);
                            $MRAGIuguPlanos->intervalo                      = (!empty($IRI['interval'])?$IRI['interval']:$MRAGIuguPlanos->intervalo);
                            $MRAGIuguPlanos->intervalo_tipo                 = (!empty($IRI['interval_type'])?$IRI['interval_type']:$MRAGIuguPlanos->intervalo_tipo);
                            $MRAGIuguPlanos->dias_ger_faturamento           = (!empty($IRI['billing_days'])?$IRI['billing_days']:$MRAGIuguPlanos->dias_ger_faturamento);
                            // ! Fix *
                            $payable_with                                   = [];
                            if(is_array($IRI['payable_with'])){
                                $payable_with                               = $IRI['payable_with'];
                            }else {
                                $payable_with[]                             = $IRI['payable_with'];
                            }
                            $MRAGIuguPlanos->fp_todos                       = null;
                            $MRAGIuguPlanos->fp_cartao_credito              = null;
                            $MRAGIuguPlanos->fp_boleto                      = null;
                            $MRAGIuguPlanos->fp_pix                         = null;
                            if(in_array('all',$payable_with)){
                                $MRAGIuguPlanos->fp_todos                   = 1;
                                $MRAGIuguPlanos->fp_cartao_credito          = 1;
                                $MRAGIuguPlanos->fp_boleto                  = 1;
                                $MRAGIuguPlanos->fp_pix                     = 1;
                            }else {
                                $MRAGIuguPlanos->fp_cartao_credito          = (in_array('credit_card', $payable_with)?1:$MRAGIuguPlanos->fp_cartao_credito);
                                $MRAGIuguPlanos->fp_boleto                  = (in_array('bank_slip', $payable_with)?1:$MRAGIuguPlanos->fp_boleto);
                                $MRAGIuguPlanos->fp_pix                     = (in_array('pix', $payable_with)?1:$MRAGIuguPlanos->fp_pix);
                                if($MRAGIuguPlanos->fp_cartao_credito and $MRAGIuguPlanos->fp_boleto and $MRAGIuguPlanos->fp_pix){
                                    $MRAGIuguPlanos->fp_todos               = 1;
                                }
                            }
                            $MRAGIuguPlanos->r_auth                         = $r_auth;

                            $MRAGIuguPlanos->save();

                        }
                    }

                    $cl_start++;
                }while(isset($MRAGIugu_resp['iugu_resp']['items']) and count($MRAGIugu_resp['iugu_resp']['items']));

                $flash_success = 'Carregamento Planos Iugu realizado com sucesso!<br/>';
                $flash_success .= '<span style="font-weight:normal">- </span>'.$qt_atualizados.' <span style="font-weight:normal">atualizados.</span><br/>';
                $flash_success .= '<span style="font-weight:normal">- </span>'.$qt_novos.' <span style="font-weight:normal">novos registrados.</span><br/>';
                Session::flash('flash_success', $flash_success);

            }else {
                return Redirect::to('/');
            }

            return Redirect::to('/mra_g_iugu/mra_g_iugu_planos');

        }catch(Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            Session::flash('flash_error', "Erro ao realizar carregamento Iugu mra_g_iugu_planos | iugu_load: " . $e->getMessage());
            return back()->withInput()->with([],400);
        }
    }

    public function consultar_plano($id, Request $request){
        try{

            $user                               = Auth::user();
            $r_auth                             = NULL;
            if($user){
                $r_auth                         = $user->id;
            }
            $store                              = $request->all();

            $MRAGIuguPlanos                = MRAGIuguPlanos::find($id);
            if(!$MRAGIuguPlanos){
                \Session::flash('flash_error', 'Registro não encontrado!');
                return Redirect::to('mra_g_iugu/mra_g_iugu_planos');
            }

            $MRAGIugu                       = new MRAGIugu();
            $JSON                           = [];
            $MRAGIugu_resp                  = $MRAGIugu->plans_buscar($JSON,$MRAGIuguPlanos->iugu_plan_id);

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

            $MRAGIuguPlanos->nome                               = (!empty($MRAGIugu_resp['iugu_resp']['name'])?$MRAGIugu_resp['iugu_resp']['name']:$MRAGIuguPlanos->nome);
            $MRAGIuguPlanos->valor                              = \App\Helper\Helper::H_Decimal_ValueCents_DB($MRAGIugu_resp['iugu_resp']['prices'][0]['value_cents']);
            $MRAGIuguPlanos->intervalo                          = (!empty($MRAGIugu_resp['iugu_resp']['interval'])?$MRAGIugu_resp['iugu_resp']['interval']:$MRAGIuguPlanos->intervalo);
            $MRAGIuguPlanos->intervalo_tipo                     = (!empty($MRAGIugu_resp['iugu_resp']['interval_type'])?$MRAGIugu_resp['iugu_resp']['interval_type']:$MRAGIuguPlanos->intervalo_tipo);
            $MRAGIuguPlanos->dias_ger_faturamento               = (!empty($MRAGIugu_resp['iugu_resp']['billing_days'])?$MRAGIugu_resp['iugu_resp']['billing_days']:$MRAGIuguPlanos->dias_ger_faturamento);
            // ! Fix *
            $payable_with                                       = [];
            if(is_array($MRAGIugu_resp['iugu_resp']['payable_with'])){
                $payable_with                                   = $MRAGIugu_resp['iugu_resp']['payable_with'];
            }else {
                $payable_with[]                                 = $MRAGIugu_resp['iugu_resp']['payable_with'];
            }
            $MRAGIuguPlanos->fp_todos                           = null;
            $MRAGIuguPlanos->fp_cartao_credito                  = null;
            $MRAGIuguPlanos->fp_boleto                          = null;
            $MRAGIuguPlanos->fp_pix                             = null;
            if(in_array('all',$payable_with)){
                $MRAGIuguPlanos->fp_todos                       = 1;
                $MRAGIuguPlanos->fp_cartao_credito              = 1;
                $MRAGIuguPlanos->fp_boleto                      = 1;
                $MRAGIuguPlanos->fp_pix                         = 1;
            }else {
                $MRAGIuguPlanos->fp_cartao_credito              = (in_array('credit_card', $payable_with)?1:$MRAGIuguPlanos->fp_cartao_credito);
                $MRAGIuguPlanos->fp_boleto                      = (in_array('bank_slip', $payable_with)?1:$MRAGIuguPlanos->fp_boleto);
                $MRAGIuguPlanos->fp_pix                         = (in_array('pix', $payable_with)?1:$MRAGIuguPlanos->fp_pix);
                if($MRAGIuguPlanos->fp_cartao_credito and $MRAGIuguPlanos->fp_boleto and $MRAGIuguPlanos->fp_pix){
                    $MRAGIuguPlanos->fp_todos                   = 1;
                }
            }

            $MRAGIuguPlanos->r_auth                             = $r_auth;

            $MRAGIuguPlanos->iugu_resq                          = $MRAGIugu_resp['MRAGIuguLog']->resq;
            $MRAGIuguPlanos->iugu_resp                          = $MRAGIugu_resp['MRAGIuguLog']->resp;
            $MRAGIuguPlanos->save();

            $flash_success = 'Carregamento Plano Iugu realizado com sucesso!';
            Session::flash('flash_success', $flash_success);

        }catch(Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            Session::flash('flash_error', "Erro ao realizar consulta Iugu mra_g_iugu_planos | consultar_plano: " . $e->getMessage());
        }

        return Redirect::to('/mra_g_iugu/mra_g_iugu_planos/'.$id.'/edit');
    }

}
