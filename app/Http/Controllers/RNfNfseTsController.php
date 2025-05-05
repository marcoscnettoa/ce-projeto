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
use \App\Models\RNfNfseTs;
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
use App\Models\REstados;
use App\Models\RCidades;
use App\Http\Controllers\RTecnoSpeedController as RTecnoSpeed;
use App\Models\RNfConfiguracoesTs;

class RNfNfseTsController extends Controller
{
    public function __construct(
        Client $client,
        UploadRepository $uploadRepository,
        ControllerRepository $controllerRepository,
        TemplateRepository $templateRepository,
        RTecnospeed $tecnospeed
    ) {
        $this->client               = $client;
        $this->upload               = $controllerRepository->upload;
        $this->maxSize              = $controllerRepository->maxSize;

        $this->uploadRepository     = $uploadRepository;
        $this->controllerRepository = $controllerRepository;
        $this->templateRepository   = $templateRepository;
        $this->tecnospeed           = $tecnospeed;
    }

    public function index(Request $request)
    {
        try {

            $user       = Auth::user();

            $RNfNfseTs  = RNfNfseTs::getAll(500,$request);

            if($user){
                Logs::cadastrar($user->id, ($user->name . ' visualizou a lista do módulo r_nf_nfse_ts'));
            }

            return view('r_nf_nfse_ts.index', [
                'exibe_filtros'     => 0,
                'RNfNfseTs'         => $RNfNfseTs
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

            $RNfNfseTs     = null;
            if(!is_null($id)){
                $RNfNfseTs = RNfNfseTs::find($id);
                if(!$RNfNfseTs){
                    \Session::flash('flash_error', 'Registro não encontrado!');
                    return Redirect::to('/nota_fiscal/nfse/ts');
                }
            }

            if(!Permissions::permissaoModerador($user) && $RNfNfseTs->r_auth != 0 && $RNfNfseTs->r_auth != $user->id){
                Session::flash('flash_error', 'Você não tem permissão para executar esta ação!');
                return Redirect::to('/nota_fiscal/nfse/ts');
            }

            $estados = REstados::selectRaw('*,CONCAT(sigla," - ",nome) as sigla_nome')
                ->pluck('sigla_nome', 'id')
                ->prepend("---", "");

            $cidades = RCidades::selectRaw('*,CONCAT(uf," - ",nome) as uf_nome')
                ->pluck('uf_nome', 'id')
                ->prepend("---", "");

            if($user){
                // Edição
                if(!is_null($RNfNfseTs)){
                    Logs::cadastrar($user->id, ($user->name . ' visualizou a tela de edição ID #' . $id . ' do módulo r_nf_nfse_ts'));
                    // Criação
                }else {
                    Logs::cadastrar($user->id, ($user->name . ' visualizou a tela de criação do módulo r_nf_nfse_ts'));
                }
            }

            return view('r_nf_nfse_ts.add_edit', [
                'exibe_filtros'  => 0,
                'RNfNfseTs'      => $RNfNfseTs,
                'estados'        => $estados,
                'cidades'        => $cidades,
                'config_empresa' => $config_empresa
            ]);

        }catch(\Exception $e){
            Log::error($e->getMessage());
            Session::flash('flash_error', "Ocorreu um erro! Tente novamente.");
            return Redirect::to('/');
        }
    }

    protected function validator(array $data, $id = ''){
        return Validator::make($data, [
            'mra_nf_cliente_id'       =>  'required',
            'tomador_pessoa'          =>  ((isset($data['tomador']) and $data['tomador'])?'required':''),
            'tomador_nome'            =>  ((isset($data['tomador']) and $data['tomador'])?'required':''),
            'tomador_cpf'             =>  ((isset($data['tomador']) and $data['tomador'] and
                                          isset($data['tomador_pessoa']) and $data['tomador_pessoa']=='F')?'required':''),
            'tomador_cnpj'            =>  ((isset($data['tomador']) and $data['tomador'] and
                                          isset($data['tomador_pessoa']) and $data['tomador_pessoa']=='J')?'required':''),
            'tomador_end_cep'         =>  ((isset($data['tomador']) and $data['tomador'])?'required':''),
            'tomador_tipo_logradouro' =>  ((isset($data['tomador_tipo_logradouro']) and $data['tomador_tipo_logradouro'])?'required':''),
            'tomador_end_rua'         =>  ((isset($data['tomador']) and $data['tomador'])?'required':''),
            'tomador_end_numero'      =>  ((isset($data['tomador']) and $data['tomador'])?'required':''),
            'tomador_end_bairro'      =>  ((isset($data['tomador']) and $data['tomador'])?'required':''),
            'tomador_end_estado'      =>  ((isset($data['tomador']) and $data['tomador'])?'required':''),
            'tomador_end_cidade'      =>  ((isset($data['tomador']) and $data['tomador'])?'required':''),
            'tomador_end_pais'        =>  ((isset($data['tomador']) and $data['tomador'])?'required':''),
            'cfg_cnae'                =>  'required',
            'cfg_lc116'               =>  'required',
            'cfg_valor_nota'          =>  'required',
            'cfg_descricao_nota'      =>  'required',
            'cfg_estado_prest_serv'   =>  'required',
            'cfg_cidade_prest_serv'   =>  'required',
        ],[
            'mra_nf_cliente_id'       => 'O campo "NFS-e com Tomador" é obrigatório.',
            'tomador_pessoa'          => 'O campo "Tipo de Pessoa" é obrigatório.',
            'tomador_nome'            => 'O campo "Nome do Tomador" é obrigatório.',
            'tomador_cpf'             => 'O campo "CPF" é obrigatório.',
            'tomador_cnpj'            => 'O campo "CNPJ" é obrigatório.',
            'tomador_end_cep'         => 'O campo "CEP" é obrigatório.',
            'tomador_tipo_logradouro' => 'O campo "Tipo de Logradouro" é obrigatório.',
            'tomador_end_rua'         => 'O campo "Logradouro / Rua" é obrigatório.',
            'tomador_end_numero'      => 'O campo "Número" é obrigatório.',
            'tomador_end_bairro'      => 'O campo "Bairro" é obrigatório.',
            'tomador_end_estado'      => 'O campo "Estado" é obrigatório.',
            'tomador_end_cidade'      => 'O campo "Cidade" é obrigatório.',
            'tomador_end_pais'        => 'O campo "País" é obrigatório.',
            'cfg_cnae'                => 'O campo "CNAE" é obrigatório.',
            'cfg_lc116'               => 'O campo "LC 116" é obrigatório.',
            'cfg_valor_nota'          => 'O campo "Valor da Nota" é obrigatório.',
            'cfg_descricao_nota'      => 'O campo "Descrição da Nota" é obrigatório.',
            'cfg_estado_prest_serv'   => 'O campo "Estado do Prestador de Serviço" é obrigatório.',
            'cfg_cidade_prest_serv'   => 'O campo "Cidade do Prestador de Serviço" é obrigatório.',
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

            $RNfNfseTs                                      = null;
            if(isset($store['id'])){
                $RNfNfseTs                                  = RNfNfseTs::find($store['id']);
                if(!$RNfNfseTs){
                    \Session::flash('flash_error', 'Registro não encontrado!');
                    return Redirect::to('/nota_fiscal/nfse/ts');
                }
            }

            // :: Cancelar Nota Fiscal
            if(isset($store['cancelar_nf'])) {

                $cancelar_nf = $this->cancelar($store, $RNfNfseTs, $user);

                $RNfNfseTs->save();
                DB::commit();

                // :: Sucesso
                if (isset($cancelar_nf['success'])) {
                    Session::flash('flash_success', "Nota Fiscal enviada para cancelamento com sucesso!");

                // :: Erro
                }elseif (isset($cancelar_nf['error'])) {
                    Session::flash('flash_error', "Erro: ".$cancelar_nf['message']);
                }

                if($user){
                    Logs::cadastrar($user->id, ($user->name .' r_nf_nfe_ts cancelou ID: '.$RNfNfseTs->nf_response_id));
                }

                return Redirect::to('/nota_fiscal/nfse/ts/'.$RNfNfseTs->id.'/edit');
            }
            // - #

            // :: Cancelar Processamento Forçado
            if(isset($store['cancelar_nf_forcado'])){ return $this->destroy($RNfNfseTs); }

            DB::beginTransaction();

            // ! Caso Exista -> Validar Passagem de Status*
            if($RNfNfseTs and $RNfNfseTs->notazz_status == 'Autorizada'){
                \Session::flash('flash_success', 'A Nota Fiscal de Serviço já foi autorizada!');
                return Redirect::to('/nota_fiscal/nfse/ts/'.$RNfNfseTs->id.'/edit');
            }
            // - #

            // :: Consultar Processamento
            if(isset($store['consultar'])){

                $consulta_tecnospeed = $this->consultar($store, $user, $RNfNfseTs);

                $RNfNfseTs->save();
                DB::commit();

                if (isset($consulta_tecnospeed['success'])) {
                    Session::flash('flash_success', 'Nota Fiscal consultada com sucesso!');
                }elseif (isset($consulta_tecnospeed['error'])) {
                    Session::flash('flash_error', $consulta_tecnospeed['message']);
                }

                if($user){
                    Logs::cadastrar($user->id, ($user->name .' r_nf_nfse_ts consultou ID: '.$RNfNfseTs->nf_response_id));
                }

                return Redirect::to('/nota_fiscal/nfse/ts/'.$RNfNfseTs->id.'/edit');
            }
            // - #

            $acao                                           = 'edit';
            if(is_null($RNfNfseTs)){
                $RNfNfseTs                                  = new RNfNfseTs();
                $acao                                       = 'add';
            }

            $RNfNfseTs->mra_nf_cfg_emp_id                   = 1;
            $RNfNfseTs->tomador                             = (isset($store['mra_nf_cliente_id'])?$store['mra_nf_cliente_id']:null);
            if($RNfNfseTs->tomador){
                $RNfNfseTs->mra_nf_cliente_id               = (isset($store['mra_nf_cliente_id'])?$store['mra_nf_cliente_id']:null);
                $RNfNfseTs->tomador_pessoa                  = (isset($store['tomador_pessoa'])?$store['tomador_pessoa']:null);
                $RNfNfseTs->tomador_nome                    = (isset($store['tomador_nome'])?$store['tomador_nome']:null);
                $RNfNfseTs->tomador_cnpj                    = (isset($store['tomador_cnpj'])?str_replace(['_'],'',$store['tomador_cnpj']):null);
                $RNfNfseTs->tomador_cpf                     = (isset($store['tomador_cpf'])?str_replace(['_'],'',$store['tomador_cpf']):null);
                $RNfNfseTs->tomador_insc_estadual           = (isset($store['tomador_insc_estadual'])?$store['tomador_insc_estadual']:null);
                $RNfNfseTs->tomador_insc_municipal          = (isset($store['tomador_insc_municipal'])?$store['tomador_insc_municipal']:null);
                // :: Física
                if($RNfNfseTs->tomador_pessoa == 'F'){
                    $RNfNfseTs->tomador_cnpj                = null;
                    $RNfNfseTs->tomador_insc_estadual       = null;
                    $RNfNfseTs->tomador_insc_municipal      = null;
                // :: Jurídica
                }elseif($RNfNfseTs->tomador_pessoa == 'J'){
                    $RNfNfseTs->tomador_cpf                 = null;

                // :: Estrangeiro
                }elseif($RNfNfseTs->tomador_pessoa == 'E'){
                    $RNfNfseTs->tomador_cpf                 = null;
                    $RNfNfseTs->tomador_cnpj                = null;
                    $RNfNfseTs->tomador_insc_estadual       = null;
                }else {
                    $RNfNfseTs->tomador_cpf                 = null;
                    $RNfNfseTs->tomador_cnpj                = null;
                    $RNfNfseTs->tomador_insc_estadual       = null;
                    $RNfNfseTs->tomador_insc_municipal      = null;
                }
                $RNfNfseTs->tomador_cont_telefone           = (isset($store['tomador_cont_telefone'])?str_replace(['_'],'',$store['tomador_cont_telefone']):null);
                $RNfNfseTs->tomador_cont_email              = (isset($store['tomador_cont_email'])?$store['tomador_cont_email']:null);
                $RNfNfseTs->tomador_cont_enviar_nf_email    = (isset($store['tomador_cont_enviar_nf_email'])?$store['tomador_cont_enviar_nf_email']:0);
                $RNfNfseTs->tomador_end_cep                 = (isset($store['tomador_end_cep'])?str_replace(['_'],'',$store['tomador_end_cep']):null);
                $RNfNfseTs->tomador_tipo_logradouro               = (isset($store['tomador_tipo_logradouro'])?$store['tomador_tipo_logradouro']:null);
                $RNfNfseTs->tomador_end_rua                 = (isset($store['tomador_end_rua'])?$store['tomador_end_rua']:null);
                $RNfNfseTs->tomador_end_numero              = (isset($store['tomador_end_numero'])?$store['tomador_end_numero']:null);
                $RNfNfseTs->tomador_end_bairro              = (isset($store['tomador_end_bairro'])?$store['tomador_end_bairro']:null);
                $RNfNfseTs->tomador_end_complemento         = (isset($store['tomador_end_complemento'])?$store['tomador_end_complemento']:null);
                $RNfNfseTs->tomador_end_estado              = (isset($store['tomador_end_estado'])?$store['tomador_end_estado']:null);
                $RNfNfseTs->tomador_end_cidade              = (isset($store['tomador_end_cidade'])?$store['tomador_end_cidade']:null);
                $RNfNfseTs->tomador_end_pais                = (isset($store['tomador_end_pais'])?$store['tomador_end_pais']:null);
            }else {
                $RNfNfseTs->mra_nf_cliente_id               = null;
                $RNfNfseTs->tomador_pessoa                  = null;
                $RNfNfseTs->tomador_nome                    = null;
                $RNfNfseTs->tomador_cnpj                    = null;
                $RNfNfseTs->tomador_cpf                     = null;
                $RNfNfseTs->tomador_insc_estadual           = null;
                $RNfNfseTs->tomador_insc_municipal          = null;
                $RNfNfseTs->tomador_cont_telefone           = null;
                $RNfNfseTs->tomador_cont_email              = null;
                $RNfNfseTs->tomador_cont_enviar_nf_email    = 0;
                $RNfNfseTs->tomador_end_cep                 = null;
                $RNfNfseTs->tomador_tipo_logradouro              = null;
                $RNfNfseTs->tomador_end_rua                 = null;
                $RNfNfseTs->tomador_end_numero              = null;
                $RNfNfseTs->tomador_end_bairro              = null;
                $RNfNfseTs->tomador_end_complemento         = null;
                $RNfNfseTs->tomador_end_estado              = null;
                $RNfNfseTs->tomador_end_cidade              = null;
                $RNfNfseTs->tomador_end_pais                = null;
            }
            $RNfNfseTs->mra_nf_prod_serv_id                 = (isset($store['mra_nf_prod_serv_id'])?$store['mra_nf_prod_serv_id']:null);
            $RNfNfseTs->cfg_data_competencia                = (isset($store['cfg_data_competencia'])?\App\Helper\Helper::H_DataHora_ptBR_DB($store['cfg_data_competencia']):Carbon::now());
            $RNfNfseTs->cfg_cnae                            = (isset($store['cfg_cnae'])?$store['cfg_cnae']:null);
            $RNfNfseTs->cfg_cofins                          = (isset($store['cfg_cofins'])?\App\Helper\Helper::H_Decimal_ptBR_DB($store['cfg_cofins']):null);
            $RNfNfseTs->cfg_csll                            = (isset($store['cfg_csll'])?\App\Helper\Helper::H_Decimal_ptBR_DB($store['cfg_csll']):null);
            $RNfNfseTs->cfg_inss                            = (isset($store['cfg_inss'])?\App\Helper\Helper::H_Decimal_ptBR_DB($store['cfg_inss']):null);
            $RNfNfseTs->cfg_ir                              = (isset($store['cfg_ir'])?\App\Helper\Helper::H_Decimal_ptBR_DB($store['cfg_ir']):null);
            $RNfNfseTs->cfg_pis                             = (isset($store['cfg_pis'])?\App\Helper\Helper::H_Decimal_ptBR_DB($store['cfg_pis']):null);
            $RNfNfseTs->cfg_iss                             = (isset($store['cfg_iss'])?\App\Helper\Helper::H_Decimal_ptBR_DB($store['cfg_iss']):null);
            $RNfNfseTs->cfg_iss_retido_fonte                = (isset($store['cfg_iss_retido_fonte'])?$store['cfg_iss_retido_fonte']:null);
            $RNfNfseTs->cfg_lc116                           = (isset($store['cfg_lc116'])?$store['cfg_lc116']:null);
            $RNfNfseTs->cfg_cod_servico                     = (isset($store['cfg_cod_servico'])?$store['cfg_cod_servico']:null);
            $RNfNfseTs->cfg_desc_servico_municipio          = (isset($store['cfg_desc_servico_municipio'])?$store['cfg_desc_servico_municipio']:null);
            $RNfNfseTs->cfg_valor_nota                      = (isset($store['cfg_valor_nota'])?\App\Helper\Helper::H_Decimal_ptBR_DB($store['cfg_valor_nota']):null);
            $RNfNfseTs->cfg_deducao                         = (isset($store['cfg_deducao'])?\App\Helper\Helper::H_Decimal_ptBR_DB($store['cfg_deducao']):null);
            $RNfNfseTs->cfg_estado_prest_serv               = (isset($store['cfg_estado_prest_serv'])?$store['cfg_estado_prest_serv']:null);
            $RNfNfseTs->cfg_cidade_prest_serv               = (isset($store['cfg_cidade_prest_serv'])?$store['cfg_cidade_prest_serv']:null);
            $RNfNfseTs->cfg_enviar_email                    = (isset($store['cfg_enviar_email'])?$store['cfg_enviar_email']:0);
            $RNfNfseTs->cfg_emails                          = (isset($store['cfg_emails'])?$store['cfg_emails']:null);
            $RNfNfseTs->cfg_descricao_nota                  = (isset($store['cfg_descricao_nota'])?$store['cfg_descricao_nota']:null);
            $RNfNfseTs->r_auth                              = $r_auth;

            $RNfNfseTs->save();

            DB::commit();

            // :: Transmitir Nota Fiscal
            if(isset($store['transferir'])){
                try {

                    if (env('TECNOSPEED_ENVIRONMENT') == 'sandbox') {
                        $sistema    = '-'.env('APP_NAME').'-SANDBOX-'.strtotime(date('Y-m-d H:i:s'));
                        $cpf_cnpj   = "08187168000160";
                    }elseif (env('TECNOSPEED_ENVIRONMENT') == 'production') {
                        if ($RNfNfseTs->ConfigEmpresa->producao) {
                            $sistema = '-'.env('APP_NAME');
                            $cpf_cnpj   = preg_replace("/\D/", "", $RNfNfseTs->ConfigEmpresa->cnpj);
                        }else {
                            $sistema = '-'.env('APP_NAME').'-HOMOL';
                            $cpf_cnpj   = preg_replace("/\D/", "", $RNfNfseTs->ConfigEmpresa->cnpj);
                        }
                    }

                    // Formatando os valores monetários
                    foreach ($store as $key => $value) {
                        if (in_array($key, [
                            'cfg_cofins', 'cfg_csll', 'cfg_inss', 'cfg_ir', 'cfg_pis', 'cfg_iss', 'cfg_valor_nota', 'cfg_deducao'
                        ])) {
                            $value = str_replace('.', '', $value);
                            $value = str_replace(',', '.', $value);
                            $store[$key] = floatval($value);
                        }
                    }

                    // Prestador de Serviço
                    $prestador = [
                        "cpfCnpj" => $cpf_cnpj,
                    ];

                    // Recuperando dados do endereço do cliente / destinatário
                    $cidade_tomador = RCidades::find($store['cfg_cidade_prest_serv']);
                    if (!$cidade_tomador) {
                        Session::flash('flash_error', 'Desculpe, cidade do Tomador não encontrada!');
                        return back()->withInput();
                    }

                    // Tomador de Serviço
                    $tomador = [
                        "cpfCnpj"       => $store['tomador_pessoa'] == 'F' ? preg_replace("/\D/", "", $store['tomador_cpf']) : preg_replace("/\D/", "", $store['tomador_cnpj']),
                        "razaoSocial"   => $store['tomador_nome'],
                        "email"         => $store['tomador_cont_email'],
                        "endereco"      => [
                            "tipoLogradouro"    => "Avenida",  // Provisório
                            "logradouro"        => $store['tomador_end_rua'],
                            "numero"            => $store['tomador_end_numero'],
                            "bairro"            => $store['tomador_end_bairro'],
                            "codigoCidade"      => (string)$cidade_tomador->codigo,  // Código IBGE
                            "descricaoCidade"   => $cidade_tomador->nome,
                            "estado"            => $cidade_tomador->uf,
                            "cep"               => preg_replace("/\D/", "", $store['tomador_end_cep']),
                        ],
                    ];

                    // Dados do Serviço
                    $discriminacao = $store['cfg_descricao_nota'] = preg_replace("/&([a-z])[a-z]+;/i", "$1", htmlentities(trim($store['cfg_descricao_nota'])));
                    $servico = [
                        "codigo"            => $store['cfg_lc116'],
                        "discriminacao"     => $discriminacao,
                        "cnae"              => $store['cfg_cnae'],
                        "iss"      => [
                            "tipoTributacao"    => intval($store['cfg_iss_tributacao']),
                            "exigibilidade"     => intval($store['cfg_iss_exigibilidade']),
                            "aliquota"          => $store['cfg_iss'] ? $store['cfg_iss'] : 0,
                        ],
                        "valor"    => [
                            "servico"                   => $store['cfg_valor_nota'],
                            "descontoCondicionado"      => isset($store['cfg_desconto_condicionado']) ? $store['cfg_desconto_condicionado'] : 0,
                            "descontoIncondicionado"    => isset($store['cfg_desconto_incondicionado']) ? $store['cfg_desconto_incondicionado'] : 0,
                        ],
                    ];

                    $data = [
                        [
                            "idIntegracao"  => str_pad($RNfNfseTs->id,6,"0",STR_PAD_LEFT).$sistema,
                            "prestador"     => $prestador,
                            "tomador"       => $tomador,
                            "servico"       => $servico,
                        ],
                    ];

                    $tecnospeed_response = $this->tecnospeed->emitir($data, '/nfse');

                    // Sucesso
                    if (isset($tecnospeed_response['status']) && $tecnospeed_response['status'] == 200) {

                        $RNfNfseTs->nf_response_id            = $tecnospeed_response['response']['documents'][0]['id'];
                        $RNfNfseTs->nf_response_idIntegracao  = $tecnospeed_response['response']['documents'][0]['idIntegracao'];
                        $RNfNfseTs->nf_response_protocol      = $tecnospeed_response['response']['protocol'];
                        $RNfNfseTs->nf_status                 = 'PENDENTE';

                        $tecnospeed_response['nf_log']->response_mensagem = $tecnospeed_response['response']['message'];

                        Session::flash('flash_success', "Nota Fiscal emitida com sucesso. Acompanhe o andamento e status do seu processamento!");
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
                        Session::flash('flash_error', "Erro ao emitir nota fiscal: " .$tecnospeed_response['response']['error']['message']);

                    }elseif ($tecnospeed_response['status'] == 409 && isset($tecnospeed_response['response']['error'])) {

                        $tecnospeed_response['nf_log']->response_mensagem    = $tecnospeed_response['response']['error']['message'];
                        $id = isset($tecnospeed_response['response']['error']['data']['current']['id']) ?
                            ' ID: '.$tecnospeed_response['response']['error']['data']['current']['id'] : '';

                        $tecnospeed_response['nf_log']->response_mensagem  = $tecnospeed_response['nf_log']->response_mensagem.$id;
                        Session::flash('flash_error', "Erro ao emitir nota fiscal: " .$tecnospeed_response['nf_log']->response_mensagem);
                    }

                    $tecnospeed_response['nf_log']->nfse_id         = $RNfNfseTs->id;
                    $tecnospeed_response['nf_log']->autor           = $user->id;
                    $tecnospeed_response['nf_log']->nf_idIntegracao = $RNfNfseTs->nf_response_idIntegracao;
                    $tecnospeed_response['nf_log']->nf_empresa_id   = 1;
                    $tecnospeed_response['nf_log']->nf_cliente_id   = $RNfNfseTs->Cliente->id;
                    $tecnospeed_response['nf_log']->response_id     = isset($RNfNfseTs->nf_response_id) ? $RNfNfseTs->nf_response_id : null;
                    $tecnospeed_response['nf_log']->save();

                    $RNfNfseTs->save();
                    DB::commit();

                    if($user){
                        Logs::cadastrar($user->id, ($user->name . ' r_nf_nfse_ts|'.$acao.': transferiu ID: ' . $RNfNfseTs->id));
                    }

                }catch(\Exception $e){
                    Session::flash('flash_error', "Erro ao trasnferir r_nf_nfse_ts: " . $e->getMessage());
                }
            }

            // :: Edição
            if($acao=='edit' and !isset($store['transferir'])){
                Session::flash('flash_success', "Serviço atualizado com sucesso!");
                if($user){
                    Logs::cadastrar($user->id, ($user->name . ' r_nf_nfse_ts|'.$acao.': atualizou ID: ' . $RNfNfseTs->id));
                }

            // :: Criação
            }elseif(!isset($store['transferir'])) {
                Session::flash('flash_success', "Serviço cadastrado com sucesso!");
                if($user){
                    Logs::cadastrar($user->id, ($user->name . ' r_nf_nfse_ts|'.$acao.': cadastrou ID: ' . $RNfNfseTs->id));
                }
            }

        }catch(Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            Session::flash('flash_error', "Erro ao realizar atualização r_nf_nfse_ts|".(isset($acao)?$acao:'').": " . $e->getMessage());
            return back()->withInput()->with([],400);
        }

        if($redirect){
            return Redirect::to($redirect);
        }

        return Redirect::to('/nota_fiscal/nfse/ts/'.$RNfNfseTs->id.'/edit');
    }

    public function consultar($data, $user, $RNfNfseTs)
    {
        try {

            $data['response_id'] = $RNfNfseTs->nf_response_id;

            $consulta_tecnospeed = $this->tecnospeed->consultar($data, '/nfse');

            // Criando log de consulta
            $consulta_tecnospeed['nf_log']->nfse_id              = $RNfNfseTs->id;
            $consulta_tecnospeed['nf_log']->autor                = $user->id;
            $consulta_tecnospeed['nf_log']->nf_empresa_id        = 1;
            $consulta_tecnospeed['nf_log']->nf_cliente_id        = $RNfNfseTs->mra_nf_cliente_id;
            $consulta_tecnospeed['nf_log']->response             = json_encode($consulta_tecnospeed['response']);
            $consulta_tecnospeed['nf_log']->created_at           = date('Y-m-d H:i:s', strtotime("+1 second"));

            // Caso o retorno seja 200, atualiza os dados da nota fiscal
            if ($consulta_tecnospeed['status'] == 200) {

                // Formatando datas
                if (isset($consulta_tecnospeed['response'][0]['emissao'])) {
                    $data_emissao = str_replace('/', '-', $consulta_tecnospeed['response'][0]['emissao']);
                    $data_emissao = date('Y-m-d', strtotime($data_emissao));
                }
                if (isset($consulta_tecnospeed['response'][0]['autorizacao'])) {
                    $data_autorizacao = str_replace('/', '-', $consulta_tecnospeed['response'][0]['autorizacao']);
                    $data_autorizacao = date('Y-m-d', strtotime($data_autorizacao));
                }

                // Atualizando dados da nota fiscal
                $RNfNfseTs->nf_emissao                  = isset($data_emissao) ? $data_emissao : null;
                $RNfNfseTs->nf_status                   = isset($consulta_tecnospeed['response'][0]['situacao']) ? $consulta_tecnospeed['response'][0]['situacao'] : null;
                $RNfNfseTs->nf_prestador                = isset($consulta_tecnospeed['response'][0]['prestador']) ? $consulta_tecnospeed['response'][0]['prestador'] : null;
                $RNfNfseTs->nf_tomador                  = isset($consulta_tecnospeed['response'][0]['tomador']) ? $consulta_tecnospeed['response'][0]['tomador'] : null;
                $RNfNfseTs->nf_numero_nfse              = isset($consulta_tecnospeed['response'][0]['numeroNfse']) ? $consulta_tecnospeed['response'][0]['numeroNfse'] : null;
                $RNfNfseTs->nf_serie                    = isset($consulta_tecnospeed['response'][0]['serie']) ? $consulta_tecnospeed['response'][0]['serie'] : null;
                $RNfNfseTs->nf_lote                     = isset($consulta_tecnospeed['response'][0]['lote']) ? $consulta_tecnospeed['response'][0]['lote'] : null;
                $RNfNfseTs->nf_numero                   = isset($consulta_tecnospeed['response'][0]['numero']) ? $consulta_tecnospeed['response'][0]['numero'] : null;
                $RNfNfseTs->nf_codigoVerificacao        = isset($consulta_tecnospeed['response'][0]['codigoVerificacao']) ? $consulta_tecnospeed['response'][0]['codigoVerificacao'] : null;
                $RNfNfseTs->nf_data_autorizacao         = isset($data_autorizacao) ? $data_autorizacao : null;
                $RNfNfseTs->save();

                $consulta_tecnospeed['nf_log']->nf_idIntegracao      = $RNfNfseTs->nf_response_idIntegracao;
                $consulta_tecnospeed['nf_log']->response_id          = $RNfNfseTs->nf_response_id;
                $consulta_tecnospeed['nf_log']->response_status      = $RNfNfseTs->nf_status;
                $consulta_tecnospeed['nf_log']->response_mensagem    = isset($consulta_tecnospeed['response'][0]['mensagem']) ? $consulta_tecnospeed['response'][0]['mensagem'] : '---';

                if (in_array($RNfNfseTs->nf_status, ['REJEITADO', 'DENEGADO'])) {
                    $consulta_tecnospeed['nf_log']->response_mensagem = isset($consulta_tecnospeed['response'][0]['mensagem']) ? $consulta_tecnospeed['response'][0]['mensagem'] : null;
                }
                $consulta_tecnospeed['nf_log']->save();

                // Baixando PDF
                if (isset($consulta_tecnospeed['response'][0]['pdf'])) {
                    $this->tecnospeed->baixarPDF($RNfNfseTs, '/nfse');
                }

                // Baixando XML
                if (isset($consulta_tecnospeed['response'][0]['xml']) && $RNfNfseTs->nf_status != 'CANCELADO') {
                    $this->tecnospeed->baixarXML($RNfNfseTs, '/nfse');
                }

            }elseif (isset($consulta_tecnospeed['response']['error'])) {

                $consulta_tecnospeed['nf_log']->response_mensagem  = $consulta_tecnospeed['response']['error']['message'];
                $consulta_tecnospeed['nf_log']->save();

                Log::info('Erro ao consultar nfse: '.json_encode($consulta_tecnospeed['response']));
                return [
                    'error'   => true,
                    'message' => $consulta_tecnospeed['response']['error']['message'],
                ];
            }

            // Log::info('Consulta nfse: '.json_encode($consulta_tecnospeed['response']));
            return [
                'success' => true,
                'data'    => $RNfNfseTs,
            ];

        }catch(\Exception $e){
            Log::info("Erro ao consultar nfse: " . $e->getMessage());
            return [
                'exception' => true,
                'message'   => $e->getMessage(),
            ];
        }
    }

    public function cancelar($data, $RNfNfseTs, $user)
    {
        try {

            $tecnospeed_response = $this->tecnospeed->cancelar($RNfNfseTs->nf_response_id, '/nfse');

            // Sucesso
            if(isset($tecnospeed_response['status']) && $tecnospeed_response['status'] == 200){

                $tecnospeed_response['nf_log']->response_mensagem    = isset($tecnospeed_response['response']['message']) ? $tecnospeed_response['response']['message'] : null;
                $tecnospeed_response['nf_log']->response             = json_encode($tecnospeed_response['nf_log']);
                $tecnospeed_response['nf_log']->response_status      = 'Cancelamento em processamento';
                $tecnospeed_response['nf_log']->save();

                $RNfNfseTs->nf_status = 'AGUARDANDO CANCELAMENTO';

                return [
                    'success' => true,
                    'data'    => $RNfNfseTs,
                ];
            }

            // Erro
            if (isset($tecnospeed_response['response']['error'])) {

                $tecnospeed_response['nf_log']->response_mensagem = isset($tecnospeed_response['response']['error']['message']) ? $tecnospeed_response['response']['error']['message'] : null;
                $tecnospeed_response['nf_log']->response          = json_encode($tecnospeed_response);

                if (isset($tecnospeed_response['response']['error']['data'])) {
                    $tecnospeed_response['nf_log']->response_status = $tecnospeed_response['response']['error']['data']['status'];
                    $RNfNfseTs->nf_status = $tecnospeed_response['response']['error']['data']['status'];
                }

                $tecnospeed_response['nf_log']->save();
                $RNfNfseTs->save();

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

    public static function baixarAnexos($tipo, $response_id)
    {
        try {

            $nfse_ts = RNfNfseTs::where('nf_response_id', $response_id)->first();
            $tecnospeed = new RTecnoSpeed();

            if (!$nfse_ts) {
                Session::flash('flash_error', "Desculpe, Nota Fiscal não encontrada!");
                return back()->withInput();
            }

            // Baixando PDF
            if ($tipo == 'pdf') {
                $tecnospeed_response = $tecnospeed->baixarPDF($nfse_ts, '/nfse');

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
                $tecnospeed_response = $tecnospeed->baixarXML($nfse_ts, '/nfse');

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

                // Baixando XML de cancelamento
                // if ($nfse_ts->nf_status == 'CANCELADO') {
                //     $tecnospeed_response = $tecnospeed->baixarXMLcancelamento($nfse_ts, '/nfse');

                //     if ($tecnospeed_response['status'] == 200) {
                //         Session::flash('flash_success', "XML de Cancelamento baixado com sucesso!");
                //         return back()->withInput();

                //     }elseif ($tecnospeed_response['status'] == 202) {
                //         Session::flash('flash_success', $tecnospeed_response['response']['message']);
                //         return back()->withInput();

                //     }elseif ($tecnospeed_response['status'] == 401 || $tecnospeed_response['status'] == 404) {
                //         Session::flash('flash_success', $tecnospeed_response['response']['error']['message']);
                //         return back()->withInput();
                //     }
                // }
            }

        }catch(\Exception $e){
            Log::info("Erro ao baixar anexos: " . $e->getMessage());
            Session::flash('flash_error', "Erro ao baixar anexos: ".$e->getMessage());
            return back()->withInput();
        }
    }

    public function destroy($id)
    {
        try {

            $RNfNfseTs = RNfNfseTs::find($id);

            if (!$RNfNfseTs) {
                Session::flash('flash_error', "Desculpe, Nota Fiscal não encontrada!");
                return back()->withInput();
            }

            if ($RNfNfseTs->nf_response_id) {
                Session::flash('flash_error', "Desculpe, Notas fiscais já emitidas não podem ser excluídas!");
                return back()->withInput();
            }

            $RNfNfseTs->delete();
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
