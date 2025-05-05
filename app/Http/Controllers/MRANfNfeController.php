<?php

namespace App\Http\Controllers;

use App\Models\MRANfNfeProdutosItens;
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

use \App\Models\MRANfNfe;
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

class MRANfNfeController extends Controller
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

            $MRANfNfe  = MRANfNfe::getAll(500,$request);

            if($user){
                Logs::cadastrar($user->id, ($user->name . ' visualizou a lista do módulo mra_nf_e'));
            }

            return view('mra_nf_e.index', [
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

            $MRANfNfe     = null;
            if(!is_null($id)){
                $MRANfNfe = MRANfNfe::find($id);
                if(!$MRANfNfe){
                    \Session::flash('flash_error', 'Registro não encontrado!');
                    return Redirect::to('mra_nota_fiscal/mra_nf_e');
                }
            }

            if(!Permissions::permissaoModerador($user) && $MRANfNfe->r_auth != 0 && $MRANfNfe->r_auth != $user->id){
                Session::flash('flash_error', 'Você não tem permissão para executar esta ação!');
                return Redirect::to('mra_nota_fiscal/mra_nf_e');
            }

            if($user){
                // Edição
                if(!is_null($MRANfNfe)){
                    Logs::cadastrar($user->id, ($user->name . ' visualizou a tela de edição ID #' . $id . ' do módulo mra_nf_e'));
                    // Criação
                }else {
                    Logs::cadastrar($user->id, ($user->name . ' visualizou a tela de criação do módulo mra_nf_e'));
                }
            }

            return view('mra_nf_e.add_edit', [
                'exibe_filtros'     => 0,
                'MRANfNfe'          => $MRANfNfe
            ]);

        }catch(\Exception $e){
            Log::error($e->getMessage());
            Session::flash('flash_error', "Ocorreu um erro! Tente novamente.");
            return Redirect::to('/');
        }
    }

    protected function validator(array $data, $id = ''){

        // ! Valida se possui ao menos um produto e se os campos obrigatórios  foram informados!
        $p_i_codigo_required = false;
        $p_i_nome_required   = false;
        $p_i_qt_required     = false;
        $p_i_valor_required  = false;
        if(isset($data['mra_nf_prod_id']) and count($data['mra_nf_prod_id'])){
            foreach($data['mra_nf_nf_e_prod_i_id'] as $K => $Prod_i){
                if(empty($data['mra_nf_prod_codigo'][$K])){     $p_i_codigo_required    = true; }
                if(empty($data['mra_nf_prod_nome'][$K])){       $p_i_nome_required      = true; }
                if(empty($data['mra_nf_prod_qt'][$K])){         $p_i_qt_required        = true; }
                if(empty($data['mra_nf_prod_valor_unit'][$K])){ $p_i_valor_required     = true; }
            }
        }

        return Validator::make($data, [
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
        ],[
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

            $MRANfNfe                                   = null;
            if(isset($store['id'])){
                $MRANfNfe                               = MRANfNfe::find($store['id']);
                if(!$MRANfNfe){
                    \Session::flash('flash_error', 'Registro não encontrado!');
                    return Redirect::to('mra_nota_fiscal/mra_nf_e');
                }
            }

            // :: Notazz - Cancelar Processamento | Só podem as ( autorizadas ou canceladas )
            if(isset($store['cancelar_nf'])){ return $this->destroy($MRANfNfe); }

            // :: Cancelar Processamento Forçado
            if(isset($store['cancelar_nf_forcado'])){ return $this->destroy($MRANfNfe); }

            DB::beginTransaction();

            // ! Caso Exista -> Validar Passagem de Status*
            if($MRANfNfe and $MRANfNfe->notazz_status == 'Autorizada'){
                \Session::flash('flash_success', 'A Nota Fiscal de Produto já foi autorizada!');
                return Redirect::to('mra_nota_fiscal/mra_nf_e/'.$MRANfNfe->id.'/edit');
            }
            // - #

            // :: Notazz - Consultar Processamento
            if(isset($store['consultar'])){
                $MRANotazz                                                      = new MRANotazz();
                $MRANotazz_send                                                 = [
                    "METHOD"                    => 'consult_nfe_55',
                    "DOCUMENT_ID"               => $MRANfNfe->notazz_id_documento,
                    "EXTERNAL_ID"               => ''
                ];

                $MRANotazz_resp                                                 = $MRANotazz->send($MRANotazz_send);

                if(isset($MRANotazz_resp['notazz_resp']['statusProcessamento'])){
                    // ! Sucesso
                    if($MRANotazz_resp['notazz_resp']['statusProcessamento'] == 'sucesso'){

                        $MRANotazz_resp['MRANfLog']->notazz_statusProcessamento     = $MRANotazz_resp['notazz_resp']['statusProcessamento'];
                        $MRANotazz_resp['MRANfLog']->notazz_codigoProcessamento     = $MRANotazz_resp['notazz_resp']['codigoProcessamento'];
                        $MRANotazz_resp['MRANfLog']->nf_numero                      = (isset($MRANotazz_resp['notazz_resp']['numero'])?$MRANotazz_resp['notazz_resp']['numero']:null);
                        $MRANotazz_resp['MRANfLog']->nf_chave                       = (isset($MRANotazz_resp['notazz_resp']['chave'])?$MRANotazz_resp['notazz_resp']['chave']:null);
                        $MRANotazz_resp['MRANfLog']->notazz_id_documento            = $MRANfNfe->notazz_id_documento;
                        $MRANotazz_resp['MRANfLog']->notazz_statusNota              = (isset($MRANotazz_resp['notazz_resp']['statusNota'])?$MRANotazz_resp['notazz_resp']['statusNota']:null);
                        $MRANotazz_resp['MRANfLog']->notazz_motivo                  = (isset($MRANotazz_resp['notazz_resp']['motivo'])?$MRANotazz_resp['notazz_resp']['motivo']:null);
                        $MRANotazz_resp['MRANfLog']->notazz_motivo                  = (isset($MRANotazz_resp['notazz_resp']['motivoStatus'])?$MRANotazz_resp['notazz_resp']['motivoStatus']:$MRANotazz_resp['MRANfLog']->notazz_motivo);

                        $MRANfNfe->notazz_status                                    = $MRANotazz_resp['MRANfLog']->notazz_statusNota;
                        $MRANfNfe->notazz_motivo                                    = $MRANotazz_resp['MRANfLog']->notazz_motivo;

                        // :: Autorizada
                        if(isset($MRANotazz_resp['notazz_resp']['statusNota']) and $MRANotazz_resp['notazz_resp']['statusNota'] == 'Autorizada'){

                            $MRANfNfe->nf_numero                                    = $MRANotazz_resp['MRANfLog']->nf_numero;
                            $MRANfNfe->nf_chave                                     = $MRANotazz_resp['MRANfLog']->nf_chave;
                            $MRANfNfe->nf_pdf                                       = (isset($MRANotazz_resp['notazz_resp']['pdf'])?$MRANotazz_resp['notazz_resp']['pdf']:null);
                            $MRANfNfe->nf_xml                                       = (isset($MRANotazz_resp['notazz_resp']['xml'])?$MRANotazz_resp['notazz_resp']['xml']:null);
                            $MRANfNfe->nf_pdf_prefeitura                            = (isset($MRANotazz_resp['notazz_resp']['linkPrefeitura'])?$MRANotazz_resp['notazz_resp']['linkPrefeitura']:null);
                            $MRANfNfe->nf_xml_cancelamento                          = (isset($MRANotazz_resp['notazz_resp']['xmlCancelamento'])?$MRANotazz_resp['notazz_resp']['xmlCancelamento']:null);
                            $MRANfNfe->nf_emissao                                   = (isset($MRANotazz_resp['notazz_resp']['emissao'])?$MRANotazz_resp['notazz_resp']['emissao']:null);

                            Session::flash('flash_success', "Nota Fiscal de Produto consultada com sucesso e autorizada!");
                        }elseif(isset($MRANotazz_resp['notazz_resp']['statusNota']) and $MRANotazz_resp['notazz_resp']['statusNota'] == 'AguardandoAutorizacao'){

                            Session::flash('flash_warning', "Nota Fiscal de Produto consultada com sucesso e aguardando autorização!");

                        }elseif(isset($MRANotazz_resp['notazz_resp']['statusNota']) and $MRANotazz_resp['notazz_resp']['statusNota'] == 'AguardandoCancelamento'){

                            Session::flash('flash_warning', "Nota Fiscal de Produto consultada com sucesso e aguardando cancelamento!");

                        }else {
                            if(isset($MRANotazz_resp['notazz_resp']['motivoStatus'])){
                                Session::flash('flash_error', "Erro! ".(!empty($MRANfNfe->notazz_motivo)?$MRANfNfe->notazz_motivo:'Tente novamente!'));
                            }else {
                                Session::flash('flash_success', "Nota Fiscal de Produto consultada com sucesso. Acompanhe o andamento e status do seu processamento!");
                            }
                        }
                    // ! Erro
                    }elseif($MRANotazz_resp['notazz_resp']['statusProcessamento'] == 'erro'){
                        $MRANotazz_resp['MRANfLog']->notazz_statusProcessamento = $MRANotazz_resp['notazz_resp']['statusProcessamento'];
                        $MRANotazz_resp['MRANfLog']->notazz_codigoProcessamento = $MRANotazz_resp['notazz_resp']['codigoProcessamento'];
                        //$MRANotazz_resp['MRANfLog']->notazz_statusNota          = (isset($MRANotazz_resp['notazz_resp']['statusNota'])?$MRANotazz_resp['notazz_resp']['statusNota']:null);
                        $MRANotazz_resp['MRANfLog']->notazz_motivo              = (isset($MRANotazz_resp['notazz_resp']['motivo'])?$MRANotazz_resp['notazz_resp']['motivo']:null);
                        //$MRANotazz_resp['MRANfLog']->notazz_motivo              = (isset($MRANotazz_resp['notazz_resp']['motivoStatus'])?$MRANotazz_resp['notazz_resp']['motivoStatus']:$MRANotazz_resp['MRANfLog']->notazz_motivo);

                        //$MRANfNfe->notazz_status                                = $MRANotazz_resp['MRANfLog']->notazz_statusNota;
                        $MRANfNfe->notazz_motivo                                = $MRANotazz_resp['MRANfLog']->notazz_motivo;

                        Session::flash('flash_error', "Erro! ".$MRANfNfe->notazz_motivo);
                    }

                    $MRANotazz_resp['MRANfLog']->mra_nf_cliente_id              = $MRANfNfe->mra_nf_cliente_id;
                    $MRANotazz_resp['MRANfLog']->mra_nf_nfs_e_id                = $MRANfNfe->id;
                    $MRANotazz_resp['MRANfLog']->save();

                    $MRANfNfe->notazz_statusProcessamento                       = $MRANotazz_resp['notazz_resp']['statusProcessamento'];
                    $MRANfNfe->notazz_codigoProcessamento                       = $MRANotazz_resp['notazz_resp']['codigoProcessamento'];
                    $MRANfNfe->notazz_resq                                      = $MRANotazz_resp['MRANfLog']->resq;
                    $MRANfNfe->notazz_resp                                      = $MRANotazz_resp['MRANfLog']->resp;

                    $MRANfNfe->save();
                    DB::commit();

                }

                if($user){
                    Logs::cadastrar($user->id, ($user->name . ' mra_nf_e|consult_nfe_55: consultou ID: ' . $MRANfNfe->id . '| DOCUMENT_ID: ' . $MRANfNfe->notazz_id_documento));
                }

                return Redirect::to('/mra_nota_fiscal/mra_nf_e/'.$MRANfNfe->id.'/edit');
            }
            // - #

            $acao                                       = 'edit';
            if(is_null($MRANfNfe)){
                $MRANfNfe                               = new MRANfNfe();
                $acao                                   = 'add';
            }

            $MRANfNfe->mra_nf_cfg_emp_id                = 1;
            $MRANfNfe->mra_nf_cliente_id                = (isset($store['mra_nf_cliente_id'])?$store['mra_nf_cliente_id']:null);
            $MRANfNfe->mra_nf_transp_id                 = (isset($store['mra_nf_transp_id'])?$store['mra_nf_transp_id']:null);
            $MRANfNfe->nfe_data_competencia             = (isset($store['nfe_data_competencia'])?\App\Helper\Helper::H_DataHora_ptBR_DB($store['nfe_data_competencia']):Carbon::now());
            $MRANfNfe->nfe_finalidade                   = (isset($store['nfe_finalidade'])?$store['nfe_finalidade']:null);
            $MRANfNfe->nfe_chave_referencia             = (isset($store['nfe_chave_referencia'])?$store['nfe_chave_referencia']:null);
            if(empty($MRANfNfe->nfe_finalidade) || $MRANfNfe->nfe_finalidade == 1){
                $MRANfNfe->nfe_chave_referencia         = null;
            }
            $MRANfNfe->nfe_natureza_operacao            = (isset($store['nfe_natureza_operacao'])?$store['nfe_natureza_operacao']:null);
            $MRANfNfe->nfe_tipo_operacao                = (isset($store['nfe_tipo_operacao'])?$store['nfe_tipo_operacao']:null);
            $MRANfNfe->nfe_valor_total                  = (isset($store['nfe_valor_total'])?\App\Helper\Helper::H_Decimal_ptBR_DB($store['nfe_valor_total']):null);
            $MRANfNfe->nfe_infor_adic_fisco             = (isset($store['nfe_infor_adic_fisco'])?$store['nfe_infor_adic_fisco']:null);
            $MRANfNfe->nfe_infor_comple_int_contr       = (isset($store['nfe_infor_comple_int_contr'])?$store['nfe_infor_comple_int_contr']:null);
            $MRANfNfe->emi_razao_social                 = (isset($store['emi_razao_social'])?$store['emi_razao_social']:null);
            $MRANfNfe->emi_cnpj                         = (isset($store['emi_cnpj'])?str_replace(['_'],'',$store['emi_cnpj']):null);
            $MRANfNfe->emi_inscricao_estadual           = (isset($store['emi_inscricao_estadual'])?$store['emi_inscricao_estadual']:null);
            $MRANfNfe->emi_inscricao_municipal          = (isset($store['emi_inscricao_municipal'])?$store['emi_inscricao_municipal']:null);
            $MRANfNfe->emi_telefone                     = (isset($store['emi_telefone'])?str_replace(['_'],'',$store['emi_telefone']):null);
            $MRANfNfe->emi_email                        = (isset($store['emi_email'])?$store['emi_email']:null);
            $MRANfNfe->emi_end_cep                      = (isset($store['emi_end_cep'])?str_replace(['_'],'',$store['emi_end_cep']):null);
            $MRANfNfe->emi_end_rua                      = (isset($store['emi_end_rua'])?$store['emi_end_rua']:null);
            $MRANfNfe->emi_end_numero                   = (isset($store['emi_end_numero'])?$store['emi_end_numero']:null);
            $MRANfNfe->emi_end_bairro                   = (isset($store['emi_end_bairro'])?$store['emi_end_bairro']:null);
            $MRANfNfe->emi_end_complemento              = (isset($store['emi_end_complemento'])?$store['emi_end_complemento']:null);
            $MRANfNfe->emi_end_estado                   = (isset($store['emi_end_estado'])?$store['emi_end_estado']:null);
            $MRANfNfe->emi_end_cidade                   = (isset($store['emi_end_cidade'])?$store['emi_end_cidade']:null);
            $MRANfNfe->nfe_cnae_fiscal                  = (isset($store['nfe_cnae_fiscal'])?$store['nfe_cnae_fiscal']:null);
            $MRANfNfe->nfe_cod_regime_tributario        = (isset($store['nfe_cod_regime_tributario'])?$store['nfe_cod_regime_tributario']:null);
            $MRANfNfe->des_nome_razao_social            = (isset($store['des_nome_razao_social'])?$store['des_nome_razao_social']:null);
            $MRANfNfe->des_tipo_pessoa                  = (isset($store['des_tipo_pessoa'])?$store['des_tipo_pessoa']:null);
            $MRANfNfe->des_cnpj                         = (isset($store['des_cnpj'])?str_replace(['_'],'',$store['des_cnpj']):null);
            $MRANfNfe->des_cpf                          = (isset($store['des_cpf'])?str_replace(['_'],'',$store['des_cpf']):null);
            $MRANfNfe->des_cnpj_inscricao_estadual      = (isset($store['des_cnpj_inscricao_estadual'])?$store['des_cnpj_inscricao_estadual']:null);
            $MRANfNfe->des_cnpj_inscricao_municipal     = (isset($store['des_cnpj_inscricao_municipal'])?$store['des_cnpj_inscricao_municipal']:null);
            // :: Física
            if($MRANfNfe->des_tipo_pessoa == 'F'){
                $MRANfNfe->des_cnpj                     = null;
                $MRANfNfe->des_cnpj_inscricao_estadual  = null;
                $MRANfNfe->des_cnpj_inscricao_municipal = null;

            // :: Jurídica
            }elseif($MRANfNfe->des_tipo_pessoa == 'J'){
                $MRANfNfe->des_cpf                      = null;

            // :: Estrangeiro
            }elseif($MRANfNfe->des_tipo_pessoa == 'E'){
                $MRANfNfe->des_cpf                      = null;
                $MRANfNfe->des_cnpj                     = null;
                $MRANfNfe->des_cnpj_inscricao_estadual  = null;
            }else {
                $MRANfNfe->des_cpf                      = null;
                $MRANfNfe->des_cnpj                     = null;
                $MRANfNfe->des_cnpj_inscricao_estadual  = null;
                $MRANfNfe->des_cnpj_inscricao_municipal = null;
            }
            $MRANfNfe->des_telefone                     = (isset($store['des_telefone'])?str_replace(['_'],'',$store['des_telefone']):null);
            $MRANfNfe->des_email                        = (isset($store['des_email'])?$store['des_email']:null);
            $MRANfNfe->des_enviar_nfe_email             = (isset($store['des_enviar_nfe_email'])?$store['des_enviar_nfe_email']:null);
            $MRANfNfe->des_end_cep                      = (isset($store['des_end_cep'])?str_replace(['_'],'',$store['des_end_cep']):null);
            $MRANfNfe->des_end_rua                      = (isset($store['des_end_rua'])?$store['des_end_rua']:null);
            $MRANfNfe->des_end_numero                   = (isset($store['des_end_numero'])?$store['des_end_numero']:null);
            $MRANfNfe->des_end_bairro                   = (isset($store['des_end_bairro'])?$store['des_end_bairro']:null);
            $MRANfNfe->des_end_complemento              = (isset($store['des_end_complemento'])?$store['des_end_complemento']:null);
            $MRANfNfe->des_end_estado                   = (isset($store['des_end_estado'])?$store['des_end_estado']:null);
            $MRANfNfe->des_end_cidade                   = (isset($store['des_end_cidade'])?$store['des_end_cidade']:null);
            $MRANfNfe->des_end_pais                     = (isset($store['des_end_pais'])?$store['des_end_pais']:null);

            if(ENV('MODULO_NF_PRODUTO_TRANSP_CLI')){
                $MRANfNfe->mra_nf_transp_id                 = (isset($store['mra_nf_transp_id'])?$store['mra_nf_transp_id']:null);
                $MRANfNfe->transp_nome_razao_social         = (isset($store['transp_nome_razao_social'])?$store['transp_nome_razao_social']:null);
                $MRANfNfe->transp_modalid_frete             = (isset($store['transp_modalid_frete'])?$store['transp_modalid_frete']:null);
                $MRANfNfe->transp_cnpj                      = (isset($store['transp_cnpj'])?str_replace(['_'],'',$store['transp_cnpj']):null);
                $MRANfNfe->transp_cpf                       = (isset($store['transp_cpf'])?str_replace(['_'],'',$store['transp_cpf']):null);
                $MRANfNfe->transp_inscricao_estadual        = (isset($store['transp_inscricao_estadual'])?$store['transp_inscricao_estadual']:null);
                $MRANfNfe->transp_cont_emails_nf            = (isset($store['transp_cont_emails_nf'])?$store['transp_cont_emails_nf']:null);
                $MRANfNfe->transp_end_cep                   = (isset($store['transp_end_cep'])?str_replace(['_'],'',$store['transp_end_cep']):null);
                $MRANfNfe->transp_end_rua                   = (isset($store['transp_end_rua'])?$store['transp_end_rua']:null);
                $MRANfNfe->transp_end_numero                = (isset($store['transp_end_numero'])?$store['transp_end_numero']:null);
                $MRANfNfe->transp_end_bairro                = (isset($store['transp_end_bairro'])?$store['transp_end_bairro']:null);
                $MRANfNfe->transp_end_estado                = (isset($store['transp_end_estado'])?$store['transp_end_estado']:null);
                $MRANfNfe->transp_end_cidade                = (isset($store['transp_end_cidade'])?$store['transp_end_cidade']:null);
                $MRANfNfe->transp_valor_frete               = (isset($store['transp_valor_frete'])?\App\Helper\Helper::H_Decimal_ptBR_DB($store['transp_valor_frete']):null);
                $MRANfNfe->transp_veiculo_placa             = (isset($store['transp_veiculo_placa'])?$store['transp_veiculo_placa']:null);
                $MRANfNfe->transp_veiculo_uf                = (isset($store['transp_veiculo_uf'])?$store['transp_veiculo_uf']:null);
                $MRANfNfe->transp_informar_volume           = (isset($store['transp_informar_volume'])?$store['transp_informar_volume']:null);
                if($MRANfNfe->transp_informar_volume){
                    $MRANfNfe->transp_iv_quantidade         = (isset($store['transp_iv_quantidade'])?$store['transp_iv_quantidade']:null);
                    $MRANfNfe->transp_iv_especie            = (isset($store['transp_iv_especie'])?$store['transp_iv_especie']:null);
                    $MRANfNfe->transp_iv_peso_liquido       = (isset($store['transp_iv_peso_liquido'])?\App\Helper\Helper::H_Decimal_ptBR_DB($store['transp_iv_peso_liquido']):null);
                    $MRANfNfe->transp_iv_peso_bruto         = (isset($store['transp_iv_peso_bruto'])?\App\Helper\Helper::H_Decimal_ptBR_DB($store['transp_iv_peso_bruto']):null);
                }else {
                    $MRANfNfe->transp_iv_quantidade         = null;
                    $MRANfNfe->transp_iv_especie            = null;
                    $MRANfNfe->transp_iv_peso_liquido       = null;
                    $MRANfNfe->transp_iv_peso_bruto         = null;
                }
            }

            //$MRANfNfe->nf_emissao                   = Carbon::now(); // Gerado pelo Notazz Automaticamente
            if($acao=='add'){
                $MRANfNfe->notazz_status                = 'Pendente';
            }
            //$MRANfNfe->notazz_id_externo                = (isset($store['notazz_id_externo'])?$store['notazz_id_externo']:null);
            $MRANfNfe->r_auth                           = $r_auth;
            $MRANfNfe->notazz_transmitir                = 1; // !! ATENÇÃO !!

            $MRANfNfe->save();

            // :: Produtos - Itens
            if(isset($store['mra_nf_prod_id']) and count($store['mra_nf_prod_id'])){
                // :: Exclusão
                //$implode = implode(',', $store['mra_nf_prod_id']);
                //print_r($implode);
                //print_r(array_values($store['mra_nf_prod_id'])); exit;
                $mra_nf_nf_e_prod_i_id = array_filter($store['mra_nf_nf_e_prod_i_id'],function($v,$k){ return ($v != ''); },ARRAY_FILTER_USE_BOTH);
                MRANfNfeProdutosItens::where('mra_nf_nf_e_id',$MRANfNfe->id)->whereNotIn('id', $mra_nf_nf_e_prod_i_id)->delete();
                // :: Lista Itens
                foreach($store['mra_nf_nf_e_prod_i_id'] as $K => $Prod_i){
                    if(!empty($store['mra_nf_nf_e_prod_i_id'][$K])){
                        $MRANfNfeProdutosItens                       = MRANfNfeProdutosItens::find($store['mra_nf_nf_e_prod_i_id'][$K]);
                        if(!$MRANfNfe){ continue; }
                    }else {
                        $MRANfNfeProdutosItens                       = new MRANfNfeProdutosItens();
                    }
                    $MRANfNfeProdutosItens->mra_nf_nf_e_id           = $MRANfNfe->id;
                    $MRANfNfeProdutosItens->mra_nf_prod_id           = (isset($store['mra_nf_prod_id'][$K])?$store['mra_nf_prod_id'][$K]:null);
                    $MRANfNfeProdutosItens->codigo                   = (isset($store['mra_nf_prod_codigo'][$K])?$store['mra_nf_prod_codigo'][$K]:null);
                    $MRANfNfeProdutosItens->nome                     = (isset($store['mra_nf_prod_nome'][$K])?$store['mra_nf_prod_nome'][$K]:null);
                    $MRANfNfeProdutosItens->quantidade               = (isset($store['mra_nf_prod_qt'][$K])?$store['mra_nf_prod_qt'][$K]:null);
                    $MRANfNfeProdutosItens->unidade_medida           = (isset($store['mra_nf_prod_umedida'][$K])?$store['mra_nf_prod_umedida'][$K]:null);
                    $MRANfNfeProdutosItens->valor_unitario           = (isset($store['mra_nf_prod_valor_unit'][$K])?\App\Helper\Helper::H_Decimal_ptBR_DB($store['mra_nf_prod_valor_unit'][$K]):null);
                    $MRANfNfeProdutosItens->valor_subtotal           = (isset($store['mra_nf_prod_valor_subtotal'][$K])?\App\Helper\Helper::H_Decimal_ptBR_DB($store['mra_nf_prod_valor_subtotal'][$K]):null);
                    $MRANfNfeProdutosItens->valor_desconto           = (isset($store['mra_nf_prod_valor_desconto'][$K])?\App\Helper\Helper::H_Decimal_ptBR_DB($store['mra_nf_prod_valor_desconto'][$K]):null);
                    $MRANfNfeProdutosItens->valor_frete              = (isset($store['mra_nf_prod_valor_frete'][$K])?\App\Helper\Helper::H_Decimal_ptBR_DB($store['mra_nf_prod_valor_frete'][$K]):null);
                    $MRANfNfeProdutosItens->valor_seguro             = (isset($store['mra_nf_prod_valor_seguro'][$K])?\App\Helper\Helper::H_Decimal_ptBR_DB($store['mra_nf_prod_valor_seguro'][$K]):null);
                    $MRANfNfeProdutosItens->valor_outras_despesas    = (isset($store['mra_nf_prod_valor_despesas'][$K])?\App\Helper\Helper::H_Decimal_ptBR_DB($store['mra_nf_prod_valor_despesas'][$K]):null);
                    $MRANfNfeProdutosItens->cfop                     = (isset($store['mra_nf_prod_cfop'][$K])?$store['mra_nf_prod_cfop'][$K]:null);
                    $MRANfNfeProdutosItens->ncm                      = (isset($store['mra_nf_prod_ncm'][$K])?$store['mra_nf_prod_ncm'][$K]:null);
                    $MRANfNfeProdutosItens->cest                     = (isset($store['mra_nf_prod_cest'][$K])?$store['mra_nf_prod_cest'][$K]:null);
                    $MRANfNfeProdutosItens->imp_cst_csosn_icms       = (isset($store['mra_nf_prod_imp_cst_csosn_icms'][$K])?$store['mra_nf_prod_imp_cst_csosn_icms'][$K]:null);
                    $MRANfNfeProdutosItens->imp_aliquota_icms        = (isset($store['mra_nf_prod_imp_aliquota_icms'][$K])?\App\Helper\Helper::H_Decimal_ptBR_DB($store['mra_nf_prod_imp_aliquota_icms'][$K]):null);
                    $MRANfNfeProdutosItens->imp_cst_ipi              = (isset($store['mra_nf_prod_imp_cst_ipi'][$K])?$store['mra_nf_prod_imp_cst_ipi'][$K]:null);
                    $MRANfNfeProdutosItens->imp_aliquota_ipi         = (isset($store['mra_nf_prod_imp_aliquota_ipi'][$K])?\App\Helper\Helper::H_Decimal_ptBR_DB($store['mra_nf_prod_imp_aliquota_ipi'][$K]):null);
                    $MRANfNfeProdutosItens->imp_cst_pis              = (isset($store['mra_nf_prod_imp_cst_pis'][$K])?$store['mra_nf_prod_imp_cst_pis'][$K]:null);
                    $MRANfNfeProdutosItens->imp_aliquota_pis         = (isset($store['mra_nf_prod_imp_aliquota_pis'][$K])?\App\Helper\Helper::H_Decimal_ptBR_DB($store['mra_nf_prod_imp_aliquota_pis'][$K]):null);
                    $MRANfNfeProdutosItens->imp_cst_cofins           = (isset($store['mra_nf_prod_imp_cst_cofins'][$K])?$store['mra_nf_prod_imp_cst_cofins'][$K]:null);
                    $MRANfNfeProdutosItens->imp_aliquota_cofins      = (isset($store['mra_nf_prod_imp_aliquota_cofins'][$K])?\App\Helper\Helper::H_Decimal_ptBR_DB($store['mra_nf_prod_imp_aliquota_cofins'][$K]):null);
                    //$MRANfNfeProdutosItens->imp_infor_adicionais     = (isset($store['xxxxxxx'][$K])?$store['xxxxxxx'][$K]:null);
                    $MRANfNfeProdutosItens->r_auth                   = $r_auth;
                    $MRANfNfeProdutosItens->save();
                }
            }

            $MRANfNfe->save();

            DB::commit();

            // :: Transmitir Nota Fiscal
            if(isset($store['transferir'])){
                try {

                    // :: CPF / CNPJ - Seleção / ! Tratamento
                    $DESTINATION_TAXID          = "";
                    if($MRANfNfe->des_tipo_pessoa=='F'){
                        $DESTINATION_TAXID      = str_replace(['.','-','_'],'',$MRANfNfe->des_cpf);
                    }elseif($MRANfNfe->des_tipo_pessoa=='J'){
                        $DESTINATION_TAXID      = str_replace(['.','-','/','_'],'',$MRANfNfe->des_cnpj);
                    }

                    // :: Emails - Send
                    $DESTINATION_EMAIL_SEND     = [];

                    // . Enviar E-mail - Transportadora
                    if(!empty($MRANfNfe->transp_cont_emails_nf)){
                        $transp_cont_emails_nf_exp = explode(',',$MRANfNfe->transp_cont_emails_nf);
                        if(count($transp_cont_emails_nf_exp)){
                            foreach($transp_cont_emails_nf_exp as $K => $TCEN){
                                $DESTINATION_EMAIL_SEND[$K+1]['EMAIL'] = $TCEN;
                            }
                        }
                    }

                    // . Enviar E-mail - Dados da Nota - Cliente/Tomador
                    if($MRANfNfe->des_enviar_nfe_email and !empty($MRANfNfe->des_email)){
                        $DESTINATION_EMAIL_SEND[count($DESTINATION_EMAIL_SEND)+1]['EMAIL'] = $MRANfNfe->des_email;
                    }

                    // . Valor Total do Produto Adicionados
                    $DOCUMENT_BASEVALUE             = 0;
                    if($MRANfNfe->MRANfNfeProdutosItens and count($MRANfNfe->MRANfNfeProdutosItens)){
                        foreach($MRANfNfe->MRANfNfeProdutosItens as $Item) {
                            $DOCUMENT_BASEVALUE    += ($Item->quantidade * $Item->valor_unitario);
                        }
                    }

                    // . Descrição da Nota ( Informações Adicionais de Interesse do Fisco | Informações Complementares de interesse do Contribuinte )
                    $DOCUMENT_DESCRIPTION           = '';
                    if(!empty($MRANfNfe->nfe_infor_adic_fisco)){
                        $DOCUMENT_DESCRIPTION      .= '-'.PHP_EOL;
                        $DOCUMENT_DESCRIPTION      .= $MRANfNfe->nfe_infor_adic_fisco.PHP_EOL.PHP_EOL;
                    }
                    if(!empty($MRANfNfe->nfe_infor_comple_int_contr)){
                        $DOCUMENT_DESCRIPTION      .= '-'.PHP_EOL;
                        $DOCUMENT_DESCRIPTION      .= $MRANfNfe->nfe_infor_comple_int_contr;
                    }

                    // . Produtos Itens da Nota
                    $DOCUMENT_PRODUCT               = [];
                    if($MRANfNfe->MRANfNfeProdutosItens and count($MRANfNfe->MRANfNfeProdutosItens)){
                        foreach($MRANfNfe->MRANfNfeProdutosItens as $K => $Item){
                            $DOCUMENT_PRODUCT[($K+1)]["DOCUMENT_PRODUCT_COD"]               = $Item->codigo;                    // ! Código Cliente
                            $DOCUMENT_PRODUCT[($K+1)]["DOCUMENT_PRODUCT_TAX_COD"]           = $MRANfNfe->id.'.'.$Item->codigo;  // ! Código ( NF.Produto ID + Código ID Item )
                            $DOCUMENT_PRODUCT[($K+1)]["DOCUMENT_PRODUCT_EAN"]               = $Item->codigo;                    // ! Código de Barras
                            $DOCUMENT_PRODUCT[($K+1)]["DOCUMENT_PRODUCT_NAME"]              = $Item->nome;
                            $DOCUMENT_PRODUCT[($K+1)]["DOCUMENT_PRODUCT_QTD"]               = $Item->quantidade;
                            $DOCUMENT_PRODUCT[($K+1)]["DOCUMENT_PRODUCT_UNITARY_VALUE"]     = (!empty($Item->valor_unitario)?$Item->valor_unitario:'0.00');
                            $DOCUMENT_PRODUCT[($K+1)]["DOCUMENT_PRODUCT_NCM"]               = $Item->ncm;
                            $DOCUMENT_PRODUCT[($K+1)]["DOCUMENT_PRODUCT_CEST"]              = $Item->cest;
                            $DOCUMENT_PRODUCT[($K+1)]["DOCUMENT_PRODUCT_CFOP"]              = $Item->cfop;
                            $DOCUMENT_PRODUCT[($K+1)]["DOCUMENT_PRODUCT_DISCOUNT"]          = (!empty($Item->valor_desconto)?$Item->valor_desconto:'0.00');
                            $DOCUMENT_PRODUCT[($K+1)]["DOCUMENT_PRODUCT_INSURANCE_VALUE"]   = (!empty($Item->valor_seguro)?$Item->valor_seguro:'0.00');
                            $DOCUMENT_PRODUCT[($K+1)]["DOCUMENT_PRODUCT_ICMS_CST"]          = $Item->imp_cst_csosn_icms;    // ! ICMS
                            $DOCUMENT_PRODUCT[($K+1)]["DOCUMENT_PRODUCT_IPI_CST"]           = $Item->imp_cst_ipi;           // ! IPI
                            $DOCUMENT_PRODUCT[($K+1)]["DOCUMENT_PRODUCT_PIS_CST"]           = $Item->imp_cst_pis;           // ! PIS
                            $DOCUMENT_PRODUCT[($K+1)]["DOCUMENT_PRODUCT_COFINS_CST"]        = $Item->imp_cst_cofins;        // ! COFINS
                            $DOCUMENT_PRODUCT[($K+1)]["DOCUMENT_PRODUCT_ICMS_ALIQUOTA"]     = (!empty($Item->imp_aliquota_icms)?$Item->imp_aliquota_icms:'0.00');           // ! ICMS
                            $DOCUMENT_PRODUCT[($K+1)]["DOCUMENT_PRODUCT_IPI_ALIQUOTA"]      = (!empty($Item->imp_aliquota_ipi)?$Item->imp_aliquota_ipi:'0.00');             // ! IPI
                            $DOCUMENT_PRODUCT[($K+1)]["DOCUMENT_PRODUCT_PIS_ALIQUOTA"]      = (!empty($Item->imp_aliquota_pis)?$Item->imp_aliquota_pis:'0.00');             // ! PIS
                            $DOCUMENT_PRODUCT[($K+1)]["DOCUMENT_PRODUCT_COFINS_ALIQUOTA"]   = (!empty($Item->imp_aliquota_cofins)?$Item->imp_aliquota_cofins:'0.00');       // ! COFINS
                            $DOCUMENT_PRODUCT[($K+1)]["DOCUMENT_PRODUCT_OTHER_EXPENSES"]    = (!empty($Item->valor_outras_despesas)?$Item->valor_outras_despesas:'0.00');
                        }
                    }

                    // . Transportadora
                    $DOCUMENT_FRETE_TRANSPORTADORA_TAXID          = "";
                    if(!empty($MRANfNfe->transp_cnpj)){
                        $DOCUMENT_FRETE_TRANSPORTADORA_TAXID      = str_replace(['.','-','_'],'',$MRANfNfe->transp_cnpj);
                    }elseif(!empty($MRANfNfe->transp_cpf)){
                        $DOCUMENT_FRETE_TRANSPORTADORA_TAXID      = str_replace(['.','-','/','_'],'',$MRANfNfe->transp_cpf);
                    }

                    $MRANotazz           = new MRANotazz();
                    $MRANotazz_send      = [
                        "METHOD"                                                => (!empty($MRANfNfe->notazz_id_documento)?"update_nfe_55":"create_nfe_55"),
                        "DOCUMENT_ID"                                           => (!empty($MRANfNfe->notazz_id_documento)?$MRANfNfe->notazz_id_documento:""),
                        "DESTINATION_NAME"                                      => $MRANfNfe->des_nome_razao_social,
                        "DESTINATION_TAXID"                                     => $DESTINATION_TAXID,
                        "DESTINATION_IE"                                        => $MRANfNfe->des_cnpj_inscricao_estadual,
                        "DESTINATION_IM"                                        => $MRANfNfe->des_cnpj_inscricao_municipal,
                        "DESTINATION_TAXTYPE"                                   => $MRANfNfe->des_tipo_pessoa,
                        "DESTINATION_STREET"                                    => $MRANfNfe->des_end_rua,
                        "DESTINATION_NUMBER"                                    => ((empty($MRANfNfe->des_end_numero)||$MRANfNfe->des_end_numero=='0')?'S/N':$MRANfNfe->des_end_numero),
                        "DESTINATION_COMPLEMENT"                                => $MRANfNfe->des_end_complemento,
                        "DESTINATION_DISTRICT"                                  => $MRANfNfe->des_end_bairro,
                        "DESTINATION_CITY"                                      => $MRANfNfe->des_end_cidade,
                        "DESTINATION_UF"                                        => $MRANfNfe->des_end_estado,
                        "DESTINATION_ZIPCODE"                                   => str_replace(['-',' ','_'],'',$MRANfNfe->des_end_cep),
                        "DESTINATION_PHONE"                                     => str_replace(['(',')','-',' ','_'],'',$MRANfNfe->des_telefone),
                        "DESTINATION_EMAIL"                                     => $MRANfNfe->des_email,
                        "DESTINATION_EMAIL_SEND"                                => $DESTINATION_EMAIL_SEND,
                        "DOCUMENT_BASEVALUE"                                    => $DOCUMENT_BASEVALUE, // ! Valor Total Produto
                        "DOCUMENT_DESCRIPTION"                                  => $DOCUMENT_DESCRIPTION,
                        "DOCUMENT_CNAE"                                         => $MRANfNfe->nfe_cnae_fiscal,
                        "DOCUMENT_GOAL"                                         => $MRANfNfe->nfe_finalidade,
                        //"DOCUMENT_PAYMENT_FORM_INDICATOR"                       => $MRANfNfe->xxxxxxxxxxxxx,
                        //"DOCUMENT_INTERMEDIARY_TAXID"                           => $MRANfNfe->xxxxxxxxxxxxx,
                        //"DOCUMENT_INTERMEDIARY_NAME"                            => $MRANfNfe->xxxxxxxxxxxxx,
                        "DOCUMENT_OPERATION_TYPE"                               => $MRANfNfe->nfe_tipo_operacao,
                        "DOCUMENT_REFERENCED"                                   => $MRANfNfe->nfe_chave_referencia,
                        "DOCUMENT_NATURE_OPERATION"                             => $MRANfNfe->nfe_natureza_operacao,
                        "DOCUMENT_PRODUCT"                                      => $DOCUMENT_PRODUCT,
                        "DOCUMENT_DUPLICATE"                                    => [],
                        "DOCUMENT_FRETE"                                        => [
                            "DOCUMENT_FRETE_MOD"            => $MRANfNfe->transp_modalid_frete,
                            "DOCUMENT_FRETE_VALUE"          => (!empty($MRANfNfe->transp_valor_frete)?$MRANfNfe->transp_valor_frete:'0.00'),
                            "DOCUMENT_FRETE_TRANSPORTADORA" => [
                                "DOCUMENT_FRETE_TRANSPORTADORA_NAME"        => $MRANfNfe->transp_nome_razao_social,
                                "DOCUMENT_FRETE_TRANSPORTADORA_TAXID"       => $DOCUMENT_FRETE_TRANSPORTADORA_TAXID,
                                "DOCUMENT_FRETE_TRANSPORTADORA_IE"          => $MRANfNfe->transp_inscricao_estadual,
                                "DOCUMENT_FRETE_TRANSPORTADORA_STREET"      => $MRANfNfe->transp_end_rua,
                                "DOCUMENT_FRETE_TRANSPORTADORA_NUMBER"      => $MRANfNfe->transp_end_numero,
                                "DOCUMENT_FRETE_TRANSPORTADORA_DISTRICT"    => $MRANfNfe->transp_end_bairro,
                                "DOCUMENT_FRETE_TRANSPORTADORA_CITY"        => $MRANfNfe->transp_end_cidade,
                                "DOCUMENT_FRETE_TRANSPORTADORA_UF"          => $MRANfNfe->transp_end_estado,
                            ],
                            "DOCUMENT_FRETE_VEICULO"        => [
                                "DOCUMENT_FRETE_VEICULO_PLACA"              => str_replace(['.','-','/','_',' '],'',$MRANfNfe->transp_veiculo_placa),
                                "DOCUMENT_FRETE_VEICULO_UF"                 => $MRANfNfe->transp_veiculo_uf,
                            ],
                            "DOCUMENT_FRETE_VOLUMES"        => [
                                "DOCUMENT_FRETE_VOLUMES_QTD"                => $MRANfNfe->transp_iv_quantidade,
                                "DOCUMENT_FRETE_VOLUMES_SPECIES"            => $MRANfNfe->transp_iv_especie,
                                "DOCUMENT_FRETE_VOLUMES_NET_WEIGHT"         => $MRANfNfe->transp_iv_peso_liquido,
                                "DOCUMENT_FRETE_VOLUMES_GROSS_WEIGHT"       => $MRANfNfe->transp_iv_peso_bruto
                            ],
                        ],
                        "EXTERNAL_ID"           => "",
                        "SALE_ID"               => "",
                        "DOCUMENT_ISSUE_DATE"   => "", // ! Automático
                        "REQUEST_ORIGIN"        => "Rxxx-APPS",
                        "TRANSMITIR"            => "1" // !! ATENÇÃO !!
                    ];
                    //print_r($MRANotazz_send); exit; // ### DEBUG ###

                    $MRANotazz_resp                                                 = $MRANotazz->send($MRANotazz_send);

                    if(isset($MRANotazz_resp['notazz_resp']['statusProcessamento'])){
                        // ! Sucesso
                        if($MRANotazz_resp['notazz_resp']['statusProcessamento'] == 'sucesso'){

                            $MRANotazz_resp['MRANfLog']->notazz_statusProcessamento = $MRANotazz_resp['notazz_resp']['statusProcessamento'];
                            $MRANotazz_resp['MRANfLog']->notazz_codigoProcessamento = $MRANotazz_resp['notazz_resp']['codigoProcessamento'];

                            //if(isset($MRANotazz_resp['notazz_resp']['id'])){

                            //}

                            Session::flash('flash_success', "Nota Fiscal de Produto transferida com sucesso. Acompanhe o andamento e status do seu processamento!");
                        // ! Erro
                        }elseif($MRANotazz_resp['notazz_resp']['statusProcessamento'] == 'erro'){
                            $MRANotazz_resp['MRANfLog']->notazz_statusProcessamento = $MRANotazz_resp['notazz_resp']['statusProcessamento'];
                            $MRANotazz_resp['MRANfLog']->notazz_codigoProcessamento = $MRANotazz_resp['notazz_resp']['codigoProcessamento'];
                            $MRANotazz_resp['MRANfLog']->notazz_motivo              = $MRANotazz_resp['notazz_resp']['motivo'];

                            $MRANfNfe->notazz_motivo                                = $MRANotazz_resp['notazz_resp']['motivo'];

                            Session::flash('flash_error', "Erro! ".$MRANfNfe->notazz_motivo);
                        }

                        $MRANotazz_resp['MRANfLog']->notazz_id_documento            = (isset($MRANotazz_resp['notazz_resp']['id'])?$MRANotazz_resp['notazz_resp']['id']:$MRANfNfe->notazz_id_documento);
                        $MRANfNfe->notazz_id_documento                              = (isset($MRANotazz_resp['notazz_resp']['id'])?$MRANotazz_resp['notazz_resp']['id']:$MRANfNfe->notazz_id_documento);

                        $MRANotazz_resp['MRANfLog']->mra_nf_cliente_id              = $MRANfNfe->mra_nf_cliente_id;
                        $MRANotazz_resp['MRANfLog']->mra_nf_nf_e_id                 = $MRANfNfe->id;
                        $MRANotazz_resp['MRANfLog']->save();

                        $MRANfNfe->notazz_statusProcessamento                       = $MRANotazz_resp['notazz_resp']['statusProcessamento'];
                        $MRANfNfe->notazz_codigoProcessamento                       = $MRANotazz_resp['notazz_resp']['codigoProcessamento'];
                        $MRANfNfe->notazz_resq                                      = $MRANotazz_resp['MRANfLog']->resq;
                        $MRANfNfe->notazz_resp                                      = $MRANotazz_resp['MRANfLog']->resp;

                        if($MRANfNfe->notazz_codigoProcessamento == '000'){
                            $MRANfNfe->notazz_status                                = 'AguardandoAutorizacao';
                        }

                        $MRANfNfe->save();
                        DB::commit();

                    }
                    //print_r($MRANotazz_resp); exit;
                    // - #

                    if($user){
                        Logs::cadastrar($user->id, ($user->name . ' mra_nf_e|'.$acao.': transferiu ID: ' . $MRANfNfe->id));
                    }

                }catch(\Exception $e){
                    Session::flash('flash_error', "Erro ao trasnferir mra_nf_e: " . $e->getMessage());
                }
            }

            // :: Edição
            if($acao=='edit' and !isset($store['transferir'])){
                Session::flash('flash_success', "Produto atualizado com sucesso!");
                if($user){
                    Logs::cadastrar($user->id, ($user->name . ' mra_nf_e|'.$acao.': atualizou ID: ' . $MRANfNfe->id));
                }

            // :: Criação
            }elseif(!isset($store['transferir'])) {
                Session::flash('flash_success', "Produto cadastrado com sucesso!");
                if($user){
                    Logs::cadastrar($user->id, ($user->name . ' mra_nf_e|'.$acao.': cadastrou ID: ' . $MRANfNfe->id));
                }
            }

        }catch(Exception $e) {
            DB::rollback();
            Log::info("Erro ao realizar atualização mra_nf_e|".(isset($acao)?$acao:'').": " . $e->getMessage());
            Session::flash('flash_error', "Erro ao realizar atualização");
            return back()->withInput()->with([],400);
        }

        if($redirect){
            return Redirect::to($redirect);
        }

        //if(isset($store['transferir'])){
            return Redirect::to('/mra_nota_fiscal/mra_nf_e/'.$MRANfNfe->id.'/edit');
        //}else {
            //return Redirect::to('/mra_nota_fiscal/mra_nf_e');
        //}
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

    public function destroy($id_object)
    {
        DB::beginTransaction();

        try {

            $request                        = Request::capture();
            $store                          = $request->all();

            if(is_object($id_object)){
                $MRANfNfe                   = $id_object;
            }else {
                $MRANfNfe                   = MRANfNfe::find($id_object);
            }
            $id                             = $MRANfNfe->id;

            // ! Se não existe
            if(!$MRANfNfe){
                \Session::flash('flash_error', 'Registro não encontrado!');
                return Redirect::to('mra_nota_fiscal/mra_nf_e');
            }

            // ! Cancelar Nota Fiscal - Forçado
            if(isset($store['cancelar_nf_forcado'])) {
                $MRANfNfe->notazz_status           = 'Cancelada';
                $MRANfNfe->notazz_status_forcado   = 1;
                $MRANfNfe->save();
                DB::commit();
                Session::flash('flash_success', "Nota Fiscal de Produto foi cancelada com sucesso dentro do sistema!");
                return Redirect::to('/mra_nota_fiscal/mra_nf_e/'.$MRANfNfe->id.'/edit');
            }
            // - #

            // ! Cancelar Nota Fiscal
            //if(isset($store['cancelar_nf'])) { $cancelar_nf = true; }
            if(isset($store['cancelar_nf']) and in_array($MRANfNfe->notazz_status,['Autorizada'])) {
                $MRANotazz                                                      = new MRANotazz();
                $MRANotazz_send                                                 = [
                    "METHOD"                    => 'cancel_nfse',
                    "DOCUMENT_ID"               => $MRANfNfe->notazz_id_documento,
                    "EXTERNAL_ID"               => ''
                ];
                //print_r($MRANotazz_send);

                $MRANotazz_resp                                                 = $MRANotazz->send($MRANotazz_send);
                if(isset($MRANotazz_resp['notazz_resp']['statusProcessamento'])){
                    // ! Sucesso
                    if($MRANotazz_resp['notazz_resp']['statusProcessamento'] == 'sucesso'){
                        $MRANotazz_resp['MRANfLog']->notazz_statusProcessamento = $MRANotazz_resp['notazz_resp']['statusProcessamento'];
                        $MRANotazz_resp['MRANfLog']->notazz_codigoProcessamento = $MRANotazz_resp['notazz_resp']['codigoProcessamento'];
                        $MRANotazz_resp['MRANfLog']->notazz_statusNota          = 'EmProcessoDeCancelamento';
                        $MRANfNfe->notazz_status                                = $MRANotazz_resp['MRANfLog']->notazz_statusNota;

                        Session::flash('flash_success', "Nota Fiscal de Produto enviada para cancelamento com sucesso. Acompanhe o andamento e status do seu processamento!");

                    // ! Erro
                    }elseif($MRANotazz_resp['notazz_resp']['statusProcessamento'] == 'erro'){
                        $MRANotazz_resp['MRANfLog']->notazz_statusProcessamento = $MRANotazz_resp['notazz_resp']['statusProcessamento'];
                        $MRANotazz_resp['MRANfLog']->notazz_codigoProcessamento = $MRANotazz_resp['notazz_resp']['codigoProcessamento'];
                        $MRANotazz_resp['MRANfLog']->notazz_motivo              = (isset($MRANotazz_resp['notazz_resp']['motivo'])?$MRANotazz_resp['notazz_resp']['motivo']:null);

                        $MRANfNfe->notazz_motivo                                = $MRANotazz_resp['MRANfLog']->notazz_motivo;

                        Session::flash('flash_error', "Erro! ".$MRANfNfe->notazz_motivo);

                    }

                    $MRANotazz_resp['MRANfLog']->mra_nf_cliente_id              = $MRANfNfe->mra_nf_cliente_id;
                    $MRANotazz_resp['MRANfLog']->mra_nf_nfs_e_id                = $MRANfNfe->id;
                    $MRANotazz_resp['MRANfLog']->save();

                    $MRANfNfe->notazz_statusProcessamento                       = $MRANotazz_resp['notazz_resp']['statusProcessamento'];
                    $MRANfNfe->notazz_codigoProcessamento                       = $MRANotazz_resp['notazz_resp']['codigoProcessamento'];
                    $MRANfNfe->notazz_resq                                      = $MRANotazz_resp['MRANfLog']->resq;
                    $MRANfNfe->notazz_resp                                      = $MRANotazz_resp['MRANfLog']->resp;

                    $MRANfNfe->save();
                    DB::commit();
                }

                return Redirect::to('/mra_nota_fiscal/mra_nf_e/'.$MRANfNfe->id.'/edit');
            }

            // ! Verifica se já gerou a "Nota Fiscal" e fez a Transferência, evitando a remoção
            if(!empty($MRANfNfe->notazz_id_documento)){
                Session::flash('flash_error', "Erro: Para realizar a exclusão a Nota Fiscal deve ser cancelada* do processamento!");
                return back();
            }
            // - #

            DB::rollBack();

            return $this->controllerRepository::destroy(new MRANfNfe(), $id, 'mra_nota_fiscal/mra_nf_e');

        }catch(Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            Session::flash('flash_error', "Erro ao realizar exclusão/cancelamento mra_nf_e: " . $e->getMessage());
            return back()->withInput()->with([],400);
        }
    }
}
