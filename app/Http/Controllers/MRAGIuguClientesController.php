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

use \App\Models\MRAGIuguClientes;
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

class MRAGIuguClientesController extends Controller
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
            // #### DEBUG ####
            /*$MRAGIugu           = new MRAGIugu();
            $MRAGIugu_resp      = $MRAGIugu->customers_listar();
            print_r($MRAGIugu_resp);
            exit;*/
            // - ####

            $user               = Auth::user();

            $MRAGIuguClientes   = MRAGIuguClientes::getAll(500,$request);

            if($user){
                Logs::cadastrar($user->id, ($user->name . ' visualizou a lista do módulo mra_g_iugu_clientes'));
            }

            return view('mra_g_iugu_clientes.index', [
                'exibe_filtros'     => 0,
                'MRAGIuguClientes'  => $MRAGIuguClientes,
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

            $MRAGIuguClientes       = null;
            if(!is_null($id)){
                $MRAGIuguClientes   = MRAGIuguClientes::find($id);
                if(!$MRAGIuguClientes){
                    \Session::flash('flash_error', 'Registro não encontrado!');
                    return Redirect::to('mra_g_iugu/mra_g_iugu_clientes');
                }
            }

            if(!Permissions::permissaoModerador($user) && $MRAGIuguClientes->r_auth != 0 && $MRAGIuguClientes->r_auth != $user->id){
                Session::flash('flash_error', 'Você não tem permissão para executar esta ação!');
                return Redirect::to('mra_g_iugu/mra_g_iugu_clientes');
            }

            if($user){
                // Edição
                if(!is_null($MRAGIuguClientes)){
                    Logs::cadastrar($user->id, ($user->name . ' visualizou a tela de edição ID #' . $id . ' do módulo mra_g_iugu_clientes'));
                    // Criação
                }else {
                    Logs::cadastrar($user->id, ($user->name . ' visualizou a tela de criação do módulo mra_g_iugu_clientes'));
                }
            }

            return view('mra_g_iugu_clientes.add_edit', [
                'exibe_filtros'     => 0,
                'MRAGIuguClientes'  => $MRAGIuguClientes
            ]);

        }catch(\Exception $e){
            Log::error($e->getMessage());
            Session::flash('flash_error', "Ocorreu um erro! Tente novamente.");
            return Redirect::to('/');
        }
    }

    protected function validator(array $data, $id = ''){
        return Validator::make($data, [
            'tipo'          => 'required',
            'nome'          => 'required',
            'cont_email'    => 'required',
            'cpf'           => ($data['tipo']=='F'?'required':''),
            'cnpj'          => ($data['tipo']=='J'?'required':'')
        ],[
            'tipo'          => 'O campo "Tipo de Pessoa" é obrigatório.',
            'nome'          => 'O campo "Nome" é obrigatório.',
            'cont_email'    => 'O campo "E-mail" é obrigatório.',
            'cpf'           => 'O campo "CPF" é obrigatório.',
            'cnpj'          => 'O campo "CNPJ" é obrigatório.'
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

            $MRAGIuguClientes                           = null;
            if(isset($store['id'])){
                $MRAGIuguClientes                       = MRAGIuguClientes::find($store['id']);
                if(!$MRAGIuguClientes){
                    \Session::flash('flash_error', 'Registro não encontrado!');
                    return Redirect::to('mra_g_iugu/mra_g_iugu_clientes');
                }
            }

            // :: Excluir Cliente Forçado
            if(isset($store['destroy_forcado'])){ return $this->destroy($MRAGIuguClientes); }

            $validator  = $this->validator($store);
            if($validator->fails()){
                return back()->withInput()->with(array('errors' => $validator->errors()), 400);
            }

            DB::beginTransaction();

            $acao                                       = 'edit';
            if(is_null($MRAGIuguClientes)){
                $MRAGIuguClientes                       = new MRAGIuguClientes();
                $acao                                   = 'add';
            }

            $MRAGIuguClientes->tipo                     = (isset($store['tipo'])?$store['tipo']:null);
            $MRAGIuguClientes->cpf                      = (isset($store['cpf'])?str_replace(['_'],'',$store['cpf']):null);
            $MRAGIuguClientes->cnpj                     = (isset($store['cnpj'])?str_replace(['_'],'',$store['cnpj']):null);
            $MRAGIuguClientes->inscricao_estadual       = (isset($store['inscricao_estadual'])?$store['inscricao_estadual']:null);
            $MRAGIuguClientes->inscricao_municipal      = (isset($store['inscricao_municipal'])?$store['inscricao_municipal']:null);

            // :: Física
            if($MRAGIuguClientes->tipo == 'F'){
                $MRAGIuguClientes->cnpj                 = null;
                $MRAGIuguClientes->inscricao_estadual   = null;
                $MRAGIuguClientes->inscricao_municipal  = null;

            // :: Jurídica
            }elseif($MRAGIuguClientes->tipo == 'J'){
                $MRAGIuguClientes->cpf                  = null;

            // :: Estrangeiro
            /*}elseif($MRAGIuguClientes->tipo == 'E'){
                $MRAGIuguClientes->cpf                  = null;
                $MRAGIuguClientes->cnpj                 = null;
                $MRAGIuguClientes->inscricao_estadual   = null;*/
            }else {
                $MRAGIuguClientes->cpf                  = null;
                $MRAGIuguClientes->cnpj                 = null;
                $MRAGIuguClientes->inscricao_estadual   = null;
                $MRAGIuguClientes->inscricao_municipal  = null;
            }
            $MRAGIuguClientes->nome                     = (isset($store['nome'])?$store['nome']:null);
            $MRAGIuguClientes->cont_telefone            = (isset($store['cont_telefone'])?str_replace(['_'],'',$store['cont_telefone']):null);
            $MRAGIuguClientes->cont_email               = (isset($store['cont_email'])?$store['cont_email']:null);
            $MRAGIuguClientes->end_cep                  = (isset($store['end_cep'])?str_replace(['_'],'',$store['end_cep']):null);
            $MRAGIuguClientes->end_rua                  = (isset($store['end_rua'])?$store['end_rua']:null);
            $MRAGIuguClientes->end_numero               = (isset($store['end_numero'])?$store['end_numero']:null);
            $MRAGIuguClientes->end_bairro               = (isset($store['end_bairro'])?$store['end_bairro']:null);
            $MRAGIuguClientes->end_complemento          = (isset($store['end_complemento'])?$store['end_complemento']:null);
            $MRAGIuguClientes->end_estado               = (isset($store['end_estado'])?$store['end_estado']:null);
            $MRAGIuguClientes->end_cidade               = (isset($store['end_cidade'])?$store['end_cidade']:null);
            $MRAGIuguClientes->end_pais                 = (isset($store['end_pais'])?$store['end_pais']:null);
            $MRAGIuguClientes->observacoes              = (isset($store['observacoes'])?$store['observacoes']:null);
            //$MRAGIuguClientes->status                   = (isset($store['status'])?$store['status']:null);
            $MRAGIuguClientes->r_auth                   = $r_auth;

            $MRAGIuguClientes->save();

            // # Verificando e Add na IUGU
            $MRAGIugu                                   = new MRAGIugu();
            $JSON_pr                                    = preg_replace("/[^0-9]/", "", $MRAGIuguClientes->cont_telefone);
            $JSON_prefix                                = '0'.substr($JSON_pr, 0, 2);
            $JSON_phone                                 = substr($JSON_pr, 2);
            $JSON                                       = [
                'email'             => $MRAGIuguClientes->cont_email,    // *
                'name'              => $MRAGIuguClientes->nome,          // *
                'phone'             => (!empty($JSON_phone)?$JSON_phone:null),
                'phone_prefix'      => (!empty($JSON_prefix)?$JSON_prefix:null),
                'cpf_cnpj'          => (($MRAGIuguClientes->tipo=='F')?$MRAGIuguClientes->cpf:(($MRAGIuguClientes->tipo=='J')?$MRAGIuguClientes->cnpj:null)),
                //'cc_emails'       => 'xxxxxx',
                'zip_code'          => $MRAGIuguClientes->end_cep,
                'number'            => $MRAGIuguClientes->end_numero,
                'street'            => $MRAGIuguClientes->end_rua,
                'city'              => $MRAGIuguClientes->end_cidade,
                'state'             => $MRAGIuguClientes->end_estado,
                'district'          => $MRAGIuguClientes->end_bairro,
                'complement'        => $MRAGIuguClientes->end_complemento,
                'notes'             => $MRAGIuguClientes->observacoes,
                'custom_variables'  => [
                    ['name'   => 'inscricao_estadual',  'value'  => $MRAGIuguClientes->inscricao_estadual],
                    ['name'   => 'inscricao_municipal', 'value'  => $MRAGIuguClientes->inscricao_municipal],
                    ['name'   => 'r_auth', 'value'  => $r_auth],
                ],
            ];
            //print_r($JSON); exit;

            if($acao=='add') {
                $MRAGIugu_resp                                      = $MRAGIugu->customers_criar($JSON);
            }elseif($acao=='edit') {
                $MRAGIugu_resp                                      = $MRAGIugu->customers_alterar($JSON,$MRAGIuguClientes->iugu_customer_id);
            }else {
                Session::flash('flash_error', 'Ocorreu um erro. Tente novamente!');
                return back()->withInput()->with([],400);
            }

            $MRAGIugu_resp['MRAGIuguLog']->mra_g_iugu_clientes_id   = $MRAGIuguClientes->id;
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
                $MRAGIuguClientes->iugu_customer_id                 = $MRAGIugu_resp['iugu_resp']['id'];
            }

            $MRAGIugu_resp['MRAGIuguLog']->iugu_customer_id         = $MRAGIuguClientes->iugu_customer_id;
            $MRAGIugu_resp['MRAGIuguLog']->save();

            $MRAGIuguClientes->save();
            // - #

            DB::commit();

            // :: Edição
            if($acao=='edit'){
                Session::flash('flash_success', "Cliente atualizado com sucesso!");
                if($user){
                    Logs::cadastrar($user->id, ($user->name . ' mra_g_iugu_clientes | '.$acao.': atualizou ID: ' . $MRAGIuguClientes->id));
                }

            // :: Criação
            }else {
                Session::flash('flash_success', "Cliente cadastrado com sucesso!");
                if($user){
                    Logs::cadastrar($user->id, ($user->name . ' mra_g_iugu_clientes | '.$acao.': cadastrou ID: ' . $MRAGIuguClientes->id));
                }
            }

        }catch(Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            Session::flash('flash_error', "Erro ao realizar atualização mra_g_iugu_clientes | '.$acao.': " . $e->getMessage());
            return back()->withInput()->with([],400);
        }

        if($redirect){
            return Redirect::to($redirect);
        }

        return Redirect::to('/mra_g_iugu/mra_g_iugu_clientes/'.$MRAGIuguClientes->id.'/edit');
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
                $MRAGIuguClientes   = MRAGIuguClientes::where($subForm, $value)->get();
            }else {
                $MRAGIuguClientes   = MRAGIuguClientes::where($subForm, $value)->where(function($q) use ($user){
                    $q->whereNull('r_auth')->orWhere('r_auth', $user->id)->orWhere('r_auth', 0);
                })->get();
            }
        }else {
            if(Permissions::permissaoModerador($user)){
                $MRAGIuguClientes   = MRAGIuguClientes::find($value);
            }else {
                $MRAGIuguClientes   = MRAGIuguClientes::where(function($q) use ($user){
                    $q->whereNull('r_auth')->orWhere('r_auth', $user->id)->orWhere('r_auth', 0);
                })->find($value);
            }
        }
        return Response::json($MRAGIuguClientes);
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
            $MRAGIuguClientes           = $id_object;
        }else {
            $MRAGIuguClientes           = MRAGIuguClientes::find($id_object);
        }
        $id                             = $MRAGIuguClientes->id;

        // ! Se não existe
        if(!$MRAGIuguClientes){
            \Session::flash('flash_error', 'Registro não encontrado!');
            return Redirect::to('mra_g_iugu/mra_g_iugu_clientes');
        }

        if(!isset($store['destroy_forcado'])) {
            // # - Iugu Excluir/Remover
            $MRAGIugu                                               = new MRAGIugu();
            $MRAGIugu_resp                                          = $MRAGIugu->customers_remover([],$MRAGIuguClientes->iugu_customer_id);

            $MRAGIugu_resp['MRAGIuguLog']->mra_g_iugu_clientes_id   = $MRAGIuguClientes->id;
            $MRAGIugu_resp['MRAGIuguLog']->iugu_customer_id         = $MRAGIuguClientes->iugu_customer_id;
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

        return $this->controllerRepository::destroy(new MRAGIuguClientes(), $id, 'mra_g_iugu/mra_g_iugu_clientes');
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
                    $MRAGIugu_resp                              = $MRAGIugu->customers_listar($JSON);
                    //print_r($MRAGIugu_resp); exit;

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

                    if(count($MRAGIugu_resp['iugu_resp']['items'])){
                        foreach($MRAGIugu_resp['iugu_resp']['items'] as $IRI){

                            // :: Cliente Sistema
                            $MRAGIuguClientes   = MRAGIuguClientes::where('iugu_customer_id',$IRI['id'])->first();
                            if(!$MRAGIuguClientes){
                                $MRAGIuguClientes = new MRAGIuguClientes();
                                $MRAGIuguClientes->iugu_customer_id                 = $IRI['id'];
                                $qt_novos++;
                            }else {
                                $qt_atualizados++;
                            }
                            $MRAGIuguClientes->iugu_resp                            = json_encode($IRI);
                            if(!empty(strlen($IRI['cpf_cnpj']))){
                                if(strlen($IRI['cpf_cnpj']) > 14){
                                    $MRAGIuguClientes->tipo                         = 'J';
                                    $MRAGIuguClientes->cnpj                         = Mask::MSK_G(preg_replace("/[^0-9]/", "", $IRI['cpf_cnpj']),'cnpj');
                                }else {
                                    $MRAGIuguClientes->tipo                         = 'F';
                                    $MRAGIuguClientes->cpf                          = Mask::MSK_G(preg_replace("/[^0-9]/", "", $IRI['cpf_cnpj']),'cpf');
                                }
                            }
                            $MRAGIuguClientes->nome                                 = (!empty($IRI['name'])?$IRI['name']:$MRAGIuguClientes->nome);
                            $MRAGIuguClientes->cont_telefone                        = ((!empty($IRI['phone_prefix']) and !empty($IRI['phone']))?substr($IRI['phone_prefix'],1).$IRI['phone']:$MRAGIuguClientes->cont_telefone);
                            $MRAGIuguClientes->cont_email                           = (!empty($IRI['email'])?$IRI['email']:$MRAGIuguClientes->cont_email);
                            $MRAGIuguClientes->end_cep                              = (!empty($IRI['zip_code'])?$IRI['zip_code']:$MRAGIuguClientes->end_cep);
                            $MRAGIuguClientes->end_rua                              = (!empty($IRI['street'])?$IRI['street']:$MRAGIuguClientes->end_rua);
                            $MRAGIuguClientes->end_numero                           = (!empty($IRI['number'])?$IRI['number']:$MRAGIuguClientes->end_numero);
                            $MRAGIuguClientes->end_bairro                           = (!empty($IRI['district'])?$IRI['district']:$MRAGIuguClientes->end_bairro);
                            $MRAGIuguClientes->end_complemento                      = (!empty($IRI['complement'])?$IRI['complement']:$MRAGIuguClientes->end_complemento);
                            $MRAGIuguClientes->end_estado                           = (!empty($IRI['state'])?$IRI['state']:$MRAGIuguClientes->end_estado);
                            $MRAGIuguClientes->end_cidade                           = (!empty($IRI['city'])?$IRI['city']:$MRAGIuguClientes->end_cidade);
                            $MRAGIuguClientes->end_pais                             = 1058; // 1058 = Brasil
                            $MRAGIuguClientes->observacoes                          = (!empty($IRI['notes'])?$IRI['notes']:$MRAGIuguClientes->observacoes);
                            if(count($IRI['custom_variables'])){
                                foreach($IRI['custom_variables'] as $CV){
                                    if($CV['name']=='inscricao_estadual'){
                                        $MRAGIuguClientes->inscricao_estadual       = ((isset($CV['value']) and !empty($CV['value']))?$CV['value']:$MRAGIuguClientes->inscricao_estadual);
                                    }elseif($CV['name']=='inscricao_municipal'){
                                        $MRAGIuguClientes->inscricao_municipal      = ((isset($CV['value']) and !empty($CV['value']))?$CV['value']:$MRAGIuguClientes->inscricao_municipal);
                                    }
                                }
                            }
                            $MRAGIuguClientes->r_auth                               = $r_auth;

                            $MRAGIuguClientes->save();
                            //print_r($MRAGIuguClientes); exit;

                        }
                    }

                    $cl_start++;
                }while(isset($MRAGIugu_resp['iugu_resp']['items']) and count($MRAGIugu_resp['iugu_resp']['items']));

                $flash_success = 'Carregamento Clientes Iugu realizado com sucesso!<br/>';
                $flash_success .= '<span style="font-weight:normal">- </span>'.$qt_atualizados.' <span style="font-weight:normal">atualizados.</span><br/>';
                $flash_success .= '<span style="font-weight:normal">- </span>'.$qt_novos.' <span style="font-weight:normal">novos registrados.</span><br/>';
                Session::flash('flash_success', $flash_success);

            }else {
                return Redirect::to('/');
            }

            return Redirect::to('/mra_g_iugu/mra_g_iugu_clientes');

        }catch(Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            Session::flash('flash_error', "Erro ao realizar carregamento Iugu mra_g_iugu_clientes | iugu_load: " . $e->getMessage());
            return back()->withInput()->with([],400);
        }
    }

    public function consultar_cliente($id, Request $request){
        try{

            $user                               = Auth::user();
            $r_auth                             = NULL;
            if($user){
                $r_auth                         = $user->id;
            }
            $store                              = $request->all();


            $MRAGIuguClientes                    = MRAGIuguClientes::find($id);
            if(!$MRAGIuguClientes){
                \Session::flash('flash_error', 'Registro não encontrado!');
                return Redirect::to('mra_g_iugu/mra_g_iugu_clientes');
            }

            $MRAGIugu                       = new MRAGIugu();
            $JSON                           = [];
            $MRAGIugu_resp                  = $MRAGIugu->customers_buscar($JSON,$MRAGIuguClientes->iugu_customer_id);

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

            if(!empty(strlen($MRAGIugu_resp['iugu_resp']['cpf_cnpj']))){
                if(strlen($MRAGIugu_resp['iugu_resp']['cpf_cnpj']) > 14){
                    $MRAGIuguClientes->tipo                         = 'J';
                    $MRAGIuguClientes->cnpj                         = Mask::MSK_G(preg_replace("/[^0-9]/", "", $MRAGIugu_resp['iugu_resp']['cpf_cnpj']),'cnpj');
                }else {
                    $MRAGIuguClientes->tipo                         = 'F';
                    $MRAGIuguClientes->cpf                          = Mask::MSK_G(preg_replace("/[^0-9]/", "", $MRAGIugu_resp['iugu_resp']['cpf_cnpj']),'cpf');
                }
            }

            $MRAGIuguClientes->nome                                 = (!empty($MRAGIugu_resp['iugu_resp']['name'])?$MRAGIugu_resp['iugu_resp']['name']:$MRAGIuguClientes->nome);
            $MRAGIuguClientes->cont_telefone                        = ((!empty($MRAGIugu_resp['iugu_resp']['phone_prefix']) and !empty($MRAGIugu_resp['iugu_resp']['phone']))?substr($MRAGIugu_resp['iugu_resp']['phone_prefix'],1).$MRAGIugu_resp['iugu_resp']['phone']:$MRAGIuguClientes->cont_telefone);
            $MRAGIuguClientes->cont_email                           = (!empty($MRAGIugu_resp['iugu_resp']['email'])?$MRAGIugu_resp['iugu_resp']['email']:$MRAGIuguClientes->cont_email);
            $MRAGIuguClientes->end_cep                              = (!empty($MRAGIugu_resp['iugu_resp']['zip_code'])?$MRAGIugu_resp['iugu_resp']['zip_code']:$MRAGIuguClientes->end_cep);
            $MRAGIuguClientes->end_rua                              = (!empty($MRAGIugu_resp['iugu_resp']['street'])?$MRAGIugu_resp['iugu_resp']['street']:$MRAGIuguClientes->end_rua);
            $MRAGIuguClientes->end_numero                           = (!empty($MRAGIugu_resp['iugu_resp']['number'])?$MRAGIugu_resp['iugu_resp']['number']:$MRAGIuguClientes->end_numero);
            $MRAGIuguClientes->end_bairro                           = (!empty($MRAGIugu_resp['iugu_resp']['district'])?$MRAGIugu_resp['iugu_resp']['district']:$MRAGIuguClientes->end_bairro);
            $MRAGIuguClientes->end_complemento                      = (!empty($MRAGIugu_resp['iugu_resp']['complement'])?$MRAGIugu_resp['iugu_resp']['complement']:$MRAGIuguClientes->end_complemento);
            $MRAGIuguClientes->end_estado                           = (!empty($MRAGIugu_resp['iugu_resp']['state'])?$MRAGIugu_resp['iugu_resp']['state']:$MRAGIuguClientes->end_estado);
            $MRAGIuguClientes->end_cidade                           = (!empty($MRAGIugu_resp['iugu_resp']['city'])?$MRAGIugu_resp['iugu_resp']['city']:$MRAGIuguClientes->end_cidade);
            $MRAGIuguClientes->observacoes                          = (!empty($MRAGIugu_resp['iugu_resp']['notes'])?$MRAGIugu_resp['iugu_resp']['notes']:$MRAGIuguClientes->observacoes);
            if(count($MRAGIugu_resp['iugu_resp']['custom_variables'])){
                foreach($MRAGIugu_resp['iugu_resp']['custom_variables'] as $CV){
                    if($CV['name']=='inscricao_estadual'){
                        $MRAGIuguClientes->inscricao_estadual       = ((isset($CV['value']) and !empty($CV['value']))?$CV['value']:$MRAGIuguClientes->inscricao_estadual);
                    }elseif($CV['name']=='inscricao_municipal'){
                        $MRAGIuguClientes->inscricao_municipal      = ((isset($CV['value']) and !empty($CV['value']))?$CV['value']:$MRAGIuguClientes->inscricao_municipal);
                    }
                }
            }
            $MRAGIuguClientes->r_auth                               = $r_auth;

            $MRAGIuguClientes->iugu_resq     = $MRAGIugu_resp['MRAGIuguLog']->resq;
            $MRAGIuguClientes->iugu_resp     = $MRAGIugu_resp['MRAGIuguLog']->resp;
            $MRAGIuguClientes->save();

            $flash_success = 'Carregamento Cliente Iugu realizada com sucesso!';
            Session::flash('flash_success', $flash_success);

        }catch(Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            Session::flash('flash_error', "Erro ao realizar consulta Iugu mra_g_iugu_clientes | consultar_cliente: " . $e->getMessage());
        }

        return Redirect::to('/mra_g_iugu/mra_g_iugu_clientes/'.$id.'/edit');
    }

}
