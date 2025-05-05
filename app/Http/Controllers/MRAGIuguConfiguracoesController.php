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

use \App\Models\MRAGIuguConfiguracoes;
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

class MRAGIuguConfiguracoesController extends Controller
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

            $user                   = Auth::user();

            $MRAGIuguConfiguracoes  = MRAGIuguConfiguracoes::find(1);

            if($user){
                Logs::cadastrar($user->id, ($user->name . ' visualizou a tela de atualização do módulo mra_g_iugu_configuracoes'));
            }

            return view('mra_g_iugu_configuracoes.add_edit', [
                'exibe_filtros'             => 0,
                'MRAGIuguConfiguracoes'     => $MRAGIuguConfiguracoes,
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
            //'token_api'         => 'required',
            //'token_webhook'     => 'required',
        ],[
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

            $MRAGIuguConfiguracoes                      = MRAGIuguConfiguracoes::find(1); // ! Fixo - No momento Liberado apenas para 1 Empresa*
            $acao                                       = 'edit';
            if(!$MRAGIuguConfiguracoes){
                $MRAGIuguConfiguracoes                  = new MRAGIuguConfiguracoes();
                $MRAGIuguConfiguracoes->id              = 1;
                $acao                                   = 'add';
            }

            $MRAGIuguConfiguracoes->token_api           = (isset($store['token_api'])?$store['token_api']:null);
            $MRAGIuguConfiguracoes->token_webhook       = (isset($store['token_webhook'])?$store['token_webhook']:null);
            $MRAGIuguConfiguracoes->prefix_order_id     = (isset($store['prefix_order_id'])?$store['prefix_order_id']:null);
            $MRAGIuguConfiguracoes->r_auth              = $r_auth;

            $MRAGIuguConfiguracoes->save();

            DB::commit();

            Session::flash('flash_success', "Configurações realizada com sucesso!");

            if($user) {
                Logs::cadastrar($user->id, ($user->name . ' mra_g_iugu_configuracoes|'.$acao.': atualizou ID: ' . $MRAGIuguConfiguracoes->id));
            }

        }catch(Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            Session::flash('flash_error', "Erro ao realizar atualização mra_g_iugu_configuracoes|'.$acao.': " . $e->getMessage());
            return back()->withInput()->with([],400);
        }

        if($redirect){
            return Redirect::to($redirect);
        }

        return Redirect::to('/mra_g_iugu/mra_g_iugu_configuracoes');
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
        $value                          = str_replace('__H2F__', '/', $value);
        $subForm                        = $request->get('subForm');
        $user                           = Auth::user();
        if($subForm){
            if(Permissions::permissaoModerador($user)){
                $MRAGIuguConfiguracoes  = MRAGIuguConfiguracoes::where($subForm, $value)->get();
            }else {
                $MRAGIuguConfiguracoes  = MRAGIuguConfiguracoes::where($subForm, $value)->where(function($q) use ($user){
                    $q->whereNull('r_auth')->orWhere('r_auth', $user->id)->orWhere('r_auth', 0);
                })->get();
            }
        }else {
            if(Permissions::permissaoModerador($user)){
                $MRAGIuguConfiguracoes  = MRAGIuguConfiguracoes::find($value);
            }else {
                $MRAGIuguConfiguracoes  = MRAGIuguConfiguracoes::where(function($q) use ($user){
                    $q->whereNull('r_auth')->orWhere('r_auth', $user->id)->orWhere('r_auth', 0);
                })->find($value);
            }
        }
        return Response::json($MRAGIuguConfiguracoes);
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
