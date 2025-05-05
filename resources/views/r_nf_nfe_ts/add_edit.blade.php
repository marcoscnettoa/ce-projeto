@php
    $acao                     = ((isset($MRANfNfe) and !is_null($MRANfNfe))?'edit':'add');
    $isPublic                 = 0;
    $controller               = get_class(\Request::route()->getController());
    $config_empresa           = App\Models\RNfConfiguracoesTs::config_empresa();

    if ($config_empresa and !empty($config_empresa->token_api)) {
        $config_empresa_token_api = true;
    }elseif(env('TECNOSPEED_ENVIRONMENT') == 'sandbox' and env('TECNOSPEED_SANDBOX_X_API_KEY')){
        $config_empresa_token_api = true;
    }elseif (env('TECNOSPEED_ENVIRONMENT') == 'production' and env('TECNOSPEED_X_API_KEY')) {
        $config_empresa_token_api = true;
    }else {
        $config_empresa_token_api = false;
    }

    $permissaoUsuario_auth_user__controller_store       =   false;
    $permissaoUsuario_auth_user__controller_update      =   false;
    $permissaoUsuario_auth_user__controller_destroy     =   false;
    if(\App\Models\Permissions::permissaoUsuario(\Auth::user(), "$controller@store")){   $permissaoUsuario_auth_user__controller_store      = true; }
    if(\App\Models\Permissions::permissaoUsuario(\Auth::user(), "$controller@update")){  $permissaoUsuario_auth_user__controller_update     = true; }
    if(\App\Models\Permissions::permissaoUsuario(\Auth::user(), "$controller@destroy")){ $permissaoUsuario_auth_user__controller_destroy    = true; }

    $disabled     = false;
    if($acao=='edit'){
        if(in_array($MRANfNfe->nf_status,[
            'PENDENTE','CONCLUIDO','PROCESSAMENTO','PROCESSANDO','DENEGADO','AGUARDANDO CANCELAMENTO', 'CANCELADO'
        ])){
            $disabled = true;
        }
    }

    $estados = App\Models\REstados::selectRaw('*,CONCAT(sigla," - ",nome) as sigla_nome')
        ->pluck('sigla_nome', 'id')
        ->prepend("---", "");

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
        <h1>Nota Fiscal - Nota Fiscal de Produto</h1>
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
    {!! Form::open(['url' => "nota_fiscal/nfe/ts".($acao=='edit'?'/'.$MRANfNfe->id:''), 'method' => ($acao=='edit'?'put':'post'), 'novalidate'=> true, 'enctype' => 'multipart/form-data', 'accept-charset' => 'utf-8', 'id' => ($acao=='edit'?'form_edit':'form_add').'_nfe_ts']) !!}
    <section class="content" style="min-height: auto;">
        <div class="box" style="margin-bottom: 0; margin-top: 0;">
            @if($acao=='edit')
                {!! Form::hidden('id', $MRANfNfe->id) !!}
                @if(\App\Models\Permissions::permissaoUsuario(\Auth::user(), "$controller@destroy"))
                    @if(!$MRANfNfe->nf_response_id)
                        <div class="row" style="position: absolute; right: 0; padding: 5px; z-index: 100;">
                            <div class="col-md-12">
                                <a href="javascript:void(0);" type="button" class="btn btn-danger excluir-auto-confirma" modulo="nota_fiscal/nfe/ts" modulo_id="{{$MRANfNfe->id}}"><i class="glyphicon glyphicon-trash"></i></a>
                            </div>
                        </div>
                    @endif
                @endif
                <div class="box-header">
                    <div class="col-md-12">
                        @php
                            if ($MRANfNfe->nf_status) {
                                if ($MRANfNfe->nf_status == 'CONCLUIDO') {
                                    $label_color    = 'success';
                                }elseif ($MRANfNfe->nf_status == 'CANCELADO') {
                                    $label_color    = 'danger';
                                }elseif ($MRANfNfe->nf_status == 'DENEGADO' || $MRANfNfe->nf_status == 'REJEITADO') {
                                    $label_color    = 'warning';
                                }else {
                                    $label_color    = 'default';
                                }
                            }else {
                                $label_color    = 'default';
                            }
                        @endphp

                        <span class="label-{{$label_color}} nf_status pull-left"> {{$MRANfNfe->nf_status ? $MRANfNfe->nf_status : 'NÃO EMITIDA' }}</span>
                    </div>
                </div>
            @endif

            <div class="box-body" style="margin-top: 0px;">
                <div size="12" class="inputbox col-md-12">
                    <h2 class="page-header" style="font-size:20px;">
                        <i class="glyphicon glyphicon-th-large"></i> Dados da Nota
                    </h2>
                </div>

                {{-- Anexos NFe --}}
                @if($acao == 'edit' && in_array($MRANfNfe->nf_status,['CONCLUIDO', 'AGUARDANDO CANCELAMENTO', 'CANCELADO']) )
                    <div size="12" class="inputbox col-md-12" style="margin-bottom: 15px">
                        <div class="row">
                            <div size="12" class="inputbox col-md-12">
                                <div class="form-group">
                                    <div class="text-center" style="margin-left: -26px">
                                        @if (!empty($MRANfNfe->nf_pdf))
                                            <div size="1" class="inputbox col-md-1">
                                                <div class="form-group">
                                                    <a href="{{ $fileurlbase.$MRANfNfe->nf_pdf }}" target="_blank" class="op75_h"><img src="{{URL('')}}/pdf-icon.png" height="34" title="Visualizar PDF" alt=""></a>
                                                    <label for="">&nbsp;PDF</label>
                                                </div>
                                            </div>
                                        @else
                                            <div size="1" class="inputbox col-md-1">
                                                <div class="form-group">
                                                    <a href="{{ route('r_nfe.ts.baixarAnexos', ['pdf', $MRANfNfe->nf_response_id]) }}" class="op75_h"><img src="{{URL('')}}/download-icon.png" height="34" title="Baixar PDF" alt=""></a>
                                                    <label for="">&nbsp;PDF</label>
                                                </div>
                                            </div>
                                        @endif

                                        @if (!empty($MRANfNfe->nf_xml))
                                            <div size="1" class="inputbox col-md-1">
                                                <div class="form-group">
                                                    <a href="{{ $fileurlbase.$MRANfNfe->nf_xml }}" target="_blank" class="op75_h"><img src="{{URL('')}}/xml-icon.png" height="34" title="Visualizar XML" alt=""></a>
                                                    <label for="">XML</label>
                                                </div>
                                            </div>
                                        @else
                                            <div size="1" class="inputbox col-md-1">
                                                <div class="form-group">
                                                    <a href="{{ route('r_nfe.ts.baixarAnexos', ['xml', $MRANfNfe->nf_response_id]) }}" class="op75_h"><img src="{{URL('')}}/download-icon.png" height="34" title="Baixar XML" alt=""></a>
                                                    <label for="">XML</label>
                                                </div>
                                            </div>
                                        @endif

                                        @if (!empty($MRANfNfe->nf_xml_cancelamento) && $MRANfNfe->nf_status == 'CANCELADO')
                                            <div size="1" class="inputbox col-md-1">
                                                <div class="form-group">
                                                    <a href="{{ $fileurlbase.$MRANfNfe->nf_xml_cancelamento }}" target="_blank" class="op75_h"><img src="{{URL('')}}/xml-icon.png" height="34" title="Visualizar" alt=""></a>
                                                    <label for=""><i class="fa fa-close text-danger"></i> XML</label>
                                                </div>
                                            </div>
                                        @elseif($MRANfNfe->nf_status == 'CANCELADO')
                                            <div size="1" class="inputbox col-md-1">
                                                <div class="form-group col">
                                                    <a href="{{ route('r_nfe.ts.baixarAnexos', ['xml', $MRANfNfe->nf_response_id]) }}" class="op75_h"><img src="{{URL('')}}/download-icon.png" height="34" title="Baixar XML Cancelamento" alt=""></a>
                                                    <label for="">XML CANCELAMENTO</label>
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
                        {{-- # Desativado # --}}
                        {{--
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','Modelo NF-e') !!} <span style="color: #ff0500;">*</span>
                                {!! Form::select('nfe_modelo', \App\Http\Controllers\MRA\MRANotasFiscais::Get_options_nf_e_modelo(), ($acao=='edit'?$MRANfNfe->nfe_modelo:55), ['class' => 'form-control select_single_no_trigger' , "id" => "input_nfe_modelo"]) !!}
                            </div>
                        </div>
                        --}}
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','Data de Competência') !!}
                                <div class="input-group">
                                    <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                                    {!! Form::text('nfe_data_competencia', ($acao=='edit'?\App\Helper\Helper::H_DataHora_DB_ptBR($MRANfNfe->nfe_data_competencia):null), ['autocomplete' =>'off', 'class' => 'form-control componenteDataHora_v2', "placeholder"=>"__/__/____ __:__", "disabled"=>$disabled, "id" => "input_nfe_data_competencia"]) !!}
                                </div>
                            </div>
                        </div>
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','Data de Emissão ( Automático )') !!}
                                <div class="input-group">
                                    <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                                    {!! Form::text('nf_emissao', ($acao=='edit'?\App\Helper\Helper::H_DataHora_DB_ptBR($MRANfNfe->nf_emissao):null), ['autocomplete' =>'off', 'class' => 'form-control componenteDataHora_v2', "placeholder"=>"__/__/____ __:__", "disabled"=>true, "id" => "input_nf_emissao"]) !!}
                                </div>
                            </div>
                        </div>
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','Número da Nota ( Automático )') !!}
                                {!! Form::text('nf_numero', ($acao=='edit'?(!empty($MRANfNfe->nf_numero)?str_pad($MRANfNfe->nf_numero, 8, '0', STR_PAD_LEFT):null):'---'), ['class' => 'form-control', "placeholder"=>"---", 'disabled'=>true, "id" => "input_nf_numero"]) !!}
                            </div>
                        </div>
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','Chave da Nota ( Automático )') !!}
                                {!! Form::text('nf_chave', ($acao=='edit'?(!empty($MRANfNfe->nf_chave)?$MRANfNfe->nf_chave:null):'---'), ['class' => 'form-control', "placeholder"=>"---", 'disabled'=>true, "id" => "input_nf_chave"]) !!}
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','Finalidade') !!} <span style="color: #ff0500;">*</span>
                                {!! Form::select('nfe_finalidade', \App\Http\Controllers\MRA\RNfListasTsController::Get_options_finalidade(), ($acao=='edit'?$MRANfNfe->nfe_finalidade:null), ['class' => 'form-control select_single_no_trigger', "disabled"=>$disabled , "id" => "input_nfe_finalidade"]) !!}
                            </div>
                        </div>
                        <div size="5" class="inputbox col-md-5">
                            <div class="form-group">
                                {!! Form::label('','Meio de Pagamento') !!} <span style="color: #ff0500;">*</span>
                                {!! Form::select('nfe_meio_de_pagamento', \App\Http\Controllers\MRA\RNfListasTsController::Get_options_meio_de_pagamento(), ($acao=='edit'?$MRANfNfe->nfe_meio_de_pagamento:null), ['class' => 'form-control select_single_no_trigger', "disabled"=>$disabled , "id" => "input_nfe_meio_de_pagamento"]) !!}
                            </div>
                        </div>
                        <div size="4" class="inputbox col-md-4">
                            <div class="form-group">
                                {!! Form::label('','Natureza da Operação') !!}
                                {!! Form::text('nfe_natureza_operacao', ($acao=='edit'?$MRANfNfe->nfe_natureza_operacao:null), ['class' => 'form-control text-uppercase', "disabled"=>$disabled, "id" => "input_nfe_natureza_operacao", "maxlength"=>200]) !!}
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div id="box_nfe_chave_referencia" size="12" class="inputbox col-md-12" style="display:none;">
                            <div class="form-group">
                                {!! Form::label('','Chave da Nota Fiscal ( Referência )') !!}
                                {!! Form::text('nfe_chave_referencia', ($acao=='edit'?$MRANfNfe->nfe_chave_referencia:null), ['class' => 'form-control select_tags', "maxTags"=>5, "disabled"=>$disabled, "id" => "input_nfe_chave_referencia", "maxlength"=>1000]) !!}
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div size="8" class="inputbox col-md-8">
                            <div class="form-group">
                                {!! Form::label('','CNAE Fiscal') !!}
                                {!! Form::select('nfe_cnae_fiscal', \App\Http\Controllers\MRA\RNfListasTsController::Get_options_nf_cnae_23(), $config_empresa->cnae_fiscal, ['class' => 'form-control select_single_no_trigger bootstrap-select-st2', 'data-live-search' => 'true', "dropdown-menu-right"=>"", "disabled"=>$disabled, "id" => "input_nfe_cnae_fiscal"]) !!}
                            </div>
                        </div>
                        <div size="4" class="inputbox col-md-4">
                            <div class="form-group">
                                {!! Form::label('','Cód. Regime Tributário') !!}
                                {!! Form::select('nfe_cod_regime_tributario', \App\Http\Controllers\MRA\RNfListasTsController::Get_options_regime_tributario(), $config_empresa->regime_tributario, ['class' => 'form-control select_single_no_trigger', 'data-live-search' => 'true', "dropdown-menu-right"=>"", "disabled"=>$disabled, "id" => "input_nfe_cod_regime_tributario"]) !!}
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div size="6" class="inputbox col-md-6">
                            <div class="form-group">
                                {!! Form::label('','Informações Adicionais de Interesse do Fisco') !!}
                                {!! Form::textarea('nfe_infor_adic_fisco', ($acao=='edit'?$MRANfNfe->nfe_infor_adic_fisco:null), ['class' => 'form-control', "disabled"=>$disabled , "id" => "input_nfe_infor_adic_fisco", "rows" => 4, "maxlength" => 1000]) !!}
                            </div>
                        </div>
                        <div size="6" class="inputbox col-md-6">
                            <div class="form-group">
                                {!! Form::label('','Informações Complementares de interesse do Contribuinte') !!}
                                {!! Form::textarea('nfe_infor_comple_int_contr', ($acao=='edit'?$MRANfNfe->nfe_infor_comple_int_contr:null), ['class' => 'form-control', "disabled"=>$disabled , "id" => "input_nfe_infor_comple_int_contr", "rows" => 4, "maxlength" => 1000]) !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="content" style="min-height: auto; margin-top: -20px;">
        <div class="box" style="margin-bottom: 0; margin-top: 0;">
            <div class="box-body" style="margin-top: 0px;">
                <div size="12" class="inputbox col-md-12">
                    <h2 class="page-header" style="font-size:20px;">
                        <i class="glyphicon glyphicon-th-large"></i> Produto(s)
                    </h2>
                </div>
                <div size="12" class="inputbox col-md-12">
                    <div class="row">
                        <div class="GridProdutos_grid Grid_grid" count="0">
                            @php
                                $total_de_produtos  = 0;
                                $total_de_descontos = 0;
                                $total_de_fretes    = 0;
                                $total_de_seguro    = 0;
                                $total              = 0;
                                function GridProdutosItens($acao,$MRANfNfe,$Item,&$total_de_produtos,&$total_de_descontos,&$total_de_fretes,&$total_de_seguro,$total,$disabled) {
                                    $total_de_produtos  += (($acao=='edit' and $Item)?($Item->quantidade * $Item->valor_unitario):0);
                                    $total_de_descontos += (($acao=='edit' and $Item)?$Item->valor_desconto:0);
                                    $total_de_fretes    += (($acao=='edit' and $Item)?$Item->valor_frete:0);
                                    $total_de_seguro    += (($acao=='edit' and $Item)?$Item->valor_seguro:0);
                                    $total              += ($total_de_produtos + $total_de_fretes + $total_de_seguro) - $total_de_descontos;
                            @endphp
                            <div class="divdefault item">
                                <div class="col-md-12" style="margin-bottom: 10px;">
                                    <div class="row">
                                        {!! Form::hidden('mra_nf_nf_e_prod_i_id[]', ($acao=='edit'?($Item?$Item->id:null):null), ["disabled"=>$disabled]) !!}
                                        <div size="12" class="inputbox col-md-12">
                                            <div class="row">
                                                <div size="10" class="inputbox col-md-10">
                                                    {!! Form::label('','Produto') !!}
                                                    <div class="input-group">
                                                        {!! Form::select('mra_nf_prod_id[]', \App\Models\RNfProdutosTs::lista_produtos(), ($acao=='edit'?($Item?$Item->mra_nf_prod_id:null):null), ['class' => 'form-control select_single_no_trigger ss-st2 mra_nf_prod_id', 'data-live-search' => 'true', "disabled"=>$disabled]) !!}
                                                        <span class="input-group-btn">
                                                            <button class="btn btn-primary" type="button" {{ ($disabled?'disabled="disabled"':'') }} onclick="javascript:$(this).parents('.item').find('select.mra_nf_prod_id').trigger('change');"><i class="glyphicon glyphicon-refresh"></i></button>
                                                        </span>
                                                    </div>
                                                </div>

                                                {!! Form::hidden('mra_nf_prod_nome[]', ($acao=='edit'?($Item?$Item->nome:null):null), ['class' => 'form-control mra_nf_prod_nome', "maxlength"=>250, "disabled"=>$disabled]) !!}

                                                <div size="2" class="inputbox col-md-2">
                                                    <div class="form-group">
                                                        {!! Form::label('','Código') !!} <span style="color: #ff0500;">*</span>
                                                        {!! Form::text('mra_nf_prod_codigo[]', ($acao=='edit'?($Item?$Item->codigo:null):null), ['class' => 'form-control mra_nf_prod_codigo', "maxlength"=>250, "disabled"=>$disabled]) !!}
                                                    </div>
                                                </div>
                                                <div size="2" class="inputbox col-md-2">
                                                    <div class="form-group">
                                                        {!! Form::label('','ORIGEM') !!} <span style="color: #ff0500;">*</span>
                                                        {!! Form::text('mra_nf_prod_origem[]', ($acao=='edit'?($Item?$Item->origem:null):null), ['class' => 'form-control mra_nf_prod_origem', "maxlength"=>4, "disabled"=>$disabled]) !!}
                                                    </div>
                                                </div>
                                                <div size="2" class="inputbox col-md-2">
                                                    <div class="form-group">
                                                        {!! Form::label('','CST') !!} <span style="color: #ff0500;">*</span>
                                                        {!! Form::text('mra_nf_prod_cst[]', ($acao=='edit'?($Item?$Item->cst:null):null), ['class' => 'form-control mra_nf_prod_cst', "maxlength"=>4, "disabled"=>$disabled]) !!}
                                                    </div>
                                                </div>
                                                <div size="2" class="inputbox col-md-2">
                                                    <div class="form-group">
                                                        {!! Form::label('','CFOP') !!} <span style="color: #ff0500;">*</span>
                                                        {!! Form::text('mra_nf_prod_cfop[]', ($acao=='edit'?($Item?$Item->cfop:null):null), ['class' => 'form-control mra_nf_prod_cfop', "maxlength"=>4, "disabled"=>$disabled]) !!}
                                                    </div>
                                                </div>
                                                <div size="2" class="inputbox col-md-2">
                                                    <div class="form-group">
                                                        {!! Form::label('','NCM') !!} <span style="color: #ff0500;">*</span>
                                                        {!! Form::number('mra_nf_prod_ncm[]', ($acao=='edit'?($Item?$Item->ncm:null):null), ['class' => 'form-control mra_nf_prod_ncm', "maxlength"=>8, "disabled"=>$disabled]) !!}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div size="6" class="inputbox col-md-6">
                                            <div class="form-group">
                                                {!! Form::label('','CÓDIGO DE BARRAS') !!}
                                                {!! Form::text('mra_nf_prod_codigo_barras[]', ($acao=='edit'?($Item?$Item->codigo_barras:null):null), ['class' => 'form-control mra_nf_prod_codigo_barras', "maxlength"=>100, "disabled"=>$disabled]) !!}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div size="2" class="inputbox col-md-2">
                                            <div class="form-group">
                                                {!! Form::label('','Qt.') !!} <span style="color: #ff0500;">*</span>
                                                {!! Form::number('mra_nf_prod_qt[]', ($acao=='edit'?($Item?$Item->quantidade:null):null), ['class' => 'form-control mra_nf_prod_qt', "placeholder"=>"0", "min"=>0, "disabled"=>$disabled]) !!}
                                            </div>
                                        </div>
                                        <div size="2" class="inputbox col-md-2">
                                            <div class="form-group">
                                                {!! Form::label('','Valor') !!} <span style="color: #ff0500;">*</span>
                                                {!! Form::text('mra_nf_prod_valor_unit[]', ($acao=='edit'?($Item?\App\Helper\Helper::H_Decimal_DB_ptBR($Item->valor_unitario):null):null), ['class' => 'form-control money_v2 mra_nf_prod_valor_unit', "placeholder" => "0,00", "disabled"=>$disabled]) !!}
                                            </div>
                                        </div>
                                        <div size="2" class="inputbox col-md-2">
                                            <div class="form-group">
                                                {!! Form::label('','Medida') !!}
                                                {!! Form::text('mra_nf_prod_umedida[]', ($acao=='edit'?($Item?$Item->unidade_medida:null):null), ['class' => 'form-control mra_nf_prod_umedida', "maxlength"=>100, "disabled"=>$disabled]) !!}
                                            </div>
                                        </div>
                                        <div size="2" class="inputbox col-md-2">
                                            <div class="form-group">
                                                {!! Form::label('','Subtotal') !!}
                                                {!! Form::text('mra_nf_prod_valor_subtotal[]', ($acao=='edit'?($Item?\App\Helper\Helper::H_Decimal_DB_ptBR($Item->valor_subtotal):null):null), ['class' => 'form-control money_v2 mra_nf_prod_valor_subtotal', "disabled"=>true, "placeholder" => "0,00"]) !!}
                                            </div>
                                        </div>
                                        <div size="2" class="inputbox col-md-2">
                                            <div class="form-group">
                                                {!! Form::label('','Desconto', ['class'=>'fc-st1']) !!}
                                                {!! Form::text('mra_nf_prod_valor_desconto[]', ($acao=='edit'?($Item?\App\Helper\Helper::H_Decimal_DB_ptBR($Item->valor_desconto):null):null), ['class' => 'form-control money_v2 bs-st1 mra_nf_prod_valor_desconto', "placeholder" => "0,00", "disabled"=>$disabled]) !!}
                                            </div>
                                        </div>
                                        <div size="2" class="inputbox col-md-2">
                                            <div class="form-group">
                                                {!! Form::label('','Frete', ['class'=>'fc-st1']) !!}
                                                {!! Form::text('mra_nf_prod_valor_frete[]', ($acao=='edit'?($Item?\App\Helper\Helper::H_Decimal_DB_ptBR($Item->valor_frete):null):null), ['class' => 'form-control money_v2 bs-st1 mra_nf_prod_valor_frete', "placeholder" => "0,00", "disabled"=>$disabled]) !!}
                                            </div>
                                        </div>
                                        <div size="2" class="inputbox col-md-2">
                                            <div class="form-group">
                                                {!! Form::label('','Seguro', ['class'=>'fc-st1']) !!}
                                                {!! Form::text('mra_nf_prod_valor_seguro[]', ($acao=='edit'?($Item?\App\Helper\Helper::H_Decimal_DB_ptBR($Item->valor_seguro):null):null), ['class' => 'form-control money_v2 bs-st1 mra_nf_prod_valor_seguro', "placeholder" => "0,00", "disabled"=>$disabled]) !!}
                                            </div>
                                        </div>
                                        <div size="2" class="inputbox col-md-2">
                                            <div class="form-group">
                                                {!! Form::label('','Despesas', ['class'=>'fc-st1']) !!}
                                                {!! Form::text('mra_nf_prod_valor_despesas[]', ($acao=='edit'?($Item?\App\Helper\Helper::H_Decimal_DB_ptBR($Item->valor_outras_despesas):null):null), ['class' => 'form-control money_v2 bs-st1 mra_nf_prod_valor_despesas', "placeholder" => "0,00", "disabled"=>$disabled, "id" => "input_valor_outras_despesas"]) !!}
                                            </div>
                                        </div>
                                        <div size="2" class="inputbox col-md-2">
                                            <div class="form-group">
                                                {!! Form::label('','CST/CSOSN ICMS', ['class'=>'fc-st1']) !!}
                                                {!! Form::text('mra_nf_prod_imp_cst_csosn_icms[]', ($acao=='edit'?($Item?$Item->imp_cst_csosn_icms:null):null), ['class' => 'form-control bs-st1 mra_nf_prod_imp_cst_csosn_icms', "maxlength"=>100, "disabled"=>$disabled]) !!}
                                            </div>
                                        </div>
                                        <div size="2" class="inputbox col-md-2">
                                            <div class="form-group">
                                                {!! Form::label('','Alíquota ICMS', ['class'=>'fc-st1']) !!}
                                                {!! Form::text('mra_nf_prod_imp_aliquota_icms[]', ($acao=='edit'?($Item?\App\Helper\Helper::H_Decimal_DB_ptBR($Item->imp_aliquota_icms):null):null), ['class' => 'form-control money_v2 bs-st1 mra_nf_prod_imp_aliquota_icms', "placeholder" => "0,00", "disabled"=>$disabled]) !!}
                                            </div>
                                        </div>
                                        <div size="2" class="inputbox col-md-2">
                                            <div class="form-group">
                                                {!! Form::label('','CST IPI', ['class'=>'fc-st1']) !!} <span style="color: #ff0500;">*</span>
                                                {!! Form::text('mra_nf_prod_imp_cst_ipi[]', ($acao=='edit'?($Item?$Item->imp_cst_ipi:null):null), ['class' => 'form-control bs-st1 mra_nf_prod_imp_cst_ipi', "maxlength"=>100, "disabled"=>$disabled]) !!}
                                            </div>
                                        </div>
                                        <div size="2" class="inputbox col-md-2">
                                            <div class="form-group">
                                                {!! Form::label('','Alíquota IPI', ['class'=>'fc-st1']) !!}
                                                {!! Form::text('mra_nf_prod_imp_aliquota_ipi[]', ($acao=='edit'?($Item?\App\Helper\Helper::H_Decimal_DB_ptBR($Item->imp_aliquota_ipi):null):null), ['class' => 'form-control money_v2 bs-st1 mra_nf_prod_imp_aliquota_ipi', "placeholder" => "0,00", "disabled"=>$disabled]) !!}
                                            </div>
                                        </div>
                                        <div size="2" class="inputbox col-md-2">
                                            <div class="form-group">
                                                {!! Form::label('','CST PIS', ['class'=>'fc-st1']) !!} <span style="color: #ff0500;">*</span>
                                                {!! Form::text('mra_nf_prod_imp_cst_pis[]', ($acao=='edit'?($Item?$Item->imp_cst_pis:null):null), ['class' => 'form-control bs-st1 mra_nf_prod_imp_cst_pis', "maxlength"=>100, "disabled"=>$disabled]) !!}
                                            </div>
                                        </div>
                                        <div size="2" class="inputbox col-md-2">
                                            <div class="form-group">
                                                {!! Form::label('','Alíquota PIS', ['class'=>'fc-st1']) !!}
                                                {!! Form::text('mra_nf_prod_imp_aliquota_pis[]', ($acao=='edit'?($Item?\App\Helper\Helper::H_Decimal_DB_ptBR($Item->imp_aliquota_pis):null):null), ['class' => 'form-control money_v2 bs-st1 mra_nf_prod_imp_aliquota_pis', "placeholder" => "0,00", "disabled"=>$disabled]) !!}
                                            </div>
                                        </div>
                                        <div size="2" class="inputbox col-md-2">
                                            <div class="form-group">
                                                {!! Form::label('','CST COFINS', ['class'=>'fc-st1']) !!} <span style="color: #ff0500;">*</span>
                                                {!! Form::text('mra_nf_prod_imp_cst_cofins[]', ($acao=='edit'?($Item?$Item->imp_cst_cofins:null):null), ['class' => 'form-control bs-st1 mra_nf_prod_imp_cst_cofins', "maxlength"=>100, "disabled"=>$disabled]) !!}
                                            </div>
                                        </div>
                                        <div size="2" class="inputbox col-md-2">
                                            <div class="form-group">
                                                {!! Form::label('','Alíquota COFINS', ['class'=>'fc-st1']) !!}
                                                {!! Form::text('mra_nf_prod_imp_aliquota_cofins[]', ($acao=='edit'?($Item?\App\Helper\Helper::H_Decimal_DB_ptBR($Item->imp_aliquota_cofins):null):null), ['class' => 'form-control money_v2 bs-st1 mra_nf_prod_imp_aliquota_cofins', "placeholder" => "0,00", "disabled"=>$disabled]) !!}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <i class="glyphicon glyphicon-trash text-danger grid_remove_v2" style="{{ ($disabled?'display:none;':'') }}" data="GridServicos_grid"></i>
                            </div>
                            @php
                                }
                            @endphp
                            @if($MRANfNfe and $MRANfNfe->RNfNfeProdutosItensTs and count($MRANfNfe->RNfNfeProdutosItensTs))
                                @foreach($MRANfNfe->RNfNfeProdutosItensTs as $Item)
                                    {{ GridProdutosItens($acao,$MRANfNfe,$Item,$total_de_produtos,$total_de_descontos,$total_de_fretes,$total_de_seguro,$total,$disabled) }}
                                @endforeach
                            @else
                                {{ GridProdutosItens($acao,null,null,$total_de_produtos,$total_de_descontos,$total_de_fretes,$total_de_seguro,$total,$disabled) }}
                            @endif
                        </div>
                        <div class="col-md-12 text-right">
                            <i class="glyphicon glyphicon-plus multiple_add_v2" style="{{ ($disabled?'display:none;':'') }}" data="GridProdutos_grid"></i>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 text-left">
                            <table cellpadding="0" cellspacing="0" style="margin:0px; padding:0px;">
                                <tr>
                                    <td style="text-align:right;"><strong>Produto(s) R$:</strong></td>
                                    <td style="text-align:left;">&nbsp;<span id="mra_total_produtos">{{ (!empty($total_de_produtos)?\App\Helper\Helper::H_Decimal_DB_ptBR($total_de_produtos):'0,00') }}</span></td>
                                    {!! Form::hidden('nfe_valor_itens', null, ['id'=>'nfe_valor_itens']) !!}
                                </tr>
                                <tr>
                                    <td style="text-align:right;"><strong>Desconto(s) R$:</strong></td>
                                    <td style="text-align:left;">&nbsp;<span id="mra_total_descontos">{{ (!empty($total_de_descontos)?\App\Helper\Helper::H_Decimal_DB_ptBR($total_de_descontos):'0,00') }}</span></td>
                                    {!! Form::hidden('nfe_valor_desconto', $total_de_descontos, ['id'=>'nfe_valor_desconto']) !!}
                                </tr>
                                <tr>
                                    <td style="text-align:right;"><strong>Frete(s) R$:</strong></td>
                                    <td style="text-align:left;">&nbsp;<span id="mra_total_fretes">{{ (!empty($total_de_fretes)?\App\Helper\Helper::H_Decimal_DB_ptBR($total_de_fretes):'0,00') }}</span></td>
                                    {!! Form::hidden('nfe_valor_frete', $total_de_fretes, ['id'=>'nfe_valor_frete']) !!}
                                </tr>
                                <tr>
                                    <td style="text-align:right;"><strong>Seguro(s) R$:</strong></td>
                                    <td style="text-align:left;">&nbsp;<span id="mra_total_seguros">{{ (!empty($total_de_seguro)?\App\Helper\Helper::H_Decimal_DB_ptBR($total_de_seguro):'0,00') }}</span></td>
                                    {!! Form::hidden('nfe_valor_seguro', $total_de_seguro, ['id'=>'nfe_valor_seguro']) !!}
                                </tr>
                                <tr>
                                    <td style="text-align:right;"><strong>Total R$:</strong></td>
                                    <td style="text-align:left;">&nbsp;<span id="mra_total">{{ (!empty($total)?\App\Helper\Helper::H_Decimal_DB_ptBR($total):'0,00') }}</span></td>
                                    {!! Form::hidden('nfe_total', $total, ['id'=>'nfe_total']) !!}
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    {{--<section class="content" style="min-height: auto;">
        <div class="box" style="margin-bottom: 0; margin-top: 0;">
            <div class="box-body" style="margin-top: 0px;">
                <div size="12" class="inputbox col-md-12">
                    <h2 class="page-header" style="font-size:20px;">
                        <i class="glyphicon glyphicon-th-large"></i> Dados da Empresa / Emitente
                    </h2>
                </div>

                <div size="12" class="inputbox col-md-12">
                    <div class="row">
                        <div size="6" class="inputbox col-md-6">
                            <div class="form-group">
                                {!! Form::label('','Razão Social') !!}
                                {!! Form::text('emi_razao_social', ($acao=='edit'?(!empty($MRANfNfe->emi_razao_social)?$MRANfNfe->emi_razao_social:($config_empresa?$config_empresa->razao_social:null)):($config_empresa?$config_empresa->razao_social:null)), ['class' => 'form-control' , "id" => "input_emi_razao_social", "disabled"=>true, "maxlength"=>200]) !!}
                            </div>
                        </div>
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','CNPJ') !!}
                                {!! Form::text('emi_cnpj', ($acao=='edit'?(!empty($MRANfNfe->emi_cnpj)?$MRANfNfe->emi_cnpj:($config_empresa?$config_empresa->cnpj:null)):($config_empresa?$config_empresa->cnpj:null)), ['class' => 'form-control cnpj_v2', "placeholder"=>"__.___.___/____-__", "id" => "input_emi_cnpj", "disabled"=>true, "maxlength"=>50]) !!}
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','Inscrição Estadual') !!}
                                {!! Form::text('emi_inscricao_estadual', ($acao=='edit'?(!empty($MRANfNfe->emi_inscricao_estadual)?$MRANfNfe->emi_inscricao_estadual:($config_empresa?$config_empresa->inscricao_estadual:null)):($config_empresa?$config_empresa->inscricao_estadual:null)), ['class' => 'form-control' , "id" => "input_emi_inscricao_estadual", "disabled"=>true, "maxlength"=>50]) !!}
                            </div>
                        </div>
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','Inscrição Municipal') !!}
                                {!! Form::text('emi_inscricao_municipal', ($acao=='edit'?(!empty($MRANfNfe->emi_inscricao_municipal)?$MRANfNfe->emi_inscricao_municipal:($config_empresa?$config_empresa->inscricao_municipal:null)):($config_empresa?$config_empresa->inscricao_municipal:null)), ['class' => 'form-control' , "id" => "input_emi_inscricao_municipal", "disabled"=>true, "maxlength"=>50]) !!}
                            </div>
                        </div>
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','Telefone') !!}
                                <div class="input-group">
                                    <div class="input-group-addon"><i class="fa fa-phone"></i></div>
                                    {!! Form::text('emi_telefone', ($acao=='edit'?(!empty($MRANfNfe->emi_telefone)?$MRANfNfe->emi_telefone:($config_empresa?$config_empresa->cont_telefone:null)):($config_empresa?$config_empresa->cont_telefone:null)), ['class' => 'form-control telefone',"placeholder"=>"(__) ____-____", "id" => "input_emi_telefone", "disabled"=>true, "maxlength"=>200]) !!}
                                </div>
                            </div>
                        </div>
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','E-mail') !!}
                                <div class="input-group">
                                    <div class="input-group-addon"><i class="fa fa-envelope"></i></div>
                                    {!! Form::text('emi_email', ($acao=='edit'?(!empty($MRANfNfe->emi_email)?$MRANfNfe->emi_email:($config_empresa?$config_empresa->cont_email:null)):($config_empresa?$config_empresa->cont_email:null)), ['class' => 'form-control' , "id" => "input_emi_email", "disabled"=>true, "maxlength"=>200]) !!}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div size="2" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','CEP') !!}
                                <div class="input-group">
                                    <div class="input-group-addon"><i class="fa fa-map-pin"></i></div>
                                    {!! Form::text('emi_end_cep', ($acao=='edit'?(!empty($MRANfNfe->emi_end_cep)?$MRANfNfe->emi_end_cep:($config_empresa?$config_empresa->end_cep:null)):($config_empresa?$config_empresa->end_cep:null)), ['class' => 'form-control cep_v2', "placeholder"=>"_____-___", "id" => "input_emi_end_cep", "disabled"=>true, "maxlength"=>50]) !!}
                                </div>
                            </div>
                        </div>
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','Rua') !!}
                                {!! Form::text('emi_end_rua', ($acao=='edit'?(!empty($MRANfNfe->emi_end_rua)?$MRANfNfe->emi_end_rua:($config_empresa?$config_empresa->end_rua:null)):($config_empresa?$config_empresa->end_rua:null)), ['class' => 'form-control' , "id" => "input_emi_end_rua", "disabled"=>true, "maxlength"=>200]) !!}
                            </div>
                        </div>
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','Número') !!}
                                {!! Form::text('emi_end_numero', ($acao=='edit'?(!empty($MRANfNfe->emi_end_numero)?$MRANfNfe->emi_end_numero:($config_empresa?$config_empresa->end_numero:null)):($config_empresa?$config_empresa->end_numero:null)), ['class' => 'form-control' , "id" => "input_emi_end_numero", "disabled"=>true, "maxlength"=>50]) !!}
                            </div>
                        </div>
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','Bairro') !!}
                                {!! Form::text('emi_end_bairro', ($acao=='edit'?(!empty($MRANfNfe->emi_end_bairro)?$MRANfNfe->emi_end_bairro:($config_empresa?$config_empresa->end_bairro:null)):($config_empresa?$config_empresa->end_bairro:null)), ['class' => 'form-control' , "id" => "input_emi_end_bairro", "disabled"=>true, "maxlength"=>50]) !!}
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div size="6" class="inputbox col-md-6">
                            <div class="form-group">
                                {!! Form::label('','Complemento') !!}
                                {!! Form::text('emi_end_complemento', ($acao=='edit'?(!empty($MRANfNfe->emi_end_complemento)?$MRANfNfe->emi_end_complemento:($config_empresa?$config_empresa->end_complemento:null)):($config_empresa?$config_empresa->end_complemento:null)), ['class' => 'form-control' , "id" => "input_emi_end_complemento", "disabled"=>true, "maxlength"=>300]) !!}
                            </div>
                        </div>
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','Estado') !!}
                                {!! Form::select('emi_end_estado', \App\Http\Controllers\MRA\MRAListas::Get_options_estados(), ($acao=='edit'?(!empty($MRANfNfe->emi_end_estado)?$MRANfNfe->emi_end_estado:($config_empresa?$config_empresa->end_estado:null)):($config_empresa?$config_empresa->end_estado:null)), ['class' => 'form-control select_single_no_trigger', 'data-live-search' => 'true', "disabled"=>true, "id" => "input_emi_end_estado"]) !!}
                            </div>
                        </div>
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','Cidade') !!}
                                {!! Form::text('emi_end_cidade', ($acao=='edit'?(!empty($MRANfNfe->emi_end_cidade)?$MRANfNfe->emi_end_cidade:($config_empresa?$config_empresa->end_cidade:null)):($config_empresa?$config_empresa->end_cidade:null)), ['class' => 'form-control' , "id" => "input_emi_end_cidade", "disabled"=>true, "maxlength"=>50]) !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>--}}
    <section class="content" style="min-height: auto; margin-top: -20px;">
        <div class="box" style="margin-bottom: 0; margin-top: 0;">
            <div class="box-body" style="margin-top: 0px;">
                <div size="12" class="inputbox col-md-12">
                    <h2 class="page-header" style="font-size:20px;">
                        <i class="glyphicon glyphicon-th-large"></i> Dados do Cliente / Destinatário
                    </h2>
                </div>

                <div size="12" class="inputbox col-md-12">
                    <div class="row">
                        <div size="2" class="inputbox col-md-6">
                            {!! Form::label('','Cliente') !!}
                            <div class="input-group mb_15p">
                                {!! Form::select('mra_nf_cliente_id', \App\Models\RNfClientesTs::lista_clientes(), ($acao=='edit'?$MRANfNfe->mra_nf_cliente_id:null), ['class' => 'form-control select_single_no_trigger ss-st2', 'data-live-search' => 'true', "disabled"=>$disabled, "id" => "input_mra_nf_cliente_id"]) !!}
                                <span class="input-group-btn">
                                    <button class="btn btn-primary" type="button" {{ ($disabled?'disabled="disabled"':'') }} onclick="javascript:$('#input_mra_nf_cliente_id').trigger('change');"><i class="glyphicon glyphicon-refresh"></i></button>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div size="6" class="inputbox col-md-6">
                            <div class="form-group">
                                {!! Form::label('','Nome / Razão Social') !!} <span style="color: #ff0500;">*</span>
                                {!! Form::text('des_nome_razao_social', ($acao=='edit'?$MRANfNfe->des_nome_razao_social:null), ['class' => 'form-control', "id" => "input_des_nome_razao_social", "maxlength"=>300, "disabled"=>$disabled]) !!}
                            </div>
                        </div>
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','Tipo de Pessoa') !!} <span style="color: #ff0500;">*</span>
                                {!! Form::select('des_tipo_pessoa', \App\Http\Controllers\MRA\MRAListas::Get_options_tipo_pessoa(), ($acao=='edit'?$MRANfNfe->des_tipo_pessoa:null), ['class' => 'form-control select_single_no_trigger', "disabled"=>$disabled , "id" => "input_des_tipo_pessoa"]) !!}
                            </div>
                        </div>
                        <div id="box_cnpj" size="3" class="inputbox col-md-3" style="display:none;">
                            <div class="form-group">
                                {!! Form::label('','CNPJ') !!} <span style="color: #ff0500;">*</span>
                                {!! Form::text('des_cnpj', ($acao=='edit'?$MRANfNfe->des_cnpj:null), ['class' => 'form-control cnpj_v2', "placeholder"=>"__.___.___/____-__", "disabled"=>$disabled, "id" => "input_des_cnpj", "maxlength"=>50]) !!}
                            </div>
                        </div>
                        <div id="box_cpf" size="3" class="inputbox col-md-3" style="display:none;">
                            <div class="form-group">
                                {!! Form::label('','CPF') !!} <span style="color: #ff0500;">*</span>
                                {!! Form::text('des_cpf', ($acao=='edit'?$MRANfNfe->des_cpf:null), ['class' => 'form-control cpf', "placeholder"=>"___.___.___-__", "disabled"=>$disabled, "id" => "input_des_cpf", "maxlength"=>50]) !!}
                            </div>
                        </div>
                        <div id="box_inscricao_estadual" size="4" class="inputbox col-md-4" style="display:none;">
                            <div class="form-group">
                                {!! Form::label('','Inscrição Estadual (IE)') !!}
                                {!! Form::text('des_cnpj_inscricao_estadual', ($acao=='edit'?$MRANfNfe->des_cnpj_inscricao_estadual:null), ['class' => 'form-control', "disabled"=>$disabled, "id" => "input_des_cnpj_inscricao_estadual", "maxlength"=>50]) !!}
                            </div>
                        </div>
                        <div id="box_inscricao_municipal" size="4" class="inputbox col-md-4" style="display:none;">
                            <div class="form-group">
                                {!! Form::label('','Inscrição Municipal') !!}
                                {!! Form::text('des_cnpj_inscricao_municipal', ($acao=='edit'?$MRANfNfe->des_cnpj_inscricao_municipal:null), ['class' => 'form-control', "disabled"=>$disabled, "id" => "input_des_cnpj_inscricao_municipal", "maxlength"=>50]) !!}
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div size="4" class="inputbox col-md-4">
                            <div class="form-group">
                                {!! Form::label('','Telefone') !!}
                                <div class="input-group">
                                    <div class="input-group-addon"><i class="fa fa-phone"></i></div>
                                    {!! Form::text('des_telefone', ($acao=='edit'?$MRANfNfe->des_telefone:null), ['class' => 'form-control telefone',"placeholder"=>"(__) ____-____", "disabled"=>$disabled, "id" => "input_des_telefone", "maxlength"=>200]) !!}
                                </div>
                            </div>
                        </div>
                        <div size="4" class="inputbox col-md-4">
                            <div class="form-group">
                                {!! Form::label('','E-mail') !!}
                                <div class="input-group">
                                    <div class="input-group-addon"><i class="fa fa-envelope"></i></div>
                                    {!! Form::text('des_email', ($acao=='edit'?$MRANfNfe->des_email:null), ['class' => 'form-control' , "id" => "input_des_email", "maxlength"=>200, "disabled"=>$disabled]) !!}
                                </div>
                            </div>
                        </div>
                        {{-- <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','Enviar N.F no e-mail?') !!}
                                {!! Form::select('des_enviar_nfe_email', [1=>'Sim',0=>'Não'], ($acao=='edit'?$MRANfNfe->des_enviar_nfe_email:0), ['class' => 'form-control select_single_no_trigger', "disabled"=>$disabled , "id" => "input_des_enviar_nfe_email"]) !!}
                            </div>
                        </div> --}}
                    </div>
                    <div class="row">
                        <div size="2" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','CEP') !!} <span style="color: #ff0500;">*</span>
                                <div class="input-group">
                                    <div class="input-group-addon"><i class="fa fa-map-pin"></i></div>
                                    {!! Form::text('des_end_cep', ($acao=='edit'?$MRANfNfe->des_end_cep:null), ['class' => 'form-control cep_v2', "placeholder"=>"_____-___", "id" => "input_des_end_cep", "maxlength"=>50, "disabled"=>$disabled]) !!}
                                </div>
                            </div>
                        </div>
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','Logradouro / Rua') !!} <span style="color: #ff0500;">*</span>
                                {!! Form::text('des_end_rua', ($acao=='edit'?$MRANfNfe->des_end_rua:null), ['class' => 'form-control', "disabled"=>$disabled , "id" => "input_des_end_rua", "maxlength"=>200]) !!}
                            </div>
                        </div>
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','Número') !!} <span style="color: #ff0500;">*</span>
                                {!! Form::text('des_end_numero', ($acao=='edit'?$MRANfNfe->des_end_numero:null), ['class' => 'form-control', "disabled"=>$disabled , "id" => "input_des_end_numero", "maxlength"=>100]) !!}
                            </div>
                        </div>
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','Bairro') !!} <span style="color: #ff0500;">*</span>
                                {!! Form::text('des_end_bairro', ($acao=='edit'?$MRANfNfe->des_end_bairro:null), ['class' => 'form-control', "disabled"=>$disabled , "id" => "input_des_end_bairro", "maxlength"=>200]) !!}
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div size="5" class="inputbox col-md-4">
                            <div class="form-group">
                                {!! Form::label('','Complemento') !!}
                                {!! Form::text('des_end_complemento', ($acao=='edit'?$MRANfNfe->des_end_complemento:null), ['class' => 'form-control', "disabled"=>$disabled , "id" => "input_des_end_complemento", "maxlength"=>200]) !!}
                            </div>
                        </div>
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','Estado') !!} <span style="color: #ff0500;">*</span>
                                {!! Form::select('des_end_estado', $estados, ($acao=='edit'?$MRANfNfe->des_end_estado:null), ['class' => 'form-control select_single_no_trigger', 'data-live-search' => 'true', "disabled"=>$disabled, "id" => "input_des_end_estado"]) !!}
                            </div>
                        </div>
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','Cidade') !!} <span style="color: #ff0500;">*</span>
                                {!! Form::select('des_end_cidade', [], ($acao=='edit'?$MRANfNfe->des_end_cidade:null), ['class' => 'form-control select_single_no_trigger' , 'data-live-search' => 'true', "disabled"=>$disabled , "id" => "input_des_end_cidade", '_value'=>($acao=='edit'?$MRANfNfe->des_end_cidade:null), "maxlength"=>50, 'placeholder'=>'---']) !!}
                            </div>
                        </div>

                        <div size="2" class="inputbox col-md-2">
                            <div class="form-group">
                                {!! Form::label('','País') !!}
                                {!! Form::select('des_end_pais', \App\Http\Controllers\MRA\MRAListas::Get_options_paises(), (($acao=='edit' and !is_null($MRANfNfe->des_end_pais))?$MRANfNfe->des_end_pais:1058), ['class' => 'form-control select_single_no_trigger', 'data-live-search' => 'true', "dropdown-menu-right"=>"", "disabled"=>$disabled, "id" => "input_des_end_pais"]) !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    @if(ENV('MODULO_NF_PRODUTO_TRANSP_CLI'))
        <section class="content" style="min-height: auto; margin-top: -20px;">
            <div class="box" style="margin-bottom: 0; margin-top: 0;">
                <div class="box-body" style="margin-top: 0px;">
                    <div size="12" class="inputbox col-md-12">
                        <h2 class="page-header" style="font-size:20px;">
                            <i class="glyphicon glyphicon-th-large"></i> Transportadora
                        </h2>
                    </div>

                    <div size="12" class="inputbox col-md-12">
                        <div class="row">
                            <div size="2" class="inputbox col-md-6">
                                {!! Form::label('','Transportadora') !!}
                                <div class="input-group mb_15p">
                                    {!! Form::select('mra_nf_transp_id', \App\Models\RNfTransportadorasTs::lista_transportadoras(), ($acao=='edit'?$MRANfNfe->mra_nf_transp_id:null), ['class' => 'form-control select_single_no_trigger ss-st2', 'data-live-search' => 'true', "disabled"=>$disabled, "id" => "input_mra_nf_transp_id"]) !!}
                                    <span class="input-group-btn">
                                    <button class="btn btn-primary" type="button" {{ ($disabled?'disabled="disabled"':'') }} onclick="javascript:$('#input_mra_nf_transp_id').trigger('change');"><i class="glyphicon glyphicon-refresh"></i></button>
                                </span>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div size="6" class="inputbox col-md-6">
                                <div class="form-group">
                                    {!! Form::label('','Nome / Razão Social') !!}
                                    {!! Form::text('transp_nome_razao_social', ($acao=='edit'?$MRANfNfe->transp_nome_razao_social:null), ['class' => 'form-control', "disabled"=>$disabled, "id" => "input_transp_nome_razao_social", "maxlength"=>100]) !!}
                                </div>
                            </div>
                            <div size="6" class="inputbox col-md-6">
                                <div class="form-group">
                                    {!! Form::label('','Modalidade Frete') !!}
                                    {!! Form::select('transp_modalid_frete', \App\Http\Controllers\MRA\RNfListasTsController::Get_options_tipo_de_frete(), (($acao=='edit' and !is_null($MRANfNfe->transp_modalid_frete))?$MRANfNfe->transp_modalid_frete:null), ['class' => 'form-control select_single_no_trigger', 'data-live-search' => 'true', "dropdown-menu-right"=>"", "disabled"=>$disabled, "id" => "input_transp_modalid_frete"]) !!}
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div size="4" class="inputbox col-md-4">
                                <div class="form-group">
                                    {!! Form::label('','CNPJ') !!}
                                    {!! Form::text('transp_cnpj', ($acao=='edit'?$MRANfNfe->transp_cnpj:null), ['class' => 'form-control cnpj_v2', "placeholder"=>"__.___.___/____-__", "disabled"=>$disabled, "id" => "input_transp_cnpj", "maxlength"=>50]) !!}
                                </div>
                            </div>
                            <div size="4" class="inputbox col-md-4">
                                <div class="form-group">
                                    {!! Form::label('','CPF') !!}
                                    {!! Form::text('transp_cpf', ($acao=='edit'?$MRANfNfe->transp_cpf:null), ['class' => 'form-control cpf', "placeholder"=>"___.___.___-__", "disabled"=>$disabled, "id" => "input_transp_cpf", "maxlength"=>50]) !!}
                                </div>
                            </div>
                            <div size="4" class="inputbox col-md-4">
                                <div class="form-group">
                                    {!! Form::label('','Inscrição Estadual (IE)') !!}
                                    {!! Form::text('transp_inscricao_estadual', ($acao=='edit'?$MRANfNfe->transp_inscricao_estadual:null), ['class' => 'form-control', "disabled"=>$disabled, "id" => "input_transp_inscricao_estadual", "maxlength"=>100]) !!}
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div size="9" class="inputbox col-md-9">
                                <div class="form-group {{ ($disabled?'disabled':'') }}">
                                    {!! Form::label('','E-mail(s) envio Nota Fiscal') !!}
                                    <div class="input-group">
                                        <div class="input-group-addon"><i class="fa fa-envelope"></i></div>
                                        {!! Form::text('transp_cont_emails_nf', ($acao=='edit'?$MRANfNfe->transp_cont_emails_nf:null), ['class' => 'form-control select_tags', "disabled"=>$disabled , "id" => "input_transp_cont_emails_nf", "maxlength"=>600, "maxTags"=>3]) !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div size="2" class="inputbox col-md-3">
                                <div class="form-group">
                                    {!! Form::label('','CEP') !!}
                                    <div class="input-group">
                                        <div class="input-group-addon"><i class="fa fa-map-pin"></i></div>
                                        {!! Form::text('transp_end_cep', ($acao=='edit'?$MRANfNfe->transp_end_cep:null), ['class' => 'form-control cep_v2', "placeholder"=>"_____-___", "disabled"=>$disabled, "id" => "input_transp_end_cep", "maxlength"=>50]) !!}
                                    </div>
                                </div>
                            </div>
                            <div size="3" class="inputbox col-md-3">
                                <div class="form-group">
                                    {!! Form::label('','Logradouro / Rua') !!}
                                    {!! Form::text('transp_end_rua', ($acao=='edit'?$MRANfNfe->transp_end_rua:null), ['class' => 'form-control', "disabled"=>$disabled, "id" => "input_transp_end_rua", "maxlength"=>200]) !!}
                                </div>
                            </div>
                            <div size="3" class="inputbox col-md-3">
                                <div class="form-group">
                                    {!! Form::label('','Número') !!}
                                    {!! Form::text('transp_end_numero', ($acao=='edit'?$MRANfNfe->transp_end_numero:null), ['class' => 'form-control', "disabled"=>$disabled, "id" => "input_transp_end_numero", "maxlength"=>200]) !!}
                                </div>
                            </div>
                            <div size="3" class="inputbox col-md-3">
                                <div class="form-group">
                                    {!! Form::label('','Bairro') !!}
                                    {!! Form::text('transp_end_bairro', ($acao=='edit'?$MRANfNfe->transp_end_bairro:null), ['class' => 'form-control', "disabled"=>$disabled, "id" => "input_transp_end_bairro", "maxlength"=>200]) !!}
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div size="3" class="inputbox col-md-3">
                                <div class="form-group">
                                    {!! Form::label('','Estado') !!}
                                    {!! Form::select('transp_end_estado', \App\Http\Controllers\MRA\MRAListas::Get_options_estados(), ($acao=='edit'?$MRANfNfe->transp_end_estado:null), ['class' => 'form-control select_single_no_trigger', 'data-live-search' => 'true', "disabled"=>$disabled, "id" => "input_transp_end_estado"]) !!}
                                </div>
                            </div>
                            <div size="3" class="inputbox col-md-3">
                                <div class="form-group">
                                    {!! Form::label('','Cidade') !!}
                                    {!! Form::text('transp_end_cidade', ($acao=='edit'?$MRANfNfe->transp_end_cidade:null), ['class' => 'form-control', "disabled"=>$disabled, "id" => "input_transp_end_cidade", "maxlength"=>50]) !!}
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div size="3" class="inputbox col-md-3">
                                <div class="form-group">
                                    {!! Form::label('','Valor do Frete') !!}
                                    <div class="input-group">
                                        <div class="input-group-addon"><strong>R$</strong></div>
                                        {!! Form::text('transp_valor_frete', ($acao=='edit'?\App\Helper\Helper::H_Decimal_DB_ptBR($MRANfNfe->transp_valor_frete):null), ['class' => 'form-control money_v2', "placeholder" => "0,00", "disabled"=>$disabled, "id" => "input_transp_valor_frete"]) !!}
                                    </div>
                                </div>
                            </div>
                            <div size="3" class="inputbox col-md-3">
                                <div class="form-group">
                                    {!! Form::label('','Placa') !!}
                                    {!! Form::text('transp_veiculo_placa', ($acao=='edit'?$MRANfNfe->transp_veiculo_placa:null), ['class' => 'form-control', "disabled"=>$disabled, "id" => "input_transp_veiculo_placa", "maxlength"=>50]) !!}
                                </div>
                            </div>
                            <div size="3" class="inputbox col-md-3">
                                <div class="form-group">
                                    {!! Form::label('','Estado do Veículo') !!}
                                    {!! Form::select('transp_veiculo_uf', \App\Http\Controllers\MRA\MRAListas::Get_options_estados(), ($acao=='edit'?$MRANfNfe->transp_veiculo_uf:null), ['class' => 'form-control select_single_no_trigger', 'data-live-search' => 'true', "disabled"=>$disabled, "id" => "input_transp_veiculo_uf"]) !!}
                                </div>
                            </div>
                            <div size="3" class="inputbox col-md-3">
                                <div class="form-group">
                                    {!! Form::label('','Informar Volumes?') !!}
                                    {!! Form::select('transp_informar_volume', [1=>'Sim',0=>'Não'], ($acao=='edit'?$MRANfNfe->transp_informar_volume:0), ['class' => 'form-control select_single_no_trigger', "disabled"=>$disabled, "id" => "input_transp_informar_volume"]) !!}
                                </div>
                            </div>
                        </div>
                        <div id="box_informar_volume" class="row" style="display:none;">
                            <div size="3" class="inputbox col-md-3">
                                <div class="form-group">
                                    {!! Form::label('','Qtd.') !!}
                                    {!! Form::number('transp_iv_quantidade', ($acao=='edit'?$MRANfNfe->transp_iv_quantidade:null), ['class' => 'form-control', "disabled"=>$disabled, "id" => "input_transp_iv_quantidade", "min"=>0]) !!}
                                </div>
                            </div>
                            <div size="3" class="inputbox col-md-3">
                                <div class="form-group">
                                    {!! Form::label('','Espécie') !!}
                                    {!! Form::text('transp_iv_especie', ($acao=='edit'?$MRANfNfe->transp_iv_especie:null), ['class' => 'form-control', "disabled"=>$disabled, "id" => "input_transp_iv_especie", "placeholder"=>"Ex: CAIXA, VOLUMES", "maxlength"=>200]) !!}
                                </div>
                            </div>
                            <div size="3" class="inputbox col-md-3">
                                <div class="form-group">
                                    {!! Form::label('','Peso Líquido (Kg)') !!}
                                    <div class="input-group">
                                        <div class="input-group-addon"><i class="fa fa-balance-scale"></i></div>
                                        {!! Form::text('transp_iv_peso_liquido', ($acao=='edit'?\App\Helper\Helper::H_Decimal_DB_ptBR($MRANfNfe->transp_iv_peso_liquido):null), ['class' => 'form-control money_v2', "placeholder" => "0,000", "maskMoney_precision"=>3, "disabled"=>$disabled, "id" => "input_transp_iv_peso_liquido"]) !!}
                                    </div>
                                </div>
                            </div>
                            <div size="3" class="inputbox col-md-3">
                                <div class="form-group">
                                    {!! Form::label('','Peso Bruto (Kg)') !!}
                                    <div class="input-group">
                                        <div class="input-group-addon"><i class="fa fa-balance-scale"></i></div>
                                        {!! Form::text('transp_iv_peso_bruto', ($acao=='edit'?\App\Helper\Helper::H_Decimal_DB_ptBR($MRANfNfe->transp_iv_peso_bruto):null), ['class' => 'form-control money_v2', "placeholder" => "0,000", "maskMoney_precision"=>3, "disabled"=>$disabled, "id" => "input_transp_iv_peso_bruto"]) !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif
    <section class="content" style="min-height: auto; margin-top: -20px;">
        <div class="box" style="margin-bottom: 0; margin-top: 0;">
            <div class="box-body" style="padding: 0px">
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
                        {{--@if($acao == 'add' and App\Models\Permissions::permissaoUsuario(\Auth::user(), "$controller@store"))
                            <button type="submit" class="btn btn-default right form-group-btn-add-cadastrar"><span class="glyphicon glyphicon-plus"></span> Cadastrar</button>
                        @endif
                        @if($acao == 'edit' and App\Models\Permissions::permissaoUsuario(\Auth::user(), "$controller@update"))
                            <button type="submit" class="btn btn-default right form-group-btn-edit-salvar"><span class="glyphicon glyphicon-ok"></span> Salvar</button>
                        @endif--}}
                        @if($acao=='add')
                            @if($permissaoUsuario_auth_user__controller_store)
                                <button type="submit" class="btn btn-default right form-group-btn-add-cadastrar"><span class="glyphicon glyphicon-plus"></span> Cadastrar</button>
                            @endif
                        @endif

                        @if($acao=='edit')
                            @if($permissaoUsuario_auth_user__controller_update and empty($MRANfNfe->nf_response_id))
                                <button type="submit" class="btn btn-default right form-group-btn-edit-salvar"><span class="glyphicon glyphicon-ok"></span> Salvar</button>
                            @endif

                            @if (!$MRANfNfe->nf_status || in_array($MRANfNfe->nf_status, ['REJEITADO']))
                                <button type="submit" name="transferir" value="1" class="btn btn-info right form-group-btn-edit-salvar" onclick="javascript: return confirm('A Nota Fiscal será transferida! Tem certeza?');" style="margin-right:15px;"><i class="glyphicon glyphicon-cloud-upload"></i> {{$MRANfNfe->nf_status == 'REJEITADO' ? 'Reemitir' : 'Emitir'}}</button>
                            @endif

                            @if(!empty($MRANfNfe->nf_response_id) && in_array($MRANfNfe->nf_status,['PENDENTE', 'AGUARDANDO CANCELAMENTO', 'PROCESSANDO']))
                                <button type="submit" name="consultar" value="1" class="btn btn-warning right form-group-btn-edit-salvar" onclick="javascript: return confirm('A Nota Fiscal será consultada!');" style="margin-right:15px;"><i class="glyphicon glyphicon-transfer"></i> Consultar Processamento</button>
                            @endif

                            {{-- @if($config_empresa_token_api and $permissaoUsuario_auth_user__controller_update and !empty($MRANfNfe->notazz_id_documento) and in_array($MRANfNfe->notazz_status,['Rejeitada','EmConflito']))
                                <button type="submit" name="transferir" value="1" class="btn btn-info right form-group-btn-edit-salvar" onclick="javascript: return confirm('A Nota Fiscal será atualizada e transferida! Tem certeza?');" style="margin-right:15px;"><i class="glyphicon glyphicon-cloud-upload"></i> Atualizar / Transferir</button>
                            @endif --}}

                            {{-- @if($permissaoUsuario_auth_user__controller_destroy and !empty($MRANfNfe->notazz_id_documento) and in_array($MRANfNfe->notazz_status,['Autorizada']))
                                @if($config_empresa_token_api)
                                    <button type="submit" name="cancelar_nf" value="1" class="btn btn-danger right form-group-btn-edit-salvar" onclick="javascript: return confirm('A Nota Fiscal será cancelada! Tem certeza?\n** Atenção! Em caso da Nota Fiscal não seja cancelada só poderá ser realizada pelo sistema da prefeitura.');" style="float:right;"><i class="glyphicon glyphicon-floppy-remove"></i>&nbsp;&nbsp;Cancelar</button>
                                @endif
                                <button type="submit" name="cancelar_nf_forcado" value="1" class="btn btn-danger right form-group-btn-edit-salvar" onclick="javascript: return (confirm('A Nota Fiscal será cancelada dentro do sistema! Tem certeza?')?true:false);" style="float:right;"><i class="glyphicon glyphicon-alert"></i>&nbsp;&nbsp;Forçar Cancelamento</button>
                            @endif --}}

                            @if ($MRANfNfe->nf_status == 'CONCLUIDO' && $MRANfNfe->nf_response_id)
                                <button type="submit" name="cancelar_nf" value="1" class="btn btn-danger pull-right" title="Solicitar Cancelamento" onclick="javascript: return confirm('Deseja realmente cancelar a Nota Fiscal?');"><i class="fa fa-ban"></i> Cancelar Nota</button>
                            @endif
                        @endif
                    </div>
                </div>
                {{-- @if($config_empresa_token_api and $permissaoUsuario_auth_user__controller_destroy and !empty($MRANfNfe->notazz_id_documento) and in_array($MRANfNfe->notazz_status,['Autorizada']))
                    <div class="col-md-12">
                        <p class="text-warning text-right">
                            <strong>** Atenção!</strong> <i class="glyphicon glyphicon-alert"></i><br/>
                            - Em caso da <strong class="text-danger">Nota Fiscal não seja cancelada</strong> só poderá ser realizada pelo <strong>sistema da prefeitura.</strong><br/>
                            - <strong class="text-danger">Forçar Cancelamento</strong> apenas será cancelado <strong>dentro do sistema</strong>!</strong>
                        </p>
                    </div>
                @endif --}}
            </div>
        </div>
    </section>
    {!! Form::close() !!}

    @if(isset($MRANfNfe->RNfLogTs) and count($MRANfNfe->RNfLogTs))
        <section class="content">
            <div class="box" style="margin-bottom: 0; margin-top: -20px;">
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
                            @foreach($MRANfNfe->RNfLogTs as $log)
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
            // :: Finalidade
            $("#input_nfe_finalidade").on('change',function(){
                if($.inArray($(this).val(),['','1']) >= 0){
                    $("#box_nfe_chave_referencia").hide();
                }else {
                    $("#box_nfe_chave_referencia").show();
                }
            }).trigger('change');

            // :: Tipo de Pessoa
            $("#input_des_tipo_pessoa").on("change",function(){
                switch($("#input_des_tipo_pessoa").val()) {
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

            // :: Cliente / Destinatário
            $("#input_mra_nf_cliente_id").on('change', async function(){
                let _this = $(this);
                if((_this.attr('af') != undefined && _this.attr('af')=='true') || _this.val() == ''){ return false; }
                _this.attr('af','true');
                setTimeout(function(){ _this.attr('af','false'); },1000); // Fix*
                try {
                    $.get('{{URL('nota_fiscal/clientes/ts')}}/'+_this.val()+'/ajax', function(d,s){
                        if(d!=undefined && Object.keys(d).length){
                            $("#input_des_nome_razao_social").val(d.nome);
                            $("#input_des_tipo_pessoa").val(d.tipo).trigger('change');
                            $("#input_des_cpf").val(d.cpf);
                            $("#input_des_cnpj").val(d.cnpj);
                            $("#input_des_cnpj_inscricao_estadual").val(d.inscricao_estadual);
                            $("#input_des_cnpj_inscricao_municipal").val(d.inscricao_municipal);
                            $("#input_des_telefone").val(d.cont_telefone);
                            $("#input_des_email").val(d.cont_email);
                            $("#input_des_end_cep").val(d.end_cep.replaceAll('.',''));
                            $("#input_des_end_rua").val(d.end_rua);
                            $("#input_des_end_numero").val(d.end_numero);
                            $("#input_des_end_bairro").val(d.end_bairro);
                            $("#input_des_end_complemento").val(d.end_complemento);
                            $("#input_des_end_estado").val(d.end_estado).trigger('change');
                            $("#input_des_end_cidade").val(d.end_cidade);
                            $("#input_des_end_pais").val((d.end_pais!=null?d.end_pais:1058)).trigger('change');
                        }
                    });
                }catch(e){
                    //console.log('Erro: '+e);
                }
            });

            // :: Transportadora
            $("#input_mra_nf_transp_id").on('change', async function(){
                let _this = $(this);
                if((_this.attr('af') != undefined && _this.attr('af')=='true') || _this.val() == ''){ return false; }
                _this.attr('af','true');
                setTimeout(function(){ _this.attr('af','false'); },1000); // Fix*
                try {
                    $.get('{{URL('nota_fiscal/transportadoras/ts')}}/'+_this.val()+'/ajax', function(d,s){
                        if(d!=undefined && Object.keys(d).length){
                            $("#input_transp_nome_razao_social").val(d.nome);
                            $("#input_transp_cpf").val(d.cpf);
                            $("#input_transp_cnpj").val(d.cnpj);
                            $("#input_transp_inscricao_estadual").val(d.ie);
                            $("#input_transp_cont_emails_nf").val(d.cont_emails_nf).tagsinput('destroy');
                            RA.load.select_tags($("#input_transp_cont_emails_nf"));
                            $("#input_transp_cont_telefone").val(d.cont_telefone);
                            $("#input_transp_cont_email").val(d.cont_email);
                            $("#input_transp_end_cep").val(d.end_cep.replaceAll('.',''));
                            $("#input_transp_end_rua").val(d.end_rua);
                            $("#input_transp_end_numero").val(d.end_numero);
                            $("#input_transp_end_bairro").val(d.end_bairro);
                            $("#input_transp_end_estado").val(d.end_estado).trigger('change');
                            $("#input_transp_end_cidade").val(d.end_cidade);
                        }
                    });
                }catch(e){
                    //console.log('Erro: '+e);
                }
            });

            // :: Produtos -> Itens
            function select_mra_nf_prod_id(E){
                $(E).on('change',function(){
                    let _this = $(this);
                    if((_this.attr('af') != undefined && _this.attr('af')=='true') || _this.val() == ''){ return false; }
                    _this.attr('af','true');
                    setTimeout(function(){ _this.attr('af','false'); },1000); // Fix*
                    try {
                        $.get('{{URL('nota_fiscal/produtos/ts')}}/'+_this.val()+'/ajax', function(d,s){
                            if(d!=undefined && Object.keys(d).length){
                                _this.parents('.item').find('.mra_nf_prod_codigo').val(d.codigo);
                                _this.parents('.item').find('.mra_nf_prod_codigo_barras').val(d.codigo_barras);
                                _this.parents('.item').find('.mra_nf_prod_nome').val(d.nome);
                                _this.parents('.item').find('.mra_nf_prod_origem').val(d.origem);
                                _this.parents('.item').find('.mra_nf_prod_cst').val(d.cst);
                                _this.parents('.item').find('.mra_nf_prod_cfop').val(d.cfop);
                                _this.parents('.item').find('.mra_nf_prod_ncm').val(d.ncm);
                                _this.parents('.item').find('.mra_nf_prod_cest').val(d.cest);
                                _this.parents('.item').find('.mra_nf_prod_umedida').val(d.unidade_medida);
                                _this.parents('.item').find('.mra_nf_prod_valor_desconto').val(RA.format.Decimal_DB_ptBR(d.valor_desconto));
                                _this.parents('.item').find('.mra_nf_prod_valor_seguro').val(RA.format.Decimal_DB_ptBR(d.valor_seguro));
                                _this.parents('.item').find('.mra_nf_prod_valor_unit').val(RA.format.Decimal_DB_ptBR(d.valor_venda));
                                _this.parents('.item').find('.mra_nf_prod_imp_cst_csosn_icms').val(d.icms_cst);
                                _this.parents('.item').find('.mra_nf_prod_imp_cst_ipi').val(d.ipi_cst);
                                _this.parents('.item').find('.mra_nf_prod_imp_cst_pis').val(d.pis_cst);
                                _this.parents('.item').find('.mra_nf_prod_imp_cst_cofins').val(d.cofins_cst);
                                _this.parents('.item').find('.mra_nf_prod_imp_aliquota_icms').val(RA.format.Decimal_DB_ptBR(d.icms_icms));
                                _this.parents('.item').find('.mra_nf_prod_imp_aliquota_ipi').val(RA.format.Decimal_DB_ptBR(d.ipi_ipi));
                                _this.parents('.item').find('.mra_nf_prod_imp_aliquota_pis').val(RA.format.Decimal_DB_ptBR(d.pis_pis));
                                _this.parents('.item').find('.mra_nf_prod_imp_aliquota_cofins').val(RA.format.Decimal_DB_ptBR(d.cofins_cofins));
                                calculo_item_e_total_produtos();
                            }
                        });
                    }catch(e){
                        //console.log('Erro: '+e);
                    }
                });
            }
            select_mra_nf_prod_id($("select.mra_nf_prod_id"));

            // :: Produtos -> Item -> Adicionar +1
            $("[data=\'GridProdutos_grid\'].multiple_add_v2").on('click',function(){
                setTimeout(function(){
                    let item = $(".GridProdutos_grid .item").last();
                    //RA.load.money_v2(item.find('.money_v2'));
                    item.find('select.select_single_no_trigger').selectpicker('refresh');
                    item.find('select.mra_nf_prod_id').val('').trigger('change');
                    item.find('input').val('');
                    select_mra_nf_prod_id(item.find('select.mra_nf_prod_id'));
                    on_keyup_change__calculo_item_e_total_produtos(item.find('input.mra_nf_prod_qt'));
                    on_keyup_change__calculo_item_e_total_produtos(item.find('input.mra_nf_prod_valor_unit'));
                    on_keyup_change__calculo_item_e_total_produtos(item.find('input.mra_nf_prod_valor_desconto'));
                    on_keyup_change__calculo_item_e_total_produtos(item.find('input.mra_nf_prod_valor_frete'));
                    on_keyup_change__calculo_item_e_total_produtos(item.find('input.mra_nf_prod_valor_seguro'));
                    on_click_produtos_item__grid_remove_v2(item.find('.grid_remove_v2'));
                },1000);
            });

            function calculo_item_e_total_produtos(){
                let itens               = $(".GridProdutos_grid .item");
                let valor_total         = 0;
                let valor_descontos     = 0;
                let valor_fretes        = 0;
                let valor_seguros       = 0;
                itens.each(function(i,e){
                    let qt              = ($(e).find('.mra_nf_prod_qt').val()!=''?Number($(e).find('.mra_nf_prod_qt').val()):0);
                    let valor           = RA.format.Decimal_ptBR_DB($(e).find('.mra_nf_prod_valor_unit').val());
                    let valor_desconto  = RA.format.Decimal_ptBR_DB($(e).find('.mra_nf_prod_valor_desconto').val());
                    let valor_frete     = RA.format.Decimal_ptBR_DB($(e).find('.mra_nf_prod_valor_frete').val());
                    let valor_seguro    = RA.format.Decimal_ptBR_DB($(e).find('.mra_nf_prod_valor_seguro').val());
                    let subtotal        = (qt * valor);
                    let subdesconto     = (qt * valor_desconto);
                    let subfrete        = (qt * valor_frete);
                    let subseguro       = (qt * valor_seguro);
                    valor_total        += subtotal;
                    valor_descontos    += subdesconto;
                    valor_fretes       += subfrete;
                    valor_seguros      += subseguro;
                    $(e).find('.mra_nf_prod_valor_subtotal').val(RA.format.Decimal_DB_ptBR(subtotal));
                });
                var total              = valor_total + valor_fretes + valor_seguros - valor_descontos;
                $("#mra_total_produtos").html(valor_total.toLocaleString('pt-BR', { minimumFractionDigits: 2}));
                $("#mra_total_descontos").html(valor_descontos.toLocaleString('pt-BR', { minimumFractionDigits: 2}));
                $("#mra_total_fretes").html(valor_fretes.toLocaleString('pt-BR', { minimumFractionDigits: 2}));
                $("#mra_total_seguros").html(valor_seguros.toLocaleString('pt-BR', { minimumFractionDigits: 2}));
                $("#mra_total").html(total.toLocaleString('pt-BR', { minimumFractionDigits: 2}));

                $("#nfe_valor_itens").val(valor_total);
                $("#nfe_valor_desconto").val(valor_descontos);
                $("#nfe_valor_frete").val(valor_fretes);
                $("#nfe_valor_seguro").val(valor_seguros);
                $("#nfe_total").val(total);
            }
            calculo_item_e_total_produtos();

            function on_keyup_change__calculo_item_e_total_produtos(E){
                $(E).on('keyup change',function(){
                    calculo_item_e_total_produtos();
                });
            }
            on_keyup_change__calculo_item_e_total_produtos($(".GridProdutos_grid .mra_nf_prod_qt, .GridProdutos_grid .mra_nf_prod_valor_unit, .GridProdutos_grid .mra_nf_prod_valor_desconto, .GridProdutos_grid .mra_nf_prod_valor_frete, .GridProdutos_grid .mra_nf_prod_valor_seguro"));

            function on_click_produtos_item__grid_remove_v2(E){
                $(E).on('click',function(){
                    calculo_item_e_total_produtos();
                });
            }
            on_click_produtos_item__grid_remove_v2($(".GridProdutos_grid .item .grid_remove_v2"));

            // :: Informar Volume
            $("#input_transp_informar_volume").on('change',function(){
                if($(this).val()=='1'){
                    $("#box_informar_volume").show();
                }else {
                    $("#box_informar_volume").hide();
                }
            }).trigger('change');

            // Ajax para carregar cidades de acordo com o estado selecionado (Cliente / Destinatário)
            $('#input_des_end_estado').on('change',function(){
                $("#input_des_end_cidade").html('<option value="">---</option>').selectpicker('refresh');
                if($(this).val == ''){ return false; }
                $.get(base + '/municipios/estado/' + $(this).val() + '/ajax?subForm=uf', function(data, status){
                    $("#input_des_end_cidade").val('');
                    if(typeof(data)!='object'){ return; }
                    if(status == 'success'){
                        let options = '';
                        options += '<option value="">---</option>';
                        for(let i = 0; i < data.length; i++) {
                            options += '<option value="' + data[i]['id'] + '">' + data[i]['nome'] + '</option>';
                        }
                        $("#input_des_end_cidade").html(options);
                        $("#input_des_end_cidade").val($("#input_des_end_cidade").attr('_value')).selectpicker('refresh');
                    }
                });
            }).trigger('change');
        </script>
    @endsection

@endsection
