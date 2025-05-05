<?php

namespace App\Http\Controllers;

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

use \App\Models\MRAFContasBancarias;
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

class MRAFContasBancariasController extends Controller
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

            $MRAFContasBancarias    = MRAFContasBancarias::getAll(500,$request);

            if($user){
                Logs::cadastrar($user->id, ($user->name . ' visualizou a lista do módulo mra_f_contas_bancarias'));
            }

            return view('mra_f_contas_bancarias.index', [
                'exibe_filtros'         => 0,
                'MRAFContasBancarias'   => $MRAFContasBancarias,
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

            $MRAFContasBancarias     = null;
            if(!is_null($id)){
                $MRAFContasBancarias = MRAFContasBancarias::find($id);
                if(!$MRAFContasBancarias){
                    \Session::flash('flash_error', 'Registro não encontrado!');
                    return Redirect::to('mra_fluxo_financeiro/mra_f_contas_bancarias');
                }
            }

            if(!Permissions::permissaoModerador($user) && $MRAFContasBancarias->r_auth != 0 && $MRAFContasBancarias->r_auth != $user->id){
                Session::flash('flash_error', 'Você não tem permissão para executar esta ação!');
                return Redirect::to('mra_fluxo_financeiro/mra_f_contas_bancarias');
            }

            if($user){
                // Edição
                if(!is_null($MRAFContasBancarias)){
                    Logs::cadastrar($user->id, ($user->name . ' visualizou a tela de edição ID #' . $id . ' do módulo mra_f_contas_bancarias'));
                // Criação
                }else {
                    Logs::cadastrar($user->id, ($user->name . ' visualizou a tela de criação do módulo mra_f_contas_bancarias'));
                }
            }

            return view('mra_f_contas_bancarias.add_edit', [
                'exibe_filtros'           => 0,
                'MRAFContasBancarias'     => $MRAFContasBancarias
            ]);

        }catch(\Exception $e){
            Log::error($e->getMessage());
            Session::flash('flash_error', "Ocorreu um erro! Tente novamente.");
            return Redirect::to('/');
        }
    }

    protected function validator(array $data, $id = ''){
        return Validator::make($data, [
            'nome'              => 'required',
            'tipo_conta'        => 'required',
            'mra_f_bancos_id'   => 'required',
            'agencia'           => 'required',
            'conta'             => 'required',
            'digito'            => 'required'
        ],[
            'nome'              => 'O campo "Nome" é obrigatório.',
            'tipo_conta'        => 'O campo "Tipo de Conta" é obrigatório.',
            'mra_f_bancos_id'   => 'O campo "Banco" é obrigatório.',
            'agencia'           => 'O campo "Agência" é obrigatório.',
            'conta'             => 'O campo "Número da Conta" é obrigatório.',
            'digito'            => 'O campo "Dígito" é obrigatório.'
        ]);
    }

    public function store(Request $request)
    {
        try{

            $user                             = Auth::user();
            $r_auth                           = NULL;
            $redirect                         = false;

            if($user) {
                $r_auth = $user->id;
            }

            $store                            = $request->all();

            if(isset($store['redirect']) && $store['redirect']){
                if (isset($_SERVER["QUERY_STRING"]) && $_SERVER["QUERY_STRING"]) {
                    $redirect                 = str_replace('redirect=', '', $_SERVER["QUERY_STRING"]);
                }
            }

            if(isset($store['r_auth'])){
                $r_auth                       = $store['r_auth'];
            }

            $validator                        = $this->validator($store);
            if($validator->fails()){
                return back()->withInput()->with(array('errors' => $validator->errors()), 400);
            }

            DB::beginTransaction();

            $MRAFContasBancarias              = null;
            if(isset($store['id'])){
                $MRAFContasBancarias          = MRAFContasBancarias::find($store['id']);
                if(!$MRAFContasBancarias){
                    \Session::flash('flash_error', 'Registro não encontrado!');
                    return Redirect::to('mra_fluxo_financeiro/mra_f_contas_bancarias');
                }
            }
            $acao                                      = 'edit';
            if(is_null($MRAFContasBancarias)){
                $MRAFContasBancarias                   = new MRAFContasBancarias();
                $acao                                  = 'add';
            }

            $MRAFContasBancarias->nome                 = (!empty($store['nome'])?$store['nome']:null);
            $MRAFContasBancarias->tipo_conta           = (!empty($store['tipo_conta'])?$store['tipo_conta']:null);
            $MRAFContasBancarias->mra_f_bancos_id      = (!empty($store['mra_f_bancos_id'])?$store['mra_f_bancos_id']:null);
            $MRAFContasBancarias->agencia              = (!empty($store['agencia'])?$store['agencia']:null);
            $MRAFContasBancarias->conta                = (!empty($store['conta'])?$store['conta']:null);
            $MRAFContasBancarias->digito               = (!empty($store['digito'])?$store['digito']:null);
            $MRAFContasBancarias->data_inicial         = (!empty($store['data_inicial'])?\App\Helper\Helper::H_Data_ptBR_DB($store['data_inicial']):null);
            $MRAFContasBancarias->valor_inicial        = (!empty($store['valor_inicial'])?\App\Helper\Helper::H_Decimal_ptBR_DB($store['valor_inicial']):null);
            $MRAFContasBancarias->valor_atual          = (!empty($store['valor_atual'])?\App\Helper\Helper::H_Decimal_ptBR_DB($store['valor_atual']):null);
            if($acao == 'add' and empty($store['valor_atual'])){
                $MRAFContasBancarias->valor_atual      = $MRAFContasBancarias->valor_inicial;
            }
            $MRAFContasBancarias->status               = (!empty($store['status'])?$store['status']:null);
            $MRAFContasBancarias->r_auth               = $r_auth;

            $MRAFContasBancarias->save();

            DB::commit();

            // :: Edição
            if($acao=='edit'){
                Session::flash('flash_success', "Conta Bancária atualizado com sucesso!");
                if($user){
                    Logs::cadastrar($user->id, ($user->name . ' mra_f_contas_bancarias|'.$acao.': atualizou ID: ' . $MRAFContasBancarias->id));
                }

            // :: Criação
            }else {
                Session::flash('flash_success', "Conta Bancária cadastrado com sucesso!");
                if($user){
                    Logs::cadastrar($user->id, ($user->name . ' mra_f_contas_bancarias|'.$acao.': cadastrou ID: ' . $MRAFContasBancarias->id));
                }
            }

        }catch(Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            Session::flash('flash_error', "Erro ao realizar atualização mra_f_contas_bancarias|'.$acao.': " . $e->getMessage());
            return back()->withInput()->with([],400);
        }

        if($redirect){
            return Redirect::to($redirect);
        }

        return Redirect::to('/mra_fluxo_financeiro/mra_f_contas_bancarias');
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
        $value                       = str_replace('__H2F__', '/', $value);
        $subForm                     = $request->get('subForm');
        $user                        = Auth::user();
        if($subForm){
            if(Permissions::permissaoModerador($user)){
                $MRAFContasBancarias = MRAFContasBancarias::where($subForm, $value)->get();
            }else {
                $MRAFContasBancarias = MRAFContasBancarias::where($subForm, $value)->where(function($q) use ($user){
                    $q->whereNull('r_auth')->orWhere('r_auth', $user->id)->orWhere('r_auth', 0);
                })->get();
            }
        }else {
            if(Permissions::permissaoModerador($user)){
                $MRAFContasBancarias = MRAFContasBancarias::find($value);
            }else {
                $MRAFContasBancarias = MRAFContasBancarias::where(function($q) use ($user){
                    $q->whereNull('r_auth')->orWhere('r_auth', $user->id)->orWhere('r_auth', 0);
                })->find($value);
            }
        }
        return Response::json($MRAFContasBancarias);
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
        $MRAFContasBancarias = MRAFContasBancarias::find($id);
        return $this->controllerRepository::destroy(new MRAFContasBancarias(), $id, 'mra_fluxo_financeiro/mra_f_contas_bancarias');
    }
}
