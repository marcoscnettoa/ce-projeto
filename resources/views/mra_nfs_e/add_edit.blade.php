@php
    $acao                       = ((isset($MRANfNfse) and !is_null($MRANfNfse))?'edit':'add');
    $isPublic                   = 0;
    $controller                 = get_class(\Request::route()->getController());
    $config_empresa             = App\Models\MRANfConfiguracoes::config_empresa();
    $config_empresa_token_api   = ($config_empresa and !empty($config_empresa->token_api)?true:false);

    $permissaoUsuario_auth_user__controller_store       =   false;
    $permissaoUsuario_auth_user__controller_update      =   false;
    $permissaoUsuario_auth_user__controller_destroy     =   false;
    if(\App\Models\Permissions::permissaoUsuario(\Auth::user(), "$controller@store")){   $permissaoUsuario_auth_user__controller_store      = true; }
    if(\App\Models\Permissions::permissaoUsuario(\Auth::user(), "$controller@update")){  $permissaoUsuario_auth_user__controller_update     = true; }
    if(\App\Models\Permissions::permissaoUsuario(\Auth::user(), "$controller@destroy")){ $permissaoUsuario_auth_user__controller_destroy    = true; }

    $disabled     = false;
    if($acao=='edit'){
        if(in_array($MRANfNfse->notazz_status,['Autorizada','AguardandoAutorizacao','EmProcessoDeCancelamento','AguardandoCancelamento','Cancelada'])){
            $disabled = true;
        }
    }
@endphp
@extends($isPublic ? 'layouts.app-public' : 'layouts.app')
@section('content')
    @section('style')
        <style type="text/css">
        </style>
    @endsection
    <section class="content-header">
        <h1>Nota Fiscal - Nota Fiscal de Serviço</h1>
        {{--
        @if(!$isPublic)
            <ol class="breadcrumb">
                <li><a href="{{ URL('/') }}">Home</a></li>
                <li><a href="{{ URL('/') }}/emissao_nota_fiscal">Emissão Nota Fiscal</a></li>
                <li class="active">Emissão Nota Fiscal</li>
            </ol>
        @endif
        --}}
    </section>
    {!! Form::open(['url' => "mra_nota_fiscal/mra_nfs_e".($acao=='edit'?'/'.$MRANfNfse->id:''), 'method' => ($acao=='edit'?'put':'post'), 'novalidate'=> true, 'enctype' => 'multipart/form-data', 'accept-charset' => 'utf-8', 'id' => ($acao=='edit'?'form_edit':'form_add').'_mra_nfs_e']) !!}
    <section class="content" style="min-height: auto;">
        <div class="box" style="margin-bottom: 0; margin-top: 0;">
            @if($acao=='edit')
                {!! Form::hidden('id', $MRANfNfse->id) !!}
                @if(\App\Models\Permissions::permissaoUsuario(\Auth::user(), "$controller@destroy"))
                    @if(empty($MRANfNfse->notazz_id_documento))
                    <div class="row" style="position: absolute; right: 0; padding: 5px; z-index: 100;">
                        <div class="col-md-12">
                            <a href="javascript:void(0);" type="button" class="btn btn-danger excluir-auto-confirma" modulo="mra_nota_fiscal/mra_nfs_e" modulo_id="{{$MRANfNfse->id}}"><i class="glyphicon glyphicon-trash"></i></a>
                        </div>
                    </div>
                    @endif
                    <div class="box-header">
                        <div class="col-md-12">
                            @php
                                $nf_badge_status  = 'badge-default';
                                switch($MRANfNfse->notazz_status){
                                    case 'Pendente':              $nf_badge_status = 'badge-info'; break;
                                    case 'Autorizada':            $nf_badge_status = 'badge-success'; break;
                                    case 'EmProcessoDeCancelamento':
                                    case 'AguardandoCancelamento':
                                    case 'EmConflito':
                                    case 'AguardandoAutorizacao': $nf_badge_status = 'badge-warning'; break;
                                    case 'Rejeitada':
                                    case 'Cancelada':
                                    case 'Denegada':             $nf_badge_status  = 'badge-danger'; break;
                                }
                                $notazz_status  = (!empty($MRANfNfse->notazz_status)?\App\Http\Controllers\MRA\MRANotasFiscais::Get_nf_status_nota_fiscal($MRANfNfse->notazz_status):'---');
                                // ! Caso o Status foi Cancelada + Cancelamento Forçado
                                if($MRANfNfse->notazz_status == 'Cancelada' and $MRANfNfse->notazz_status_forcado){
                                    $notazz_status = 'Cancelada no Sistema';
                                }
                            @endphp
                            <span class="badge {{$nf_badge_status}} fw-600">{{ $notazz_status }}</span>
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
                @if(
                    $acao=='edit' and
                    in_array($MRANfNfse->notazz_status,['Autorizada']) and
                    (
                        !empty($MRANfNfse->nf_pdf) ||
                        !empty($MRANfNfse->nf_pdf_prefeitura) ||
                        !empty($MRANfNfse->nf_xml)
                    )
                )
                <div size="12" class="inputbox col-md-12">
                    <div class="row">
                        <div size="12" class="inputbox col-md-12">
                            <div class="form-group">
                                {!! Form::label('','Anexos') !!}
                                <div>
                                    @if(!empty($MRANfNfse->nf_pdf))
                                        <a href="{{$MRANfNfse->nf_pdf}}" target="_blank" class="op75_h"><img src="{{URL('')}}/pdf-icon.png" height="34" title="PDF - Nota Fiscal - Notazz"></a>
                                    @endif
                                    @if(!empty($MRANfNfse->nf_pdf_prefeitura))
                                        @php
                                            // # Fix *
                                            if(!(strpos($MRANfNfse->nf_pdf_prefeitura, '&nf=') !== false)){ $MRANfNfse->nf_pdf_prefeitura.= '&nf='.$MRANfNfse->nf_numero; }
                                            if(!(strpos($MRANfNfse->nf_pdf_prefeitura, '&verificacao=') !== false)){ $MRANfNfse->nf_pdf_prefeitura.= '&verificacao='.$MRANfNfse->nf_codigoVerificacao; }
                                            // - #
                                        @endphp
                                        <a href="{{$MRANfNfse->nf_pdf_prefeitura}}" target="_blank" class="op75_h"><img src="{{URL('')}}/pdfnfev2-icon.png" height="34" title="PDF - Nota Fiscal - Prefeitura"></a>
                                    @endif
                                    @if(!empty($MRANfNfse->nf_xml))
                                        <a href="{{$MRANfNfse->nf_xml}}" target="_blank" class="op75_h"><img src="{{URL('')}}/xml-icon.png" height="34" title="XML - Nota Fiscal"></a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                <div size="12" class="inputbox col-md-12">
                    <div class="row">
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','Data de Competência') !!}
                                <div class="input-group">
                                    <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                                    {!! Form::text('cfg_data_competencia', ($acao=='edit'?\App\Helper\Helper::H_DataHora_DB_ptBR($MRANfNfse->cfg_data_competencia):null), ['autocomplete' =>'off', 'class' => 'form-control componenteDataHora_v2', "placeholder"=>"__/__/____ __:__", "disabled"=>$disabled, "id" => "input_cfg_data_competencia"]) !!}
                                </div>
                            </div>
                        </div>
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','Data de Emissão ( Automático )') !!}
                                <div class="input-group">
                                    <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                                    {!! Form::text('nf_emissao', ($acao=='edit'?\App\Helper\Helper::H_DataHora_DB_ptBR($MRANfNfse->nf_emissao):null), ['autocomplete' =>'off', 'class' => 'form-control componenteDataHora_v2', "placeholder"=>"__/__/____ __:__", 'disabled'=>true,"id" => "input_nf_emissao"]) !!}
                                </div>
                            </div>
                        </div>
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','Número da Nota ( Automático )') !!}
                                {!! Form::text('nf_numero', ($acao=='edit'?(!empty($MRANfNfse->nf_numero)?str_pad($MRANfNfse->nf_numero, 8, '0', STR_PAD_LEFT):null):'---'), ['class' => 'form-control', "placeholder"=>"---", 'disabled'=>true, "id" => "input_nf_numero"]) !!}
                            </div>
                        </div>
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','Código de Verificação ( Automático )') !!}
                                {!! Form::text('nf_codigoVerificacao', ($acao=='edit'?$MRANfNfse->nf_codigoVerificacao:null), ['class' => 'form-control', "placeholder"=>"---", 'disabled'=>true,"id" => "input_nf_codigoVerificacao"]) !!}
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div size="6" class="inputbox col-md-6">
                            {!! Form::label('','Serviço') !!}
                            <div class="input-group mb_15p">
                                {!! Form::select('mra_nf_prod_serv_id', \App\Models\MRANfServicos::lista_servicos(), ($acao=='edit'?$MRANfNfse->mra_nf_prod_serv_id:null), ['class' => 'form-control select_single_no_trigger ss-st2', 'data-live-search' => 'true', "disabled"=>$disabled, "id" => "input_mra_nf_prod_serv_id"]) !!}
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
                                    {!! Form::select('cfg_cnae', \App\Http\Controllers\MRA\MRANotasFiscais::Get_options_nf_cnae_23(), ($acao=='edit'?$MRANfNfse->cfg_cnae:null), ['class' => 'form-control select_single_no_trigger bootstrap-select-st2', 'data-live-search' => 'true', "dropdown-menu-right"=>"", "disabled"=>$disabled, "id" => "input_cfg_cnae"]) !!}
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div size="2" class="inputbox col-md-2">
                                <div class="form-group">
                                    {!! Form::label('','Cofins %') !!}
                                    <div class="input-group">
                                        <div class="input-group-addon"><strong>%</strong></div>
                                        {!! Form::text('cfg_cofins', ($acao=='edit'?\App\Helper\Helper::H_Decimal_DB_ptBR($MRANfNfse->cfg_cofins):null), ['class' => 'form-control money_v2', "placeholder" => "0,00", "disabled"=>$disabled, "id" => "input_cfg_cofins"]) !!}
                                    </div>
                                </div>
                            </div>
                            <div size="2" class="inputbox col-md-2">
                                <div class="form-group">
                                    {!! Form::label('','CSLL %') !!}
                                    <div class="input-group">
                                        <div class="input-group-addon"><strong>%</strong></div>
                                        {!! Form::text('cfg_csll', ($acao=='edit'?\App\Helper\Helper::H_Decimal_DB_ptBR($MRANfNfse->cfg_csll):null), ['class' => 'form-control money_v2', "placeholder" => "0,00", "disabled"=>$disabled, "id" => "input_cfg_csll"]) !!}
                                    </div>
                                </div>
                            </div>
                            <div size="2" class="inputbox col-md-2">
                                <div class="form-group">
                                    {!! Form::label('','INSS %') !!}
                                    <div class="input-group">
                                        <div class="input-group-addon"><strong>%</strong></div>
                                        {!! Form::text('cfg_inss', ($acao=='edit'?\App\Helper\Helper::H_Decimal_DB_ptBR($MRANfNfse->cfg_inss):null), ['class' => 'form-control money_v2', "placeholder" => "0,00", "disabled"=>$disabled, "id" => "input_cfg_inss"]) !!}
                                    </div>
                                </div>
                            </div>
                            <div size="2" class="inputbox col-md-2">
                                <div class="form-group">
                                    {!! Form::label('','IR %') !!}
                                    <div class="input-group">
                                        <div class="input-group-addon"><strong>%</strong></div>
                                        {!! Form::text('cfg_ir', ($acao=='edit'?\App\Helper\Helper::H_Decimal_DB_ptBR($MRANfNfse->cfg_ir):null), ['class' => 'form-control money_v2', "placeholder" => "0,00", "disabled"=>$disabled, "id" => "input_cfg_ir"]) !!}
                                    </div>
                                </div>
                            </div>
                            <div size="2" class="inputbox col-md-2">
                                <div class="form-group">
                                    {!! Form::label('','PIS %') !!}
                                    <div class="input-group">
                                        <div class="input-group-addon"><strong>%</strong></div>
                                        {!! Form::text('cfg_pis', ($acao=='edit'?\App\Helper\Helper::H_Decimal_DB_ptBR($MRANfNfse->cfg_pis):null), ['class' => 'form-control money_v2', "placeholder" => "0,00", "disabled"=>$disabled, "id" => "input_cfg_pis"]) !!}
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
                                        {!! Form::text('cfg_iss', ($acao=='edit'?\App\Helper\Helper::H_Decimal_DB_ptBR($MRANfNfse->cfg_iss,4):null), ['class' => 'form-control money_v2', "placeholder" => "0,0000", "disabled"=>$disabled, "id" => "input_cfg_iss", "maskMoney_precision"=>4]) !!}
                                    </div>
                                </div>
                            </div>
                            <div size="2" class="inputbox col-md-2">
                                <div class="form-group">
                                    {!! Form::label('','ISS Retido na Fonte') !!}
                                    {!! Form::select('cfg_iss_retido_fonte', [1=>'Sim',0=>'Não'], ($acao=='edit'?$MRANfNfse->cfg_iss_retido_fonte:0), ['class' => 'form-control select_single_no_trigger', "disabled"=>$disabled, "id" => "input_cfg_iss_retido_fonte"]) !!}
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div size="12" class="inputbox col-md-12">
                                <div class="form-group">
                                    {!! Form::label('','Item da Lista de Serviço (LC116)') !!}
                                    @php
                                        $cfg_lc116 = null;
                                        if(old('cfg_lc116')){
                                            $cfg_lc116 = old('cfg_lc116');
                                        }else {
                                            $cfg_lc116 = ($acao=='edit'?$MRANfNfse->cfg_lc116:null);
                                        }
                                    @endphp
                                    <select id="input_cfg_lc116" name="cfg_lc116" class="form-control select_single_no_trigger bootstrap-select-st2" {{ ($disabled?'disabled="disabled"':'') }} data-live-search="true">
                                        {!! App\Http\Controllers\MRA\MRANotasFiscais::Get_options_nf_servicos_lc116($cfg_lc116) !!}
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div size="3" class="inputbox col-md-3">
                                <div class="form-group">
                                    {!! Form::label('','Código de Serviço no Município') !!}
                                    {!! Form::text('cfg_cod_servico', ($acao=='edit'?$MRANfNfse->cfg_cod_servico:null), ['class' => 'form-control', "disabled"=>$disabled, "id" => "input_cfg_cod_servico", "maxlength"=>200]) !!}
                                </div>
                            </div>
                            <div size="6" class="inputbox col-md-6">
                                <div class="form-group">
                                    {!! Form::label('','Descrição do Serviço no Município') !!}
                                    {!! Form::text('cfg_desc_servico_municipio', ($acao=='edit'?$MRANfNfse->cfg_desc_servico_municipio:null), ['class' => 'form-control', "disabled"=>$disabled, "id" => "input_cfg_desc_servico_municipio", "maxlength"=>200]) !!}
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div size="3" class="inputbox col-md-3">
                                <div class="form-group">
                                    {!! Form::label('','Valor') !!} <span style="color: #ff0500;">*</span>
                                    <div class="input-group">
                                        <div class="input-group-addon"><strong>R$</strong></div>
                                        {!! Form::text('cfg_valor_nota', ($acao=='edit'?\App\Helper\Helper::H_Decimal_DB_ptBR($MRANfNfse->cfg_valor_nota):null), ['class' => 'form-control money_v2', "placeholder" => "0,00", "disabled"=>$disabled, "id" => "input_cfg_valor_nota"]) !!}
                                    </div>
                                </div>
                            </div>
                            <div size="3" class="inputbox col-md-3">
                                <div class="form-group">
                                    {!! Form::label('','Dedução') !!}
                                    <div class="input-group">
                                        <div class="input-group-addon"><strong>R$</strong></div>
                                        {!! Form::text('cfg_deducao', ($acao=='edit'?\App\Helper\Helper::H_Decimal_DB_ptBR($MRANfNfse->cfg_deducao):null), ['class' => 'form-control money_v2', "placeholder" => "0,00", "disabled"=>$disabled, "id" => "input_cfg_deducao"]) !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div size="4" class="inputbox col-md-3">
                                <div class="form-group">
                                    {!! Form::label('','UF - ( Prestação de Serviço )') !!}
                                    {!! Form::select('cfg_estado_prest_serv', \App\Http\Controllers\MRA\MRAListas::Get_options_estados(), ($acao=='edit'?$MRANfNfse->cfg_estado_prest_serv:null), ['class' => 'form-control select_single_no_trigger', 'data-live-search' => 'true', "disabled"=>$disabled, "id" => "input_cfg_estado_prest_serv"]) !!}
                                </div>
                            </div>
                            <div size="4" class="inputbox col-md-3">
                                <div class="form-group">
                                    {!! Form::label('','Cidade - ( Prestação do Serviço )') !!}
                                    {!! Form::text('cfg_cidade_prest_serv', ($acao=='edit'?$MRANfNfse->cfg_cidade_prest_serv:null), ['class' => 'form-control', "disabled"=>$disabled, "id" => "input_cfg_cidade_prest_serv", "maxlength"=>50]) !!}
                                </div>
                            </div>
                            {{-- ! Removido - Temporário
                            <div size="4" class="inputbox col-md-3">
                                <div class="form-group">
                                    {!! Form::label('','ID Externo') !!}
                                    {!! Form::text('cfg_id_externo', ($acao=='edit'?$MRANfNfse->cfg_id_externo:null), ['class' => 'form-control', "disabled"=>$disabled, "id" => "input_cfg_id_externo", "maxlength"=>600]) !!}
                                </div>
                            </div>
                            --}}
                        </div>
                        <div class="row">
                            <div size="3" class="inputbox col-md-3">
                                <div class="form-group">
                                    {!! Form::label('','Enviar no e-mail?') !!}
                                    {!! Form::select('cfg_enviar_email', [1=>'Sim',0=>'Não'], ($acao=='edit'?$MRANfNfse->cfg_enviar_email:0), ['class' => 'form-control select_single_no_trigger', "disabled"=>$disabled, "id" => "input_cfg_enviar_email"]) !!}
                                </div>
                            </div>
                            <div id="box_cfg_emails" size="9" class="inputbox col-md-9" {{--style="display:none;"--}}>
                                <div class="form-group {{ ($disabled?'disabled':'') }}">
                                    {!! Form::label('','E-mail(s)') !!}
                                    {!! Form::text('cfg_emails', ($acao=='edit'?$MRANfNfse->cfg_emails:null), ['class' => 'form-control select_tags', "disabled"=>$disabled, "id" => "input_cfg_emails", "maxTags"=>3, "maxlength"=>600]) !!}
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div size="8" class="inputbox col-md-8">
                                <div class="form-group">
                                    {!! Form::label('','Descrição da Nota') !!} <span style="color: #ff0500;">*</span>
                                    {!! Form::textarea('cfg_descricao_nota', ($acao=='edit'?$MRANfNfse->cfg_descricao_nota:null), ['class' => 'form-control' , "disabled"=>$disabled, "id" => "input_cfg_descricao_nota", "rows" => 4, "maxlength" => 1000]) !!}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="content" style="min-height: auto;">
        <div class="box" style="margin-bottom: 0; margin-top: 0;">
            <div class="box-body" style="margin-top: 0px;">
                <div size="12" class="inputbox col-md-12">
                    <h2 class="page-header" style="font-size:20px;">
                        <i class="glyphicon glyphicon-th-large"></i> Dados do Tomador
                    </h2>
                </div>
                <div size="12" class="inputbox col-md-12">
                    <div class="row">
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','NFS-e com Tomador') !!}
                                {!! Form::select('tomador', \App\Http\Controllers\MRA\MRANotasFiscais::Get_options_nfs_e_tomador(), ($acao=='edit'?$MRANfNfse->tomador:1), ['class' => 'form-control select_single_no_trigger', "disabled"=>$disabled, "id" => "input_tomador"]) !!}
                            </div>
                        </div>
                        <div size="2" class="inputbox col-md-6 box_cliente" style="display:none;">
                            {!! Form::label('','Cliente/Tomador') !!}
                            <div class="input-group mb_15p">
                                {!! Form::select('mra_nf_cliente_id', \App\Models\MRANfClientes::lista_clientes(), ($acao=='edit'?$MRANfNfse->mra_nf_cliente_id:null), ['class' => 'form-control select_single_no_trigger ss-st2', 'data-live-search' => 'true', "disabled"=>$disabled, "id" => "input_mra_nf_cliente_id"]) !!}
                                <span class="input-group-btn">
                                    <button class="btn btn-primary" type="button" {{ ($disabled?'disabled="disabled"':'') }} onclick="javascript:$('#input_mra_nf_cliente_id').trigger('change');"><i class="glyphicon glyphicon-refresh"></i></button>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="row box_cliente" style="display:none;">
                        <div size="2" class="inputbox col-md-2">
                            <div class="form-group">
                                {!! Form::label('','Tipo de Pessoa') !!} <span style="color: #ff0500;">*</span>
                                {!! Form::select('tomador_pessoa', \App\Http\Controllers\MRA\MRAListas::Get_options_tipo_pessoa(), ($acao=='edit'?$MRANfNfse->tomador_pessoa:null), ['class' => 'form-control select_single_no_trigger', "disabled"=>$disabled, "id" => "input_tomador_pessoa"]) !!}
                            </div>
                        </div>
                        <div size="6" class="inputbox col-md-6">
                            <div class="form-group">
                                {!! Form::label('','Nome do Tomador') !!} <span style="color: #ff0500;">*</span>
                                {!! Form::text('tomador_nome', ($acao=='edit'?$MRANfNfse->tomador_nome:null), ['class' => 'form-control', "disabled"=>$disabled, "id" => "input_tomador_nome", "maxlength"=>300]) !!}
                            </div>
                        </div>
                    </div>
                    <div class="row box_cliente" style="display:none;">
                        <div id="box_cnpj" size="4" class="inputbox col-md-4" style="display:none;">
                            <div class="form-group">
                                {!! Form::label('','CNPJ') !!} <span style="color: #ff0500;">*</span>
                                {!! Form::text('tomador_cnpj', ($acao=='edit'?$MRANfNfse->tomador_cnpj:null), ['class' => 'form-control cnpj_v2', "placeholder"=>"__.___.___/____-__", "disabled"=>$disabled, "id" => "input_tomador_cnpj", "maxlength"=>50]) !!}
                            </div>
                        </div>
                        <div id="box_cpf" size="4" class="inputbox col-md-4" style="display:none;">
                            <div class="form-group">
                                {!! Form::label('','CPF') !!} <span style="color: #ff0500;">*</span>
                                {!! Form::text('tomador_cpf', ($acao=='edit'?$MRANfNfse->tomador_cpf:null), ['class' => 'form-control cpf', "placeholder"=>"___.___.___-__", "disabled"=>$disabled, "id" => "input_tomador_cpf", "maxlength"=>50]) !!}
                            </div>
                        </div>
                        <div id="box_inscricao_estadual" size="4" class="inputbox col-md-4"  style="display:none;">
                            <div class="form-group">
                                {!! Form::label('','Inscrição Estadual (IE)') !!}
                                {!! Form::text('tomador_insc_estadual', ($acao=='edit'?$MRANfNfse->tomador_insc_estadual:null), ['class' => 'form-control', "disabled"=>$disabled, "id" => "input_tomador_insc_estadual", "maxlength"=>50]) !!}
                            </div>
                        </div>
                        <div id="box_inscricao_municipal" size="4" class="inputbox col-md-4" style="display:none;">
                            <div class="form-group">
                                {!! Form::label('','Inscrição Municipal') !!}
                                {!! Form::text('tomador_insc_municipal', ($acao=='edit'?$MRANfNfse->tomador_insc_municipal:null), ['class' => 'form-control', "disabled"=>$disabled, "id" => "input_tomador_insc_municipal", "maxlength"=>50]) !!}
                            </div>
                        </div>
                    </div>
                    <div class="row box_cliente" style="display:none;">
                        <div size="4" class="inputbox col-md-4">
                            <div class="form-group">
                                {!! Form::label('','Telefone') !!}
                                <div class="input-group">
                                    <div class="input-group-addon"><i class="fa fa-phone"></i></div>
                                    {!! Form::text('tomador_cont_telefone', ($acao=='edit'?$MRANfNfse->tomador_cont_telefone:null), ['class' => 'form-control telefone',"placeholder"=>"(__) ____-____", "disabled"=>$disabled, "id" => "input_tomador_cont_telefone", "maxlength"=>200]) !!}
                                </div>
                            </div>
                        </div>
                        <div size="4" class="inputbox col-md-4">
                            <div class="form-group">
                                {!! Form::label('','E-mail') !!}
                                <div class="input-group">
                                    <div class="input-group-addon"><i class="fa fa-envelope"></i></div>
                                    {!! Form::text('tomador_cont_email', ($acao=='edit'?$MRANfNfse->tomador_cont_email:null), ['class' => 'form-control', "disabled"=>$disabled, "id" => "input_tomador_cont_email", "maxlength"=>200]) !!}
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div size="4" class="inputbox col-md-4">
                                <div class="form-group">
                                    {!! Form::label('','Enviar Nota Fiscal - E-mail') !!}
                                    {!! Form::select('tomador_cont_enviar_nf_email', [1=>'Sim',0=>'Não'], ($acao=='edit'?$MRANfNfse->tomador_cont_enviar_nf_email:null), ['class' => 'form-control select_single_no_trigger', "disabled"=>$disabled, "id" => "input_tomador_cont_enviar_nf_email"]) !!}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div size="12" class="inputbox col-md-12 box_cliente" style="display:none;">
                    <div class="row">
                        <div size="2" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','CEP') !!} <span style="color: #ff0500;">*</span>
                                <div class="input-group">
                                    <div class="input-group-addon"><i class="fa fa-map-pin"></i></div>
                                    {!! Form::text('tomador_end_cep', ($acao=='edit'?$MRANfNfse->tomador_end_cep:null), ['class' => 'form-control cep_v2', "placeholder"=>"_____-___", "disabled"=>$disabled, "id" => "input_tomador_end_cep", "maxlength"=>50]) !!}
                                </div>
                            </div>
                        </div>
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','Logradouro / Rua') !!} <span style="color: #ff0500;">*</span>
                                {!! Form::text('tomador_end_rua', ($acao=='edit'?$MRANfNfse->tomador_end_rua:null), ['class' => 'form-control', "disabled"=>$disabled, "id" => "input_tomador_end_rua", "maxlength"=>200]) !!}
                            </div>
                        </div>
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','Número') !!} <span style="color: #ff0500;">*</span>
                                {!! Form::text('tomador_end_numero', ($acao=='edit'?$MRANfNfse->tomador_end_numero:null), ['class' => 'form-control', "disabled"=>$disabled, "id" => "input_tomador_end_numero", "maxlength"=>50]) !!}
                            </div>
                        </div>
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','Bairro') !!} <span style="color: #ff0500;">*</span>
                                {!! Form::text('tomador_end_bairro', ($acao=='edit'?$MRANfNfse->tomador_end_bairro:null), ['class' => 'form-control', "disabled"=>$disabled, "id" => "input_tomador_end_bairro", "maxlength"=>200]) !!}
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div size="5" class="inputbox col-md-4">
                            <div class="form-group">
                                {!! Form::label('','Complemento') !!}
                                {!! Form::text('tomador_end_complemento', ($acao=='edit'?$MRANfNfse->tomador_end_complemento:null), ['class' => 'form-control', "disabled"=>$disabled, "id" => "input_tomador_end_complemento", "maxlength"=>300]) !!}
                            </div>
                        </div>
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','Estado') !!} <span style="color: #ff0500;">*</span>
                                {!! Form::select('tomador_end_estado', \App\Http\Controllers\MRA\MRAListas::Get_options_estados(), ($acao=='edit'?$MRANfNfse->tomador_end_estado:null), ['class' => 'form-control select_single_no_trigger', 'data-live-search' => 'true', "disabled"=>$disabled, "id" => "input_tomador_end_estado"]) !!}
                            </div>
                        </div>
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','Cidade') !!} <span style="color: #ff0500;">*</span>
                                {!! Form::text('tomador_end_cidade', ($acao=='edit'?$MRANfNfse->tomador_end_cidade:null), ['class' => 'form-control', "disabled"=>$disabled, "id" => "input_tomador_end_cidade", "maxlength"=>50]) !!}
                            </div>
                        </div>
                        <div size="2" class="inputbox col-md-2">
                            <div class="form-group">
                                {!! Form::label('','País') !!} <span style="color: #ff0500;">*</span>
                                {!! Form::select('tomador_end_pais', \App\Http\Controllers\MRA\MRAListas::Get_options_paises(), (($acao=='edit' and !is_null($MRANfNfse->tomador_end_pais))?$MRANfNfse->tomador_end_pais:1058), ['class' => 'form-control select_single_no_trigger', 'data-live-search' => 'true', "dropdown-menu-right"=>"", "disabled"=>$disabled, "id" => "input_tomador_end_pais"]) !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="content" style="min-height: auto;">
        <div class="box" style="margin-bottom:0px;">
            <div class="box-body" style="">
                {{--@if(0)
                    @if(App\Models\Permissions::permissaoModerador(\Auth::user()))
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="">Para quem essa informação ficará disponível? Selecione um
                                    usuário. </label>
                                @php
                                    $parserList = array();
                                    $userlist = App\Models\User::get()->toArray();
                                    array_unshift($userlist, array('id' => '',  'name' => ''));
                                    array_unshift($userlist, array('id' => 0,  'name' => 'Disponível para todos'));
                                    foreach($userlist as $u)
                                    {
                                        $parserList[$u['id']] = $u['name'];
                                    }
                                @endphp
                                {!! Form::select('r_auth', $parserList, null, ['class' => 'form-control']) !!}
                            </div>
                        </div>
                    @endif
                @endif--}}
                <div class="col-md-12" style="">
                    <div class="form-group form-group-btn-{{($acao=='edit'?'edit':'add')}}">
                        <a href="{{ URL::previous() }}" class="btn btn-default form-group-btn-add-voltar"><i class="glyphicon glyphicon-backward"></i> Voltar</a>
                        @if($acao=='add')
                            @if($permissaoUsuario_auth_user__controller_store)
                                <button type="submit" class="btn btn-default right form-group-btn-add-cadastrar"><span class="glyphicon glyphicon-plus"></span> Cadastrar</button>
                            @endif
                        @endif
                        @if($acao=='edit')
                            @if($permissaoUsuario_auth_user__controller_update and empty($MRANfNfse->notazz_id_documento))
                                <button type="submit" class="btn btn-default right form-group-btn-edit-salvar"><span class="glyphicon glyphicon-ok"></span> Salvar</button>
                                @if($config_empresa_token_api)
                                <button type="submit" name="transferir" value="1" class="btn btn-info right form-group-btn-edit-salvar" onclick="javascript: return confirm('A Nota Fiscal será transferida! Tem certeza?');" style="margin-right:15px;"><i class="glyphicon glyphicon-cloud-upload"></i> Transferir</button>
                                @endif
                            @endif
                            @if($config_empresa_token_api and $permissaoUsuario_auth_user__controller_update and !empty($MRANfNfse->notazz_id_documento) and in_array($MRANfNfse->notazz_status,['Pendente','EmProcessoDeCancelamento','AguardandoCancelamento','AguardandoAutorizacao']))
                                <button type="submit" name="consultar" value="1" class="btn btn-warning right form-group-btn-edit-salvar" onclick="javascript: return confirm('A Nota Fiscal será consultada!');" style="margin-right:15px;"><i class="glyphicon glyphicon-transfer"></i> Consultar Processamento</button>
                            @endif
                            @if($config_empresa_token_api and $permissaoUsuario_auth_user__controller_update and !empty($MRANfNfse->notazz_id_documento) and in_array($MRANfNfse->notazz_status,['Rejeitada','EmConflito']))
                                <button type="submit" name="transferir" value="1" class="btn btn-info right form-group-btn-edit-salvar" onclick="javascript: return confirm('A Nota Fiscal será atualizada e transferida! Tem certeza?');" style="margin-right:15px;"><i class="glyphicon glyphicon-cloud-upload"></i> Atualizar / Transferir</button>
                            @endif
                            @if($permissaoUsuario_auth_user__controller_destroy and !empty($MRANfNfse->notazz_id_documento) and in_array($MRANfNfse->notazz_status,['Autorizada']))
                                @if($config_empresa_token_api)
                                <button type="submit" name="cancelar_nf" value="1" class="btn btn-danger right form-group-btn-edit-salvar" onclick="javascript: return confirm('A Nota Fiscal será cancelada! Tem certeza?\n** Atenção! Em caso da Nota Fiscal não seja cancelada só poderá ser realizada pelo sistema da prefeitura.');" style="float:right;"><i class="glyphicon glyphicon-floppy-remove"></i>&nbsp;&nbsp;Cancelar</button>
                                @endif
                                <button type="submit" name="cancelar_nf_forcado" value="1" class="btn btn-danger right form-group-btn-edit-salvar" onclick="javascript: return (confirm('A Nota Fiscal será cancelada dentro do sistema! Tem certeza?')?true:false);" style="float:right;"><i class="glyphicon glyphicon-alert"></i>&nbsp;&nbsp;Forçar Cancelamento</button>
                            @endif
                        @endif
                    </div>
                </div>
                @if($config_empresa_token_api and $permissaoUsuario_auth_user__controller_destroy and !empty($MRANfNfse->notazz_id_documento) and in_array($MRANfNfse->notazz_status,['Autorizada']))
                <div class="col-md-12">
                    <p class="text-warning text-right">
                        <strong>** Atenção!</strong> <i class="glyphicon glyphicon-alert"></i><br/>
                        - Em caso da <strong class="text-danger">Nota Fiscal não seja cancelada</strong> só poderá ser realizada pelo <strong>sistema da prefeitura.</strong><br/>
                        - <strong class="text-danger">Forçar Cancelamento</strong> apenas será cancelado <strong>dentro do sistema</strong>!</strong>
                    </p>
                </div>
                @endif
            </div>
        </div>
    </section>
    {!! Form::close() !!}

    @if($acao=='edit' and $MRANfNfse->MRANfLog and count($MRANfNfse->MRANfLog))
    <section class="content">
        <div class="box" style="margin-bottom: 0; margin-top: 0;">
            <div class="box-body" style="">
                <div size="12" class="inputbox col-md-12">
                    <h2 class="page-header" style="font-size:20px;">
                        <i class="glyphicon glyphicon-transfer"></i> Log de Processamento(s)
                    </h2>
                </div>
                <table class="display table-striped table-bordered stripe dataTable no-footer">
                    <tr>
                        <th>Data e Hora</th>
                        <th>Ação</th>
                        <th>Log</th>
                    </tr>
                        @foreach($MRANfNfse->MRANfLog as $Log)
                            <tr>
                                <td>{{ \App\Helper\Helper::H_DataHora_DB_ptBR($Log->created_at) }}</td>
                                @php
                                    $acao = '---';
                                    switch($Log->acao){
                                        case 'create_nfse':  $acao    = 'Criação'; break;
                                        case 'update_nfse':  $acao    = 'Atualização'; break;
                                        case 'delete_nfse':  $acao    = 'Exclusão'; break;
                                        case 'consult_nfse': $acao    = 'Consulta'; break;
                                        case 'cancel_nfse':  $acao    = 'Cancelamento'; break;
                                        case 'webhook':      $acao    = 'Webhook'; break;
                                    }
                                @endphp
                                <td>{{ $acao }}</td>
                                @php
                                    $mensagem       = '---';
                                    if(!empty($Log->notazz_motivo)){
                                        $mensagem   = $Log->notazz_motivo;
                                    }elseif(!empty($Log->notazz_codigoProcessamento)) {
                                        $mensagem   = \App\Http\Controllers\MRA\MRANotazz::Get_notazz_api_erros($Log->notazz_codigoProcessamento);
                                    }
                                @endphp
                                <td>{{ $mensagem }}</td>
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
                    $.get('{{URL('mra_nota_fiscal/mra_clientes')}}/'+_this.val()+'/ajax', function(d,s){
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
                    $.get('{{URL('mra_nota_fiscal/mra_servicos')}}/'+_this.val()+'/ajax', function(d,s){
                        if(d!=undefined && Object.keys(d).length){
                            $("#input_cfg_cnae").val(d.imp_atividade_cnae).trigger('change');
                            $("#input_cfg_cofins").val(RA.format.Decimal_DB_ptBR(d.imp_confis));
                            $("#input_cfg_csll").val(RA.format.Decimal_DB_ptBR(d.imp_csll));
                            $("#input_cfg_inss").val(RA.format.Decimal_DB_ptBR(d.imp_inss));
                            $("#input_cfg_ir").val(RA.format.Decimal_DB_ptBR(d.imp_ir));
                            $("#input_cfg_pis").val(RA.format.Decimal_DB_ptBR(d.imp_pis));
                            $("#input_cfg_iss").val(RA.format.Decimal_DB_ptBR(d.imp_iss,4));
                            $("#input_cfg_iss_retido_fonte").val(d.imp_iss_retido_fonte).trigger('change');
                            $("#input_cfg_lc116").val(d.imp_servico_lc116).trigger('change');
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
        </script>
    @endsection

@endsection
