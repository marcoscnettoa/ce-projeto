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

use \App\Models\RNfProdutosTs;
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

class RNfProdutosTsController extends Controller
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

            $MRANfProdutos  = RNfProdutosTs::getAll(500);

            if($user){
                Logs::cadastrar($user->id, ($user->name . ' visualizou a lista do módulo r_nf_produtos_ts'));
            }

            return view('r_nf_produtos_ts.index', [
                'exibe_filtros'     => 0,
                'MRANfProdutos'     => $MRANfProdutos,
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

            $MRANfProdutos     = null;
            if(!is_null($id)){
                $MRANfProdutos = RNfProdutosTs::find($id);
                if(!$MRANfProdutos){
                    \Session::flash('flash_error', 'Registro não encontrado!');
                    return Redirect::to('nota_fiscal/mra_nf_ts_produtos');
                }
            }

            if(!Permissions::permissaoModerador($user) && $MRANfProdutos->r_auth != 0 && $MRANfProdutos->r_auth != $user->id){
                Session::flash('flash_error', 'Você não tem permissão para executar esta ação!');
                return Redirect::to('nota_fiscal/mra_nf_ts_produtos');
            }

            if($user){
                // Edição
                if(!is_null($MRANfProdutos)){
                    Logs::cadastrar($user->id, ($user->name . ' visualizou a tela de edição ID #' . $id . ' do módulo r_nf_produtos_ts'));
                    // Criação
                }else {
                    Logs::cadastrar($user->id, ($user->name . ' visualizou a tela de criação do módulo r_nf_produtos_ts'));
                }
            }

            return view('r_nf_produtos_ts.add_edit', [
                'exibe_filtros'     => 0,
                'MRANfProdutos'     => $MRANfProdutos
            ]);

        }catch(\Exception $e){
            Log::error($e->getMessage());
            Session::flash('flash_error', "Ocorreu um erro! Tente novamente.");
            return Redirect::to('/');
        }
    }

    protected function validator(array $data, $id = ''){
        return Validator::make($data, [
            'codigo'        => 'required|unique:r_nf_produtos_ts,codigo,'.$id.',id',
            'nome'          => 'required',
            'valor_venda'   => 'required',
            'origem'        => 'required',
            'cst'           => 'required',
            'cfop'          => 'required',
            'ncm'           => 'required',
            'ipi_cst'       => 'required',
            'pis_cst'       => 'required',
            'cofins_cst'    => 'required',
        ],[
            'codigo.required'   => 'O campo "Código do Produto" é obrigatório.',
            'codigo.unique'     => 'O "Código do Produto" informado já está em uso.',
            'nome'              => 'O campo "Nome" é obrigatório.',
            'valor_venda'       => 'O campo "Valor" é obrigatório.',
            'origem'            => 'O campo "Origem" é obrigatório.',
            'cst'               => 'O campo "CST" é obrigatório.',
            'cfop'              => 'O campo "CFOP" é obrigatório.',
            'ncm'               => 'O campo "NCM" é obrigatório.',
            'ipi_cst'           => 'O campo "CST IPI" é obrigatório.',
            'pis_cst'           => 'O campo "CST PIS" é obrigatório.',
            'cofins_cst'        => 'O campo "CST COFINS" é obrigatório.',
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

            $validator  = $this->validator($store, (isset($store['id'])?$store['id']:null));
            if($validator->fails()){
                return back()->withInput()->with(array('errors' => $validator->errors()), 400);
            }

            DB::beginTransaction();

            $MRANfProdutos                       = null;
            if(isset($store['id'])){
                $MRANfProdutos                      = RNfProdutosTs::find($store['id']);
                if(!$MRANfProdutos){
                    \Session::flash('flash_error', 'Registro não encontrado!');
                    return Redirect::to('/nota_fiscal/produtos/ts');
                }
            }
            $acao                                   = 'edit';
            if(is_null($MRANfProdutos)){
                $MRANfProdutos                      = new RNfProdutosTs();
                $acao                               = 'add';
            }

            $MRANfProdutos->codigo                  = (isset($store['codigo'])?$store['codigo']:null);
            $MRANfProdutos->codigo_fiscal           = (isset($store['codigo_fiscal'])?$store['codigo_fiscal']:null);
            $MRANfProdutos->codigo_barras           = (isset($store['codigo_barras'])?$store['codigo_barras']:null);
            $MRANfProdutos->unidade_medida          = (isset($store['unidade_medida'])?$store['unidade_medida']:null);
            $MRANfProdutos->nome                    = (isset($store['nome'])?$store['nome']:null);
            $MRANfProdutos->valor_venda             = (isset($store['valor_venda'])?\App\Helper\Helper::H_Decimal_ptBR_DB($store['valor_venda']):null);
            $MRANfProdutos->origem                  = (isset($store['origem'])?$store['origem']:null);
            $MRANfProdutos->cst                     = (isset($store['cst'])?$store['cst']:null);
            $MRANfProdutos->cfop                    = (isset($store['cfop'])?$store['cfop']:null);
            $MRANfProdutos->cfop                    = (isset($store['cfop'])?$store['cfop']:null);
            $MRANfProdutos->ncm                     = (isset($store['ncm'])?$store['ncm']:null);
            $MRANfProdutos->cest                    = (isset($store['cest'])?$store['cest']:null);
            $MRANfProdutos->valor_desconto          = (isset($store['valor_desconto'])?\App\Helper\Helper::H_Decimal_ptBR_DB($store['valor_desconto']):null);
            $MRANfProdutos->valor_seguro            = (isset($store['valor_seguro'])?\App\Helper\Helper::H_Decimal_ptBR_DB($store['valor_seguro']):null);
            $MRANfProdutos->observacoes             = (isset($store['observacoes'])?$store['observacoes']:null);
            $MRANfProdutos->icms_cst                = (isset($store['icms_cst'])?$store['icms_cst']:null);
            $MRANfProdutos->icms_icms               = (isset($store['icms_icms'])?\App\Helper\Helper::H_Decimal_ptBR_DB($store['icms_icms']):null);
            $MRANfProdutos->ipi_cst                 = (isset($store['ipi_cst'])?$store['ipi_cst']:null);
            $MRANfProdutos->ipi_ipi                 = (isset($store['ipi_ipi'])?\App\Helper\Helper::H_Decimal_ptBR_DB($store['ipi_ipi']):null);
            $MRANfProdutos->pis_cst                 = (isset($store['pis_cst'])?$store['pis_cst']:null);
            $MRANfProdutos->pis_pis                 = (isset($store['pis_pis'])?\App\Helper\Helper::H_Decimal_ptBR_DB($store['pis_pis']):null);
            $MRANfProdutos->cofins_cst              = (isset($store['cofins_cst'])?$store['cofins_cst']:null);
            $MRANfProdutos->cofins_cofins           = (isset($store['cofins_cofins'])?\App\Helper\Helper::H_Decimal_ptBR_DB($store['cofins_cofins']):null);
            $MRANfProdutos->status                  = (isset($store['status'])?$store['status']:null);
            $MRANfProdutos->r_auth                  = $r_auth;

            $MRANfProdutos->save();

            DB::commit();

            // :: Edição
            if($acao=='edit'){
                Session::flash('flash_success', "Serviço atualizado com sucesso!");
                if($user){
                    Logs::cadastrar($user->id, ($user->name . ' r_nf_produtos_ts|'.$acao.': atualizou ID: ' . $MRANfProdutos->id));
                }

                // :: Criação
            }else {
                Session::flash('flash_success', "Serviço cadastrado com sucesso!");
                if($user){
                    Logs::cadastrar($user->id, ($user->name . ' r_nf_produtos_ts|'.$acao.': cadastrou ID: ' . $MRANfProdutos->id));
                }
            }

        }catch(Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            Session::flash('flash_error', "Erro ao realizar atualização r_nf_produtos_ts|'.$acao.': " . $e->getMessage());
            return back()->withInput()->with([],400);
        }

        if($redirect){
            return Redirect::to($redirect);
        }

        return Redirect::to('/nota_fiscal/produtos/ts');
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
                $MRANfProdutos = RNfProdutosTs::where($subForm, $value)->get();
            }else {
                $MRANfProdutos = RNfProdutosTs::where($subForm, $value)->where(function($q) use ($user){
                    $q->whereNull('r_auth')->orWhere('r_auth', $user->id)->orWhere('r_auth', 0);
                })->get();
            }
        }else {
            if(Permissions::permissaoModerador($user)){
                $MRANfProdutos = RNfProdutosTs::find($value);
            }else {
                $MRANfProdutos = RNfProdutosTs::where(function($q) use ($user){
                    $q->whereNull('r_auth')->orWhere('r_auth', $user->id)->orWhere('r_auth', 0);
                })->find($value);
            }
        }
        return Response::json($MRANfProdutos);
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
        $MRANfProdutos = RNfProdutosTs::find($id);
        return $this->controllerRepository::destroy(new RNfProdutosTs(), $id, 'nota_fiscal/produtos/ts');
    }
}
