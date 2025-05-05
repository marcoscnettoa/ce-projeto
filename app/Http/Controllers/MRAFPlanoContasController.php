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

use \App\Models\MRAFPlanoContas;
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

class MRAFPlanoContasController extends Controller
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

            $user       = Auth::user();

            $MRAFPlanoContas  = MRAFPlanoContas::getAll(500,$request);

            if($user){
                Logs::cadastrar($user->id, ($user->name . ' visualizou a lista do módulo mra_f_plano_contas'));
            }

            return view('mra_f_plano_contas.index', [
                'exibe_filtros'       => 0,
                'MRAFPlanoContas'     => $MRAFPlanoContas,
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

            $MRAFPlanoContas     = null;
            if(!is_null($id)){
                $MRAFPlanoContas = MRAFPlanoContas::find($id);
                if(!$MRAFPlanoContas){
                    \Session::flash('flash_error', 'Registro não encontrado!');
                    return Redirect::to('mra_fluxo_financeiro/mra_f_plano_contas');
                }
            }

            if(!Permissions::permissaoModerador($user) && $MRAFPlanoContas->r_auth != 0 && $MRAFPlanoContas->r_auth != $user->id){
                Session::flash('flash_error', 'Você não tem permissão para executar esta ação!');
                return Redirect::to('mra_fluxo_financeiro/mra_f_plano_contas');
            }

            if($user){
                // Edição
                if(!is_null($MRAFPlanoContas)){
                    Logs::cadastrar($user->id, ($user->name . ' visualizou a tela de edição ID #' . $id . ' do módulo mra_f_plano_contas'));
                // Criação
                }else {
                    Logs::cadastrar($user->id, ($user->name . ' visualizou a tela de criação do módulo mra_f_plano_contas'));
                }
            }

            return view('mra_f_plano_contas.add_edit', [
                'exibe_filtros'            => 0,
                'MRAFPlanoContas'     => $MRAFPlanoContas
            ]);

        }catch(\Exception $e){
            Log::error($e->getMessage());
            Session::flash('flash_error', "Ocorreu um erro! Tente novamente.");
            return Redirect::to('/');
        }
    }

    protected function validator(array $data, $id = ''){
        return Validator::make($data, [
            'nome'     => 'required'
        ],[
            'nome'     => 'O campo "Nome" é obrigatório.'
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

            $MRAFPlanoContas                       = null;
            if(isset($store['id'])){
                $MRAFPlanoContas                   = MRAFPlanoContas::find($store['id']);
                if(!$MRAFPlanoContas){
                    \Session::flash('flash_error', 'Registro não encontrado!');
                    return Redirect::to('mra_fluxo_financeiro/mra_f_plano_contas');
                }
            }
            $acao                                       = 'edit';
            if(is_null($MRAFPlanoContas)){
                $MRAFPlanoContas                   = new MRAFPlanoContas();
                $acao                                   = 'add';
            }

            $MRAFPlanoContas->nome                 = (isset($store['nome'])?$store['nome']:null);
            $MRAFPlanoContas->status               = (isset($store['status'])?$store['status']:null);
            $MRAFPlanoContas->r_auth               = $r_auth;

            $MRAFPlanoContas->save();

            DB::commit();

            // :: Edição
            if($acao=='edit'){
                Session::flash('flash_success', "Plano de Contas atualizado com sucesso!");
                if($user){
                    Logs::cadastrar($user->id, ($user->name . ' mra_f_plano_contas|'.$acao.': atualizou ID: ' . $MRAFPlanoContas->id));
                }

            // :: Criação
            }else {
                Session::flash('flash_success', "Plano de Contas cadastrado com sucesso!");
                if($user){
                    Logs::cadastrar($user->id, ($user->name . ' mra_f_plano_contas|'.$acao.': cadastrou ID: ' . $MRAFPlanoContas->id));
                }
            }

        }catch(Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            Session::flash('flash_error', "Erro ao realizar atualização mra_f_plano_contas|'.$acao.': " . $e->getMessage());
            return back()->withInput()->with([],400);
        }

        if($redirect){
            return Redirect::to($redirect);
        }

        return Redirect::to('/mra_fluxo_financeiro/mra_f_plano_contas');
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
                $MRAFPlanoContas = MRAFPlanoContas::where($subForm, $value)->get();
            }else {
                $MRAFPlanoContas = MRAFPlanoContas::where($subForm, $value)->where(function($q) use ($user){
                    $q->whereNull('r_auth')->orWhere('r_auth', $user->id)->orWhere('r_auth', 0);
                })->get();
            }
        }else {
            if(Permissions::permissaoModerador($user)){
                $MRAFPlanoContas = MRAFPlanoContas::find($value);
            }else {
                $MRAFPlanoContas = MRAFPlanoContas::where(function($q) use ($user){
                    $q->whereNull('r_auth')->orWhere('r_auth', $user->id)->orWhere('r_auth', 0);
                })->find($value);
            }
        }
        return Response::json($MRAFPlanoContas);
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
        $MRAFPlanoContas = MRAFPlanoContas::find($id);
        return $this->controllerRepository::destroy(new MRAFPlanoContas(), $id, 'mra_fluxo_financeiro/mra_f_plano_contas');
    }
}
