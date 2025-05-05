<?php

namespace App\Http\Controllers;

use App\Models\MRAFContasBancarias;
use App\Models\MRAFExtratoBancario;
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

use \App\Models\MRAFTransferenciaContas;
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

class MRAFTransferenciaContasController extends Controller
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

            $MRAFTransferenciaContas    = MRAFTransferenciaContas::getAll(500,$request);

            if($user){
                Logs::cadastrar($user->id, ($user->name . ' visualizou a lista do módulo mra_f_transf_contas'));
            }

            return view('mra_f_transf_contas.index', [
                'exibe_filtros'     => 0,
                'MRAFTransferenciaContas'        => $MRAFTransferenciaContas,
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

            $MRAFTransferenciaContas     = null;
            if(!is_null($id)){
                $MRAFTransferenciaContas = MRAFTransferenciaContas::find($id);
                if(!$MRAFTransferenciaContas){
                    \Session::flash('flash_error', 'Registro não encontrado!');
                    return Redirect::to('mra_fluxo_financeiro/mra_f_transf_contas');
                }
            }

            if(!Permissions::permissaoModerador($user) && $MRAFTransferenciaContas->r_auth != 0 && $MRAFTransferenciaContas->r_auth != $user->id){
                Session::flash('flash_error', 'Você não tem permissão para executar esta ação!');
                return Redirect::to('mra_fluxo_financeiro/mra_f_transf_contas');
            }

            if($user){
                // Edição
                if(!is_null($MRAFTransferenciaContas)){
                    Logs::cadastrar($user->id, ($user->name . ' visualizou a tela de edição ID #' . $id . ' do módulo mra_f_transf_contas'));
                // Criação
                }else {
                    Logs::cadastrar($user->id, ($user->name . ' visualizou a tela de criação do módulo mra_f_transf_contas'));
                }
            }

            return view('mra_f_transf_contas.add_edit', [
                'exibe_filtros'           => 0,
                'MRAFTransferenciaContas'     => $MRAFTransferenciaContas
            ]);

        }catch(\Exception $e){
            Log::error($e->getMessage());
            Session::flash('flash_error', "Ocorreu um erro! Tente novamente.");
            return Redirect::to('/');
        }
    }

    protected function validator(array $data, $id = ''){
        return Validator::make($data, [
            'descricao'             => 'required',
            'mra_f_conta_ori_id'    => 'required',
            'mra_f_conta_des_id'    => 'required',
            'valor'                 => 'required',
        ],[
            'descricao'             => 'O campo "Descrição" é obrigatório.',
            'mra_f_conta_ori_id'    => 'O campo "Conta Origem" é obrigatório.',
            'mra_f_conta_des_id'    => 'O campo "Conta Destino" é obrigatório.',
            'valor'                 => 'O campo "Valor" é obrigatório.',
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

            //print_r($store); exit;

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

            $MRAFTransferenciaContas              = null;
            if(isset($store['id'])){
                $MRAFTransferenciaContas          = MRAFTransferenciaContas::find($store['id']);
                if(!$MRAFTransferenciaContas){
                    \Session::flash('flash_error', 'Registro não encontrado!');
                    return Redirect::to('mra_fluxo_financeiro/mra_f_transf_contas');
                }
            }
            $acao                                           = 'edit';
            if(is_null($MRAFTransferenciaContas)){
                $MRAFTransferenciaContas                    = new MRAFTransferenciaContas();
                $acao                                       = 'add';
            }

            $MRAFTransferenciaContas->descricao             = (isset($store['descricao'])?$store['descricao']:null);
            $MRAFTransferenciaContas->mra_f_conta_ori_id    = (isset($store['mra_f_conta_ori_id'])?$store['mra_f_conta_ori_id']:null);
            $MRAFTransferenciaContas->mra_f_conta_des_id    = (isset($store['mra_f_conta_des_id'])?$store['mra_f_conta_des_id']:null);
            $MRAFTransferenciaContas->valor                 = (isset($store['valor'])?\App\Helper\Helper::H_Decimal_ptBR_DB($store['valor']):null);
            $MRAFTransferenciaContas->r_auth                = $r_auth;

            $MRAFTransferenciaContas->save();

            // ! "Conta Origem"
            $MRAFContasBancarias_ori = MRAFContasBancarias::find($MRAFTransferenciaContas->mra_f_conta_ori_id);
            if(!$MRAFContasBancarias_ori){
                \Session::flash('flash_error', '"Conta Origem" não foi encontrada!');
                return back()->withInput()->with(array('errors' => $validator->errors()));
            }

            // ! Verifica na "Conta Origem" possuí saldo suficiente para Transferência
            if($MRAFTransferenciaContas->valor > $MRAFContasBancarias_ori->valor_atual){
                \Session::flash('flash_error', '"Conta Origem" não possuí saldo suficiente para realizar a transferência!');
                return back()->withInput()->with(array('errors' => $validator->errors()));
            }
            // ! Descrecenta
            $MRAFContasBancarias_ori->valor_atual -= $MRAFTransferenciaContas->valor;
            $MRAFContasBancarias_ori->save();

            // ! "Conta Destino"
            $MRAFContasBancarias_des = MRAFContasBancarias::find($MRAFTransferenciaContas->mra_f_conta_des_id);
            if(!$MRAFContasBancarias_des){
                \Session::flash('flash_error', '"Conta Destino" não foi encontrada!');
                return back()->withInput()->with(array('errors' => $validator->errors()));
            }
            // ! Acrescenta
            $MRAFContasBancarias_des->valor_atual += $MRAFTransferenciaContas->valor;
            $MRAFContasBancarias_des->save();

            // # Cria Extrato Bancário - Saída
            MRAFExtratoBancario::create([
                'descricao'                 => $MRAFTransferenciaContas->descricao,
                'mra_f_transf_contas_id'    => $MRAFTransferenciaContas->id,
                'mra_f_conta_ori_id'        => $MRAFTransferenciaContas->mra_f_conta_ori_id,
                'mra_f_conta_des_id'        => $MRAFTransferenciaContas->mra_f_conta_des_id,
                'mra_f_contas_bancarias_id' => $MRAFTransferenciaContas->mra_f_conta_ori_id,
                'valor'                     => $MRAFTransferenciaContas->valor,
                'tipo'                      => 2, // 2 = Saída
                //'status'                    => 1  // 1 = Concluído
                'created_at'                => $MRAFTransferenciaContas->created_at
            ]);

            // # Cria Extrato Bancário - Entrada
            MRAFExtratoBancario::create([
                'descricao'                 => $MRAFTransferenciaContas->descricao,
                'mra_f_transf_contas_id'    => $MRAFTransferenciaContas->id,
                'mra_f_conta_ori_id'        => $MRAFTransferenciaContas->mra_f_conta_ori_id,
                'mra_f_conta_des_id'        => $MRAFTransferenciaContas->mra_f_conta_des_id,
                'mra_f_contas_bancarias_id' => $MRAFTransferenciaContas->mra_f_conta_des_id,
                'valor'                     => $MRAFTransferenciaContas->valor,
                'tipo'                      => 1, // 1 = Entrada
                //'status'                    => 1  // 1 = Concluído
                'created_at'                => $MRAFTransferenciaContas->created_at
            ]);

            DB::commit();

            // :: Edição
            if($acao=='edit'){
                Session::flash('flash_success', "Transferência entre Conta atualizada com sucesso!");
                if($user){
                    Logs::cadastrar($user->id, ($user->name . ' mra_f_transf_contas|'.$acao.': atualizou ID: ' . $MRAFTransferenciaContas->id));
                }

            // :: Criação
            }else {
                Session::flash('flash_success', "Transferência entre Conta cadastrada com sucesso!");
                if($user){
                    Logs::cadastrar($user->id, ($user->name . ' mra_f_transf_contas|'.$acao.': cadastrou ID: ' . $MRAFTransferenciaContas->id));
                }
            }

        }catch(Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            Session::flash('flash_error', "Erro ao realizar atualização mra_f_transf_contas|'.$acao.': " . $e->getMessage());
            return back()->withInput()->with([],400);
        }

        if($redirect){
            return Redirect::to($redirect);
        }

        return Redirect::to('/mra_fluxo_financeiro/mra_f_transf_contas');
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
                $MRAFTransferenciaContas = MRAFTransferenciaContas::where($subForm, $value)->get();
            }else {
                $MRAFTransferenciaContas = MRAFTransferenciaContas::where($subForm, $value)->where(function($q) use ($user){
                    $q->whereNull('r_auth')->orWhere('r_auth', $user->id)->orWhere('r_auth', 0);
                })->get();
            }
        }else {
            if(Permissions::permissaoModerador($user)){
                $MRAFTransferenciaContas = MRAFTransferenciaContas::find($value);
            }else {
                $MRAFTransferenciaContas = MRAFTransferenciaContas::where(function($q) use ($user){
                    $q->whereNull('r_auth')->orWhere('r_auth', $user->id)->orWhere('r_auth', 0);
                })->find($value);
            }
        }
        return Response::json($MRAFTransferenciaContas);
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
        //return $this->create($id);
    }

    public function update(Request $request)
    {
        /* ... */
        //return $this->store($request);
    }

    public function destroy($id)
    {
        $MRAFTransferenciaContas = MRAFTransferenciaContas::find($id);
        return $this->controllerRepository::destroy(new MRAFTransferenciaContas(), $id, 'mra_fluxo_financeiro/mra_f_transf_contas');
    }
}
