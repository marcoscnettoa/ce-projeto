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
use Carbon\Carbon;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

use \App\Http\Controllers\MRA\MRANotazz;
use \App\Models\MRANfNfse;
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

class MRANfNfseController extends Controller
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

            $MRANfNfse  = MRANfNfse::getAll(500,$request);

            if($user){
                Logs::cadastrar($user->id, ($user->name . ' visualizou a lista do módulo mra_nfs_e'));
            }

            return view('mra_nfs_e.index', [
                'exibe_filtros'     => 0,
                'MRANfNfse'         => $MRANfNfse
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

            $MRANfNfse     = null;
            if(!is_null($id)){
                $MRANfNfse = MRANfNfse::find($id);
                if(!$MRANfNfse){
                    \Session::flash('flash_error', 'Registro não encontrado!');
                    return Redirect::to('mra_nota_fiscal/mra_nfs_e');
                }
            }

            if(!Permissions::permissaoModerador($user) && $MRANfNfse->r_auth != 0 && $MRANfNfse->r_auth != $user->id){
                Session::flash('flash_error', 'Você não tem permissão para executar esta ação!');
                return Redirect::to('mra_nota_fiscal/mra_nfs_e');
            }

            if($user){
                // Edição
                if(!is_null($MRANfNfse)){
                    Logs::cadastrar($user->id, ($user->name . ' visualizou a tela de edição ID #' . $id . ' do módulo mra_nfs_e'));
                    // Criação
                }else {
                    Logs::cadastrar($user->id, ($user->name . ' visualizou a tela de criação do módulo mra_nfs_e'));
                }
            }

            return view('mra_nfs_e.add_edit', [
                'exibe_filtros'     => 0,
                'MRANfNfse'     => $MRANfNfse
            ]);

        }catch(\Exception $e){
            Log::error($e->getMessage());
            Session::flash('flash_error', "Ocorreu um erro! Tente novamente.");
            return Redirect::to('/');
        }
    }

    protected function validator(array $data, $id = ''){
        return Validator::make($data, [
            'tomador'               =>  'required',
            'tomador_pessoa'        =>  ((isset($data['tomador']) and $data['tomador'])?'required':''),
            'tomador_nome'          =>  ((isset($data['tomador']) and $data['tomador'])?'required':''),
            'tomador_cpf'           =>  ((isset($data['tomador']) and $data['tomador'] and
                                          isset($data['tomador_pessoa']) and $data['tomador_pessoa']=='F')?'required':''),
            'tomador_cnpj'          =>  ((isset($data['tomador']) and $data['tomador'] and
                                          isset($data['tomador_pessoa']) and $data['tomador_pessoa']=='J')?'required':''),
            'tomador_end_cep'       =>  ((isset($data['tomador']) and $data['tomador'])?'required':''),
            'tomador_end_rua'       =>  ((isset($data['tomador']) and $data['tomador'])?'required':''),
            'tomador_end_numero'    =>  ((isset($data['tomador']) and $data['tomador'])?'required':''),
            'tomador_end_bairro'    =>  ((isset($data['tomador']) and $data['tomador'])?'required':''),
            'tomador_end_estado'    =>  ((isset($data['tomador']) and $data['tomador'])?'required':''),
            'tomador_end_cidade'    =>  ((isset($data['tomador']) and $data['tomador'])?'required':''),
            'tomador_end_pais'      =>  ((isset($data['tomador']) and $data['tomador'])?'required':''),
            'cfg_valor_nota'        =>  'required',
            'cfg_descricao_nota'    =>  'required'
        ],[
            'tomador'               => 'O campo "NFS-e com Tomador" é obrigatório.',
            'tomador_pessoa'        => 'O campo "Tipo de Pessoa" é obrigatório.',
            'tomador_nome'          => 'O campo "Nome do Tomador" é obrigatório.',
            'tomador_cpf'           => 'O campo "CPF" é obrigatório.',
            'tomador_cnpj'          => 'O campo "CNPJ" é obrigatório.',
            'tomador_end_cep'       => 'O campo "CEP" é obrigatório.',
            'tomador_end_rua'       => 'O campo "Logradouro / Rua" é obrigatório.',
            'tomador_end_numero'    => 'O campo "Número" é obrigatório.',
            'tomador_end_bairro'    => 'O campo "Bairro" é obrigatório.',
            'tomador_end_estado'    => 'O campo "Estado" é obrigatório.',
            'tomador_end_cidade'    => 'O campo "Cidade" é obrigatório.',
            'tomador_end_pais'      => 'O campo "País" é obrigatório.',
            'cfg_valor_nota'        => 'O campo "Valor da Nota" é obrigatório.',
            'cfg_descricao_nota'    => 'O campo "Descrição da Nota" é obrigatório.'
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

            $MRANfNfse                                      = null;
            if(isset($store['id'])){
                $MRANfNfse                                  = MRANfNfse::find($store['id']);
                if(!$MRANfNfse){
                    \Session::flash('flash_error', 'Registro não encontrado!');
                    return Redirect::to('mra_nota_fiscal/mra_nfs_e');
                }
            }

            // :: Notazz - Cancelar Processamento | Só podem as ( autorizadas ou canceladas )
            if(isset($store['cancelar_nf'])){ return $this->destroy($MRANfNfse); }

            // :: Cancelar Processamento Forçado
            if(isset($store['cancelar_nf_forcado'])){ return $this->destroy($MRANfNfse); }

            DB::beginTransaction();

            // ! Caso Exista -> Validar Passagem de Status*
            if($MRANfNfse and $MRANfNfse->notazz_status == 'Autorizada'){
                \Session::flash('flash_success', 'A Nota Fiscal de Serviço já foi autorizada!');
                return Redirect::to('mra_nota_fiscal/mra_nfs_e/'.$MRANfNfse->id.'/edit');
            }
            // - #

            // :: Notazz - Consultar Processamento
            if(isset($store['consultar'])){
                $MRANotazz                                                      = new MRANotazz();
                $MRANotazz_send                                                 = [
                    "METHOD"                    => 'consult_nfse',
                    "DOCUMENT_ID"               => $MRANfNfse->notazz_id_documento,
                    "EXTERNAL_ID"               => ''
                ];

                $MRANotazz_resp                                                 = $MRANotazz->send($MRANotazz_send);

                if(isset($MRANotazz_resp['notazz_resp']['statusProcessamento'])){
                    // ! Sucesso
                    if($MRANotazz_resp['notazz_resp']['statusProcessamento'] == 'sucesso'){

                        $MRANotazz_resp['MRANfLog']->notazz_statusProcessamento     = $MRANotazz_resp['notazz_resp']['statusProcessamento'];
                        $MRANotazz_resp['MRANfLog']->notazz_codigoProcessamento     = $MRANotazz_resp['notazz_resp']['codigoProcessamento'];
                        $MRANotazz_resp['MRANfLog']->nf_codigoVerificacao           = (isset($MRANotazz_resp['notazz_resp']['codigoVerificacao'])?$MRANotazz_resp['notazz_resp']['codigoVerificacao']:null);
                        $MRANotazz_resp['MRANfLog']->nf_numero                      = (isset($MRANotazz_resp['notazz_resp']['numero'])?$MRANotazz_resp['notazz_resp']['numero']:null);
                        $MRANotazz_resp['MRANfLog']->notazz_id_documento            = $MRANfNfse->notazz_id_documento;
                        $MRANotazz_resp['MRANfLog']->notazz_statusNota              = (isset($MRANotazz_resp['notazz_resp']['statusNota'])?$MRANotazz_resp['notazz_resp']['statusNota']:null);
                        $MRANotazz_resp['MRANfLog']->notazz_motivo                  = (isset($MRANotazz_resp['notazz_resp']['motivo'])?$MRANotazz_resp['notazz_resp']['motivo']:null);
                        $MRANotazz_resp['MRANfLog']->notazz_motivo                  = (isset($MRANotazz_resp['notazz_resp']['motivoStatus'])?$MRANotazz_resp['notazz_resp']['motivoStatus']:$MRANotazz_resp['MRANfLog']->notazz_motivo);

                        $MRANfNfse->notazz_status                                   = $MRANotazz_resp['MRANfLog']->notazz_statusNota;
                        $MRANfNfse->notazz_motivo                                   = $MRANotazz_resp['MRANfLog']->notazz_motivo;

                        // :: Autorizada
                        if(isset($MRANotazz_resp['notazz_resp']['statusNota']) and $MRANotazz_resp['notazz_resp']['statusNota'] == 'Autorizada'){

                            $MRANfNfse->nf_numero                                   = $MRANotazz_resp['MRANfLog']->nf_numero;
                            $MRANfNfse->nf_codigoVerificacao                        = $MRANotazz_resp['MRANfLog']->nf_codigoVerificacao;
                            $MRANfNfse->nf_pdf                                      = (isset($MRANotazz_resp['notazz_resp']['pdf'])?$MRANotazz_resp['notazz_resp']['pdf']:null);
                            $MRANfNfse->nf_xml                                      = (isset($MRANotazz_resp['notazz_resp']['xml'])?$MRANotazz_resp['notazz_resp']['xml']:null);
                            $MRANfNfse->nf_pdf_prefeitura                           = (isset($MRANotazz_resp['notazz_resp']['linkPrefeitura'])?$MRANotazz_resp['notazz_resp']['linkPrefeitura']:null);
                            $MRANfNfse->nf_xml_cancelamento                         = (isset($MRANotazz_resp['notazz_resp']['xmlCancelamento'])?$MRANotazz_resp['notazz_resp']['xmlCancelamento']:null);
                            $MRANfNfse->nf_emissao                                  = (isset($MRANotazz_resp['notazz_resp']['emissao'])?$MRANotazz_resp['notazz_resp']['emissao']:null);

                            Session::flash('flash_success', "Nota Fiscal de Serviço consultada com sucesso e autorizada!");
                        }elseif(isset($MRANotazz_resp['notazz_resp']['statusNota']) and $MRANotazz_resp['notazz_resp']['statusNota'] == 'AguardandoAutorizacao'){

                            Session::flash('flash_warning', "Nota Fiscal de Serviço consultada com sucesso e aguardando autorização!");

                        }elseif(isset($MRANotazz_resp['notazz_resp']['statusNota']) and $MRANotazz_resp['notazz_resp']['statusNota'] == 'AguardandoCancelamento'){

                            Session::flash('flash_warning', "Nota Fiscal de Serviço consultada com sucesso e aguardando cancelamento!");

                        }else {
                            if(isset($MRANotazz_resp['notazz_resp']['motivoStatus'])){
                                Session::flash('flash_error', "Erro! ".(!empty($MRANfNfse->notazz_motivo)?$MRANfNfse->notazz_motivo:'Tente novamente!'));
                            }else {
                                Session::flash('flash_success', "Nota Fiscal de Serviço consultada com sucesso. Acompanhe o andamento e status do seu processamento!");
                            }
                        }
                    // ! Erro
                    }elseif($MRANotazz_resp['notazz_resp']['statusProcessamento'] == 'erro'){
                        $MRANotazz_resp['MRANfLog']->notazz_statusProcessamento = $MRANotazz_resp['notazz_resp']['statusProcessamento'];
                        $MRANotazz_resp['MRANfLog']->notazz_codigoProcessamento = $MRANotazz_resp['notazz_resp']['codigoProcessamento'];
                        //$MRANotazz_resp['MRANfLog']->notazz_statusNota          = (isset($MRANotazz_resp['notazz_resp']['statusNota'])?$MRANotazz_resp['notazz_resp']['statusNota']:null);
                        $MRANotazz_resp['MRANfLog']->notazz_motivo              = (isset($MRANotazz_resp['notazz_resp']['motivo'])?$MRANotazz_resp['notazz_resp']['motivo']:null);
                        //$MRANotazz_resp['MRANfLog']->notazz_motivo              = (isset($MRANotazz_resp['notazz_resp']['motivoStatus'])?$MRANotazz_resp['notazz_resp']['motivoStatus']:$MRANotazz_resp['MRANfLog']->notazz_motivo);

                        //$MRANfNfse->notazz_status                               = $MRANotazz_resp['MRANfLog']->notazz_statusNota;
                        $MRANfNfse->notazz_motivo                               = $MRANotazz_resp['MRANfLog']->notazz_motivo;

                        Session::flash('flash_error', "Erro! ".$MRANfNfse->notazz_motivo);
                    }

                    $MRANotazz_resp['MRANfLog']->mra_nf_cliente_id              = $MRANfNfse->mra_nf_cliente_id;
                    $MRANotazz_resp['MRANfLog']->mra_nf_prod_serv_id            = $MRANfNfse->mra_nf_prod_serv_id;
                    $MRANotazz_resp['MRANfLog']->mra_nf_nfs_e_id                = $MRANfNfse->id;
                    $MRANotazz_resp['MRANfLog']->save();

                    $MRANfNfse->notazz_statusProcessamento                      = $MRANotazz_resp['notazz_resp']['statusProcessamento'];
                    $MRANfNfse->notazz_codigoProcessamento                      = $MRANotazz_resp['notazz_resp']['codigoProcessamento'];
                    $MRANfNfse->notazz_resq                                     = $MRANotazz_resp['MRANfLog']->resq;
                    $MRANfNfse->notazz_resp                                     = $MRANotazz_resp['MRANfLog']->resp;

                    $MRANfNfse->save();
                    DB::commit();

                }

                if($user){
                    Logs::cadastrar($user->id, ($user->name . ' mra_nfs_e|consult_nfse: consultou ID: ' . $MRANfNfse->id . '| DOCUMENT_ID: ' . $MRANfNfse->notazz_id_documento));
                }

                return Redirect::to('/mra_nota_fiscal/mra_nfs_e/'.$MRANfNfse->id.'/edit');
            }
            // - #

            $acao                                           = 'edit';
            if(is_null($MRANfNfse)){
                $MRANfNfse                                  = new MRANfNfse();
                $acao                                       = 'add';
            }

            $MRANfNfse->mra_nf_cfg_emp_id                   = 1;
            $MRANfNfse->tomador                             = (isset($store['tomador'])?$store['tomador']:null);
            if($MRANfNfse->tomador){
                $MRANfNfse->mra_nf_cliente_id               = (isset($store['mra_nf_cliente_id'])?$store['mra_nf_cliente_id']:null);
                $MRANfNfse->tomador_pessoa                  = (isset($store['tomador_pessoa'])?$store['tomador_pessoa']:null);
                $MRANfNfse->tomador_nome                    = (isset($store['tomador_nome'])?$store['tomador_nome']:null);
                $MRANfNfse->tomador_cnpj                    = (isset($store['tomador_cnpj'])?str_replace(['_'],'',$store['tomador_cnpj']):null);
                $MRANfNfse->tomador_cpf                     = (isset($store['tomador_cpf'])?str_replace(['_'],'',$store['tomador_cpf']):null);
                $MRANfNfse->tomador_insc_estadual           = (isset($store['tomador_insc_estadual'])?$store['tomador_insc_estadual']:null);
                $MRANfNfse->tomador_insc_municipal          = (isset($store['tomador_insc_municipal'])?$store['tomador_insc_municipal']:null);
                // :: Física
                if($MRANfNfse->tomador_pessoa == 'F'){
                    $MRANfNfse->tomador_cnpj                = null;
                    $MRANfNfse->tomador_insc_estadual       = null;
                    $MRANfNfse->tomador_insc_municipal      = null;
                // :: Jurídica
                }elseif($MRANfNfse->tomador_pessoa == 'J'){
                    $MRANfNfse->tomador_cpf                 = null;

                // :: Estrangeiro
                }elseif($MRANfNfse->tomador_pessoa == 'E'){
                    $MRANfNfse->tomador_cpf                 = null;
                    $MRANfNfse->tomador_cnpj                = null;
                    $MRANfNfse->tomador_insc_estadual       = null;
                }else {
                    $MRANfNfse->tomador_cpf                 = null;
                    $MRANfNfse->tomador_cnpj                = null;
                    $MRANfNfse->tomador_insc_estadual       = null;
                    $MRANfNfse->tomador_insc_municipal      = null;
                }
                $MRANfNfse->tomador_cont_telefone           = (isset($store['tomador_cont_telefone'])?str_replace(['_'],'',$store['tomador_cont_telefone']):null);
                $MRANfNfse->tomador_cont_email              = (isset($store['tomador_cont_email'])?$store['tomador_cont_email']:null);
                $MRANfNfse->tomador_cont_enviar_nf_email    = (isset($store['tomador_cont_enviar_nf_email'])?$store['tomador_cont_enviar_nf_email']:0);
                $MRANfNfse->tomador_end_cep                 = (isset($store['tomador_end_cep'])?str_replace(['_'],'',$store['tomador_end_cep']):null);
                $MRANfNfse->tomador_end_rua                 = (isset($store['tomador_end_rua'])?$store['tomador_end_rua']:null);
                $MRANfNfse->tomador_end_numero              = (isset($store['tomador_end_numero'])?$store['tomador_end_numero']:null);
                $MRANfNfse->tomador_end_bairro              = (isset($store['tomador_end_bairro'])?$store['tomador_end_bairro']:null);
                $MRANfNfse->tomador_end_complemento         = (isset($store['tomador_end_complemento'])?$store['tomador_end_complemento']:null);
                $MRANfNfse->tomador_end_estado              = (isset($store['tomador_end_estado'])?$store['tomador_end_estado']:null);
                $MRANfNfse->tomador_end_cidade              = (isset($store['tomador_end_cidade'])?$store['tomador_end_cidade']:null);
                $MRANfNfse->tomador_end_pais                = (isset($store['tomador_end_pais'])?$store['tomador_end_pais']:null);
            }else {
                $MRANfNfse->mra_nf_cliente_id               = null;
                $MRANfNfse->tomador_pessoa                  = null;
                $MRANfNfse->tomador_nome                    = null;
                $MRANfNfse->tomador_cnpj                    = null;
                $MRANfNfse->tomador_cpf                     = null;
                $MRANfNfse->tomador_insc_estadual           = null;
                $MRANfNfse->tomador_insc_municipal          = null;
                $MRANfNfse->tomador_cont_telefone           = null;
                $MRANfNfse->tomador_cont_email              = null;
                $MRANfNfse->tomador_cont_enviar_nf_email    = 0;
                $MRANfNfse->tomador_end_cep                 = null;
                $MRANfNfse->tomador_end_rua                 = null;
                $MRANfNfse->tomador_end_numero              = null;
                $MRANfNfse->tomador_end_bairro              = null;
                $MRANfNfse->tomador_end_complemento         = null;
                $MRANfNfse->tomador_end_estado              = null;
                $MRANfNfse->tomador_end_cidade              = null;
                $MRANfNfse->tomador_end_pais                = null;
            }
            $MRANfNfse->mra_nf_prod_serv_id                 = (isset($store['mra_nf_prod_serv_id'])?$store['mra_nf_prod_serv_id']:null);
            $MRANfNfse->cfg_data_competencia                = (isset($store['cfg_data_competencia'])?\App\Helper\Helper::H_DataHora_ptBR_DB($store['cfg_data_competencia']):Carbon::now());
            //$MRANfNfse->cfg_data_emissao                  = (isset($store['cfg_data_emissao'])?\App\Helper\Helper::H_DataHora_ptBR_DB($store['cfg_data_emissao']):Carbon::now());
            $MRANfNfse->cfg_cnae                            = (isset($store['cfg_cnae'])?$store['cfg_cnae']:null);
            $MRANfNfse->cfg_cofins                          = (isset($store['cfg_cofins'])?\App\Helper\Helper::H_Decimal_ptBR_DB($store['cfg_cofins']):null);
            $MRANfNfse->cfg_csll                            = (isset($store['cfg_csll'])?\App\Helper\Helper::H_Decimal_ptBR_DB($store['cfg_csll']):null);
            $MRANfNfse->cfg_inss                            = (isset($store['cfg_inss'])?\App\Helper\Helper::H_Decimal_ptBR_DB($store['cfg_inss']):null);
            $MRANfNfse->cfg_ir                              = (isset($store['cfg_ir'])?\App\Helper\Helper::H_Decimal_ptBR_DB($store['cfg_ir']):null);
            $MRANfNfse->cfg_pis                             = (isset($store['cfg_pis'])?\App\Helper\Helper::H_Decimal_ptBR_DB($store['cfg_pis']):null);
            $MRANfNfse->cfg_iss                             = (isset($store['cfg_iss'])?\App\Helper\Helper::H_Decimal_ptBR_DB($store['cfg_iss']):null);
            $MRANfNfse->cfg_iss_retido_fonte                = (isset($store['cfg_iss_retido_fonte'])?$store['cfg_iss_retido_fonte']:null);
            $MRANfNfse->cfg_lc116                           = (isset($store['cfg_lc116'])?$store['cfg_lc116']:null);
            $MRANfNfse->cfg_cod_servico                     = (isset($store['cfg_cod_servico'])?$store['cfg_cod_servico']:null);
            $MRANfNfse->cfg_desc_servico_municipio          = (isset($store['cfg_desc_servico_municipio'])?$store['cfg_desc_servico_municipio']:null);
            $MRANfNfse->cfg_valor_nota                      = (isset($store['cfg_valor_nota'])?\App\Helper\Helper::H_Decimal_ptBR_DB($store['cfg_valor_nota']):null);
            $MRANfNfse->cfg_deducao                         = (isset($store['cfg_deducao'])?\App\Helper\Helper::H_Decimal_ptBR_DB($store['cfg_deducao']):null);
            $MRANfNfse->cfg_estado_prest_serv               = (isset($store['cfg_estado_prest_serv'])?$store['cfg_estado_prest_serv']:null);
            $MRANfNfse->cfg_cidade_prest_serv               = (isset($store['cfg_cidade_prest_serv'])?$store['cfg_cidade_prest_serv']:null);
            //$MRANfNfse->cfg_id_externo                      = (isset($store['cfg_cidade_prest_serv'])?$store['cfg_id_externo']:null);
            $MRANfNfse->cfg_enviar_email                    = (isset($store['cfg_enviar_email'])?$store['cfg_enviar_email']:0);
            $MRANfNfse->cfg_emails                          = (isset($store['cfg_emails'])?$store['cfg_emails']:null);
            $MRANfNfse->cfg_descricao_nota                  = (isset($store['cfg_descricao_nota'])?$store['cfg_descricao_nota']:null);
            //$MRANfNfse->nf_emissao                    = Carbon::now(); // Gerado pelo Notazz Automaticamente
            if($acao=='add'){
                $MRANfNfse->notazz_status                   = 'Pendente';
            }
            $MRANfNfse->r_auth                              = $r_auth;
            $MRANfNfse->notazz_transmitir                   = 1; // !! ATENÇÃO !!

            $MRANfNfse->save();

            DB::commit();

            // :: Transmitir Nota Fiscal
            if(isset($store['transferir'])){
                try {

                    // :: CPF / CNPJ - Seleção / ! Tratamento
                    $DESTINATION_TAXID       = "";
                    if($MRANfNfse->tomador_pessoa=='F'){
                        $DESTINATION_TAXID   = str_replace(['.','-','_'],'',$MRANfNfse->tomador_cpf);
                    }elseif($MRANfNfse->tomador_pessoa=='J'){
                        $DESTINATION_TAXID   = str_replace(['.','-','/','_'],'',$MRANfNfse->tomador_cnpj);
                    }

                    // :: Emails - Send
                    $DESTINATION_EMAIL_SEND      = [];
                    // . Enviar E-mail - Dados da Nota - Serviço
                    if($MRANfNfse->cfg_enviar_email){
                        // - E-mails Nota Fiscal
                        if(!empty($MRANfNfse->cfg_emails)){
                            $cfg_emails_exp = explode(',',$MRANfNfse->cfg_emails);
                            if(count($cfg_emails_exp)){
                                foreach($cfg_emails_exp as $K => $CEE){
                                    $DESTINATION_EMAIL_SEND[$K+1]['EMAIL'] = $CEE;
                                }
                            }
                        }
                    }
                    // . Enviar E-mail - Dados da Nota - Cliente/Tomador
                    if($MRANfNfse->tomador_cont_enviar_nf_email and !empty($MRANfNfse->tomador_cont_email)){
                        $DESTINATION_EMAIL_SEND[count($DESTINATION_EMAIL_SEND)+1]['EMAIL'] = $MRANfNfse->tomador_cont_email;
                    }

                    $MRANotazz           = new MRANotazz();
                    $MRANotazz_send      = [
                        "METHOD"                    => (!empty($MRANfNfse->notazz_id_documento)?"update_nfse":"create_nfse"),
                        "DOCUMENT_ID"               => (!empty($MRANfNfse->notazz_id_documento)?$MRANfNfse->notazz_id_documento:""),
                        "DESTINATION_NAME"          => $MRANfNfse->tomador_nome,
                        "DESTINATION_TAXID"         => $DESTINATION_TAXID,
                        "DESTINATION_IE"            => $MRANfNfse->tomador_insc_estadual,
                        "DESTINATION_IM"            => $MRANfNfse->tomador_insc_municipal,
                        "DESTINATION_TAXTYPE"       => $MRANfNfse->tomador_pessoa,
                        "DESTINATION_STREET"        => $MRANfNfse->tomador_end_rua,
                        //"DESTINATION_NUMBER"        => '0', // ! Forçando Erro - Temporário [ ! Não funciona na Atualização, caso errado mantem o mesmo anterior ! ]
                        "DESTINATION_NUMBER"        => ((empty($MRANfNfse->tomador_end_numero)||$MRANfNfse->tomador_end_numero=='0')?'S/N':$MRANfNfse->tomador_end_numero),
                        "DESTINATION_COMPLEMENT"    => $MRANfNfse->tomador_end_complemento,
                        "DESTINATION_DISTRICT"      => $MRANfNfse->tomador_end_bairro,
                        "DESTINATION_CITY"          => $MRANfNfse->tomador_end_cidade,
                        "DESTINATION_UF"            => $MRANfNfse->tomador_end_estado,
                        "DESTINATION_ZIPCODE"       => str_replace(['-',' ','_'],'',$MRANfNfse->tomador_end_cep),
                        "DESTINATION_PHONE"         => str_replace(['(',')','-',' ','_'],'',$MRANfNfse->tomador_cont_telefone),
                        "DESTINATION_EMAIL"         => $MRANfNfse->tomador_cont_email,
                        "DESTINATION_EMAIL_SEND"    => $DESTINATION_EMAIL_SEND,
                        "DOCUMENT_BASEVALUE"        => $MRANfNfse->cfg_valor_nota,
                        "DOCUMENT_DESCRIPTION"      => $MRANfNfse->cfg_descricao_nota,
                        "DOCUMENT_COMPETENCE"       => (!empty($MRANfNfse->cfg_data_competencia)?date('Y-m-d',strtotime($MRANfNfse->cfg_data_competencia)):""),
                        "DOCUMENT_CNAE"             => $MRANfNfse->cfg_cnae,
                        "SERVICE_LIST_LC116"        => $MRANfNfse->cfg_lc116,
                        "WITHHELD_ISS"              => $MRANfNfse->cfg_iss_retido_fonte,
                        "CITY_SERVICE_CODE"         => $MRANfNfse->cfg_cod_servico,
                        "CITY_SERVICE_DESCRIPTION"  => $MRANfNfse->cfg_desc_servico_municipio,
                        "ALIQUOTAS"                 => array(
                            "COFINS"    => (!empty($MRANfNfse->cfg_cofins)?$MRANfNfse->cfg_cofins:"0.00"),
                            "CSLL"      => (!empty($MRANfNfse->cfg_csll)?$MRANfNfse->cfg_csll:"0.00"),
                            "INSS"      => (!empty($MRANfNfse->cfg_inss)?$MRANfNfse->cfg_inss:"0.00"),
                            "IR"        => (!empty($MRANfNfse->cfg_ir)?$MRANfNfse->cfg_ir:"0.00"),
                            "PIS"       => (!empty($MRANfNfse->cfg_pis)?$MRANfNfse->cfg_pis:"0.00"),
                            "ISS"       => (!empty($MRANfNfse->cfg_iss)?$MRANfNfse->cfg_iss:"0.00")
                        ),
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

                            Session::flash('flash_success', "Nota Fiscal de Serviço transferida com sucesso. Acompanhe o andamento e status do seu processamento!");
                        // ! Erro
                        }elseif($MRANotazz_resp['notazz_resp']['statusProcessamento'] == 'erro'){
                            $MRANotazz_resp['MRANfLog']->notazz_statusProcessamento = $MRANotazz_resp['notazz_resp']['statusProcessamento'];
                            $MRANotazz_resp['MRANfLog']->notazz_codigoProcessamento = $MRANotazz_resp['notazz_resp']['codigoProcessamento'];
                            $MRANotazz_resp['MRANfLog']->notazz_motivo              = $MRANotazz_resp['notazz_resp']['motivo'];

                            $MRANfNfse->notazz_motivo                               = $MRANotazz_resp['notazz_resp']['motivo'];

                            Session::flash('flash_error', "Erro! ".$MRANfNfse->notazz_motivo);
                        }

                        $MRANotazz_resp['MRANfLog']->notazz_id_documento            = (isset($MRANotazz_resp['notazz_resp']['id'])?$MRANotazz_resp['notazz_resp']['id']:$MRANfNfse->notazz_id_documento);
                        $MRANfNfse->notazz_id_documento                             = (isset($MRANotazz_resp['notazz_resp']['id'])?$MRANotazz_resp['notazz_resp']['id']:$MRANfNfse->notazz_id_documento);

                        $MRANotazz_resp['MRANfLog']->mra_nf_cliente_id              = $MRANfNfse->mra_nf_cliente_id;
                        $MRANotazz_resp['MRANfLog']->mra_nf_prod_serv_id            = $MRANfNfse->mra_nf_prod_serv_id;
                        $MRANotazz_resp['MRANfLog']->mra_nf_nfs_e_id                = $MRANfNfse->id;
                        $MRANotazz_resp['MRANfLog']->save();

                        $MRANfNfse->notazz_statusProcessamento                      = $MRANotazz_resp['notazz_resp']['statusProcessamento'];
                        $MRANfNfse->notazz_codigoProcessamento                      = $MRANotazz_resp['notazz_resp']['codigoProcessamento'];
                        $MRANfNfse->notazz_resq                                     = $MRANotazz_resp['MRANfLog']->resq;
                        $MRANfNfse->notazz_resp                                     = $MRANotazz_resp['MRANfLog']->resp;

                        if($MRANfNfse->notazz_codigoProcessamento == '000'){
                            $MRANfNfse->notazz_status                               = 'AguardandoAutorizacao';
                        }

                        $MRANfNfse->save();
                        DB::commit();

                    }
                    //print_r($MRANotazz_resp); exit;
                    // - #

                    if($user){
                        Logs::cadastrar($user->id, ($user->name . ' mra_nfs_e|'.$acao.': transferiu ID: ' . $MRANfNfse->id));
                    }

                }catch(\Exception $e){
                    Session::flash('flash_error', "Erro ao trasnferir mra_nfs_e: " . $e->getMessage());
                }
            }


            // :: Edição
            if($acao=='edit' and !isset($store['transferir'])){
                Session::flash('flash_success', "Serviço atualizado com sucesso!");
                if($user){
                    Logs::cadastrar($user->id, ($user->name . ' mra_nfs_e|'.$acao.': atualizou ID: ' . $MRANfNfse->id));
                }

            // :: Criação
            }elseif(!isset($store['transferir'])) {
                Session::flash('flash_success', "Serviço cadastrado com sucesso!");
                if($user){
                    Logs::cadastrar($user->id, ($user->name . ' mra_nfs_e|'.$acao.': cadastrou ID: ' . $MRANfNfse->id));
                }
            }

        }catch(Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            Session::flash('flash_error', "Erro ao realizar atualização mra_nfs_e|".(isset($acao)?$acao:'').": " . $e->getMessage());
            return back()->withInput()->with([],400);
        }

        if($redirect){
            return Redirect::to($redirect);
        }

        //if(isset($store['transferir'])){
            return Redirect::to('/mra_nota_fiscal/mra_nfs_e/'.$MRANfNfse->id.'/edit');
        //}else {
            //return Redirect::to('/mra_nota_fiscal/mra_nfs_e');
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
                $MRANfNfse                  = $id_object;
            }else {
                $MRANfNfse                  = MRANfNfse::find($id_object);
            }
            $id                             = $MRANfNfse->id;

            // ! Se não existe
            if(!$MRANfNfse){
                \Session::flash('flash_error', 'Registro não encontrado!');
                return Redirect::to('mra_nota_fiscal/mra_nfs_e');
            }

            // ! Cancelar Nota Fiscal - Forçado
            if(isset($store['cancelar_nf_forcado'])) {
                $MRANfNfse->notazz_status           = 'Cancelada';
                $MRANfNfse->notazz_status_forcado   = 1;
                $MRANfNfse->save();
                DB::commit();
                Session::flash('flash_success', "Nota Fiscal de Serviço foi cancelada com sucesso dentro do sistema!");
                return Redirect::to('/mra_nota_fiscal/mra_nfs_e/'.$MRANfNfse->id.'/edit');
            }
            // - #

            // ! Cancelar Nota Fiscal
            //if(isset($store['cancelar_nf'])) { $cancelar_nf = true; }
            if(isset($store['cancelar_nf']) and in_array($MRANfNfse->notazz_status,['Autorizada'])) {
                $MRANotazz                                                      = new MRANotazz();
                $MRANotazz_send                                                 = [
                    "METHOD"                    => 'cancel_nfse',
                    "DOCUMENT_ID"               => $MRANfNfse->notazz_id_documento,
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
                        $MRANfNfse->notazz_status                               = $MRANotazz_resp['MRANfLog']->notazz_statusNota;

                        Session::flash('flash_success', "Nota Fiscal de Serviço enviada para cancelamento com sucesso. Acompanhe o andamento e status do seu processamento!");

                    // ! Erro
                    }elseif($MRANotazz_resp['notazz_resp']['statusProcessamento'] == 'erro'){
                        $MRANotazz_resp['MRANfLog']->notazz_statusProcessamento = $MRANotazz_resp['notazz_resp']['statusProcessamento'];
                        $MRANotazz_resp['MRANfLog']->notazz_codigoProcessamento = $MRANotazz_resp['notazz_resp']['codigoProcessamento'];
                        $MRANotazz_resp['MRANfLog']->notazz_motivo              = (isset($MRANotazz_resp['notazz_resp']['motivo'])?$MRANotazz_resp['notazz_resp']['motivo']:null);

                        $MRANfNfse->notazz_motivo                               = $MRANotazz_resp['MRANfLog']->notazz_motivo;

                        Session::flash('flash_error', "Erro! ".$MRANfNfse->notazz_motivo);

                    }

                    $MRANotazz_resp['MRANfLog']->mra_nf_cliente_id              = $MRANfNfse->mra_nf_cliente_id;
                    $MRANotazz_resp['MRANfLog']->mra_nf_prod_serv_id            = $MRANfNfse->mra_nf_prod_serv_id;
                    $MRANotazz_resp['MRANfLog']->mra_nf_nfs_e_id                = $MRANfNfse->id;
                    $MRANotazz_resp['MRANfLog']->save();

                    $MRANfNfse->notazz_statusProcessamento                      = $MRANotazz_resp['notazz_resp']['statusProcessamento'];
                    $MRANfNfse->notazz_codigoProcessamento                      = $MRANotazz_resp['notazz_resp']['codigoProcessamento'];
                    $MRANfNfse->notazz_resq                                     = $MRANotazz_resp['MRANfLog']->resq;
                    $MRANfNfse->notazz_resp                                     = $MRANotazz_resp['MRANfLog']->resp;

                    $MRANfNfse->save();
                    DB::commit();
                }

                return Redirect::to('/mra_nota_fiscal/mra_nfs_e/'.$MRANfNfse->id.'/edit');
            }

            // ! Verifica se já gerou a "Nota Fiscal" e fez a Transferência, evitando a remoção
            if(!empty($MRANfNfse->notazz_id_documento)){
                Session::flash('flash_error', "Erro: Para realizar a exclusão a Nota Fiscal deve ser cancelada* do processamento!");
                return back();
            }
            // - #

            DB::rollBack();

            return $this->controllerRepository::destroy(new MRANfNfse(), $id_object, 'mra_nota_fiscal/mra_nfs_e');

        }catch(Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            Session::flash('flash_error', "Erro ao realizar exclusão/cancelamento mra_nfs_e: " . $e->getMessage());
            return back()->withInput()->with([],400);
        }
    }

}
