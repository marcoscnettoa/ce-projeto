@php
    $acao                       = ((isset($RNfNfseTs) and !is_null($RNfNfseTs))?'edit':'add');
    $isPublic                   = 0;
    $controller                 = get_class(\Request::route()->getController());
    $config_empresa             = App\Models\RNfConfiguracoesTs::config_empresa();
    $config_empresa_token_api   = ($config_empresa and !empty($config_empresa->token_api)?true:false);

    $permissaoUsuario_auth_user__controller_store       =   false;
    $permissaoUsuario_auth_user__controller_update      =   false;
    $permissaoUsuario_auth_user__controller_destroy     =   false;
    if(\App\Models\Permissions::permissaoUsuario(\Auth::user(), "$controller@store")){   $permissaoUsuario_auth_user__controller_store      = true; }
    if(\App\Models\Permissions::permissaoUsuario(\Auth::user(), "$controller@update")){  $permissaoUsuario_auth_user__controller_update     = true; }
    if(\App\Models\Permissions::permissaoUsuario(\Auth::user(), "$controller@destroy")){ $permissaoUsuario_auth_user__controller_destroy    = true; }

    $disabled     = false;
    if($acao=='edit'){
        if(in_array($RNfNfseTs->nf_status,[
            'PENDENTE','CONCLUIDO','PROCESSAMENTO','PROCESSANDO','DENEGADO','AGUARDANDO CANCELAMENTO','CANCELADO'
        ])){
            $disabled = true;
        }
    }

    if (env('FILESYSTEM_DRIVER') == 's3') {
        $fileurlbase = env('URLS3') . '/' . env('FILEKEY');
    } else {
        $fileurlbase = env('APP_URL').'/storage';
    }
@endphp
@extends($isPublic ? 'layouts.app-public' : 'layouts.app')
@section('content')
    @section('style')
        <style type="text/css">
            .nf_status {
                margin-top: 5px;
                margin-bottom: 0px;
                border: solid 1px #CCC;
                padding: 1px 10px;
                border-radius: 7px;
                font-size: 14px
            }
        </style>
    @endsection
    <section class="content-header">
        <h1>Nota Fiscal - Nota Fiscal de Serviço</h1>
    </section>
    {!! Form::open(['url' => "nota_fiscal/nfse/ts".($acao=='edit'?'/'.$RNfNfseTs->id:''), 'method' => ($acao=='edit'?'put':'post'), 'novalidate'=> true, 'enctype' => 'multipart/form-data', 'accept-charset' => 'utf-8', 'id' => ($acao=='edit'?'form_edit':'form_add').'_nfse_ts']) !!}
    <section class="content" style="min-height: auto;">
        <div class="box" style="margin-bottom: 0; margin-top: 0;">
            @if($acao=='edit')
                {!! Form::hidden('id', $RNfNfseTs->id) !!}
                @if(\App\Models\Permissions::permissaoUsuario(\Auth::user(), "$controller@destroy"))
                    @if(!$RNfNfseTs->nf_response_id)
                        <div class="row" style="position: absolute; right: 0; padding: 5px; z-index: 100;">
                            <div class="col-md-12">
                                <a href="javascript:void(0);" type="button" class="btn btn-danger excluir-auto-confirma" modulo="nota_fiscal/nfse/ts" modulo_id="{{$RNfNfseTs->id}}"><i class="glyphicon glyphicon-trash"></i></a>
                            </div>
                        </div>
                    @endif
                    <div class="box-header">
                        <div class="col-md-12">
                            @php
                                if ($RNfNfseTs->nf_status) {
                                    if ($RNfNfseTs->nf_status == 'CONCLUIDO') {
                                        $label_color    = 'success';
                                    }elseif ($RNfNfseTs->nf_status == 'CANCELADO') {
                                        $label_color    = 'danger';
                                    }elseif ($RNfNfseTs->nf_status == 'DENEGADO' || $RNfNfseTs->nf_status == 'REJEITADO') {
                                        $label_color    = 'warning';
                                    }elseif ($RNfNfseTs->nf_status == 'SUBSTITUIDO') {
                                        $label_color    = 'info';
                                    }else {
                                        $label_color    = 'default';
                                    }
                                }else {
                                    $label_color    = 'default';
                                }
                            @endphp

                            <span class="label-{{$label_color}} nf_status pull-left"> {{$RNfNfseTs->nf_status ? $RNfNfseTs->nf_status : 'NÃO EMITIDA' }}</span>
                        </div>
                    </div>
                @endif
            @endif
            <div class="box-body" style="margin-top: 0px;">
                <div size="12" class="inputbox col-md-12">
                    <h2 class="page-header" style="font-size:20px;">
                        <i class="glyphicon glyphicon-th-large"></i> Dados da Nota
                    </h2>
                </div>
                
                {{-- Anexos NFe --}}
                @if($acao == 'edit' && in_array($RNfNfseTs->nf_status,['CONCLUIDO', 'AGUARDANDO CANCELAMENTO', 'CANCELADO']) )
                    <div size="12" class="inputbox col-md-12" style="margin-bottom: 15px">
                        <div class="row">
                            <div size="12" class="inputbox col-md-12">
                                <div class="form-group">
                                    <div class="text-center" style="margin-left: -26px">
                                        @if (!empty($RNfNfseTs->nf_pdf))
                                            <div size="1" class="inputbox col-md-1">
                                                <div class="form-group">
                                                    <a href="{{ $fileurlbase.$RNfNfseTs->nf_pdf }}" target="_blank" class="op75_h"><img src="{{URL('')}}/pdf-icon.png" height="34" title="Visualizar PDF" alt=""></a>
                                                    <label for="">&nbsp;PDF</label>
                                                </div>
                                            </div>
                                        @else
                                            <div size="1" class="inputbox col-md-1">
                                                <div class="form-group">
                                                    <a href="{{ route('r_nfse.ts.baixarAnexos', ['pdf', $RNfNfseTs->nf_response_id]) }}" class="op75_h"><img src="{{URL('')}}/download-icon.png" height="34" title="Baixar PDF" alt=""></a>
                                                    <label for="">&nbsp;PDF</label>
                                                </div>
                                            </div>
                                        @endif

                                        @if (!empty($RNfNfseTs->nf_xml))
                                            <div size="1" class="inputbox col-md-1">
                                                <div class="form-group">
                                                    <a href="{{ $fileurlbase.$RNfNfseTs->nf_xml }}" target="_blank" class="op75_h"><img src="{{URL('')}}/xml-icon.png" height="34" title="Visualizar XML" alt=""></a>
                                                    <label for="">XML</label>
                                                </div>
                                            </div>
                                        @else
                                            <div size="1" class="inputbox col-md-1">
                                                <div class="form-group">
                                                    <a href="{{ route('r_nfse.ts.baixarAnexos', ['xml', $RNfNfseTs->nf_response_id]) }}" class="op75_h"><img src="{{URL('')}}/download-icon.png" height="34" title="Baixar XML" alt=""></a>
                                                    <label for="">XML</label>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div size="12" class="inputbox col-md-12" style="margin-top: -35px">
                        <h2 class="page-header"></h2>
                    </div>
                @endif

                <div size="12" class="inputbox col-md-12">
                    <div class="row">
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','Data de Competência') !!}
                                <div class="input-group">
                                    <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                                    {!! Form::text('cfg_data_competencia', ($acao=='edit'?\App\Helper\Helper::H_DataHora_DB_ptBR($RNfNfseTs->cfg_data_competencia):null), ['autocomplete' =>'off', 'class' => 'form-control componenteDataHora_v2', "placeholder"=>"__/__/____ __:__", "disabled"=>$disabled, "id" => "input_cfg_data_competencia"]) !!}
                                </div>
                            </div>
                        </div>
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','Data de Emissão') !!}
                                <div class="input-group">
                                    <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                                    {!! Form::text('nf_emissao', ($acao=='edit'?\App\Helper\Helper::H_DataHora_DB_ptBR($RNfNfseTs->nf_emissao):null), ['autocomplete' =>'off', 'class' => 'form-control componenteDataHora_v2', "placeholder"=>"Automático", 'disabled'=>true,"id" => "input_nf_emissao"]) !!}
                                </div>
                            </div>
                        </div>
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','Número da Nota') !!}
                                {!! Form::text('nf_numero_nfse', ($acao=='edit'?(!empty($RNfNfseTs->nf_numero_nfse)?str_pad($RNfNfseTs->nf_numero_nfse, 8, '0', STR_PAD_LEFT):null):'---'), ['class' => 'form-control', "placeholder"=>"Automático", 'disabled'=>true, "id" => "input_nf_numero_nfse"]) !!}
                            </div>
                        </div>
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','Código de Verificação') !!}
                                {!! Form::text('nf_codigoVerificacao', ($acao=='edit'?$RNfNfseTs->nf_codigoVerificacao:null), ['class' => 'form-control', "placeholder"=>"Automático", 'disabled'=>true,"id" => "input_nf_codigoVerificacao"]) !!}
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div size="6" class="inputbox col-md-6">
                            {!! Form::label('','Serviço') !!}
                            <div class="input-group mb_15p">
                                {!! Form::select('mra_nf_prod_serv_id', \App\Models\RNfServicosTs::lista_servicos(), ($acao=='edit'?$RNfNfseTs->mra_nf_prod_serv_id:null), ['class' => 'form-control select_single_no_trigger ss-st2', 'data-live-search' => 'true', "disabled"=>$disabled, "id" => "input_mra_nf_prod_serv_id"]) !!}
                                <span class="input-group-btn">
                                    <button class="btn btn-primary" type="button" {{ ($disabled?'disabled="disabled"':'') }} onclick="javascript:$('#input_mra_nf_prod_serv_id').trigger('change');"><i class="glyphicon glyphicon-refresh"></i></button>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div>
                        <div class="row">
                            <div size="12" class="inputbox col-md-12">
                                <div class="form-group">
                                    {!! Form::label('','Atividade / CNAE') !!}
                                    {!! Form::select('cfg_cnae', \App\Http\Controllers\MRA\RNfListasTsController::Get_options_nf_cnae_23(), ($acao=='edit'?$RNfNfseTs->cfg_cnae:null), ['class' => 'form-control select_single_no_trigger bootstrap-select-st2', 'data-live-search' => 'true', "dropdown-menu-right"=>"", "disabled"=>$disabled, "id" => "input_cfg_cnae"]) !!}
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div size="2" class="inputbox col-md-2">
                                <div class="form-group">
                                    {!! Form::label('','Cofins %') !!}
                                    <div class="input-group">
                                        <div class="input-group-addon"><strong>%</strong></div>
                                        {!! Form::text('cfg_cofins', ($acao=='edit'?\App\Helper\Helper::H_Decimal_DB_ptBR($RNfNfseTs->cfg_cofins):null), ['class' => 'form-control money_v2', "placeholder" => "0,00", "disabled"=>$disabled, "id" => "input_cfg_cofins"]) !!}
                                    </div>
                                </div>
                            </div>
                            <div size="2" class="inputbox col-md-2">
                                <div class="form-group">
                                    {!! Form::label('','CSLL %') !!}
                                    <div class="input-group">
                                        <div class="input-group-addon"><strong>%</strong></div>
                                        {!! Form::text('cfg_csll', ($acao=='edit'?\App\Helper\Helper::H_Decimal_DB_ptBR($RNfNfseTs->cfg_csll):null), ['class' => 'form-control money_v2', "placeholder" => "0,00", "disabled"=>$disabled, "id" => "input_cfg_csll"]) !!}
                                    </div>
                                </div>
                            </div>
                            <div size="2" class="inputbox col-md-2">
                                <div class="form-group">
                                    {!! Form::label('','INSS %') !!}
                                    <div class="input-group">
                                        <div class="input-group-addon"><strong>%</strong></div>
                                        {!! Form::text('cfg_inss', ($acao=='edit'?\App\Helper\Helper::H_Decimal_DB_ptBR($RNfNfseTs->cfg_inss):null), ['class' => 'form-control money_v2', "placeholder" => "0,00", "disabled"=>$disabled, "id" => "input_cfg_inss"]) !!}
                                    </div>
                                </div>
                            </div>
                            <div size="2" class="inputbox col-md-2">
                                <div class="form-group">
                                    {!! Form::label('','IR %') !!}
                                    <div class="input-group">
                                        <div class="input-group-addon"><strong>%</strong></div>
                                        {!! Form::text('cfg_ir', ($acao=='edit'?\App\Helper\Helper::H_Decimal_DB_ptBR($RNfNfseTs->cfg_ir):null), ['class' => 'form-control money_v2', "placeholder" => "0,00", "disabled"=>$disabled, "id" => "input_cfg_ir"]) !!}
                                    </div>
                                </div>
                            </div>
                            <div size="2" class="inputbox col-md-2">
                                <div class="form-group">
                                    {!! Form::label('','PIS %') !!}
                                    <div class="input-group">
                                        <div class="input-group-addon"><strong>%</strong></div>
                                        {!! Form::text('cfg_pis', ($acao=='edit'?\App\Helper\Helper::H_Decimal_DB_ptBR($RNfNfseTs->cfg_pis):null), ['class' => 'form-control money_v2', "placeholder" => "0,00", "disabled"=>$disabled, "id" => "input_cfg_pis"]) !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div size="2" class="inputbox col-md-2">
                                <div class="form-group">
                                    {!! Form::label('','ISS %') !!}
                                    <div class="input-group">
                                        <div class="input-group-addon"><strong>%</strong></div>
                                        {!! Form::text('cfg_iss', ($acao=='edit'?\App\Helper\Helper::H_Decimal_DB_ptBR($RNfNfseTs->cfg_iss,4):null), ['class' => 'form-control money_v2', "placeholder" => "0,0000", "disabled"=>$disabled, "id" => "input_cfg_iss", "maskMoney_precision"=>4]) !!}
                                    </div>
                                </div>
                            </div>
                            <div size="2" class="inputbox col-md-2">
                                <div class="form-group">
                                    {!! Form::label('','ISS Retido na Fonte') !!}
                                    {!! Form::select('cfg_iss_retido_fonte', [1=>'Sim',0=>'Não'], ($acao=='edit'?$RNfNfseTs->cfg_iss_retido_fonte:0), ['class' => 'form-control select_single_no_trigger', "disabled"=>$disabled, "id" => "input_cfg_iss_retido_fonte"]) !!}
                                </div>
                            </div>
                            <div size="4" class="inputbox col-md-4">
                                <div class="form-group">
                                    {!! Form::label('','ISS Tributação') !!}
                                    {!! Form::select('cfg_iss_tributacao', \App\Http\Controllers\MRA\RNfListasTsController::Get_options_iss_tipo_tributacao(), ($acao=='edit'?$RNfNfseTs->cfg_iss_tributacao:0), ['class' => 'form-control select_single_no_trigger', "disabled"=>$disabled, "id" => "input_cfg_iss_tributacao"]) !!}
                                </div>
                            </div>
                            <div size="4" class="inputbox col-md-4">
                                <div class="form-group">
                                    {!! Form::label('','ISS Exigibilidade') !!}
                                    {!! Form::select('cfg_iss_exigibilidade', \App\Http\Controllers\MRA\RNfListasTsController::Get_options_iss_exigibilidade(), ($acao=='edit'?$RNfNfseTs->cfg_iss_exigibilidade:0), ['class' => 'form-control select_single_no_trigger', "disabled"=>$disabled, "id" => "input_cfg_iss_exigibilidade"]) !!}
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div size="12" class="inputbox col-md-12">
                                <div class="form-group">
                                    {!! Form::label('','Lista de Serviço (LC116)') !!}
                                    {!! Form::select('cfg_lc116', \App\Http\Controllers\MRA\RNfListasTsController::Get_options_lista_LC116(), ($acao=='edit'?$RNfNfseTs->cfg_lc116:null), ['class' => 'form-control select_single_no_trigger bootstrap-select-st2', 'data-live-search' => 'true', "dropdown-menu-right"=>"", "disabled"=>$disabled, "id" => "input_imp_servico_lc116"]) !!}
                                </div>
                            </div>
                        </div>
                        {{-- <div class="row">
                            <div size="3" class="inputbox col-md-3">
                                <div class="form-group">
                                    {!! Form::label('','Código de Serviço no Município') !!}
                                    {!! Form::text('cfg_cod_servico', ($acao=='edit'?$RNfNfseTs->cfg_cod_servico:null), ['class' => 'form-control', "disabled"=>$disabled, "id" => "input_cfg_cod_servico", "maxlength"=>200]) !!}
                                </div>
                            </div>
                            <div size="6" class="inputbox col-md-6">
                                <div class="form-group">
                                    {!! Form::label('','Descrição do Serviço no Município') !!}
                                    {!! Form::text('cfg_desc_servico_municipio', ($acao=='edit'?$RNfNfseTs->cfg_desc_servico_municipio:null), ['class' => 'form-control', "disabled"=>$disabled, "id" => "input_cfg_desc_servico_municipio", "maxlength"=>200]) !!}
                                </div>
                            </div>
                        </div> --}}
                        <div class="row">
                            <div size="3" class="inputbox col-md-3">
                                <div class="form-group">
                                    {!! Form::label('','Valor') !!} <span style="color: #ff0500;">*</span>
                                    <div class="input-group">
                                        <div class="input-group-addon"><strong>R$</strong></div>
                                        {!! Form::text('cfg_valor_nota', ($acao=='edit'?\App\Helper\Helper::H_Decimal_DB_ptBR($RNfNfseTs->cfg_valor_nota):null), ['class' => 'form-control money_v2', "placeholder" => "0,00", "disabled"=>$disabled, "id" => "input_cfg_valor_nota"]) !!}
                                    </div>
                                </div>
                            </div>
                            <div size="3" class="inputbox col-md-3">
                                <div class="form-group">
                                    {!! Form::label('','Dedução') !!}
                                    <div class="input-group">
                                        <div class="input-group-addon"><strong>R$</strong></div>
                                        {!! Form::text('cfg_deducao', ($acao=='edit'?\App\Helper\Helper::H_Decimal_DB_ptBR($RNfNfseTs->cfg_deducao):null), ['class' => 'form-control money_v2', "placeholder" => "0,00", "disabled"=>$disabled, "id" => "input_cfg_deducao"]) !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div size="4" class="inputbox col-md-3">
                                <div class="form-group">
                                    {!! Form::label('','UF - ( Prestação de Serviço )') !!} <span style="color: #ff0500;">*</span>
                                    {!! Form::select('cfg_estado_prest_serv', $estados, ($acao=='edit'?$RNfNfseTs->cfg_estado_prest_serv:null), ['class' => 'form-control select_single_no_trigger', 'data-live-search' => 'true', "disabled"=>$disabled, "id" => "input_cfg_estado_prest_serv"]) !!}
                                </div>
                            </div>
                            <div size="4" class="inputbox col-md-3">
                                <div class="form-group">
                                    {!! Form::label('','Cidade - ( Prestação do Serviço )') !!} <span style="color: #ff0500;">*</span>
                                    {!! Form::select('cfg_cidade_prest_serv', [], ($acao=='edit'?$RNfNfseTs->cfg_cidade_prest_serv:null), ['class' => 'form-control select_single_no_trigger', 'data-live-search' => 'true', "disabled"=>$disabled, "id" => "input_cfg_cidade_prest_serv", '_value'=>($acao=='edit'?$RNfNfseTs->cfg_cidade_prest_serv:null)]) !!}
                                </div>
                            </div>

                            {{-- ! Removido - Temporário
                            <div size="4" class="inputbox col-md-3">
                                <div class="form-group">
                                    {!! Form::label('','ID Externo') !!}
                                    {!! Form::text('cfg_id_externo', ($acao=='edit'?$RNfNfseTs->cfg_id_externo:null), ['class' => 'form-control', "disabled"=>$disabled, "id" => "input_cfg_id_externo", "maxlength"=>600]) !!}
                                </div>
                            </div>
                            --}}
                        </div>
                        <div class="row">
                            {{-- <div size="3" class="inputbox col-md-3">
                                <div class="form-group">
                                    {!! Form::label('','Enviar no e-mail?') !!}
                                    {!! Form::select('cfg_enviar_email', [1=>'Sim',0=>'Não'], ($acao=='edit'?$RNfNfseTs->cfg_enviar_email:0), ['class' => 'form-control select_single_no_trigger', "disabled"=>$disabled, "id" => "input_cfg_enviar_email"]) !!}
                                </div>
                            </div> --}}
                            <div id="box_cfg_emails" size="9" class="inputbox col-md-9" {{--style="display:none;"--}}>
                                <div class="form-group {{ ($disabled?'disabled':'') }}">
                                    {!! Form::label('','E-mail(s)') !!}
                                    {!! Form::text('cfg_emails', ($acao=='edit'?$RNfNfseTs->cfg_emails:null), ['class' => 'form-control select_tags', "disabled"=>$disabled, "id" => "input_cfg_emails", "maxTags"=>3, "maxlength"=>600]) !!}
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div size="8" class="inputbox col-md-8">
                                <div class="form-group">
                                    {!! Form::label('','Descrição da Nota') !!} <span style="color: #ff0500;">*</span>
                                    {!! Form::textarea('cfg_descricao_nota', ($acao=='edit'?$RNfNfseTs->cfg_descricao_nota:null), ['class' => 'form-control' , "disabled"=>$disabled, "id" => "input_cfg_descricao_nota", "rows" => 4, "maxlength" => 1000]) !!}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="content" style="min-height: auto; margin-top: -25px">
        <div class="box" style="margin-bottom: 0; margin-top: 0;">
            <div class="box-body" style="margin-top: 0px;">
                <div size="12" class="inputbox col-md-12">
                    <h2 class="page-header" style="font-size:20px;">
                        <i class="glyphicon glyphicon-th-large"></i> Dados do Tomador
                    </h2>
                </div>
                <div size="12" class="inputbox col-md-12">
                    <div class="row">
                        <div size="2" class="inputbox col-md-6 box_cliente">
                            {!! Form::label('','Cliente/Tomador') !!}
                            <div class="input-group mb_15p">
                                {!! Form::select('mra_nf_cliente_id', \App\Models\RNfClientesTs::lista_clientes(), ($acao=='edit'?$RNfNfseTs->mra_nf_cliente_id:null), ['class' => 'form-control select_single_no_trigger ss-st2', 'data-live-search' => 'true', "disabled"=>$disabled, "id" => "input_mra_nf_cliente_id"]) !!}
                                <span class="input-group-btn">
                                    <button class="btn btn-primary" type="button" {{ ($disabled?'disabled="disabled"':'') }} onclick="javascript:$('#input_mra_nf_cliente_id').trigger('change');"><i class="glyphicon glyphicon-refresh"></i></button>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="row box_cliente">
                        <div size="2" class="inputbox col-md-2">
                            <div class="form-group">
                                {!! Form::label('','Tipo de Pessoa') !!} <span style="color: #ff0500;">*</span>
                                {!! Form::select('tomador_pessoa', \App\Http\Controllers\MRA\MRAListas::Get_options_tipo_pessoa(), ($acao=='edit'?$RNfNfseTs->tomador_pessoa:null), ['class' => 'form-control select_single_no_trigger', "disabled"=>$disabled, "id" => "input_tomador_pessoa"]) !!}
                            </div>
                        </div>
                        <div size="6" class="inputbox col-md-6">
                            <div class="form-group">
                                {!! Form::label('','Nome do Tomador') !!} <span style="color: #ff0500;">*</span>
                                {!! Form::text('tomador_nome', ($acao=='edit'?$RNfNfseTs->tomador_nome:null), ['class' => 'form-control', "disabled"=>$disabled, "id" => "input_tomador_nome", "maxlength"=>300]) !!}
                            </div>
                        </div>
                    </div>
                    <div class="row box_cliente">
                        <div id="box_cnpj" size="4" class="inputbox col-md-4">
                            <div class="form-group">
                                {!! Form::label('','CNPJ') !!} <span style="color: #ff0500;">*</span>
                                {!! Form::text('tomador_cnpj', ($acao=='edit'?$RNfNfseTs->tomador_cnpj:null), ['class' => 'form-control cnpj_v2', "placeholder"=>"__.___.___/____-__", "disabled"=>$disabled, "id" => "input_tomador_cnpj", "maxlength"=>50]) !!}
                            </div>
                        </div>
                        <div id="box_cpf" size="4" class="inputbox col-md-4">
                            <div class="form-group">
                                {!! Form::label('','CPF') !!} <span style="color: #ff0500;">*</span>
                                {!! Form::text('tomador_cpf', ($acao=='edit'?$RNfNfseTs->tomador_cpf:null), ['class' => 'form-control cpf', "placeholder"=>"___.___.___-__", "disabled"=>$disabled, "id" => "input_tomador_cpf", "maxlength"=>50]) !!}
                            </div>
                        </div>
                        <div id="box_inscricao_estadual" size="4" class="inputbox col-md-4" >
                            <div class="form-group">
                                {!! Form::label('','Inscrição Estadual (IE)') !!}
                                {!! Form::text('tomador_insc_estadual', ($acao=='edit'?$RNfNfseTs->tomador_insc_estadual:null), ['class' => 'form-control', "disabled"=>$disabled, "id" => "input_inscricao_estadual", "maxlength"=>50]) !!}
                            </div>
                        </div>
                        <div id="box_inscricao_municipal" size="4" class="inputbox col-md-4">
                            <div class="form-group">
                                {!! Form::label('','Inscrição Municipal') !!}
                                {!! Form::text('tomador_insc_municipal', ($acao=='edit'?$RNfNfseTs->tomador_insc_municipal:null), ['class' => 'form-control', "disabled"=>$disabled, "id" => "input_tomador_insc_municipal", "maxlength"=>50]) !!}
                            </div>
                        </div>
                    </div>
                    <div class="row box_cliente">
                        <div size="4" class="inputbox col-md-4">
                            <div class="form-group">
                                {!! Form::label('','Telefone') !!}
                                <div class="input-group">
                                    <div class="input-group-addon"><i class="fa fa-phone"></i></div>
                                    {!! Form::text('tomador_cont_telefone', ($acao=='edit'?$RNfNfseTs->tomador_cont_telefone:null), ['class' => 'form-control telefone',"placeholder"=>"(__) ____-____", "disabled"=>$disabled, "id" => "input_tomador_cont_telefone", "maxlength"=>200]) !!}
                                </div>
                            </div>
                        </div>
                        <div size="6" class="inputbox col-md-6">
                            <div class="form-group">
                                {!! Form::label('','E-mail') !!}
                                <div class="input-group">
                                    <div class="input-group-addon"><i class="fa fa-envelope"></i></div>
                                    {!! Form::text('tomador_cont_email', ($acao=='edit'?$RNfNfseTs->tomador_cont_email:null), ['class' => 'form-control', "disabled"=>$disabled, "id" => "input_tomador_cont_email", "maxlength"=>200]) !!}
                                </div>
                            </div>
                        </div>
                        {{-- <div class="row">
                            <div size="4" class="inputbox col-md-4">
                                <div class="form-group">
                                    {!! Form::label('','Enviar Nota Fiscal - E-mail') !!}
                                    {!! Form::select('tomador_cont_enviar_nf_email', [1=>'Sim',0=>'Não'], ($acao=='edit'?$RNfNfseTs->tomador_cont_enviar_nf_email:null), ['class' => 'form-control select_single_no_trigger', "disabled"=>$disabled, "id" => "input_tomador_cont_enviar_nf_email"]) !!}
                                </div>
                            </div>
                        </div> --}}
                    </div>
                </div>
                <div size="12" class="inputbox col-md-12 box_cliente">
                    <div class="row">
                        <div size="2" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','CEP') !!} <span style="color: #ff0500;">*</span>
                                <div class="input-group">
                                    <div class="input-group-addon"><i class="fa fa-map-pin"></i></div>
                                    {!! Form::text('tomador_end_cep', ($acao=='edit'?$RNfNfseTs->tomador_end_cep:null), ['class' => 'form-control cep_v2', "placeholder"=>"_____-___", "disabled"=>$disabled, "id" => "input_tomador_end_cep", "maxlength"=>50]) !!}
                                </div>
                            </div>
                        </div>
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','Tipo de Logradouro') !!} <span style="color: #ff0500;">*</span>
                                {!! Form::select('tomador_tipo_logradouro', \App\Http\Controllers\MRA\RNfListasTsController::Get_options_tipo_de_logradouro(), ($acao=='edit'?$RNfNfseTs->tomador_tipo_logradouro:null), ['class' => 'form-control', "id" => "input_tomador_tipo_logradouro"]) !!}
                            </div>
                        </div>
                        <div size="6" class="inputbox col-md-6">
                            <div class="form-group">
                                {!! Form::label('','Logradouro / Rua') !!} <span style="color: #ff0500;">*</span>
                                {!! Form::text('tomador_end_rua', ($acao=='edit'?$RNfNfseTs->tomador_end_rua:null), ['class' => 'form-control', "disabled"=>$disabled, "id" => "input_tomador_end_rua", "maxlength"=>200]) !!}
                            </div>
                        </div>
                        <div size="2" class="inputbox col-md-2">
                            <div class="form-group">
                                {!! Form::label('','Número') !!} <span style="color: #ff0500;">*</span>
                                {!! Form::text('tomador_end_numero', ($acao=='edit'?$RNfNfseTs->tomador_end_numero:null), ['class' => 'form-control', "disabled"=>$disabled, "id" => "input_tomador_end_numero", "maxlength"=>50]) !!}
                            </div>
                        </div>
                        <div size="5" class="inputbox col-md-5">
                            <div class="form-group">
                                {!! Form::label('','Bairro') !!} <span style="color: #ff0500;">*</span>
                                {!! Form::text('tomador_end_bairro', ($acao=='edit'?$RNfNfseTs->tomador_end_bairro:null), ['class' => 'form-control', "disabled"=>$disabled, "id" => "input_tomador_end_bairro", "maxlength"=>200]) !!}
                            </div>
                        </div>
                        <div size="5" class="inputbox col-md-5">
                            <div class="form-group">
                                {!! Form::label('','Complemento') !!}
                                {!! Form::text('tomador_end_complemento', ($acao=='edit'?$RNfNfseTs->tomador_end_complemento:null), ['class' => 'form-control', "disabled"=>$disabled, "id" => "input_tomador_end_complemento", "maxlength"=>300]) !!}
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','Estado') !!} <span style="color: #ff0500;">*</span>
                                {!! Form::select('tomador_end_estado', $estados, ($acao=='edit'?$RNfNfseTs->tomador_end_estado:null), ['class' => 'form-control select_single_no_trigger', 'data-live-search' => 'true', "disabled"=>$disabled, "id" => "input_tomador_end_estado"]) !!}
                            </div>
                        </div>
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','Cidade') !!} <span style="color: #ff0500;">*</span>
                                {!! Form::select('tomador_end_cidade', [], ($acao=='edit'?$RNfNfseTs->tomador_end_cidade:null), ['class' => 'form-control', 'data-live-search' => 'true', "disabled"=>$disabled, "id" => "input_tomador_end_cidade", '_value'=>($acao=='edit'?$RNfNfseTs->tomador_end_cidade:null)]) !!}
                            </div>
                        </div>
                        <div size="2" class="inputbox col-md-2">
                            <div class="form-group">
                                {!! Form::label('','País') !!} <span style="color: #ff0500;">*</span>
                                {!! Form::select('tomador_end_pais', \App\Http\Controllers\MRA\MRAListas::Get_options_paises(), (($acao=='edit' and !is_null($RNfNfseTs->tomador_end_pais))?$RNfNfseTs->tomador_end_pais:1058), ['class' => 'form-control select_single_no_trigger', 'data-live-search' => 'true', "dropdown-menu-right"=>"", "disabled"=>$disabled, "id" => "input_tomador_end_pais"]) !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="content" style="min-height: auto; margin-top: -25px">
        <div class="box" style="margin-bottom:0px;">
            <div class="box-body" style="">
                <div class="col-md-12" style="">
                    <div class="form-group form-group-btn-{{($acao=='edit'?'edit':'add')}}">
                        <a href="{{ URL::previous() }}" class="btn btn-default form-group-btn-add-voltar"><i class="glyphicon glyphicon-backward"></i> Voltar</a>
                        @if($acao=='add')
                            @if($permissaoUsuario_auth_user__controller_store)
                                <button type="submit" class="btn btn-default right form-group-btn-add-cadastrar"><span class="glyphicon glyphicon-plus"></span> Cadastrar</button>
                            @endif
                        @endif
                        @if($acao=='edit')
                            @if($permissaoUsuario_auth_user__controller_update and empty($RNfNfseTs->nf_response_id))
                                <button type="submit" class="btn btn-default right form-group-btn-edit-salvar"><span class="glyphicon glyphicon-ok"></span> Salvar</button>
                            @endif
                            @if (!$RNfNfseTs->nf_status || in_array($RNfNfseTs->nf_status, ['REJEITADO']))
                                <button type="submit" name="transferir" value="1" class="btn btn-info right form-group-btn-edit-salvar" onclick="javascript: return confirm('A Nota Fiscal será transferida! Tem certeza?');" style="margin-right:15px;"><i class="glyphicon glyphicon-cloud-upload"></i> {{$RNfNfseTs->nf_status == 'REJEITADO' ? 'Reemitir' : 'Emitir'}}</button>
                            @endif

                            @if(!empty($RNfNfseTs->nf_response_id) && in_array($RNfNfseTs->nf_status,['PENDENTE', 'AGUARDANDO CANCELAMENTO', 'PROCESSANDO']))
                                <button type="submit" name="consultar" value="1" class="btn btn-warning right form-group-btn-edit-salvar" onclick="javascript: return confirm('A Nota Fiscal será consultada!');" style="margin-right:15px;"><i class="glyphicon glyphicon-transfer"></i> Consultar Processamento</button>
                            @endif

                            @if ($RNfNfseTs->nf_status == 'CONCLUIDO' && $RNfNfseTs->nf_response_id)
                                <button type="submit" name="cancelar_nf" value="1" class="btn btn-danger pull-right" title="Solicitar Cancelamento" onclick="javascript: return confirm('Deseja realmente cancelar a Nota Fiscal?');"><i class="fa fa-ban"></i> Cancelar Nota</button>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
    {!! Form::close() !!}

    @if($acao == 'edit' && isset($RNfNfseTs->RNfLogTs) and count($RNfNfseTs->RNfLogTs))
        <section class="content">
            <div class="box" style="margin-bottom: 0; margin-top: -25px;">
                <div class="box-body" style="">
                    <div size="12" class="inputbox col-md-12">
                        <h2 class="page-header" style="font-size:20px; margin-top: -15px">
                            <i class="glyphicon glyphicon-transfer"></i> Log de Processamento(s)
                        </h2>
                    </div>
                    <table class="display table-striped table-bordered stripe dataTable no-footer">
                        <tr>
                            <th>Data e Hora</th>
                            <th>Autor</th>
                            <th>Ação</th>
                            <th>Log</th>
                        </tr>
                            @foreach($RNfNfseTs->RNfLogTs as $log)
                                <tr>
                                    <td>{{ date('d/m/Y H:i', strtotime($log->created_at)) }}</td>
                                    <td>{{ $log->autor ? Auth::user($log->autor)->name : 'Webhook' }}</td>
                                    @php
                                        $acao = '---';
                                        switch($log->acao){
                                            case 'Emitir':      $acao   = 'Emissão'; break;
                                            case 'Consultar':   $acao   = 'Consulta'; break;
                                            case 'Cancelar':    $acao   = 'Cancelamento'; break;
                                            case 'Webhook':     $acao   = 'Webhook'; break;
                                        }
                                    @endphp
                                    <td>{{ $acao }}</td>
                                    @php
                                        $mensagem       = '---';
                                        if(!empty($log->response_mensagem)){
                                            $mensagem   = $log->response_mensagem;
                                        }
                                    @endphp
                                    <td>{{ $log->response_mensagem ?? '---' }}</td>
                                </tr>
                            @endforeach
                    </table>
                </div>
            </div>
        </section>
    @endif

    @section('script')
        <script type="text/javascript">
            // :: Tomador?
            $("#input_tomador").on('change',function(){
                if($(this).val() == 1){
                    $(".box_cliente").show();
                }else {
                    $(".box_cliente").hide();
                }
            }).trigger('change');

            // :: Tipo de Pessoa
            $("#input_tomador_pessoa").on("change",function(){
                switch($("#input_tomador_pessoa").val()) {
                    case 'F':
                        $("#box_cnpj").hide();
                        $("#box_cpf").show();
                        $("#box_inscricao_estadual").hide();
                        $("#box_inscricao_municipal").hide();
                        break;
                    case 'J':
                        $("#box_cnpj").show();
                        $("#box_cpf").hide();
                        $("#box_inscricao_estadual").show();
                        $("#box_inscricao_municipal").show();
                        break;
                    case 'E':
                        $("#box_cnpj").hide();
                        $("#box_cpf").hide();
                        $("#box_inscricao_estadual").hide();
                        $("#box_inscricao_municipal").show();
                        break;
                    default:
                        $("#box_cnpj").hide();
                        $("#box_cpf").hide();
                        $("#box_inscricao_estadual").hide();
                        $("#box_inscricao_municipal").hide();
                        break;
                }
            }).trigger('change');

            // :: Cliente / Tomador
            $("#input_mra_nf_cliente_id").on('change', async function(){
                let _this = $(this);
                if((_this.attr('af') != undefined && _this.attr('af')=='true') || _this.val() == ''){ return false; }
                _this.attr('af','true');
                setTimeout(function(){ _this.attr('af','false'); },1000); // Fix*
                try {
                    $.get('{{URL('nota_fiscal/clientes/ts')}}/'+_this.val()+'/ajax', function(d,s){
                        if(d!=undefined && Object.keys(d).length){
                            $("#input_tomador_pessoa").val(d.tipo).trigger('change');
                            $("#input_tomador_nome").val(d.nome);
                            $("#input_tomador_cnpj").val(d.cnpj);
                            $("#input_tomador_cpf").val(d.cpf);
                            $("#input_tomador_cont_telefone").val(d.cont_telefone);
                            $("#input_tomador_cont_email").val(d.cont_email);
                            $("#input_tomador_cont_enviar_nf_email").val(d.enviar_nf_email).trigger('change');
                            $("#input_tomador_end_cep").val(d.end_cep.replaceAll('.',''));
                            $("#input_tomador_end_rua").val(d.end_rua);
                            $("#input_tomador_end_numero").val(d.end_numero);
                            $("#input_tomador_end_bairro").val(d.end_bairro);
                            $("#input_tomador_end_complemento").val(d.end_complemento);
                            $("#input_tomador_end_estado").val(d.end_estado).trigger('change');
                            $("#input_tomador_end_cidade").val(d.end_cidade);
                            $("#input_tomador_end_pais").val((d.end_pais!=null?d.end_pais:1058)).trigger('change');
                        }
                    });
                }catch(e){
                    //console.log('Erro: '+e);
                }
            });

            // :: Serviço
            $("#input_mra_nf_prod_serv_id").on('change', async function(){
                let _this = $(this);
                if((_this.attr('af') != undefined && _this.attr('af')=='true') || _this.val() == ''){ return false; }
                _this.attr('af','true');
                setTimeout(function(){ _this.attr('af','false'); },1000); // Fix*
                try {
                    $.get('{{URL('nota_fiscal/servicos/ts')}}/'+_this.val()+'/ajax', function(d,s){
                        if(d!=undefined && Object.keys(d).length){
                            $("#input_cfg_cnae").val(d.imp_atividade_cnae).trigger('change');
                            $("#input_cfg_cofins").val(RA.format.Decimal_DB_ptBR(d.imp_confis));
                            $("#input_cfg_csll").val(RA.format.Decimal_DB_ptBR(d.imp_csll));
                            $("#input_cfg_inss").val(RA.format.Decimal_DB_ptBR(d.imp_inss));
                            $("#input_cfg_ir").val(RA.format.Decimal_DB_ptBR(d.imp_ir));
                            $("#input_cfg_pis").val(RA.format.Decimal_DB_ptBR(d.imp_pis));
                            $("#input_cfg_iss").val(RA.format.Decimal_DB_ptBR(d.imp_iss,4));
                            $("#input_cfg_iss_retido_fonte").val(d.imp_iss_retido_fonte).trigger('change');
                            $("#input_imp_servico_lc116").val(d.imp_servico_lc116).trigger('change');
                            $("#input_cfg_cod_servico").val(d.imp_cod_servico_municip);
                            $("#input_cfg_desc_servico_municipio").val(d.imp_desc_servico_municip);
                            $("#input_cfg_valor_nota").val(RA.format.Decimal_DB_ptBR(d.valor));
                            $("#input_cfg_descricao_nota").val(d.descricao_servico);
                            $("#input_cfg_emails").val(d.cfg_emails).tagsinput('destroy');
                            RA.load.select_tags($("#input_cfg_emails"));
                            $("#input_cfg_enviar_email").val(d.cfg_enviar_email).trigger('change');
                        }
                    });
                }catch(e){
                    //console.log('Erro: '+e);
                }
            });

            // Ajax para carregar cidades de acordo com o estado selecionado
            $('#input_tomador_end_estado').on('change',function(){
                $("#input_tomador_end_cidade").html('<option value="">---</option>').selectpicker('refresh');
                if($(this).val == ''){ return false; }
                $.get(base + '/municipios/estado/' + $(this).val() + '/ajax?subForm=uf', function(data, status){
                    $("#input_tomador_end_cidade").val('');
                    if(typeof(data)!='object'){ return; }
                    if(status == 'success'){
                        let options = '';
                        options += '<option value="">---</option>';
                        for(let i = 0; i < data.length; i++) {
                            options += '<option value="' + data[i]['id'] + '">' + data[i]['nome'] + '</option>';
                        }
                        $("#input_tomador_end_cidade").html(options);
                        $("#input_tomador_end_cidade").val($("#input_tomador_end_cidade").attr('_value')).selectpicker('refresh');
                    }
                });
            }).trigger('change');

            // Ajax para carregar cidades de acordo com o estado selecionado da prestação do serviço
            $('#input_cfg_estado_prest_serv').on('change',function(){
                $("#input_cfg_cidade_prest_serv").html('<option value="">---</option>').selectpicker('refresh');
                if($(this).val == ''){ return false; }
                $.get(base + '/municipios/estado/' + $(this).val() + '/ajax?subForm=uf', function(data, status){
                    $("#input_cfg_cidade_prest_serv").val('');
                    if(typeof(data)!='object'){ return; }
                    if(status == 'success'){
                        let options = '';
                        options += '<option value="">---</option>';
                        for(let i = 0; i < data.length; i++) {
                            options += '<option value="' + data[i]['id'] + '">' + data[i]['nome'] + '</option>';
                        }
                        $("#input_cfg_cidade_prest_serv").html(options);
                        $("#input_cfg_cidade_prest_serv").val($("#input_cfg_cidade_prest_serv").attr('_value')).selectpicker('refresh');
                    }
                });
            }).trigger('change');
        </script>
    @endsection

@endsection
