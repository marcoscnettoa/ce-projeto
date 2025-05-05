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

use \App\Models\MRAFContasReceber;
use \App\Models\MRAFContasBancarias;
use \App\Models\MRAFContasReceberParcelas;
use \App\Models\MRAFExtratoBancario;
use \App\Models\MRAFFluxoCaixa;
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

class MRAFContasReceberController extends Controller
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

            $MRAFContasReceber  = MRAFContasReceber::getAll(500,$request);

            if($user){
                Logs::cadastrar($user->id, ($user->name . ' visualizou a lista do módulo mra_f_contas_receber'));
            }

            return view('mra_f_contas_receber.index', [
                'exibe_filtros'     => 0,
                'MRAFContasReceber'          => $MRAFContasReceber
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

            $MRAFContasReceber     = null;
            if(!is_null($id)){
                $MRAFContasReceber = MRAFContasReceber::find($id);
                if(!$MRAFContasReceber){
                    \Session::flash('flash_error', 'Registro não encontrado!');
                    return Redirect::to('mra_fluxo_financeiro/mra_f_contas_receber');
                }
            }

            if(!Permissions::permissaoModerador($user) && $MRAFContasReceber->r_auth != 0 && $MRAFContasReceber->r_auth != $user->id){
                Session::flash('flash_error', 'Você não tem permissão para executar esta ação!');
                return Redirect::to('mra_fluxo_financeiro/mra_f_contas_receber');
            }

            if($user){
                // Edição
                if(!is_null($MRAFContasReceber)){
                    Logs::cadastrar($user->id, ($user->name . ' visualizou a tela de edição ID #' . $id . ' do módulo mra_f_contas_receber'));
                    // Criação
                }else {
                    Logs::cadastrar($user->id, ($user->name . ' visualizou a tela de criação do módulo mra_f_contas_receber'));
                }
            }

            return view('mra_f_contas_receber.add_edit', [
                'exibe_filtros'     => 0,
                'MRAFContasReceber'          => $MRAFContasReceber
            ]);

        }catch(\Exception $e){
            Log::error($e->getMessage());
            Session::flash('flash_error', "Ocorreu um erro! Tente novamente.");
            return Redirect::to('/');
        }
    }

    protected function validator(array $data, $id = ''){


        $valor_entrada_status_pagamento_required    = false;
        $parcelas_required                          = false;
        $parcelas_input_lista_equired               = false;
        $parcelas_vencimento_required               = false;
        $parcelas_forma_pagamento_required          = false;
        $parcelas_valor_required                    = false;
        $parcelas_status_pagamento_required         = false;

        if(($data['tipo_pagamento']==2)){
            // ! Parcela deve ser informada sendo maior que 0 (zero)
            if(!(!empty($data['parcelas']) and $data['parcelas'] > 0)){
                $parcelas_required              = true;
            }
            // ! Número de Parcelas = Número de Parcelas Informadas na Lista
            if($data['parcelas'] != count($data['mra_f_contas_receber_parc_id'])){
                $parcelas_input_lista_equired   = true;
            }
            // ! Campos obrigatórios das Parcelas
            if(isset($data['mra_f_contas_receber_parc_id']) and count($data['mra_f_contas_receber_parc_id'])){
                foreach($data['mra_f_contas_receber_parc_id'] as $K => $Parcelas){
                    if(empty($data['parcelas_vencimento'][$K])){       $parcelas_vencimento_required            = true; }
                    if(empty($data['parcelas_forma_pagamento'][$K])){  $parcelas_forma_pagamento_required       = true; }
                    if(empty($data['parcelas_valor'][$K])){            $parcelas_valor_required                 = true; }
                    if(empty($data['parcelas_status_pagamento'][$K])){ $parcelas_status_pagamento_required      = true; }
                }
            }
        }

        // ! Valida se o campo "Valor de Entrada" ou "Status Pagamento Entrada" foi inserida, os 2 campos são obrigatórios
        if(
            (!empty($data['valor_entrada']) and  empty($data['entrada_status_pagamento'])) ||
            ( empty($data['valor_entrada']) and !empty($data['entrada_status_pagamento']))
        ){
            $valor_entrada_status_pagamento_required = true;
        }
        // - #


        return Validator::make($data, [
            'status'                                => 'required',
            'data_competencia'                      => 'required',
            'mra_f_clientes_id'                     => 'required',
            'mra_f_contas_bancarias_id'             => 'required',
            'tipo_pagamento'                        => 'required',
            'vencimento'                            => 'required',
            'av_forma_pagamento'                    => (($data['tipo_pagamento']==1)?'required':''),
            'av_status_pagamento'                   => (($data['tipo_pagamento']==1)?'required':''),
            'valor'                                 => 'required',
            'valor_entrada_status_pagamento'        => ($valor_entrada_status_pagamento_required?'required':''),
            'parcelas_required'                     => ($parcelas_required?'required':''),
            'parcelas_input_lista_equired'          => ($parcelas_input_lista_equired?'required':''),
            'parcelas_vencimento_required'          => ($parcelas_vencimento_required?'required':''),
            'parcelas_forma_pagamento_required'     => ($parcelas_forma_pagamento_required?'required':''),
            'parcelas_valor_required'               => ($parcelas_valor_required?'required':''),
            'parcelas_status_pagamento_required'    => ($parcelas_status_pagamento_required?'required':''),
        ],[
            'status'                                => 'O campo "Status" é obrigatório.',
            'data_competencia'                      => 'O campo "Data de Competência" é obrigatório.',
            'mra_f_clientes_id'                     => 'O campo "Cliente" é obrigatório.',
            'mra_f_contas_bancarias_id'             => 'O campo "Conta Recebimento" é obrigatório.',
            'tipo_pagamento'                        => 'O campo "Tipo de Pagamento" é obrigatório.',
            'vencimento'                            => 'O campo "Vencimento" é obrigatório.',
            'av_forma_pagamento'                    => 'O campo "Forma de Pagamento" é obrigatório.',
            'av_status_pagamento'                   => 'O campo "Status Pagamento" é obrigatório.',
            'valor'                                 => 'O campo "Valor" é obrigatório.',
            'valor_entrada_status_pagamento'        => 'O campo "Valor de Entrada" e "Status Pagamento Entrada" caso utilizados, são obrigatórios.',
            'parcelas_required'                     => 'O campo "Parcelas" é obrigatório.',
            'parcelas_input_lista_equired'          => 'A quantidade do campo "Parcelas" não corresponde as "Parcelas" adicionadas.',
            'parcelas_vencimento_required'          => 'O campo "Vencimento" em Parcelas é obrigatório.',
            'parcelas_forma_pagamento_required'     => 'O campo "Forma de Pagamento" em Parcelas é obrigatório.',
            'parcelas_valor_required'               => 'O campo "Valor" em Parcelas é obrigatório.',
            'parcelas_status_pagamento_required'    => 'O campo "Status Pagamento" em Parcelas é obrigatório.'
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

            $MRAFContasReceber                                      = null;
            if(isset($store['id'])){
                $MRAFContasReceber                                  = MRAFContasReceber::find($store['id']);
                if(!$MRAFContasReceber){
                    \Session::flash('flash_error', 'Registro não encontrado!');
                    return Redirect::to('mra_fluxo_financeiro/mra_f_contas_receber');
                }
            }

            DB::beginTransaction();

            $acao                                                   = 'edit';
            if(is_null($MRAFContasReceber)){
                $MRAFContasReceber                                  = new MRAFContasReceber();
                $acao                                               = 'add';
            }

            $MRAFContasReceber_status_ant                           = $MRAFContasReceber->status; // Status Conclusão - Anterior
            $MRAFContasReceber_entrada_status_pagamento_ant         = $MRAFContasReceber->entrada_status_pagamento; // Status Entrada Pagamento - Anterior
            $MRAFContasReceber_av_status_pagamento_ant              = $MRAFContasReceber->av_status_pagamento; // Status "À Vista" Pagamento - Anterior

            $MRAFContasReceber->status                              = (isset($store['status'])?$store['status']:null); // 1 = Concluído | 2 = Pendente
            $MRAFContasReceber->data_competencia                    = (isset($store['data_competencia'])?\App\Helper\Helper::H_Data_ptBR_DB($store['data_competencia']):null);
            $MRAFContasReceber->mra_f_clientes_id                   = (isset($store['mra_f_clientes_id'])?$store['mra_f_clientes_id']:null);
            $MRAFContasReceber->mra_f_centro_custo_id               = (isset($store['mra_f_centro_custo_id'])?$store['mra_f_centro_custo_id']:null);
            $MRAFContasReceber->mra_f_plano_contas_id               = (isset($store['mra_f_plano_contas_id'])?$store['mra_f_plano_contas_id']:null);
            $MRAFContasReceber->mra_f_contas_bancarias_id           = (isset($store['mra_f_contas_bancarias_id'])?$store['mra_f_contas_bancarias_id']:null);
            $MRAFContasReceber->descricao                           = (isset($store['descricao'])?$store['descricao']:null);
            // :: Anexo I
            if($request->anexo) {
                if($request->hasFile("anexo")) {
                    if(!in_array($request->anexo->getClientOriginalExtension(), $this->upload)) {
                        return back()->withInput()->withErrors("Tipo de arquivo não permitido! Extensões permitidas: " . implode(", ", $this->upload));
                    }
                    if($request->anexo->getSize() > $this->maxSize) {
                        return back()->withInput()->withErrors("Tamanho não permitido! O tamanho do arquivo não pode ser maior que 5MB");
                    }
                    $file                                           = base64_encode($request->anexo->getClientOriginalName()) . "-" . uniqid().".".$request->anexo->getClientOriginalExtension();
                    if(env("FILESYSTEM_DRIVER") == "s3"){
                        Storage::disk("s3")->put("/files/" . env("FILEKEY") . "/images/" . $file, file_get_contents($request->file("anexo")));
                    }else {
                        $request->anexo->move(public_path("images"), $file);
                    }
                    $MRAFContasReceber->anexo                       = $file;
                }
            }else {
                $MRAFContasReceber->anexo                           = (isset($store['anexo'])?$store['anexo']:null);
            }
            // - #
            // :: Anexo II
            if($request->anexo2) {
                if($request->hasFile("anexo2")) {
                    if(!in_array($request->anexo2->getClientOriginalExtension(), $this->upload)) {
                        return back()->withInput()->withErrors("Tipo de arquivo não permitido! Extensões permitidas: " . implode(", ", $this->upload));
                    }
                    if($request->anexo2->getSize() > $this->maxSize) {
                        return back()->withInput()->withErrors("Tamanho não permitido! O tamanho do arquivo não pode ser maior que 5MB");
                    }
                    $file                                           = base64_encode($request->anexo2->getClientOriginalName()) . "-" . uniqid().".".$request->anexo2->getClientOriginalExtension();
                    if(env("FILESYSTEM_DRIVER") == "s3"){
                        Storage::disk("s3")->put("/files/" . env("FILEKEY") . "/images/" . $file, file_get_contents($request->file("anexo2")));
                    }else {
                        $request->anexo2->move(public_path("images"), $file);
                    }
                    $MRAFContasReceber->anexo2                      = $file;
                }
            }else {
                $MRAFContasReceber->anexo2                          = (isset($store['anexo2'])?$store['anexo2']:null);
            }
            // - #
            $MRAFContasReceber->valor                               = (isset($store['valor'])?\App\Helper\Helper::H_Decimal_ptBR_DB($store['valor']):null);

            // Se o "Valor de Entrada" e "Status de Pagamento de Entrada" forem preenchidos
            if(!empty($store['valor_entrada']) and !empty($store['entrada_status_pagamento'])) {
                $MRAFContasReceber->valor_entrada                   = (isset($store['valor_entrada'])?\App\Helper\Helper::H_Decimal_ptBR_DB($store['valor_entrada']):null);
                $MRAFContasReceber->entrada_forma_pagamento         = (isset($store['entrada_forma_pagamento'])?$store['entrada_forma_pagamento']:null);
                $MRAFContasReceber->entrada_status_pagamento        = (isset($store['entrada_status_pagamento'])?$store['entrada_status_pagamento']:null);
            }else {
                $MRAFContasReceber->valor_entrada                   = null;
                $MRAFContasReceber->entrada_forma_pagamento         = null;
                $MRAFContasReceber->entrada_status_pagamento        = null;
            }

            $MRAFContasReceber->juros                               = (isset($store['juros'])?\App\Helper\Helper::H_Decimal_ptBR_DB($store['juros']):null);
            $MRAFContasReceber->multa                               = (isset($store['multa'])?\App\Helper\Helper::H_Decimal_ptBR_DB($store['multa']):null);
            $MRAFContasReceber->tipo_pagamento                      = (isset($store['tipo_pagamento'])?$store['tipo_pagamento']:null);
            $MRAFContasReceber->vencimento                          = (isset($store['vencimento'])?\App\Helper\Helper::H_Data_ptBR_DB($store['vencimento']):null);
            if($MRAFContasReceber->tipo_pagamento == 1){
                $MRAFContasReceber->av_forma_pagamento              = (isset($store['av_forma_pagamento'])?$store['av_forma_pagamento']:null);
                $MRAFContasReceber->av_status_pagamento             = (isset($store['av_status_pagamento'])?$store['av_status_pagamento']:null);
                $MRAFContasReceber->parcelas                        = null;
            }else {
                $MRAFContasReceber->av_forma_pagamento              = null;
                $MRAFContasReceber->av_status_pagamento             = null;
                $MRAFContasReceber->parcelas                        = (isset($store['parcelas'])?$store['parcelas']:null);
            }
            $MRAFContasReceber->r_auth                              = $r_auth;

            $MRAFContasReceber->save();

            // ! Se o "Status Pagamento À Vista" foi alterada para "Pago"
            if($MRAFContasReceber_av_status_pagamento_ant != 1 and $MRAFContasReceber->av_status_pagamento == 1){
                // :: Gerar Fluxo de Caixa
                MRAFFluxoCaixa::create([
                    'descricao'                         => 'À Vista - '.$MRAFContasReceber->descricao,
                    'mra_f_contas_receber_id'           => $MRAFContasReceber->id,
                    'mra_f_centro_custo_id'             => $MRAFContasReceber->mra_f_centro_custo_id,
                    'mra_f_plano_contas_id'             => $MRAFContasReceber->mra_f_plano_contas_id,
                    'mra_f_contas_bancarias_id'         => $MRAFContasReceber->mra_f_contas_bancarias_id,
                    'mra_f_clientes_id'                 => $MRAFContasReceber->mra_f_clientes_id,
                    'valor'                             => ($MRAFContasReceber->valor + $MRAFContasReceber->juros + $MRAFContasReceber->multa - $MRAFContasReceber->valor_entrada),
                    'tipo'                              => 1, // 1 = Entrada
                    //'status'                    => 1  // 1 = Concluído
                    'created_at'                        => Carbon::now()
                ]);

                // :: Gerar Extrato Bancário
                MRAFExtratoBancario::create([
                    'descricao'                         => 'À Vista - '.$MRAFContasReceber->descricao,
                    'mra_f_contas_bancarias_id'         => $MRAFContasReceber->mra_f_contas_bancarias_id,
                    'mra_f_clientes_id'                 => $MRAFContasReceber->mra_f_clientes_id,
                    'valor'                             => ($MRAFContasReceber->valor + $MRAFContasReceber->juros + $MRAFContasReceber->multa - $MRAFContasReceber->valor_entrada),
                    'tipo'                              => 1, // 1 = Entrada
                    //'status'                    => 1  // 1 = Concluído
                    'created_at'                        => Carbon::now()
                ]);

                // + Adiciona o "Valor" a "Conta Bancária" selecionada
                $MRAFContasBancarias                    = MRAFContasBancarias::find($MRAFContasReceber->mra_f_contas_bancarias_id);
                if($MRAFContasBancarias) {
                    $MRAFContasBancarias->valor_atual  += ($MRAFContasReceber->valor + $MRAFContasReceber->juros + $MRAFContasReceber->multa - $MRAFContasReceber->valor_entrada);
                    $MRAFContasBancarias->save();
                }
                // - #
            }
            // - #

            // ! Se o "Status Pagamento de Entrada" foi alterada para "Pago"
            if($MRAFContasReceber_entrada_status_pagamento_ant != 1 and $MRAFContasReceber->entrada_status_pagamento == 1){
                // :: Gerar Fluxo de Caixa
                MRAFFluxoCaixa::create([
                    'descricao'                         => 'Valor de Entrada - '.$MRAFContasReceber->descricao,
                    'mra_f_contas_receber_id'           => $MRAFContasReceber->id,
                    'mra_f_centro_custo_id'             => $MRAFContasReceber->mra_f_centro_custo_id,
                    'mra_f_plano_contas_id'             => $MRAFContasReceber->mra_f_plano_contas_id,
                    'mra_f_contas_bancarias_id'         => $MRAFContasReceber->mra_f_contas_bancarias_id,
                    'mra_f_clientes_id'                 => $MRAFContasReceber->mra_f_clientes_id,
                    'valor'                             => $MRAFContasReceber->valor_entrada,
                    'tipo'                              => 1, // 1 = Entrada
                    //'status'                    => 1  // 1 = Concluído
                    'created_at'                        => Carbon::now()
                ]);

                // :: Gerar Extrato Bancário
                MRAFExtratoBancario::create([
                    'descricao'                         => 'Valor de Entrada - '.$MRAFContasReceber->descricao,
                    'mra_f_contas_bancarias_id'         => $MRAFContasReceber->mra_f_contas_bancarias_id,
                    'mra_f_clientes_id'                 => $MRAFContasReceber->mra_f_clientes_id,
                    'valor'                             => $MRAFContasReceber->valor_entrada,
                    'tipo'                              => 1, // 1 = Entrada
                    //'status'                    => 1  // 1 = Concluído
                    'created_at'                        => Carbon::now()
                ]);

                // + Adiciona o "Valor" a "Conta Bancária" selecionada
                $MRAFContasBancarias                    = MRAFContasBancarias::find($MRAFContasReceber->mra_f_contas_bancarias_id);
                if($MRAFContasBancarias) {
                    $MRAFContasBancarias->valor_atual  += $MRAFContasReceber->valor_entrada;
                    $MRAFContasBancarias->save();
                }
                // - #
            }
            // - #

            // /!\ ---------
            $status_pagamento_pendente = false;

            // :: Tipo de Pagamento 'Parcelado' | 2 = Parcelado
            if($MRAFContasReceber->tipo_pagamento == 2) {
                if(isset($store['parcelas_vencimento']) and count($store['parcelas_vencimento'])){
                    $mra_f_contas_receber_parc_id = array_filter($store['mra_f_contas_receber_parc_id'],function($v,$k){ return ($v != ''); },ARRAY_FILTER_USE_BOTH);
                    MRAFContasReceberParcelas::where('mra_f_contas_receber_id', $MRAFContasReceber->id)->whereNotIn('id', $mra_f_contas_receber_parc_id)->delete();
                    // :: Lista Parcelas
                    foreach($store['mra_f_contas_receber_parc_id'] as $K => $Prod_i){
                        if(!empty($store['mra_f_contas_receber_parc_id'][$K])){
                            $MRAFContasReceberParcelas                       = MRAFContasReceberParcelas::find($store['mra_f_contas_receber_parc_id'][$K]);
                            if(!$MRAFContasReceber){ continue; }
                        }else {
                            $MRAFContasReceberParcelas                       = new MRAFContasReceberParcelas();
                        }
                        $MRAFContasReceberParcelas_status_pagamento_ant      = $MRAFContasReceberParcelas->status_pagamento;
                        $MRAFContasReceberParcelas->mra_f_contas_receber_id  = $MRAFContasReceber->id;
                        $MRAFContasReceberParcelas->vencimento               = (isset($store['parcelas_vencimento'][$K])?\App\Helper\Helper::H_Data_ptBR_DB($store['parcelas_vencimento'][$K]):null);
                        $MRAFContasReceberParcelas->forma_pagamento          = (isset($store['parcelas_forma_pagamento'][$K])?$store['parcelas_forma_pagamento'][$K]:null);
                        $MRAFContasReceberParcelas->valor                    = (isset($store['parcelas_valor'][$K])?\App\Helper\Helper::H_Decimal_ptBR_DB($store['parcelas_valor'][$K]):null);
                        $MRAFContasReceberParcelas->status_pagamento         = (isset($store['parcelas_status_pagamento'][$K])?$store['parcelas_status_pagamento'][$K]:null);
                        $MRAFContasReceberParcelas->r_auth                   = $r_auth;
                        $MRAFContasReceberParcelas->save();

                        // ! Se o "Status Pagamento" da Parcela foi alterada para "Pago"
                        if($MRAFContasReceberParcelas_status_pagamento_ant != 1 and $MRAFContasReceberParcelas->status_pagamento == 1){
                            // :: Gerar Fluxo de Caixa
                            MRAFFluxoCaixa::create([
                                'descricao'                         => 'Parcela '.($K+1).'/'.(count($store['mra_f_contas_receber_parc_id'])).' - '.$MRAFContasReceber->descricao,
                                'mra_f_contas_receber_id'           => $MRAFContasReceber->id,
                                'mra_f_centro_custo_id'             => $MRAFContasReceber->mra_f_centro_custo_id,
                                'mra_f_plano_contas_id'             => $MRAFContasReceber->mra_f_plano_contas_id,
                                'mra_f_contas_bancarias_id'         => $MRAFContasReceber->mra_f_contas_bancarias_id,
                                'mra_f_clientes_id'                 => $MRAFContasReceber->mra_f_clientes_id,
                                'mra_f_contas_receber_parc_id'      => $MRAFContasReceberParcelas->id,
                                'valor'                             => $MRAFContasReceberParcelas->valor,
                                'tipo'                              => 1, // 1 = Entrada
                                //'status'                    => 1  // 1 = Concluído
                                'created_at'                        => Carbon::now()
                            ]);

                            // :: Gerar Extrato Bancário
                            MRAFExtratoBancario::create([
                                'descricao'                         => 'Parcela '.($K+1).'/'.(count($store['mra_f_contas_receber_parc_id'])).' - '.$MRAFContasReceber->descricao,
                                'mra_f_contas_bancarias_id'         => $MRAFContasReceber->mra_f_contas_bancarias_id,
                                'mra_f_clientes_id'                 => $MRAFContasReceber->mra_f_clientes_id,
                                'mra_f_contas_receber_parc_id'      => $MRAFContasReceberParcelas->id,
                                'valor'                             => $MRAFContasReceberParcelas->valor,
                                'tipo'                              => 1, // 1 = Entrada
                                //'status'                    => 1  // 1 = Concluído
                                'created_at'                        => Carbon::now()
                            ]);

                            // + Adiciona o "Valor" a "Conta Bancária" selecionada
                            $MRAFContasBancarias                    = MRAFContasBancarias::find($MRAFContasReceber->mra_f_contas_bancarias_id);
                            if($MRAFContasBancarias){
                                $MRAFContasBancarias->valor_atual  += $MRAFContasReceberParcelas->valor;
                                $MRAFContasBancarias->save();
                            }
                            // - #
                        }

                        // ! Se o "Status Pagamento" da Parcela - "Pendente"
                        if($MRAFContasReceberParcelas->status_pagamento != 1){
                            $status_pagamento_pendente = true;
                        }
                    }
                }
            }else {
                MRAFContasReceberParcelas::where('mra_f_contas_receber_id', $MRAFContasReceber->id)->delete();
            }

            $MRAFContasReceber->save();

            // ! - Verifica se os ( Status Pagamento À Vista ) ou ( Status Pagamento Entrada ) estão "Pendente"
            if(
                // Se for "À Vista" + "Status Pagamento diferente" diferente de "Pendente"
                ($MRAFContasReceber->tipo_pagamento == 1 and $MRAFContasReceber->av_status_pagamento != 1) ||
                // Se tiver "Valor de Entrada" e maior que zero + "Status Pagamento Entrada" diferente de "Pendente"
                (!empty($MRAFContasReceber->valor_entrada) and $MRAFContasReceber->valor_entrada > 0 and $MRAFContasReceber->entrada_status_pagamento != 1)
            ) {
                $status_pagamento_pendente = true;
            }
            // - #

            // ! - Validação - Alterando "Status Geral Conclusão" para "Concluído"
            if($MRAFContasReceber_status_ant != 1 and $MRAFContasReceber->status == 1){
                if($status_pagamento_pendente){
                    Session::flash('flash_error', 'Existem pagamentos "Pendentes" não foi possível alterar o Status para "Concluído"');
                    return back()->withInput()->with([],400);
                }
            }
            // - #

            // ! Verifica se Todos os Status Pagamento estão como "Pago" altera o "Status Conclusão Geral" para Concluído
            if(!$status_pagamento_pendente){
                $MRAFContasReceber->status = 1; // 1 = Concluído
                $MRAFContasReceber->save();
            }
            // - #

            DB::commit();

            // :: Edição
            if($acao=='edit'){
                Session::flash('flash_success', "Contas a Receber atualizada com sucesso!");
                if($user){
                    Logs::cadastrar($user->id, ($user->name . ' mra_f_contas_receber|'.$acao.': atualizou ID: ' . $MRAFContasReceber->id));
                }

            // :: Criação
            }else {
                Session::flash('flash_success', "Contas a Receber cadastrada com sucesso!");
                if($user){
                    Logs::cadastrar($user->id, ($user->name . ' mra_f_contas_receber|'.$acao.': cadastrou ID: ' . $MRAFContasReceber->id));
                }
            }

        }catch(Exception $e) {
            DB::rollback();
            Log::info("Erro ao realizar atualização mra_f_contas_receber|".(isset($acao)?$acao:'').": " . $e->getMessage());
            Session::flash('flash_error', "Erro ao realizar atualização");
            return back()->withInput()->with([],400);
        }

        if($redirect){
            return Redirect::to($redirect);
        }

        return Redirect::to('/mra_fluxo_financeiro/mra_f_contas_receber');

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
        $MRAFContasReceber = MRAFContasReceber::find($id);
        return $this->controllerRepository::destroy(new MRAFContasReceber(), $id, 'mra_fluxo_financeiro/mra_f_contas_receber');
    }
}
