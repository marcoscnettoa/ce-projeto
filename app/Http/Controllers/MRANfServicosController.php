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

use \App\Models\MRANfServicos;
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

class MRANfServicosController extends Controller
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

            $user           = Auth::user();

            $MRANfServicos  = MRANfServicos::getAll(500);

            if($user){
                Logs::cadastrar($user->id, ($user->name . ' visualizou a lista do módulo mra_servicos'));
            }

            return view('mra_servicos.index', [
                'exibe_filtros'     => 0,
                'MRANfServicos'     => $MRANfServicos,
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

    public function create($id = null)
    {
        try {

            $user = Auth::user();

            $MRANfServicos     = null;
            if(!is_null($id)){
                $MRANfServicos = MRANfServicos::find($id);
                if(!$MRANfServicos){
                    \Session::flash('flash_error', 'Registro não encontrado!');
                    return Redirect::to('mra_nota_fiscal/mra_servicos');
                }
            }

            if(!Permissions::permissaoModerador($user) && $MRANfServicos->r_auth != 0 && $MRANfServicos->r_auth != $user->id){
                Session::flash('flash_error', 'Você não tem permissão para executar esta ação!');
                return Redirect::to('mra_nota_fiscal/mra_servicos');
            }

            if($user){
                // Edição
                if(!is_null($MRANfServicos)){
                    Logs::cadastrar($user->id, ($user->name . ' visualizou a tela de edição ID #' . $id . ' do módulo mra_servicos'));
                // Criação
                }else {
                    Logs::cadastrar($user->id, ($user->name . ' visualizou a tela de criação do módulo mra_servicos'));
                }
            }

            return view('mra_servicos.add_edit', [
                'exibe_filtros'     => 0,
                'MRANfServicos'     => $MRANfServicos
            ]);

        }catch(\Exception $e){
            Log::error($e->getMessage());
            Session::flash('flash_error', "Ocorreu um erro! Tente novamente.");
            return Redirect::to('/');
        }
    }

    protected function validator(array $data, $id = ''){
        return Validator::make($data, [
            'nome'      => 'required',
            'valor'     => 'required'
        ],[
            'nome'      => 'O campo "Nome" é obrigatório.',
            'valor'     => 'O campo "Valor" é obrigatório.'
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

            $MRANfServicos                       = null;
            if(isset($store['id'])){
                $MRANfServicos                   = MRANfServicos::find($store['id']);
                if(!$MRANfServicos){
                    \Session::flash('flash_error', 'Registro não encontrado!');
                    return Redirect::to('mra_nota_fiscal/mra_servicos');
                }
            }
            $acao                                       = 'edit';
            if(is_null($MRANfServicos)){
                $MRANfServicos                          = new MRANfServicos();
                $acao                                   = 'add';
            }

            $MRANfServicos->codigo                      = (isset($store['codigo'])?$store['codigo']:null);
            $MRANfServicos->nome                        = (isset($store['nome'])?$store['nome']:null);
            $MRANfServicos->valor                       = (isset($store['valor'])?\App\Helper\Helper::H_Decimal_ptBR_DB($store['valor']):null);
            $MRANfServicos->descricao_servico           = (isset($store['descricao_servico'])?$store['descricao_servico']:null);
            $MRANfServicos->imp_atividade_cnae          = (isset($store['imp_atividade_cnae'])?$store['imp_atividade_cnae']:null);
            $MRANfServicos->imp_confis                  = (isset($store['imp_confis'])?\App\Helper\Helper::H_Decimal_ptBR_DB($store['imp_confis']):null);
            $MRANfServicos->imp_csll                    = (isset($store['imp_csll'])?\App\Helper\Helper::H_Decimal_ptBR_DB($store['imp_csll']):null);
            $MRANfServicos->imp_inss                    = (isset($store['imp_inss'])?\App\Helper\Helper::H_Decimal_ptBR_DB($store['imp_inss']):null);
            $MRANfServicos->imp_ir                      = (isset($store['imp_ir'])?\App\Helper\Helper::H_Decimal_ptBR_DB($store['imp_ir']):null);
            $MRANfServicos->imp_pis                     = (isset($store['imp_pis'])?\App\Helper\Helper::H_Decimal_ptBR_DB($store['imp_pis']):null);
            $MRANfServicos->imp_iss                     = (isset($store['imp_iss'])?\App\Helper\Helper::H_Decimal_ptBR_DB($store['imp_iss']):null);
            $MRANfServicos->imp_confis                  = (isset($store['imp_confis'])?\App\Helper\Helper::H_Decimal_ptBR_DB($store['imp_confis']):null);
            $MRANfServicos->imp_iss_retido_fonte        = (isset($store['imp_iss_retido_fonte'])?$store['imp_iss_retido_fonte']:null);
            $MRANfServicos->imp_servico_lc116           = (isset($store['imp_servico_lc116'])?$store['imp_servico_lc116']:null);
            $MRANfServicos->imp_cod_servico_municip     = (isset($store['imp_cod_servico_municip'])?$store['imp_cod_servico_municip']:null);
            $MRANfServicos->imp_desc_servico_municip    = (isset($store['imp_desc_servico_municip'])?$store['imp_desc_servico_municip']:null);
            $MRANfServicos->cfg_enviar_email            = (isset($store['cfg_enviar_email'])?$store['cfg_enviar_email']:null);
            $MRANfServicos->cfg_emails                  = (isset($store['cfg_emails'])?$store['cfg_emails']:null);
            if(!$MRANfServicos->cfg_enviar_email){
                $MRANfServicos->cfg_emails              = null;
            }
            $MRANfServicos->status                  = (isset($store['status'])?$store['status']:null);
            $MRANfServicos->r_auth                  = $r_auth;

            $MRANfServicos->save();

            DB::commit();

            // :: Edição
            if($acao=='edit'){
                Session::flash('flash_success', "Serviço atualizado com sucesso!");
                if($user){
                    Logs::cadastrar($user->id, ($user->name . ' mra_servicos|'.$acao.': atualizou ID: ' . $MRANfServicos->id));
                }

            // :: Criação
            }else {
                Session::flash('flash_success', "Serviço cadastrado com sucesso!");
                if($user){
                    Logs::cadastrar($user->id, ($user->name . ' mra_servicos|'.$acao.': cadastrou ID: ' . $MRANfServicos->id));
                }
            }

        }catch(Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            Session::flash('flash_error', "Erro ao realizar atualização mra_servicos|'.$acao.': " . $e->getMessage());
            return back()->withInput()->with([],400);
        }

        if($redirect){
            return Redirect::to($redirect);
        }

        return Redirect::to('/mra_nota_fiscal/mra_servicos');
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
                $MRANfServicos = MRANfServicos::where($subForm, $value)->get();
            }else {
                $MRANfServicos = MRANfServicos::where($subForm, $value)->where(function($q) use ($user){
                    $q->whereNull('r_auth')->orWhere('r_auth', $user->id)->orWhere('r_auth', 0);
                })->get();
            }
        }else {
            if(Permissions::permissaoModerador($user)){
                $MRANfServicos = MRANfServicos::find($value);
            }else {
                $MRANfServicos = MRANfServicos::where(function($q) use ($user){
                    $q->whereNull('r_auth')->orWhere('r_auth', $user->id)->orWhere('r_auth', 0);
                })->find($value);
            }
        }
        return Response::json($MRANfServicos);
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
        $MRANfServicos = MRANfServicos::find($id);
        return $this->controllerRepository::destroy(new MRANfServicos(), $id, 'mra_nota_fiscal/mra_servicos');
    }
}
