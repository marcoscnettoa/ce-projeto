<?php

namespace App\Http\Controllers;

use App\Models\RNfNfeProdutosItensTs;
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
use Carbon\Carbon;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

use App\Http\Controllers\MRA\MRANotazz;

use \App\Models\RNfNfeTs;
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
use App\Http\Controllers\RTecnoSpeedController as RTecnoSpeed;
use App\Models\RCidades;
use App\Models\RNfConfiguracoesTs;

class RNfNfeTsController extends Controller
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

            $user      = Auth::user();
            $MRANfNfe  = RNfNfeTs::getAll(500,$request);

            if($user){
                Logs::cadastrar($user->id, ($user->name . ' visualizou a lista do módulo r_nf_nfe_ts'));
            }

            return view('r_nf_nfe_ts.index', [
                'exibe_filtros'     => 0,
                'MRANfNfe'          => $MRANfNfe
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
            $config_empresa = RNfConfiguracoesTs::find(1);

            if (!$config_empresa) {
                Session::flash('flash_error', 'É necessário registrar as Configurações para emissão de Nota Fiscal!');
                return redirect()->back();
            }

            $MRANfNfe     = null;
            if(!is_null($id)){
                $MRANfNfe = RNfNfeTs::find($id);
                if(!$MRANfNfe){
                    \Session::flash('flash_error', 'Registro não encontrado!');
                    return Redirect::to('nota_fiscal/mra_nf_ts_nf_e');
                }
            }

            if(!Permissions::permissaoModerador($user) && $MRANfNfe->r_auth != 0 && $MRANfNfe->r_auth != $user->id){
                Session::flash('flash_error', 'Você não tem permissão para executar esta ação!');
                return Redirect::to('nota_fiscal/mra_nf_ts_nf_e');
            }

            if($user){
                // Edição
                if(!is_null($MRANfNfe)){
                    Logs::cadastrar($user->id, ($user->name . ' visualizou a tela de edição ID #' . $id . ' do módulo r_nf_nfe_ts'));
                    // Criação
                }else {
                    Logs::cadastrar($user->id, ($user->name . ' visualizou a tela de criação do módulo r_nf_nfe_ts'));
                }
            }

            return view('r_nf_nfe_ts.add_edit', [
                'exibe_filtros'     => 0,
                'MRANfNfe'          => $MRANfNfe,
                'config_empresa'    => $config_empresa,
            ]);

        }catch(\Exception $e){
            Log::error($e->getMessage());
            Session::flash('flash_error', "Ocorreu um erro! Tente novamente.");
            return Redirect::to('/');
        }
    }

    protected function validator(array $data, $id = ''){

        // ! Valida se possui ao menos um produto e se os campos obrigatórios  foram informados!
        $p_i_codigo_required        = false;
        $p_i_nome_required          = false;
        $p_i_qt_required            = false;
        $p_i_valor_required         = false;
        $p_i_origem_required        = false;
        $p_i_cst_required           = false;
        $p_i_cfop_required          = false;
        $p_i_ncm_required           = false;
        $p_i_codigo_barras_required = false;
        $p_i_cst_ipi_required       = false;
        $p_i_cst_pis_required       = false;
        $p_i_cst_cofins_required    = false;
        
        if(isset($data['mra_nf_prod_id']) and count($data['mra_nf_prod_id'])){
            foreach($data['mra_nf_nf_e_prod_i_id'] as $K => $Prod_i){
                if(empty($data['mra_nf_prod_codigo'][$K])){     $p_i_codigo_required            = true; }
                if(empty($data['mra_nf_prod_nome'][$K])){       $p_i_nome_required              = true; }
                if(empty($data['mra_nf_prod_qt'][$K])){         $p_i_qt_required                = true; }
                if(empty($data['mra_nf_prod_valor_unit'][$K])){ $p_i_valor_required             = true; }

                if(empty($data['mra_nf_prod_origem'][$K])){ $p_i_origem_required                = true; }
                if($data['mra_nf_prod_origem'][$K]
                    == '0'){ $p_i_origem_required                                               = false;}
                if(empty($data['mra_nf_prod_cst'][$K])){ $p_i_cst_required                      = true; }
                if(empty($data['mra_nf_prod_cfop'][$K])){ $p_i_cfop_required                    = true; }
                if(empty($data['mra_nf_prod_ncm'][$K])){ $p_i_ncm_required                      = true; }
                if(empty($data['mra_nf_prod_codigo_barras'][$K])){ $p_i_codigo_barras_required  = true; }
                if(empty($data['mra_nf_prod_imp_cst_ipi'][$K])){ $p_i_cst_ipi_required          = true; }
                if(empty($data['mra_nf_prod_imp_cst_pis'][$K])){ $p_i_cst_pis_required          = true; }
                if(empty($data['mra_nf_prod_imp_cst_cofins'][$K])){ $p_i_cst_cofins_required    = true; }
            }
        }

        return Validator::make($data, [
            'nfe_finalidade'            =>  'required',
            'nfe_meio_de_pagamento'     =>  'required',
            'des_nome_razao_social'     =>  'required',
            'des_tipo_pessoa'           =>  'required',
            'des_cpf'                   =>  ((isset($data['des_tipo_pessoa']) and $data['des_tipo_pessoa']=='F')?'required':''),
            'des_cnpj'                  =>  ((isset($data['des_tipo_pessoa']) and $data['des_tipo_pessoa']=='J')?'required':''),
            'des_end_cep'               =>  'required',
            'des_end_rua'               =>  'required',
            'des_end_numero'            =>  'required',
            'des_end_bairro'            =>  'required',
            'des_end_estado'            =>  'required',
            'des_end_cidade'            =>  'required',
            'prod_i_codigo'             =>  ($p_i_codigo_required?'required':''),
            'prod_i_nome'               =>  ($p_i_nome_required?'required':''),
            'prod_i_qt'                 =>  ($p_i_qt_required?'required':''),
            'prod_i_valor'              =>  ($p_i_valor_required?'required':''),
            'prod_i_origem'             =>  ($p_i_origem_required?'required':''),
            'prod_i_cst'                =>  ($p_i_cst_required?'required':''),
            'prod_i_cfop'               =>  ($p_i_cfop_required?'required':''),
            'prod_i_ncm'                =>  ($p_i_ncm_required?'required':''),
            'prod_i_codigo_barras'      =>  ($p_i_codigo_barras_required?'required':''),
            'prod_i_cst_ipi'            =>  ($p_i_cst_ipi_required?'required':''),
            'prod_i_cst_pis'            =>  ($p_i_cst_pis_required?'required':''),
            'prod_i_cst_cofins'         =>  ($p_i_cst_cofins_required?'required':''),
        ],[
            'nfe_finalidade'            => 'O campo "Finalidade" é obrigatório.',
            'nfe_meio_de_pagamento'     => 'O campo "Meio de Pagamento" é obrigatório.',
            'des_nome_razao_social'     => 'O campo "Nome / Razão Social" do "Cliente / Destinatário" é obrigatório.',
            'des_tipo_pessoa'           => 'O campo "Tipo de Pessoa" do "Cliente / Destinatário" é obrigatório.',
            'des_cpf'                   => 'O campo "CPF" do "Cliente / Destinatário" é obrigatório.',
            'des_cnpj'                  => 'O campo "CNPJ" do "Cliente / Destinatário" é obrigatório.',
            'des_end_cep'               => 'O campo "CEP" do "Cliente / Destinatário" é obrigatório.',
            'des_end_rua'               => 'O campo "Logradouro / Rua" do "Cliente / Destinatário" é obrigatório.',
            'des_end_numero'            => 'O campo "Número" do "Cliente / Destinatário" é obrigatório.',
            'des_end_bairro'            => 'O campo "Bairro" do "Cliente / Destinatário" é obrigatório.',
            'des_end_estado'            => 'O campo "Estado" do "Cliente / Destinatário" é obrigatório.',
            'des_end_cidade'            => 'O campo "Cidade" do "Cliente / Destinatário" é obrigatório.',
            'prod_i_codigo'             => 'O campo "Código" do "Produto" é obrigatório.',
            'prod_i_nome'               => 'O campo "Nome do Produto" do "Produto" é obrigatório.',
            'prod_i_qt'                 => 'O campo "Quantidade" do "Produto" é obrigatório.',
            'prod_i_valor'              => 'O campo "Valor" do "Produto" é obrigatório.',
            'prod_i_origem'             => 'O campo "Origem" do "Produto" é obrigatório.',
            'prod_i_cst'                => 'O campo "CST" do "Produto" é obrigatório.',
            'prod_i_cfop'               => 'O campo "CFOP" do "Produto" é obrigatório.',
            'prod_i_ncm'                => 'O campo "NCM" do "Produto" é obrigatório.',
            'prod_i_codigo_barras'      => 'O campo "Código de Barras" do "Produto" é obrigatório.',
            'prod_i_cst_ipi'            => 'O campo "CST IPI" do "Produto" é obrigatório.',
            'prod_i_cst_pis'            => 'O campo "CST PIS" do "Produto" é obrigatório.',
            'prod_i_cst_cofins'         => 'O campo "CST COFINS" do "Produto" é obrigatório.',
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

            // :: Validação de Campos
            if(!isset($store['consultar']) and !isset($store['cancelar_nf']) and !isset($store['cancelar_nf_forcado'])){
                $validator  = $this->validator($store);
                if($validator->fails()){
                    return back()->withInput()->with(array('errors' => $validator->errors()), 400);
                }
            }
            // - #

            $RNfNfeTs                                   = null;
            if(isset($store['id'])){
                $RNfNfeTs                               = RNfNfeTs::find($store['id']);
                if(!$RNfNfeTs){
                    \Session::flash('flash_error', 'Registro não encontrado!');
                    return Redirect::to('nota_fiscal/nfe/ts');
                }
            }

            // :: Cancelar Nota Fiscal
            if(isset($store['cancelar_nf'])) {
                
                $cancelar_nf = $this->cancelar($store, $RNfNfeTs, $user);

                $RNfNfeTs->save();
                DB::commit();

                // :: Sucesso
                if (isset($cancelar_nf['success'])) {
                    Session::flash('flash_success', "Nota Fiscal enviada para cancelamento com sucesso!");

                // :: Erro
                }elseif (isset($cancelar_nf['error'])) {
                    Session::flash('flash_error', "Erro: ".$cancelar_nf['message']);
                }

                if($user){
                    Logs::cadastrar($user->id, ($user->name .' r_nf_nfe_ts cancelou ID: '.$RNfNfeTs->nf_response_id));
                }

                return Redirect::to('/nota_fiscal/nfe/ts/'.$RNfNfeTs->id.'/edit');
            }

            // :: Cancelar Processamento Forçado
            if(isset($store['cancelar_nf_forcado'])){ return $this->destroy($RNfNfeTs); }

            DB::beginTransaction();

            // ! Caso Exista -> Validar Passagem de Status*
            if($RNfNfeTs and $RNfNfeTs->notazz_status == 'Autorizada'){
                \Session::flash('flash_success', 'A Nota Fiscal de Produto já foi autorizada!');
                return Redirect::to('nota_fiscal/nfe/ts'.$RNfNfeTs->id.'/edit');
            }
            // - #

            // :: Consultar Processamento
            if(isset($store['consultar'])){

                $consulta_tecnospeed = $this->consultar($store, $user, $RNfNfeTs);

                $RNfNfeTs->save();
                DB::commit();

                if (isset($consulta_tecnospeed['success'])) {
                    Session::flash('flash_success', 'A Nota Fiscal consultada com sucesso!');
                }elseif (isset($consulta_tecnospeed['error'])) {
                    Session::flash('flash_error', $consulta_tecnospeed['message']);
                }

                if($user){
                    Logs::cadastrar($user->id, ($user->name .' r_nf_nfe_ts consultou ID: '.$RNfNfeTs->nf_response_id));
                }

                return Redirect::to('/nota_fiscal/nfe/ts/'.$RNfNfeTs->id.'/edit');
            }
            // - #

            $acao                                       = 'edit';
            if(is_null($RNfNfeTs)){
                $RNfNfeTs                             = new RNfNfeTs();
                $acao                                   = 'add';
            }

            $RNfNfeTs->mra_nf_cfg_emp_id                = 1;
            $RNfNfeTs->mra_nf_cliente_id                = (isset($store['mra_nf_cliente_id'])?$store['mra_nf_cliente_id']:null);
            $RNfNfeTs->mra_nf_transp_id                 = (isset($store['mra_nf_transp_id'])?$store['mra_nf_transp_id']:null);
            $RNfNfeTs->nfe_data_competencia             = (isset($store['nfe_data_competencia'])?\App\Helper\Helper::H_DataHora_ptBR_DB($store['nfe_data_competencia']):Carbon::now());
            $RNfNfeTs->nfe_finalidade                   = (isset($store['nfe_finalidade'])?$store['nfe_finalidade']:null);
            $RNfNfeTs->nfe_meio_de_pagamento            = (isset($store['nfe_meio_de_pagamento'])?$store['nfe_meio_de_pagamento']:null);
            $RNfNfeTs->nfe_chave_referencia             = (isset($store['nfe_chave_referencia'])?$store['nfe_chave_referencia']:null);
            if(empty($RNfNfeTs->nfe_finalidade) || $RNfNfeTs->nfe_finalidade == 1){
                $RNfNfeTs->nfe_chave_referencia         = null;
            }
            $RNfNfeTs->nfe_natureza_operacao            = (isset($store['nfe_natureza_operacao'])?$store['nfe_natureza_operacao']:null);
            $RNfNfeTs->nfe_tipo_operacao                = (isset($store['nfe_tipo_operacao'])?$store['nfe_tipo_operacao']:null);
            $RNfNfeTs->nfe_valor_total                  = (isset($store['nfe_valor_total'])?\App\Helper\Helper::H_Decimal_ptBR_DB($store['nfe_valor_total']):null);
            $RNfNfeTs->nfe_infor_adic_fisco             = (isset($store['nfe_infor_adic_fisco'])?$store['nfe_infor_adic_fisco']:null);
            $RNfNfeTs->nfe_infor_comple_int_contr       = (isset($store['nfe_infor_comple_int_contr'])?$store['nfe_infor_comple_int_contr']:null);
            $RNfNfeTs->emi_razao_social                 = (isset($store['emi_razao_social'])?$store['emi_razao_social']:null);
            $RNfNfeTs->emi_cnpj                         = (isset($store['emi_cnpj'])?str_replace(['_'],'',$store['emi_cnpj']):null);
            $RNfNfeTs->emi_inscricao_estadual           = (isset($store['emi_inscricao_estadual'])?$store['emi_inscricao_estadual']:null);
            $RNfNfeTs->emi_inscricao_municipal          = (isset($store['emi_inscricao_municipal'])?$store['emi_inscricao_municipal']:null);
            $RNfNfeTs->emi_telefone                     = (isset($store['emi_telefone'])?str_replace(['_'],'',$store['emi_telefone']):null);
            $RNfNfeTs->emi_email                        = (isset($store['emi_email'])?$store['emi_email']:null);
            $RNfNfeTs->emi_end_cep                      = (isset($store['emi_end_cep'])?str_replace(['_'],'',$store['emi_end_cep']):null);
            $RNfNfeTs->emi_end_rua                      = (isset($store['emi_end_rua'])?$store['emi_end_rua']:null);
            $RNfNfeTs->emi_end_numero                   = (isset($store['emi_end_numero'])?$store['emi_end_numero']:null);
            $RNfNfeTs->emi_end_bairro                   = (isset($store['emi_end_bairro'])?$store['emi_end_bairro']:null);
            $RNfNfeTs->emi_end_complemento              = (isset($store['emi_end_complemento'])?$store['emi_end_complemento']:null);
            $RNfNfeTs->emi_end_estado                   = (isset($store['emi_end_estado'])?$store['emi_end_estado']:null);
            $RNfNfeTs->emi_end_cidade                   = (isset($store['emi_end_cidade'])?$store['emi_end_cidade']:null);
            $RNfNfeTs->nfe_cnae_fiscal                  = (isset($store['nfe_cnae_fiscal'])?$store['nfe_cnae_fiscal']:null);
            $RNfNfeTs->nfe_cod_regime_tributario        = (isset($store['nfe_cod_regime_tributario'])?$store['nfe_cod_regime_tributario']:null);
            $RNfNfeTs->des_nome_razao_social            = (isset($store['des_nome_razao_social'])?$store['des_nome_razao_social']:null);
            $RNfNfeTs->des_tipo_pessoa                  = (isset($store['des_tipo_pessoa'])?$store['des_tipo_pessoa']:null);
            $RNfNfeTs->des_cnpj                         = (isset($store['des_cnpj'])?str_replace(['_'],'',$store['des_cnpj']):null);
            $RNfNfeTs->des_cpf                          = (isset($store['des_cpf'])?str_replace(['_'],'',$store['des_cpf']):null);
            $RNfNfeTs->des_cnpj_inscricao_estadual      = (isset($store['des_cnpj_inscricao_estadual'])?$store['des_cnpj_inscricao_estadual']:null);
            $RNfNfeTs->des_cnpj_inscricao_municipal     = (isset($store['des_cnpj_inscricao_municipal'])?$store['des_cnpj_inscricao_municipal']:null);

            // :: Física
            if($RNfNfeTs->des_tipo_pessoa == 'F'){
                $RNfNfeTs->des_cnpj                     = null;
                $RNfNfeTs->des_cnpj_inscricao_estadual  = null;
                $RNfNfeTs->des_cnpj_inscricao_municipal = null;

            // :: Jurídica
            }elseif($RNfNfeTs->des_tipo_pessoa == 'J'){
                $RNfNfeTs->des_cpf                      = null;

            // :: Estrangeiro
            }elseif($RNfNfeTs->des_tipo_pessoa == 'E'){
                $RNfNfeTs->des_cpf                      = null;
                $RNfNfeTs->des_cnpj                     = null;
                $RNfNfeTs->des_cnpj_inscricao_estadual  = null;
            }else {
                $RNfNfeTs->des_cpf                      = null;
                $RNfNfeTs->des_cnpj                     = null;
                $RNfNfeTs->des_cnpj_inscricao_estadual  = null;
                $RNfNfeTs->des_cnpj_inscricao_municipal = null;
            }

            $RNfNfeTs->des_telefone                     = (isset($store['des_telefone'])?str_replace(['_'],'',$store['des_telefone']):null);
            $RNfNfeTs->des_email                        = (isset($store['des_email'])?$store['des_email']:null);
            $RNfNfeTs->des_enviar_nfe_email             = (isset($store['des_enviar_nfe_email'])?$store['des_enviar_nfe_email']:null);
            $RNfNfeTs->des_end_cep                      = (isset($store['des_end_cep'])?str_replace(['_'],'',$store['des_end_cep']):null);
            $RNfNfeTs->des_end_rua                      = (isset($store['des_end_rua'])?$store['des_end_rua']:null);
            $RNfNfeTs->des_end_numero                   = (isset($store['des_end_numero'])?$store['des_end_numero']:null);
            $RNfNfeTs->des_end_bairro                   = (isset($store['des_end_bairro'])?$store['des_end_bairro']:null);
            $RNfNfeTs->des_end_complemento              = (isset($store['des_end_complemento'])?$store['des_end_complemento']:null);
            $RNfNfeTs->des_end_estado                   = (isset($store['des_end_estado'])?$store['des_end_estado']:null);
            $RNfNfeTs->des_end_cidade                   = (isset($store['des_end_cidade'])?$store['des_end_cidade']:null);
            $RNfNfeTs->des_end_pais                     = (isset($store['des_end_pais'])?$store['des_end_pais']:null);

            if(ENV('MODULO_NF_PRODUTO_TRANSP_CLI')){
                $RNfNfeTs->mra_nf_transp_id                 = (isset($store['mra_nf_transp_id'])?$store['mra_nf_transp_id']:null);
                $RNfNfeTs->transp_nome_razao_social         = (isset($store['transp_nome_razao_social'])?$store['transp_nome_razao_social']:null);
                $RNfNfeTs->transp_modalid_frete             = (isset($store['transp_modalid_frete'])?$store['transp_modalid_frete']:null);
                $RNfNfeTs->transp_cnpj                      = (isset($store['transp_cnpj'])?str_replace(['_'],'',$store['transp_cnpj']):null);
                $RNfNfeTs->transp_cpf                       = (isset($store['transp_cpf'])?str_replace(['_'],'',$store['transp_cpf']):null);
                $RNfNfeTs->transp_inscricao_estadual        = (isset($store['transp_inscricao_estadual'])?$store['transp_inscricao_estadual']:null);
                $RNfNfeTs->transp_cont_emails_nf            = (isset($store['transp_cont_emails_nf'])?$store['transp_cont_emails_nf']:null);
                $RNfNfeTs->transp_end_cep                   = (isset($store['transp_end_cep'])?str_replace(['_'],'',$store['transp_end_cep']):null);
                $RNfNfeTs->transp_end_rua                   = (isset($store['transp_end_rua'])?$store['transp_end_rua']:null);
                $RNfNfeTs->transp_end_numero                = (isset($store['transp_end_numero'])?$store['transp_end_numero']:null);
                $RNfNfeTs->transp_end_bairro                = (isset($store['transp_end_bairro'])?$store['transp_end_bairro']:null);
                $RNfNfeTs->transp_end_estado                = (isset($store['transp_end_estado'])?$store['transp_end_estado']:null);
                $RNfNfeTs->transp_end_cidade                = (isset($store['transp_end_cidade'])?$store['transp_end_cidade']:null);
                $RNfNfeTs->transp_valor_frete               = (isset($store['transp_valor_frete'])?\App\Helper\Helper::H_Decimal_ptBR_DB($store['transp_valor_frete']):null);
                $RNfNfeTs->transp_veiculo_placa             = (isset($store['transp_veiculo_placa'])?$store['transp_veiculo_placa']:null);
                $RNfNfeTs->transp_veiculo_uf                = (isset($store['transp_veiculo_uf'])?$store['transp_veiculo_uf']:null);
                $RNfNfeTs->transp_informar_volume           = (isset($store['transp_informar_volume'])?$store['transp_informar_volume']:null);
                if($RNfNfeTs->transp_informar_volume){
                    $RNfNfeTs->transp_iv_quantidade         = (isset($store['transp_iv_quantidade'])?$store['transp_iv_quantidade']:null);
                    $RNfNfeTs->transp_iv_especie            = (isset($store['transp_iv_especie'])?$store['transp_iv_especie']:null);
                    $RNfNfeTs->transp_iv_peso_liquido       = (isset($store['transp_iv_peso_liquido'])?\App\Helper\Helper::H_Decimal_ptBR_DB($store['transp_iv_peso_liquido']):null);
                    $RNfNfeTs->transp_iv_peso_bruto         = (isset($store['transp_iv_peso_bruto'])?\App\Helper\Helper::H_Decimal_ptBR_DB($store['transp_iv_peso_bruto']):null);
                }else {
                    $RNfNfeTs->transp_iv_quantidade         = null;
                    $RNfNfeTs->transp_iv_especie            = null;
                    $RNfNfeTs->transp_iv_peso_liquido       = null;
                    $RNfNfeTs->transp_iv_peso_bruto         = null;
                }
            }

            $RNfNfeTs->r_auth                           = $r_auth;
            $RNfNfeTs->save();

            // :: Produtos - Itens
            if(isset($store['mra_nf_prod_id']) and count($store['mra_nf_prod_id'])){

                // :: Exclusão de Itens anteriores
                RNfNfeProdutosItensTs::where('mra_nf_nf_e_id', $RNfNfeTs->id)->delete();
                
                // :: Lista Itens
                $k = 0;
                foreach($store['mra_nf_prod_id'] as $K => $Prod_i){
                   
                    $RNfNfeProdutosItensTs                           = new RNfNfeProdutosItensTs();
                    $RNfNfeProdutosItensTs->mra_nf_nf_e_id           = $RNfNfeTs->id;
                    $RNfNfeProdutosItensTs->mra_nf_prod_id           = (isset($store['mra_nf_prod_id'][$K])?$store['mra_nf_prod_id'][$K]:null);
                    $RNfNfeProdutosItensTs->codigo                   = (isset($store['mra_nf_prod_codigo'][$K])?$store['mra_nf_prod_codigo'][$K]:null);
                    $RNfNfeProdutosItensTs->codigo_barras            = (isset($store['mra_nf_prod_codigo_barras'][$K])?$store['mra_nf_prod_codigo_barras'][$K]:null);
                    $RNfNfeProdutosItensTs->nome                     = (isset($store['mra_nf_prod_nome'][$K])?$store['mra_nf_prod_nome'][$K]:null);
                    $RNfNfeProdutosItensTs->quantidade               = (isset($store['mra_nf_prod_qt'][$K])?$store['mra_nf_prod_qt'][$K]:null);
                    $RNfNfeProdutosItensTs->unidade_medida           = (isset($store['mra_nf_prod_umedida'][$K])?$store['mra_nf_prod_umedida'][$K]:null);
                    $RNfNfeProdutosItensTs->valor_unitario           = (isset($store['mra_nf_prod_valor_unit'][$K])?\App\Helper\Helper::H_Decimal_ptBR_DB($store['mra_nf_prod_valor_unit'][$K]):null);
                    $RNfNfeProdutosItensTs->valor_subtotal           = (isset($store['mra_nf_prod_valor_subtotal'][$K])?\App\Helper\Helper::H_Decimal_ptBR_DB($store['mra_nf_prod_valor_subtotal'][$K]):null);
                    $RNfNfeProdutosItensTs->valor_desconto           = (isset($store['mra_nf_prod_valor_desconto'][$K])?\App\Helper\Helper::H_Decimal_ptBR_DB($store['mra_nf_prod_valor_desconto'][$K]):null);
                    $RNfNfeProdutosItensTs->valor_frete              = (isset($store['mra_nf_prod_valor_frete'][$K])?\App\Helper\Helper::H_Decimal_ptBR_DB($store['mra_nf_prod_valor_frete'][$K]):null);
                    $RNfNfeProdutosItensTs->valor_seguro             = (isset($store['mra_nf_prod_valor_seguro'][$K])?\App\Helper\Helper::H_Decimal_ptBR_DB($store['mra_nf_prod_valor_seguro'][$K]):null);
                    $RNfNfeProdutosItensTs->valor_outras_despesas    = (isset($store['mra_nf_prod_valor_despesas'][$K])?\App\Helper\Helper::H_Decimal_ptBR_DB($store['mra_nf_prod_valor_despesas'][$K]):null);
                    $RNfNfeProdutosItensTs->cfop                     = (isset($store['mra_nf_prod_cfop'][$K])?$store['mra_nf_prod_cfop'][$K]:null);
                    $RNfNfeProdutosItensTs->origem                   = (isset($store['mra_nf_prod_origem'][$K])?$store['mra_nf_prod_origem'][$K]:null);
                    $RNfNfeProdutosItensTs->cst                      = (isset($store['mra_nf_prod_cst'][$K])?$store['mra_nf_prod_cst'][$K]:null);
                    $RNfNfeProdutosItensTs->ncm                      = (isset($store['mra_nf_prod_ncm'][$K])?$store['mra_nf_prod_ncm'][$K]:null);
                    $RNfNfeProdutosItensTs->cest                     = (isset($store['mra_nf_prod_cest'][$K])?$store['mra_nf_prod_cest'][$K]:null);
                    $RNfNfeProdutosItensTs->imp_cst_csosn_icms       = (isset($store['mra_nf_prod_imp_cst_csosn_icms'][$K])?$store['mra_nf_prod_imp_cst_csosn_icms'][$K]:null);
                    $RNfNfeProdutosItensTs->imp_aliquota_icms        = (isset($store['mra_nf_prod_imp_aliquota_icms'][$K])?\App\Helper\Helper::H_Decimal_ptBR_DB($store['mra_nf_prod_imp_aliquota_icms'][$K]):null);
                    $RNfNfeProdutosItensTs->imp_cst_ipi              = (isset($store['mra_nf_prod_imp_cst_ipi'][$K])?$store['mra_nf_prod_imp_cst_ipi'][$K]:null);
                    $RNfNfeProdutosItensTs->imp_aliquota_ipi         = (isset($store['mra_nf_prod_imp_aliquota_ipi'][$K])?\App\Helper\Helper::H_Decimal_ptBR_DB($store['mra_nf_prod_imp_aliquota_ipi'][$K]):null);
                    $RNfNfeProdutosItensTs->imp_cst_pis              = (isset($store['mra_nf_prod_imp_cst_pis'][$K])?$store['mra_nf_prod_imp_cst_pis'][$K]:null);
                    $RNfNfeProdutosItensTs->imp_aliquota_pis         = (isset($store['mra_nf_prod_imp_aliquota_pis'][$K])?\App\Helper\Helper::H_Decimal_ptBR_DB($store['mra_nf_prod_imp_aliquota_pis'][$K]):null);
                    $RNfNfeProdutosItensTs->imp_cst_cofins           = (isset($store['mra_nf_prod_imp_cst_cofins'][$K])?$store['mra_nf_prod_imp_cst_cofins'][$K]:null);
                    $RNfNfeProdutosItensTs->imp_aliquota_cofins      = (isset($store['mra_nf_prod_imp_aliquota_cofins'][$K])?\App\Helper\Helper::H_Decimal_ptBR_DB($store['mra_nf_prod_imp_aliquota_cofins'][$K]):null);
                    //$RNfNfeProdutosItensTs->imp_infor_adicionais     = (isset($store['xxxxxxx'][$K])?$store['xxxxxxx'][$K]:null);
                    $RNfNfeProdutosItensTs->r_auth                   = $r_auth;
                    $RNfNfeProdutosItensTs->save();

                    $k++;
                }
            }

            $RNfNfeTs->save();

            DB::commit();

            // :: Transmitir Nota Fiscal
            if(isset($store['transferir'])){
                try {

                    if (env('TECNOSPEED_ENVIRONMENT') == 'sandbox') {
                        $sistema    = '-'.env('APP_NAME').'-SANDBOX-'.strtotime(date('Y-m-d H:i:s'));
                        $cpf_cnpj   = "08187168000160";
                    }elseif (env('TECNOSPEED_ENVIRONMENT') == 'production') {
                        if ($RNfNfeTs->ConfigEmpresa->producao) {
                            $sistema = '-'.env('APP_NAME');
                            $cpf_cnpj   = preg_replace("/\D/", "", $RNfNfeTs->ConfigEmpresa->cnpj);
                        }else {
                            $sistema = '-'.env('APP_NAME').'-HOMOL';
                            $cpf_cnpj   = preg_replace("/\D/", "", $RNfNfeTs->ConfigEmpresa->cnpj);
                        }
                    }

                    // Emitente
                    $emitente = [
                        "cpfCnpj"           => $cpf_cnpj,
                        "inscricaoEstadual" => $RNfNfeTs->ConfigEmpresa->inscricao_estadual,
                        "razaoSocial"       => $RNfNfeTs->ConfigEmpresa->razao_social,
                        "nomeFantasia"      => $RNfNfeTs->ConfigEmpresa->nome_fantasia,
                        "email"             => $RNfNfeTs->ConfigEmpresa->cont_email,
                        "telefone"          => [
                            "ddd"               => substr($RNfNfeTs->ConfigEmpresa->cont_telefone, 1, 2),
                            "numero"            => preg_replace("/\D/", "", substr($RNfNfeTs->ConfigEmpresa->cont_telefone, 5)) ,
                        ],
                        // "endereco"          => [
                        //     "tipoLogradouro"    => "Avenida",
                        //     "logradouro"        => $RNfNfeTs->ConfigEmpresa->endereco_empresa,
                        //     "numero"            => $RNfNfeTs->ConfigEmpresa->numero_empresa,
                        //     "bairro"            => $RNfNfeTs->ConfigEmpresa->bairro_empresa,
                        //     "codigoCidade"      => '2927408',  // Código IBGE Provisório
                        //     "descricaoCidade"   => $RNfNfeTs->ConfigEmpresa->cidade_empresa,
                        //     "estado"            => $RNfNfeTs->ConfigEmpresa->uf_empresa,
                        //     "cep"               => preg_replace("/[^0-9]/", "", $RNfNfeTs->ConfigEmpresa->cep_empresa),
                        // ],
                    ];

                    // Recuperando dados do endereço do cliente / destinatário
                    $cidade_destinatario = RCidades::find($store['des_end_cidade']);
                    if (!$cidade_destinatario) {
                        Session::flash('flash_error', 'Desculpe, cidade não encontrada!');
                        return back()->withInput();
                    }

                    // Destinatário
                    $destinatario = [
                        "cpfCnpj"       => $store['des_tipo_pessoa'] == 'F' ? preg_replace("/\D/", "", $store['des_cpf']) : preg_replace("/\D/", "", $store['des_cnpj']),
                        "razaoSocial"   => $store['des_nome_razao_social'],
                        "email"         => $store['des_email'],
                        "endereco"      => [
                            "tipoLogradouro"    => "Avenida",  // Provisório
                            "logradouro"        => $store['des_end_rua'],
                            "numero"            => $store['des_end_numero'],
                            "bairro"            => $store['des_end_bairro'],
                            "codigoCidade"      => $cidade_destinatario->codigo,  // Código IBGE
                            "descricaoCidade"   => $cidade_destinatario->nome,
                            "estado"            => $cidade_destinatario->uf,
                            "cep"               => preg_replace("/\D/", "", $store['des_end_cep']),
                        ],
                    ];

                    // Transportadora
                    $transportadora = [];
                    if ($store['mra_nf_transp_id']) {
                        if ($store['transp_cnpj']) {
                            $cpf    = null;
                            $cnpj   = preg_replace("/\D/", "", $store['transp_cnpj']);
                        }elseif ($store['transp_cpf']) {
                            $cpf    = preg_replace("/\D/", "", $store['transp_cpf']);
                            $cnpj   = null;
                        }else {
                            $cpf = null;
                            $cnpj = null;
                        }

                        $transportadora = [
                            "modalidadeFrete"       => $store['transp_modalid_frete'],
                            "transportador"         => [
                                "cnpj"                  => $cnpj,
                                "cpf"                   => $cpf,
                                "nome"                  => $store['transp_nome_razao_social'],
                                "inscricaoEstadual"     => $store['transp_inscricao_estadual'],
                                "endereco"          => [
                                    "logradouro"        => $store['transp_end_rua'],
                                    "descricaoCidade"   => $store['transp_end_cidade'],
                                    "uf"                => $store['transp_end_estado'],
                                ],
                            ]
                        ];
                    }

                    // Produtos
                    $itens = [];
                    foreach ($store['mra_nf_prod_codigo'] as $key => $value) {
                        
                        // Extraindo apenas os números (Valor Unitário)
                        $store['mra_nf_prod_valor_unit'][$key] = str_replace(".", "", $store['mra_nf_prod_valor_unit'][$key]);
                        $store['mra_nf_prod_valor_unit'][$key] = str_replace(",", ".", $store['mra_nf_prod_valor_unit'][$key]);

                        // Desconto
                        if (isset($store['mra_nf_prod_valor_desconto'][$key]) && $store['mra_nf_prod_valor_desconto'][$key]) {
                            $store['mra_nf_prod_valor_desconto'][$key] = str_replace(".", "", $store['mra_nf_prod_valor_desconto'][$key]);
                            $store['mra_nf_prod_valor_desconto'][$key] = str_replace(",", ".", $store['mra_nf_prod_valor_desconto'][$key]);
                        }else {
                            $store['mra_nf_prod_valor_desconto'][$key] = 0;
                        }

                        // Frete
                        if (isset($store['mra_nf_prod_valor_frete'][$key]) && $store['mra_nf_prod_valor_frete'][$key]) {
                            $store['mra_nf_prod_valor_frete'][$key] = str_replace(".", "", $store['mra_nf_prod_valor_frete'][$key]);
                            $store['mra_nf_prod_valor_frete'][$key] = str_replace(",", ".", $store['mra_nf_prod_valor_frete'][$key]);
                        }else {
                            $store['mra_nf_prod_valor_frete'][$key] = 0;
                        }

                        // Seguro
                        if (isset($store['mra_nf_prod_valor_seguro'][$key]) && $store['mra_nf_prod_valor_seguro'][$key]) {
                            $store['mra_nf_prod_valor_seguro'][$key] = str_replace(".", "", $store['mra_nf_prod_valor_seguro'][$key]);
                            $store['mra_nf_prod_valor_seguro'][$key] = str_replace(",", ".", $store['mra_nf_prod_valor_seguro'][$key]);
                        }else {
                            $store['mra_nf_prod_valor_seguro'][$key] = 0;
                        }
                        
                        // Calculando valor total bruto dos produtos
                        $valor_itens = $store['mra_nf_prod_qt'][$key] * $store['mra_nf_prod_valor_unit'][$key];

                        /**
                         *  Para resolver a Rejeição 590, informe o CSOSN ao invés do CST. Caso o emitente seja simples nacional (campo CRT), 
                         *  é necessário informar o CSOSN, e não o CST.
                         *  Solução para Rejeição 590 segundo documentação da TecnoSpeed.
                         */
                        if ($store['nfe_cod_regime_tributario'] == 1)  {  // Simples Nacional
                            $icms = [
                                "origem" => isset($store['mra_nf_prod_origem'][$key]) ? (string)$store['mra_nf_prod_origem'][$key] : null,
                                "cst" => "102",  // 
                            ];
                        }else {
                            $icms = [
                                "origem" => isset($store['mra_nf_prod_origem'][$key]) ? (string)$store['mra_nf_prod_origem'][$key] : null,
                                "cst" => isset($store['mra_nf_prod_cst'][$key]) ? $store['mra_nf_prod_cst'][$key] : null,
                                "baseCalculo" => [
                                    "modalidadeDeterminacao" => 0,
                                    "valor" => 0,
                                ],
                                "aliquota" => 0,
                                "valor" => 0,
                            ];
                        }
                        
                        $itens[$key] = [
                            "codigo"                    => $store['mra_nf_prod_codigo'][$key],
                            "descricao"                 => $store['mra_nf_prod_nome'][$key],
                            "codigoBarras"              => $store['mra_nf_prod_codigo_barras'][$key],
                            "codigoBarrasTributavel"    => $store['mra_nf_prod_codigo_barras'][$key],
                            "ncm"                       => $store['mra_nf_prod_ncm'][$key],
                            "cfop"                      => $store['mra_nf_prod_cfop'][$key],
                            "valorUnitario" => [
                                "comercial"     => floatval($store['mra_nf_prod_valor_unit'][$key]),
                                "tributavel"    => floatval($store['mra_nf_prod_valor_unit'][$key]),
                            ],
                            "quantidade"    => [
                                "comercial"     => floatval($store['mra_nf_prod_qt'][$key]),
                                "tributavel"    => floatval($store['mra_nf_prod_qt'][$key]),
                            ],
                            "valor"         => isset($valor_itens) ? floatval($valor_itens) : 0,
                            "tributos"      => [
                                "icms"  => $icms,
                                "pis"   => [
                                    "cst"   => isset($store['mra_nf_prod_imp_cst_pis'][$key]) ? $store['mra_nf_prod_imp_cst_pis'][$key] : null,
                                    "baseCalculo"   => [
                                        "valor"         => 0,
                                        "quantidade"    => 0,
                                    ],
                                    "aliquota"  => 0,
                                    "valor"     => 0,
                                ],
                                "cofins"    => [
                                    "cst"   => isset($store['mra_nf_prod_imp_cst_cofins']) ? $store['mra_nf_prod_imp_cst_cofins'][$key] : null,
                                    "baseCalculo" => [
                                        "valor"     => 0,
                                    ],
                                    "aliquota"  => 0,
                                    "valor"     => 0,
                                ],
                            ],
                            "valorFrete"    => $store['mra_nf_prod_qt'][$key] * floatval($store['mra_nf_prod_valor_frete'][$key]),
                            "valorSeguro"   => $store['mra_nf_prod_qt'][$key] * floatval($store['mra_nf_prod_valor_seguro'][$key]),
                            "valorDesconto" => $store['mra_nf_prod_qt'][$key] * floatval($store['mra_nf_prod_valor_desconto'][$key]),
                        ];
                    }

                    // :: Dados da Nota
                    $data = [
                        [
                            "idIntegracao"      => str_pad($RNfNfeTs->id,6,"0",STR_PAD_LEFT).$sistema,
                            "finalidade"        => $store['nfe_finalidade'],
                            "natureza"          => isset($store['nfe_natureza_operacao']) ? $store['nfe_natureza_operacao'] : "OPERAÇÃO INTERNA",
                            "presencial"        => false,
                            "consumidorFinal"   => true,
                            'emitente'          => $emitente,
                            'destinatario'      => $destinatario,
                            'itens'             => $itens,
                            'transporte'        => $transportadora,
                            'total'             => [
                                'valorFrete'        => floatval($store['nfe_valor_frete']),
                                'valorSeguro'       => floatval($store['nfe_valor_seguro']),
                                'valorDesconto'     => floatval($store['nfe_valor_desconto']),
                                'valorNfe'          => floatval($store['nfe_total']),
                            ],
                            'pagamentos'        => [
                                [
                                    // 'aVista'  => $RNfNfeTs->OrcamentoPedido->condicao_de_pagamento == 2 ? true : false,
                                    'meio'    => $store['nfe_meio_de_pagamento'],
                                    'valor'   => floatval($store['nfe_total']),
                                ],
                            ],
                            'informacoesComplementares'             => isset($store['nfe_infor_adic_fisco']) ? $store['nfe_infor_adic_fisco'] : null,
                            'informacoesComplementaresContribuinte' => isset($store['nfe_infor_comple_int_contr']) ? $store['nfe_infor_comple_int_contr'] : null,
                            "responsavelAutorizado"                => [
                                "cpfCnpj"       => '13.937.073/0001-56',  // CNPJ da SEFAZ
                            ],
                        ],
                    ];

                    $tecnospeed = new RTecnoSpeed();
                    $tecnospeed_response = $tecnospeed->emitir($data, '/nfe');

                    // Sucesso
                    if (isset($tecnospeed_response['status']) && $tecnospeed_response['status'] == 200) {
                    
                        $RNfNfeTs->nf_response_id            = $tecnospeed_response['response']['documents'][0]['id'];
                        $RNfNfeTs->nf_response_idIntegracao  = $tecnospeed_response['response']['documents'][0]['idIntegracao'];
                        $RNfNfeTs->nf_response_protocol      = $tecnospeed_response['response']['protocol'];
                        $RNfNfeTs->nf_status                 = 'PENDENTE';
                        $RNfNfeTs->save();

                        $tecnospeed_response['nf_log']->response_mensagem = $tecnospeed_response['response']['message'];
                    }

                    // Erros
                    if ($tecnospeed_response['status'] == 400 ||
                        $tecnospeed_response['status'] == 401 ||
                        $tecnospeed_response['status'] == 'RequestException' &&
                        isset($tecnospeed_response['response']['error'])
                    ) {
                        $tecnospeed_response['nf_log']->response_mensagem = $tecnospeed_response['response']['error']['message'];

                        // Exibindo erros de campos
                        if (isset($tecnospeed_response['response']['error']['data']['fields'])) {
                            $error = '';
                            foreach ($tecnospeed_response['response']['error']['data']['fields'] as $key => $value) {
                                $error = $error.nl2br($key.' - '.$value."\n");
                            }
                            $tecnospeed_response['response']['error']['message'] = $error;
                        }
                       
                    }elseif ($tecnospeed_response['status'] == 409 && isset($tecnospeed_response['response']['error'])) {
                        
                        $tecnospeed_response['nf_log']->response_mensagem    = $tecnospeed_response['response']['error']['message'];
                        $id = isset($tecnospeed_response['response']['error']['data']['current']['id']) ? 
                            ' ID: '.$tecnospeed_response['response']['error']['data']['current']['id'] : '';

                        $tecnospeed_response['nf_log']->response_mensagem  = $tecnospeed_response['nf_log']->response_mensagem.$id;
                    }

                    $tecnospeed_response['nf_log']->nf_id         = $RNfNfeTs->id;
                    $tecnospeed_response['nf_log']->nf_empresa_id = 1;
                    $tecnospeed_response['nf_log']->nf_cliente_id = $RNfNfeTs->Cliente->id;
                    $tecnospeed_response['nf_log']->response_id   = isset($RNfNfeTs->nf_response_id) ? $RNfNfeTs->nf_response_id : null;
                    $tecnospeed_response['nf_log']->save();

                    $RNfNfeTs->save();
                    DB::commit();

                    if ($tecnospeed_response['status'] == 200) {
                        Session::flash('flash_success', "Nota Fiscal transmitida com sucesso. Acompanhe o andamento e status do seu processamento!");
                        return Redirect::to('/nota_fiscal/nfe/ts/'.$RNfNfeTs->id.'/edit');

                    }elseif ($tecnospeed_response['status'] == 400 ||
                        $tecnospeed_response['status'] == 401 ||
                        $tecnospeed_response['status'] == 'RequestException' &&
                        isset($tecnospeed_response['response']['error'])
                    ) {
                        Session::flash('flash_error', "Erro ao emitir nota fiscal: " .$tecnospeed_response['response']['error']['message']);
                        return back()->withInput();

                    }elseif ($tecnospeed_response['status'] == 409 && isset($tecnospeed_response['response']['error'])) {
                        Session::flash('flash_error', "Erro ao emitir nota fiscal: " .$tecnospeed_response['nf_log']->response_mensagem);
                        return back()->withInput();
                    }

                    if($user){
                        Logs::cadastrar($user->id, ($user->name . ' r_nf_nfe_ts|'.$acao.': transferiu ID: ' . $RNfNfeTs->id));
                    }

                }catch(\Exception $e){
                    Session::flash('flash_error', "Erro ao trasnferir r_nf_nfe_ts: " . $e->getMessage());
                }
            }

            // :: Edição
            if($acao=='edit' and !isset($store['transferir'])){
                Session::flash('flash_success', "Produto atualizado com sucesso!");
                if($user){
                    Logs::cadastrar($user->id, ($user->name . ' r_nf_nfe_ts|'.$acao.': atualizou ID: ' . $RNfNfeTs->id));
                }

            // :: Criação
            }elseif(!isset($store['transferir'])) {
                Session::flash('flash_success', "Produto cadastrado com sucesso!");
                if($user){
                    Logs::cadastrar($user->id, ($user->name . ' r_nf_nfe_ts|'.$acao.': cadastrou ID: ' . $RNfNfeTs->id));
                }
            }

        }catch(Exception $e) {
            DB::rollback();
            Log::info("Erro ao realizar atualização r_nf_nfe_ts|".(isset($acao)?$acao:'').": " . $e->getMessage());
            Session::flash('flash_error', "Erro ao realizar atualização");
            return back()->withInput()->with([],400);
        }

        if($redirect){
            return Redirect::to($redirect);
        }

        //if(isset($store['transferir'])){
            return Redirect::to('/nota_fiscal/nfe/ts/'.$RNfNfeTs->id.'/edit');
        //}else {
            //return Redirect::to('/nota_fiscal/mra_nf_ts_nf_e');
        //}
    }

    public function consultar($data, $user, $RNfNfeTs)
    {
        try {

            $data['response_id'] = $RNfNfeTs->nf_response_id;

            $tecnospeed = new RTecnoSpeed();
            $consulta_tecnospeed = $tecnospeed->consultar($data, '/nfe');

            // Criando log de consulta
            $consulta_tecnospeed['nf_log']->nf_id                = $RNfNfeTs->id;
            $consulta_tecnospeed['nf_log']->autor                = $user->id;
            $consulta_tecnospeed['nf_log']->nf_empresa_id        = 1;
            $consulta_tecnospeed['nf_log']->nf_cliente_id        = $RNfNfeTs->mra_nf_cliente_id;
            $consulta_tecnospeed['nf_log']->response             = json_encode($consulta_tecnospeed['response']);
            $consulta_tecnospeed['nf_log']->created_at           = date('Y-m-d H:i:s', strtotime("+1 second"));

            // Caso o retorno seja 200, atualiza os dados da nota fiscal
            if ($consulta_tecnospeed['status'] == 200) {

                // Formatando datas
                if (isset($consulta_tecnospeed['response'][0]['emissao'])) {
                    $data_emissao = str_replace('/', '-', $consulta_tecnospeed['response'][0]['emissao']);
                    $data_emissao = date('Y-m-d', strtotime($data_emissao));
                }
                if (isset($consulta_tecnospeed['response'][0]['dataAutorizacao'])) {
                    $data_autorizacao = str_replace('/', '-', $consulta_tecnospeed['response'][0]['dataAutorizacao']);
                    $data_autorizacao = date('Y-m-d', strtotime($data_autorizacao));
                }
                
                // Atualizando dados da nota fiscal
                $RNfNfeTs->nf_emissao                   = isset($data_emissao) ? $data_emissao : null;
                $RNfNfeTs->nf_status                    = isset($consulta_tecnospeed['response'][0]['status']) ? $consulta_tecnospeed['response'][0]['status'] : null;
                $RNfNfeTs->nf_numero                    = isset($consulta_tecnospeed['response'][0]['numero']) ? $consulta_tecnospeed['response'][0]['numero'] : null;
                $RNfNfeTs->nf_response_serie            = isset($consulta_tecnospeed['response'][0]['serie']) ? $consulta_tecnospeed['response'][0]['serie'] : null;
                $RNfNfeTs->nf_chave                     = isset($consulta_tecnospeed['response'][0]['chave']) ? $consulta_tecnospeed['response'][0]['chave'] : null;
                $RNfNfeTs->nf_response_protocol         = isset($consulta_tecnospeed['response'][0]['protocolo']) ? $consulta_tecnospeed['response'][0]['protocolo'] : null;
                $RNfNfeTs->nf_response_dataAutorizacao  = isset($data_autorizacao) ? $data_autorizacao : null;
                $RNfNfeTs->save();

                $consulta_tecnospeed['nf_log']->nf_idIntegracao      = $RNfNfeTs->nf_response_idIntegracao;
                $consulta_tecnospeed['nf_log']->response_id          = $RNfNfeTs->nf_response_id;
                $consulta_tecnospeed['nf_log']->nf_id                = $RNfNfeTs->id;
                $consulta_tecnospeed['nf_log']->response_status      = $RNfNfeTs->nf_status;
                $consulta_tecnospeed['nf_log']->response_mensagem    = $RNfNfeTs->nf_chave ? 'Autorizado o uso da NF-e' : 'Nota em processamento';

                if ($RNfNfeTs->nf_status == 'PROCESSANDO') {
                    $consulta_tecnospeed['nf_log']->response_mensagem = 'Nota(as) em processamento';
                }

                if (in_array($RNfNfeTs->nf_status, ['REJEITADO', 'DENEGADO'])) {
                    $consulta_tecnospeed['nf_log']->response_mensagem = isset($consulta_tecnospeed['response'][0]['mensagem']) ? $consulta_tecnospeed['response'][0]['mensagem'] : null;
                }
                $consulta_tecnospeed['nf_log']->save();

                // Baixando PDF
                if (isset($consulta_tecnospeed['response'][0]['pdf'])) {
                    $tecnospeed->baixarPDF($RNfNfeTs, '/nfe');
                }

                // Baixando XML
                if (isset($consulta_tecnospeed['response'][0]['xml']) && $RNfNfeTs->nf_status != 'CANCELADO') {
                    $tecnospeed->baixarXML($RNfNfeTs, '/nfe');
                }

                // Baixando XML de cancelamento
                if (isset($consulta_tecnospeed['response'][0]['xmlCancelamento'])) {
                    $tecnospeed->baixarXMLcancelamento($RNfNfeTs, '/nfe');
                }

            }elseif (isset($consulta_tecnospeed['response']['error'])) {

                $consulta_tecnospeed['nf_log']->response_mensagem  = isset($consulta_tecnospeed['response']['error']['message']) ? $consulta_tecnospeed['response']['error']['message'] : 'Erro ao consultar nota fiscal após emissão';
                $consulta_tecnospeed['nf_log']->save();

                Log::info('Erro ao consultar nota fiscal após emissão: '.json_encode($consulta_tecnospeed['response']));
                return [
                    'error'   => true,
                    'message' => $consulta_tecnospeed['nf_log']->response_mensagem,
                ];
            }

            // Log::info('Consultando nota fiscal após emissão: '.json_encode($consulta_tecnospeed['response']));
            return [
                'success' => true,
                'data'    => $RNfNfeTs,
            ];

        }catch(\Exception $e){
            Log::info("Erro ao consultar nfe: " . $e->getMessage());
            return [
                'exception' => true,
                'message'   => $e->getMessage(),
            ];
        }
    }

    public function cancelar($data, $RNfNfeTs, $user)
    {
        try {

            $tecnospeed = new RTecnoSpeed();
            $tecnospeed_response = $tecnospeed->cancelar($RNfNfeTs->nf_response_id, '/nfe');

            // Sucesso
            if(isset($tecnospeed_response['status']) && $tecnospeed_response['status'] == 200){
                
                $tecnospeed_response['nf_log']->response_mensagem    = isset($tecnospeed_response['response']['message']) ? $tecnospeed_response['response']['message'] : null;
                $tecnospeed_response['nf_log']->response             = json_encode($tecnospeed_response['nf_log']);
                $tecnospeed_response['nf_log']->response_status      = 'Cancelamento em processamento';
                $tecnospeed_response['nf_log']->save();

                $RNfNfeTs->nf_status = 'AGUARDANDO CANCELAMENTO';

                return [
                    'success' => true,
                    'data'    => $RNfNfeTs,
                ];
            }
            
            // Erro
            if (isset($tecnospeed_response['response']['error'])) {

                $tecnospeed_response['nf_log']->response_mensagem = isset($tecnospeed_response['response']['error']['message']) ? $tecnospeed_response['response']['error']['message'] : null;
                $tecnospeed_response['nf_log']->response          = json_encode($tecnospeed_response);

                if (isset($tecnospeed_response['response']['error']['data'])) {
                    $tecnospeed_response['nf_log']->response_status = $tecnospeed_response['response']['error']['data']['status'];
                    $RNfNfeTs->nf_status = $tecnospeed_response['response']['error']['data']['status'];
                }

                $tecnospeed_response['nf_log']->save();
                $RNfNfeTs->save();

                return [
                    'error'   => true,
                    'message' => isset($tecnospeed_response['response']['error']['message']) ? $tecnospeed_response['response']['error']['message'] : $tecnospeed_response['response']['message'],
                ];
            }

        }catch(\Exception $e){
            Log::info("Erro ao consultar nota_fiscal: " . $e->getMessage());
            return [
                'exception' => true,
                'message'   => $e->getMessage(),
            ];
        }
    }

    public static function baixarAnexos($tipo, $response_id)
    {
        try {
            
            $nfe_ts = RNfNfeTs::where('nf_response_id', $response_id)->first();
            $tecnospeed = new RTecnoSpeed();

            if (!$nfe_ts) {
                Session::flash('flash_error', "Desculpe, Nota Fiscal não encontrada!");
                return back()->withInput();
            }

            // Baixando PDF
            if ($tipo == 'pdf') {
                $tecnospeed_response = $tecnospeed->baixarPDF($nfe_ts, '/nfe');

                if ($tecnospeed_response['status'] == 200) {
                    Session::flash('flash_success', "PDF baixado com sucesso!");
                    return back()->withInput();

                }elseif ($tecnospeed_response['status'] == 202) {
                    Session::flash('flash_success', $tecnospeed_response['response']['message']);
                    return back()->withInput();

                }elseif ($tecnospeed_response['status'] == 401 || $tecnospeed_response['status'] == 404) {
                    Session::flash('flash_success', $tecnospeed_response['response']['error']['message']);
                    return back()->withInput();
                }
            }

            // Baixando XML
            if ($tipo == 'xml') {
                if ($nfe_ts->nf_status != 'CANCELADO') {
                    $tecnospeed_response = $tecnospeed->baixarXML($nfe_ts, '/nfe');

                    if ($tecnospeed_response['status'] == 200) {
                        Session::flash('flash_success', "XML baixado com sucesso!");
                        return back()->withInput();
    
                    }elseif ($tecnospeed_response['status'] == 202) {
                        Session::flash('flash_success', $tecnospeed_response['response']['message']);
                        return back()->withInput();
    
                    }elseif ($tecnospeed_response['status'] == 401 || $tecnospeed_response['status'] == 404) {
                        Session::flash('flash_success', $tecnospeed_response['response']['error']['message']);
                        return back()->withInput();
                    }
                }
    
                // Baixando XML de cancelamento
                if ($nfe_ts->nf_status == 'CANCELADO') {
                    $tecnospeed_response = $tecnospeed->baixarXMLcancelamento($nfe_ts, '/nfe');

                    if ($tecnospeed_response['status'] == 200) {
                        Session::flash('flash_success', "XML de Cancelamento baixado com sucesso!");
                        return back()->withInput();
    
                    }elseif ($tecnospeed_response['status'] == 202) {
                        Session::flash('flash_success', $tecnospeed_response['response']['message']);
                        return back()->withInput();
    
                    }elseif ($tecnospeed_response['status'] == 401 || $tecnospeed_response['status'] == 404) {
                        Session::flash('flash_success', $tecnospeed_response['response']['error']['message']);
                        return back()->withInput();
                    }
                }
            }
            
        }catch(\Exception $e){
            Log::info("Erro ao baixar anexos: " . $e->getMessage());
            Session::flash('flash_error', "Erro ao baixar anexos: ".$e->getMessage());
            return back()->withInput();
        }
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
        /* ... */
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
        try {

            $RNfNfeTs = RNfNfeTs::find($id);

            if (!$RNfNfeTs) {
                Session::flash('flash_error', "Desculpe, Nota Fiscal não encontrada!");
                return back()->withInput();
            }

            if ($RNfNfeTs->nf_response_id) {
                Session::flash('flash_error', "Desculpe, Notas fiscais já emitidas não podem ser excluídas!");
                return back()->withInput();
            }

            $RNfNfeTs->delete();
            Session::flash('flash_success', "Nota Fiscal excluída com sucesso!");
            return back()->withInput();

        }catch(Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            Session::flash('flash_error', "Erro ao realizar exclusão Nfe ".$e->getMessage());
            return back()->withInput()->with([],400);
        }
    }
}
