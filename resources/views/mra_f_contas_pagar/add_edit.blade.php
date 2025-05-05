@php
    $acao                     = ((isset($MRAFContasPagar) and !is_null($MRAFContasPagar))?'edit':'add');
    $isPublic                 = 0;
    $controller               = get_class(\Request::route()->getController());

    $permissaoUsuario_auth_user__controller_store       =   false;
    $permissaoUsuario_auth_user__controller_update      =   false;
    $permissaoUsuario_auth_user__controller_destroy     =   false;
    if(\App\Models\Permissions::permissaoUsuario(\Auth::user(), "$controller@store")){   $permissaoUsuario_auth_user__controller_store      = true; }
    if(\App\Models\Permissions::permissaoUsuario(\Auth::user(), "$controller@update")){  $permissaoUsuario_auth_user__controller_update     = true; }
    if(\App\Models\Permissions::permissaoUsuario(\Auth::user(), "$controller@destroy")){ $permissaoUsuario_auth_user__controller_destroy    = true; }

    $disabled     = false;

    if(env('FILESYSTEM_DRIVER') == 's3') {
        $fileurlbase = env('URLS3') . '/' . env('FILEKEY') . '/';
    }else {
        $fileurlbase = env('APP_URL') . '/';
    }
@endphp
@extends($isPublic ? 'layouts.app-public' : 'layouts.app')
@section('content')
    @section('style')
        <style type="text/css">
        </style>
    @endsection
    <section class="content-header">
        <h1>Financeiro - Contas a Pagar</h1>
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
    {!! Form::open(['url' => "mra_fluxo_financeiro/mra_f_contas_pagar".($acao=='edit'?'/'.$MRAFContasPagar->id:''), 'method' => ($acao=='edit'?'put':'post'), 'novalidate'=> true, 'enctype' => 'multipart/form-data', 'accept-charset' => 'utf-8', 'id' => ($acao=='edit'?'form_edit':'form_add').'_mra_f_contas_pagar']) !!}
    <section class="content" style="min-height: auto;">
        <div class="box" style="margin-bottom: 0; margin-top: 0;">
            @if($acao=='edit')
                {!! Form::hidden('id', $MRAFContasPagar->id) !!}
                @if(\App\Models\Permissions::permissaoUsuario(\Auth::user(), "$controller@destroy"))
                    @if(empty($MRAFContasPagar->notazz_id_documento))
                        <div class="row" style="position: absolute; right: 0; padding: 5px; z-index: 100;">
                            <div class="col-md-12">
                                <a href="javascript:void(0);" type="button" class="btn btn-danger excluir-auto-confirma" modulo="mra_fluxo_financeiro/mra_f_contas_pagar" modulo_id="{{$MRAFContasPagar->id}}"><i class="glyphicon glyphicon-trash"></i></a>
                            </div>
                        </div>
                    @endif
                    {{--<div class="box-header">
                        <div class="col-md-12">
                            @php
                                $nf_badge_status  = 'badge-default';
                                switch($MRAFContasPagar->notazz_status){
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
                                $notazz_status  = (!empty($MRAFContasPagar->notazz_status)?\App\Http\Controllers\MRA\MRANotasFiscais::Get_nf_status_nota_fiscal($MRAFContasPagar->notazz_status):'---');
                                // ! Caso o Status foi Cancelada + Cancelamento Forçado
                                if($MRAFContasPagar->notazz_status == 'Cancelada' and $MRAFContasPagar->notazz_status_forcado){
                                    $notazz_status = 'Cancelada no Sistema';
                                }
                            @endphp
                            <span class="badge {{$nf_badge_status}} fw-600">{{ $notazz_status }}</span>
                        </div>
                    </div>--}}
                @endif
            @endif
            <div class="box-body" style="margin-top: 0px;">
                <div size="12" class="inputbox col-md-12">
                    <h2 class="page-header" style="font-size:20px;">
                        <i class="glyphicon glyphicon-th-large"></i> Conta a Pagar
                    </h2>
                </div>
                <div size="12" class="inputbox col-md-12">
                    <div class="row">
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','Status',['class'=>'text-warning']) !!} <span style="color: #ff0500;">*</span>
                                {!! Form::select('status', \App\Http\Controllers\MRA\MRAListas::Get_options_status_conclusao([""]), ($acao=='edit'?$MRAFContasPagar->status:2), ['class' => 'form-control select_single_no_trigger', 'data-live-search' => 'true', "id" => "input_status"]) !!}
                            </div>
                        </div>
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','Data de Competência') !!} <span style="color: #ff0500;">*</span>
                                <div class="input-group">
                                    <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                                    {!! Form::text('data_competencia', ($acao=='edit'?\App\Helper\Helper::H_Data_DB_ptBR($MRAFContasPagar->data_competencia):date('d/m/Y')), ['autocomplete' =>'off', 'class' => 'form-control componenteData_v2', "placeholder"=>"__/__/____", "id" => "input_data_competencia"]) !!}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div size="6" class="inputbox col-md-6">
                            <div class="form-group">
                                {!! Form::label('','Fornecedor') !!} <span style="color: #ff0500;">*</span>
                                {!! Form::select('mra_f_fornecedores_id', \App\Models\MRAFFornecedores::lista_fornecedores(), ($acao=='edit'?$MRAFContasPagar->mra_f_fornecedores_id:null), ['class' => 'form-control select_single_no_trigger', 'data-live-search' => 'true', "id" => "input_mra_f_fornecedores_id"]) !!}
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div size="4" class="inputbox col-md-4">
                            <div class="form-group ss-st2">
                                {!! Form::label('','Centro de Custo') !!}
                                {!! Form::select('mra_f_centro_custo_id', \App\Models\MRAFCentroCusto::Get_CentroDeCustos_options(), ($acao=='edit'?$MRAFContasPagar->mra_f_centro_custo_id:null), ['class' => 'form-control select_single_no_trigger', 'data-live-search' => 'true', "id" => "input_mra_f_centro_custo_id"]) !!}
                            </div>
                        </div>
                        <div size="4" class="inputbox col-md-4">
                            <div class="form-group">
                                {!! Form::label('','Plano de Conta') !!}
                                {!! Form::select('mra_f_plano_contas_id', \App\Models\MRAFPlanoContas::Get_PlanoDeContas_options(), ($acao=='edit'?$MRAFContasPagar->mra_f_plano_contas_id:null), ['class' => 'form-control select_single_no_trigger', 'data-live-search' => 'true', "id" => "input_mra_f_plano_contas_id"]) !!}
                            </div>
                        </div>
                        <div size="4" class="inputbox col-md-4">
                            <div class="form-group">
                                {!! Form::label('','Conta Recebimento') !!} <span style="color: #ff0500;">*</span>
                                {!! Form::select('mra_f_contas_bancarias_id', \App\Models\MRAFTransferenciaContas::Get_ContasBancarias_options(), ($acao=='edit'?$MRAFContasPagar->mra_f_contas_bancarias_id:null), ['class' => 'form-control select_single_no_trigger', 'data-live-search' => 'true', "id" => "input_mra_f_contas_bancarias_id"]) !!}
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div size="12" class="inputbox col-md-12">
                            <div class="form-group">
                                {!! Form::label('','Descrição') !!}
                                {!! Form::text('descricao', ($acao=='edit'?$MRAFContasPagar->descricao:null), ['class' => 'form-control', "id" => "input_descricao", "maxlength"=>200]) !!}
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div size="6" class="inputbox col-md-6">
                            <div class="form-group">
                                {!! Form::label('','Anexo I') !!}
                                {!! Form::file('anexo', ['class' => 'form-control isFile' , "id" => "input_anexo"]) !!}
                            </div>
                            @if($acao == 'edit' and $MRAFContasPagar->anexo)
                                {!! Form::hidden('anexo', $MRAFContasPagar->anexo) !!}
                                @if($MRAFContasPagar->anexo && count(explode(".", $MRAFContasPagar->anexo)) >= 2)
                                    <a class="fancybox" rel="gallery1" target="_blank" href="{{ $fileurlbase . "images/" . $MRAFContasPagar->anexo }}">
                                        <img src="{{in_array(explode(".", $MRAFContasPagar->anexo)[1], array("mp4", "pdf", "doc", "docx", "rar", "zip", "txt", "7zip", "csv", "xls", "xlsx")) ? URL("/") . "/" . explode(".", $MRAFContasPagar->anexo)[1] . "-icon.png" : $fileurlbase . "images/" . $MRAFContasPagar->anexo}}" height="50">
                                    </a>
                                @endif
                            @endif
                            <i class='glyphicon glyphicon-trash input_remove' style='margin-top: 5px;'></i>
                        </div>
                        <div size="6" class="inputbox col-md-6">
                            <div class="form-group">
                                {!! Form::label('','Anexo II') !!}
                                {!! Form::file('anexo2', ['class' => 'form-control isFile' , "id" => "input_anexo2"]) !!}
                            </div>
                            @if($acao == 'edit' and $MRAFContasPagar->anexo2)
                                {!! Form::hidden('anexo2', $MRAFContasPagar->anexo2) !!}
                                @if($MRAFContasPagar->anexo2 && count(explode(".", $MRAFContasPagar->anexo2)) >= 2)
                                    <a class="fancybox" rel="gallery1" target="_blank" href="{{ $fileurlbase . "images/" . $MRAFContasPagar->anexo2 }}">
                                        <img src="{{in_array(explode(".", $MRAFContasPagar->anexo2)[1], array("mp4", "pdf", "doc", "docx", "rar", "zip", "txt", "7zip", "csv", "xls", "xlsx")) ? URL("/") . "/" . explode(".", $MRAFContasPagar->anexo2)[1] . "-icon.png" : $fileurlbase . "images/" . $MRAFContasPagar->anexo2}}" height="50">
                                    </a>
                                @endif
                            @endif
                            <i class='glyphicon glyphicon-trash input_remove' style='margin-top: 5px;'></i>
                        </div>
                    </div>
                </div>
                <div size="12" class="inputbox col-md-12">
                    <h2 class="page-header" style="font-size:20px;">
                        <i class="glyphicon glyphicon-th-large"></i> Formas de Pagamento
                    </h2>
                </div>
                <div size="12" class="inputbox col-md-12">
                    <div class="row">
                        <div size="4" class="inputbox col-md-4">
                            <div class="form-group">
                                {!! Form::label('','Tipo de Pagamento') !!} <span style="color: #ff0500;">*</span>
                                {!! Form::select('tipo_pagamento', \App\Http\Controllers\MRA\MRAListas::Get_options_tipo_pagamento(), ($acao=='edit'?$MRAFContasPagar->tipo_pagamento:null), ['class' => 'form-control select_single_no_trigger', 'data-live-search' => 'true', "id" => "input_tipo_pagamento"]) !!}
                            </div>
                        </div>
                        <div size="4" class="inputbox col-md-4">
                            <div class="form-group">
                                {!! Form::label('','Vencimento') !!} <span style="color: #ff0500;">*</span>
                                <div class="input-group">
                                    <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                                    {!! Form::text('vencimento', ($acao=='edit'?\App\Helper\Helper::H_Data_DB_ptBR($MRAFContasPagar->vencimento):null), ['autocomplete' =>'off', 'class' => 'form-control componenteData_v2', "placeholder"=>"__/__/____", "id" => "input_vencimento"]) !!}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','Valor do Serviço') !!} <span style="color: #ff0500;">*</span>
                                <div class="input-group">
                                    <div class="input-group-addon"><strong>R$</strong></div>
                                    {!! Form::text('valor', ($acao=='edit'?\App\Helper\Helper::H_Decimal_DB_ptBR($MRAFContasPagar->valor):null), ['class' => 'form-control money_v2', "placeholder" => "0,00", "id" => "input_valor"]) !!}
                                </div>
                            </div>
                        </div>
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','Juros ( + )') !!}
                                <div class="input-group">
                                    <div class="input-group-addon"><strong>R$</strong></div>
                                    {!! Form::text('juros', ($acao=='edit'?\App\Helper\Helper::H_Decimal_DB_ptBR($MRAFContasPagar->juros):null), ['class' => 'form-control money_v2', "placeholder" => "0,00", "id" => "input_juros"]) !!}
                                </div>
                            </div>
                        </div>
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','Multa ( + )') !!}
                                <div class="input-group">
                                    <div class="input-group-addon"><strong>R$</strong></div>
                                    {!! Form::text('multa', ($acao=='edit'?\App\Helper\Helper::H_Decimal_DB_ptBR($MRAFContasPagar->multa):null), ['class' => 'form-control money_v2', "placeholder" => "0,00", "id" => "input_multa"]) !!}
                                </div>
                            </div>
                        </div>
                        @php
                            $valor_total_a_pagar      = 0;
                            if($acao == 'edit'){
                                $valor_total_a_pagar  = ($MRAFContasPagar->valor + $MRAFContasPagar->juros + $MRAFContasPagar->multa);
                            }
                        @endphp
                        <div id="box_valor_total_a_pagar" size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','Valor Total a Pagar') !!}
                                <div class="input-group">
                                    <div class="input-group-addon"><strong>R$</strong></div>
                                    {!! Form::text('valor_total_a_pagar', ($acao=='edit'?number_format($valor_total_a_pagar,2,',','.'):null), ['class' => 'form-control', "disabled" => true, "placeholder" => "0,00", "id" => "input_valor_total_a_pagar"]) !!}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div id="box_av_forma_pagamento" size="4" class="inputbox col-md-4" style="display:none;">
                            <div class="form-group">
                                {!! Form::label('','Forma de Pagamento') !!} <span style="color: #ff0500;">*</span>
                                {!! Form::select('av_forma_pagamento', \App\Http\Controllers\MRA\MRAListas::Get_options_formas_pagamentos(), ($acao=='edit'?$MRAFContasPagar->av_forma_pagamento:null), ['class' => 'form-control select_single_no_trigger', 'data-live-search' => 'true', "id" => "input_av_forma_pagamento"]) !!}
                            </div>
                        </div>
                        <div id="box_av_status_pagamento" size="3" class="inputbox col-md-3" style="display:none;">
                            <div class="form-group">
                                {!! Form::label('','Status Pagamento',['class'=>'text-warning']) !!} <span style="color: #ff0500;">*</span>
                                {!! Form::select('av_status_pagamento', \App\Http\Controllers\MRA\MRAListas::Get_options_status_pagamento([""]), (($acao=='edit' and !empty($MRAFContasPagar->av_status_pagamento))?$MRAFContasPagar->av_status_pagamento:2), ['class' => 'form-control select_single_no_trigger', 'data-live-search' => 'true', "id" => "input_av_status_pagamento"]) !!}
                            </div>
                        </div>
                    </div>
                    <div class="row row_bg_st1">
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','Valor de Entrada ( - )') !!}
                                <div class="input-group">
                                    <div class="input-group-addon"><strong>R$</strong></div>
                                    {!! Form::text('valor_entrada', ($acao=='edit'?\App\Helper\Helper::H_Decimal_DB_ptBR($MRAFContasPagar->valor_entrada):null), ['class' => 'form-control money_v2', "placeholder" => "0,00", "id" => "input_valor_entrada"]) !!}
                                </div>
                            </div>
                        </div>
                        <div size="4" class="inputbox col-md-4">
                            <div class="form-group">
                                {!! Form::label('','Forma de Pagamento Entada') !!}
                                {!! Form::select('entrada_forma_pagamento', \App\Http\Controllers\MRA\MRAListas::Get_options_formas_pagamentos(), ($acao=='edit'?$MRAFContasPagar->entrada_forma_pagamento:null), ['class' => 'form-control select_single_no_trigger', 'data-live-search' => 'true', "id" => "input_entrada_forma_pagamento"]) !!}
                            </div>
                        </div>
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','Status Pagamento Entrada',['class'=>'text-warning']) !!}
                                {!! Form::select('entrada_status_pagamento', \App\Http\Controllers\MRA\MRAListas::Get_options_status_pagamento(), ($acao=='edit'?$MRAFContasPagar->entrada_status_pagamento:null), ['class' => 'form-control select_single_no_trigger', 'data-live-search' => 'true', "id" => "input_entrada_status_pagamento"]) !!}
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        @php
                            $valor_total_a_parcelar     = 0;
                            if($acao == 'edit'){
                                $valor_total_a_parcelar = ($MRAFContasPagar->valor + $MRAFContasPagar->juros + $MRAFContasPagar->multa - $MRAFContasPagar->valor_entrada);
                            }
                        @endphp
                        <div id="box_valor_total_a_parcelar" size="3" class="inputbox col-md-3" style="display:none;">
                            <div class="form-group">
                                {!! Form::label('','Valor a Parcelar') !!}
                                <div class="input-group">
                                    <div class="input-group-addon"><strong>R$</strong></div>
                                    {!! Form::text('valor_total_a_parcelar', ($acao=='edit'?number_format($valor_total_a_parcelar,2,',','.'):null), ['class' => 'form-control', "disabled" => true, "placeholder" => "0,00", "id" => "input_valor_total_a_parcelar"]) !!}
                                </div>
                            </div>
                        </div>
                        <div id="box_parcelas" size="3" class="inputbox col-md-3" style="display:none;">
                            <div class="form-group">
                                {!! Form::label('','Parcelas') !!} <span style="color: #ff0500;">*</span>
                                <div class="input-group mb_15p">
                                    {!! Form::number('parcelas', ($acao=='edit'?$MRAFContasPagar->parcelas:null), ['class' => 'form-control', "placeholder"=>"0", "id" => "input_parcelas", "min"=>0]) !!}
                                    <span class="input-group-btn">
                                        <button class="btn btn-primary" type="button" onclick="javascript:calculo_qt_parcelas_valor_total();"><i class="glyphicon glyphicon-refresh"></i></button>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="box_parcelas_grid" class="row" style="display:none;">
                        <div size="12" class="inputbox col-md-12">
                            <h2 class="page-header" style="font-size:20px;">
                                <i class="glyphicon glyphicon-record"></i> Parcelas
                            </h2>
                        </div>
                        <div class="GridContasPagarParcelas_grid Grid_grid" count="0">
                            @php
                                function GridContasPagarParcelas($acao,$MRAFContasPagar,$Item) {
                            @endphp
                            <div class="divdefault item">
                                <div class="col-md-12" style="margin-bottom: 10px;">
                                    <div class="row">
                                        {!! Form::hidden('mra_f_contas_pagar_parc_id[]', ($acao=='edit'?($Item?$Item->id:null):null), []) !!}
                                        @php
                                            $parcelas_posicao = null;
                                            if($MRAFContasPagar and $Item){
                                                $parcelas_posicao = ($Item->k+1)."/".$MRAFContasPagar->parcelas;
                                            }
                                        @endphp
                                        <div size="1" class="inputbox col-md-1">
                                            {!! Form::label('','Parcela') !!}
                                            {!! Form::text('parcelas_posicao[]', $parcelas_posicao, ['autocomplete' =>'off', 'class' => 'form-control parcelas_posicao', "placeholder"=>"_/_", "disabled"=>true]) !!}
                                        </div>
                                        <div size="3" class="inputbox col-md-3">
                                            <div class="form-group">
                                                {!! Form::label('','Vencimento') !!} <span style="color: #ff0500;">*</span>
                                                {!! Form::text('parcelas_vencimento[]', ($acao=='edit'?($Item?\App\Helper\Helper::H_Data_DB_ptBR($Item->vencimento):null):null), ['autocomplete' =>'off', 'class' => 'form-control componenteData_v2 parcelas_vencimento', "placeholder"=>"__/__/____"]) !!}
                                            </div>
                                        </div>
                                        <div size="3" class="inputbox col-md-3">
                                            <div class="form-group">
                                                {!! Form::label('','Forma de Pagamento') !!} <span style="color: #ff0500;">*</span>
                                                {!! Form::select('parcelas_forma_pagamento[]', \App\Http\Controllers\MRA\MRAListas::Get_options_formas_pagamentos(), ($acao=='edit'?($Item?$Item->forma_pagamento:null):null), ['class' => 'form-control select_single_no_trigger parcelas_forma_pagamento', 'data-live-search' => 'true']) !!}
                                            </div>
                                        </div>
                                        <div size="2" class="inputbox col-md-2">
                                            <div class="form-group">
                                                {!! Form::label('','Valor') !!} <span style="color: #ff0500;">*</span>
                                                {!! Form::text('parcelas_valor[]', ($acao=='edit'?($Item?\App\Helper\Helper::H_Decimal_DB_ptBR($Item->valor):null):null), ['class' => 'form-control money_v2 parcelas_valor', "placeholder" => "0,00"]) !!}
                                            </div>
                                        </div>
                                        <div size="3" class="inputbox col-md-3">
                                            <div class="form-group">
                                                {!! Form::label('','Status Pagamento',['class'=>'text-warning']) !!} <span style="color: #ff0500;">*</span>
                                                {!! Form::select('parcelas_status_pagamento[]', \App\Http\Controllers\MRA\MRAListas::Get_options_status_pagamento([""]), ($acao=='edit'?($Item?$Item->status_pagamento:2):2), ['class' => 'form-control select_single_no_trigger parcelas_status_pagamento', 'data-live-search' => 'true']) !!}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <i class="glyphicon glyphicon-trash text-danger grid_remove_v2" data="GridContasPagarParcelas_grid"></i>
                            </div>
                            @php
                                }
                            @endphp
                            @php
                                $MRAFContasPagar_old = [];
                                if(old('mra_f_contas_pagar_parc_id')){
                                    foreach(old('mra_f_contas_pagar_parc_id') as $K => $old) {
                                        $old_stdClass                   = new stdClass();
                                        $old_stdClass->k                = $K;
                                        $old_stdClass->id               = old('mra_f_contas_pagar_parc_id')[$K];
                                        $old_stdClass->vencimento       = (!empty(old('parcelas_vencimento')[$K])?\App\Helper\Helper::H_Data_ptBR_DB(old('parcelas_vencimento')[$K]):null);
                                        $old_stdClass->forma_pagamento  = old('parcelas_forma_pagamento')[$K];
                                        $old_stdClass->valor            = (!empty(old('parcelas_valor')[$K])?\App\Helper\Helper::H_Decimal_ptBR_DB(old('parcelas_valor')[$K]):null);
                                        $old_stdClass->status_pagamento = old('parcelas_status_pagamento')[$K];
                                        $MRAFContasPagar_old[]        = $old_stdClass;
                                    }
                                }
                            @endphp
                            @if(count($MRAFContasPagar_old))
                                @foreach($MRAFContasPagar_old as $K => $Item)
                                    {{ GridContasPagarParcelas($acao,$MRAFContasPagar,$Item) }}
                                @endforeach
                            @elseif($MRAFContasPagar and $MRAFContasPagar->MRAFContasPagarParcelas and count($MRAFContasPagar->MRAFContasPagarParcelas))
                                @foreach($MRAFContasPagar->MRAFContasPagarParcelas as $K => $Item)
                                    @php
                                        $Item->k = $K;
                                    @endphp
                                    {{ GridContasPagarParcelas($acao,$MRAFContasPagar,$Item) }}
                                @endforeach
                            @else
                                {{ GridContasPagarParcelas($acao,null,null) }}
                            @endif
                        </div>
                        <div class="col-md-12 text-right">
                            <i class="glyphicon glyphicon-plus multiple_add_v2" data="GridContasPagarParcelas_grid"></i>
                        </div>
                    </div>
                </div>
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
                <div class="col-md-12" style="margin-top: 20px; margin-bottom: 20px;">
                    <div class="form-group form-group-btn-{{($acao=='edit'?'edit':'add')}}">
                        <a href="{{ URL::previous() }}" class="btn btn-default form-group-btn-add-voltar"><i class="glyphicon glyphicon-backward"></i> Voltar</a>
                        @if($acao == 'add' and App\Models\Permissions::permissaoUsuario(\Auth::user(), "$controller@store"))
                            <button type="submit" class="btn btn-default right form-group-btn-add-cadastrar"><span class="glyphicon glyphicon-plus"></span> Cadastrar</button>
                        @endif
                        @if($acao == 'edit' and App\Models\Permissions::permissaoUsuario(\Auth::user(), "$controller@update"))
                            <button type="submit" class="btn btn-default right form-group-btn-edit-salvar"><span class="glyphicon glyphicon-ok"></span> Salvar</button>
                        @endif
                    </div>
                </div>
                <div class="col-md-12">
                    <p class="text-warning text-right">
                        <strong>** Atenção!</strong> <i class="glyphicon glyphicon-alert"></i><br/>
                        - <strong class="text-danger">Alteração de Status de Pagamento</strong> interfere no seu <strong>Fluxo de Caixa, Extrato Bancários e Contas Bancárias</strong><br/>
                        - Caso ocorra <strong class="text-danger">reversão de pagamento</strong> seu <strong>Fluxo, Extrato e Contas</strong> devem ser <strong>revisadas manualmente</strong>!</strong>
                    </p>
                </div>
            </div>
        </div>
    </section>
    {!! Form::close() !!}

    @section('script')
        <script type="text/javascript">

            function calculo_valor_total(){
                let valor                   = RA.format.Decimal_ptBR_DB($('#input_valor').val());
                let valor_entrada           = RA.format.Decimal_ptBR_DB($('#input_valor_entrada').val());
                let valor_juros             = RA.format.Decimal_ptBR_DB($('#input_juros').val());
                let valor_multa             = RA.format.Decimal_ptBR_DB($('#input_multa').val());
                // Se o "Valor de Entrada" e "Status Pagamento de Entrada" não forem preenchido, valor de entrada será (0) zero
                if($.inArray($('#input_valor_entrada').val(),['','0,00']) > -1 || $("#input_entrada_status_pagamento").val() == ''){
                    valor_entrada           = 0;
                }
                let valor_a_pagar_calc    = (valor + valor_juros + valor_multa);
                let valor_a_parcelar_calc   = (valor + valor_juros + valor_multa - valor_entrada);

                $("#input_valor_total_a_pagar").val(RA.format.Decimal_DB_ptBR(valor_a_pagar_calc));
                $("#input_valor_total_a_parcelar").val(RA.format.Decimal_DB_ptBR(valor_a_parcelar_calc));
            }

            $("#input_valor").on('keyup change',function(){ calculo_valor_total(); });
            $("#input_valor_entrada").on('keyup change',function(){
                calculo_valor_total();
            });
            $("#input_entrada_status_pagamento").on('change',function(){ calculo_valor_total(); });
            $("#input_juros").on('keyup change',function(){ calculo_valor_total(); });
            $("#input_multa").on('keyup change',function(){ calculo_valor_total(); });

            function exibir_parcelas(){
                let _input_parcelas = $("#input_parcelas");
                if($("#input_tipo_pagamento").val() == '2' && _input_parcelas.val() != '' && _input_parcelas.val() > 0){
                    $("#box_parcelas_grid").show();
                }else {
                    $("#box_parcelas_grid").hide();
                }
            }

            $("#input_tipo_pagamento").on('change',function(){
                $(  "#box_av_forma_pagamento, " +
                    "#box_av_status_pagamento, " +
                    "#box_parcelas, " +
                    "#box_parcelas_grid").hide();
                switch($(this).val()){
                    case '1':
                        $("#box_av_forma_pagamento").show();
                        $("#box_av_status_pagamento").show();
                        $("#box_valor_total_a_parcelar").hide();
                        $("#box_parcelas").hide();
                        break;
                    case '2':
                        $("#box_av_forma_pagamento").hide();
                        $("#box_av_status_pagamento").hide();
                        $("#box_valor_total_a_parcelar").show();
                        $("#box_parcelas").show();
                        break;
                }

                exibir_parcelas();
            }).trigger('change');

            function qt_parcelas(){
                let e_parcelas_pos      = $("#box_parcelas_grid input.parcelas_posicao");
                let e_parcelas_pos_qt   = e_parcelas_pos.length;
                e_parcelas_pos.each(function(i,e){
                    $(e).val((i+1)+'/'+e_parcelas_pos_qt);
                });
            }

            function calculo_qt_parcelas_valor_total(){
                let qt_parcelas             = $("#input_parcelas").val();
                if(qt_parcelas=="" || qt_parcelas <= 0){ return false; }
                let e_parcelas_item         = $("#box_parcelas_grid .item");
                let e_parcelas_item_qt      = e_parcelas_item.length;

                // # add / remove ( parcelas ) ( item )
                for(let i = 0; i < qt_parcelas; i++){
                    if(!e_parcelas_item.eq(i).length) {
                        $("#box_parcelas_grid .multiple_add_v2").trigger('click');
                    }
                }

                let valor_total_a_parcelar  = RA.format.Decimal_ptBR_DB($("#input_valor_total_a_parcelar").val());
                let valor_parcela           = 0;

                if(valor_total_a_parcelar > 0 && qt_parcelas > 0){
                    valor_parcela           = (valor_total_a_parcelar / qt_parcelas);
                    valor_parcela           = RA.format.Decimal_DB_ptBR(valor_parcela,2);
                }

                let d_vencimento            = $("#input_vencimento").val();
                e_parcelas_item             = $("#box_parcelas_grid .item");
                e_parcelas_item.each(function(i,e){
                    if(d_vencimento != ''){
                        if(i == 0){
                            $(e).find('.parcelas_vencimento').val(d_vencimento);
                        }else {
                            d_vencimento    = RA.format.Data_Prox_Mes('ptBR',d_vencimento,'ptBR');
                            $(e).find('.parcelas_vencimento').val(d_vencimento);
                        }
                    }
                    $(e).find('.parcelas_valor').val((valor_parcela!='0'?valor_parcela:''));
                    if((i+1) > qt_parcelas){
                        $(e).find('.grid_remove_v2').trigger('click');
                    }
                });
                // - #
            }

            $("#input_parcelas").on("keyup change", function(){
                exibir_parcelas();
                qt_parcelas();
            }).trigger('change');

            $("#box_parcelas_grid .multiple_add_v2").on('click', function(){
                let e_item_pos_i = $("#box_parcelas_grid .item").length - 1;
                //setTimeout(function(){
                    let last_item = $("#box_parcelas_grid .item").eq(e_item_pos_i);
                    qt_parcelas();
                    click_parcela_grid_remove_v2(last_item.find('.grid_remove_v2'));
                    RA.load.componenteData_v2(last_item.find('.parcelas_vencimento'));
                    last_item.find('.parcelas_forma_pagamento').val('').trigger('change');
                    last_item.find('.parcelas_status_pagamento').val(2).trigger('change');
                //},500);
            });

            function click_parcela_grid_remove_v2(E){
                E.on('click', function(){
                    setTimeout(function(){
                        qt_parcelas();
                    },500);
                });
            }
            click_parcela_grid_remove_v2($("#box_parcelas_grid .grid_remove_v2"));

        </script>
    @endsection

@endsection
