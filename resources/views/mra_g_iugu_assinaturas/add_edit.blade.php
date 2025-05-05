@php
    $acao               = ((isset($MRAGIuguAssinaturas) and !is_null($MRAGIuguAssinaturas))?'edit':'add');
    $isPublic           = 0;
    $controller         = get_class(\Request::route()->getController());

    $MRAGIuguClientes   = ($acao=='edit'?$MRAGIuguAssinaturas->MRAGIuguClientes:null);
    $MRAGIuguPlanos     = ($acao=='edit'?$MRAGIuguAssinaturas->MRAGIuguPlanos:null);
    $disabled           = (($acao=='edit')?true:false);
@endphp
@extends($isPublic ? 'layouts.app-public' : 'layouts.app')
@section('content')
    @section('style')
        <style type="text/css">
        </style>
    @endsection
    <section class="content-header">
        <h1>Gateway Iugu - Assinaturas</h1>
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
    {!! Form::open(['url' => "mra_g_iugu/mra_g_iugu_assinaturas".($acao=='edit'?'/'.$MRAGIuguAssinaturas->id:''), 'method' => ($acao=='edit'?'put':'post'), 'novalidate'=> true, 'enctype' => 'multipart/form-data', 'accept-charset' => 'utf-8', 'id' => ($acao=='edit'?'form_edit':'form_add').'_mra_g_iugu_assinaturas']) !!}
    <section class="content">
        <div class="box">
            @if($acao=='edit')
                {!! Form::hidden('id', $MRAGIuguAssinaturas->id) !!}
                @if(\App\Models\Permissions::permissaoUsuario(\Auth::user(), "$controller@destroy"))
                    <div class="row" style="position: absolute; right: 0; padding: 5px; z-index: 100;">
                        <div class="col-md-12">
                            <a href="javascript:void(0);" type="button" class="btn btn-info consultar-assinatura" modulo="mra_g_iugu/mra_g_iugu_assinaturas" modulo_id="{{$MRAGIuguAssinaturas->id}}" style="margin:2px;"><i class="glyphicon glyphicon-refresh"></i> Recarregar Assinatura Iugu</a>
                            <a href="javascript:void(0);" type="button" class="btn btn-danger excluir-auto-confirma" modulo="mra_g_iugu/mra_g_iugu_assinaturas" modulo_id="{{$MRAGIuguAssinaturas->id}}" style="float:none;"><i class="glyphicon glyphicon-trash"></i></a>
                        </div>
                    </div>
                @endif
            @endif
            <div class="box-body" id="div_mra_g_iugu_assinaturas">

                <div size="12" class="inputbox col-md-12">
                    <h2 class="page-header" style="font-size:20px;">
                        <i class="glyphicon glyphicon-th-large"></i> Informações da Assinatura
                    </h2>
                </div>

                <div size="12" class="inputbox col-md-12">
                    <div class="row">
                        {{--<div size="2" class="inputbox col-md-2">
                            <div class="form-group">
                                {!! Form::label('','Status') !!}
                                {!! Form::select('status', \App\Http\Controllers\MRA\MRAListas::Get_options_status_ai([""]), ($acao=='edit'?$MRAGIuguAssinaturas->status:null), ['class' => 'form-control select_single_no_trigger' , "id" => "input_status"]) !!}
                            </div>
                        </div>--}}
                        @if($acao=='edit')
                            <div size="4" class="inputbox col-md-4">
                                <div class="form-group">
                                    {!! Form::label('','ID Iugu Assinatura',['class'=>'text-warning']) !!}
                                    {!! Form::text('iugu_subscriptions_id', ($acao=='edit'?$MRAGIuguAssinaturas->iugu_subscriptions_id:null), ['class' => 'form-control', "id" => "input_iugu_subscriptions_id", "disabled"=>$disabled]) !!}
                                </div>
                            </div>
                        @endif
                        {{--<div size="4" class="inputbox col-md-4">
                            <div class="form-group">
                                {!! Form::label('','Identificador Iugu') !!} <span style="color: #ff0500;">*</span>
                                {!! Form::text('iugu_plan_identifier', ($acao=='edit'?$MRAGIuguAssinaturas->iugu_plan_identifier:null), ['class' => 'form-control', "id" => "input_iugu_plan_identifier", "disabled"=>($acao=='edit'?true:false)]) !!}
                            </div>
                        </div>--}}
                    </div>
                    <div class="row">
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','Data de Expiração') !!}
                                <div class="input-group">
                                    <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                                    {!! Form::text('iugu_expires_at', ($acao=='edit'?\App\Helper\Helper::H_Data_DB_ptBR($MRAGIuguAssinaturas->iugu_expires_at):date('d-m-Y')), ['autocomplete' =>'off', 'class' => 'form-control componenteData_v2', "placeholder"=>"__/__/____","id" => "input_iugu_expires_at"]) !!}
                                </div>
                                @if($acao=='add')
                                <p class="help-block"><i>Data da primeira cobrança.</i></p>
                                @elseif($acao=='edit')
                                <p class="help-block"><i>Data de Expiração e da próxima Cobrança.</i></p>
                                @endif
                            </div>
                        </div>
                        <div size="4" class="inputbox col-md-4">
                            {!! Form::label('','Cliente',['class'=>'text-info']) !!} <span style="color: #ff0500;">*</span>
                            <div class="input-group mb_15p">
                                {!! Form::select('mra_g_iugu_clientes_id', \App\Models\MRAGIuguClientes::lista_clientes(), (($MRAGIuguClientes and $acao=='edit')?$MRAGIuguClientes->id:null), ['class' => 'form-control select_single_no_trigger', 'data-live-search' => 'true', "id" => "input_mra_g_iugu_clientes_id", "disabled"=>$disabled]) !!}
                                <span class="input-group-btn">
                                    <button class="btn btn-primary" type="button" {{ ($disabled?'disabled="disabled"':'') }} onclick="javascript:$('#input_mra_g_iugu_clientes_id').trigger('change');"><i class="glyphicon glyphicon-refresh"></i></button>
                                </span>
                            </div>
                        </div>
                        <div size="4" class="inputbox col-md-4">
                            {!! Form::label('','Plano',['class'=>'text-info']) !!} <span style="color: #ff0500;">*</span>
                            <div class="input-group mb_15p">
                                {!! Form::select('mra_g_iugu_planos_id', \App\Models\MRAGIuguPlanos::lista_planos(), (($MRAGIuguPlanos and $acao=='edit')?$MRAGIuguPlanos->id:null), ['class' => 'form-control select_single_no_trigger', 'data-live-search' => 'true', "id" => "input_mra_g_iugu_planos_id"]) !!}
                                <span class="input-group-btn">
                                    <button class="btn btn-primary" type="button" onclick="javascript:$('#input_mra_g_iugu_planos_id').trigger('change');"><i class="glyphicon glyphicon-refresh"></i></button>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        @if($acao=='edit')
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','Ciclo / Cobrança') !!}
                                <div class="input-group">
                                    <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                                    {!! Form::text('iugu_cycled_at', ($acao=='edit'?\App\Helper\Helper::H_DataHora_DB_ptBR($MRAGIuguAssinaturas->iugu_cycled_at):null), ['autocomplete' =>'off', 'class' => 'form-control componenteData_v2', "placeholder"=>"__/__/____","id" => "input_iugu_cycled_at", "disabled"=>true]) !!}
                                </div>
                            </div>
                        </div>
                        <div size="2" class="inputbox col-md-2">
                            <div class="form-group">
                                {!! Form::label('','Status') !!}
                                {!! Form::select('iugu_suspended', \App\Models\MRAGIuguAssinaturas::Get_options_iugu_suspended([""]), ($acao=='edit'?$MRAGIuguAssinaturas->iugu_suspended:null), ['class' => 'form-control select_single_no_trigger' , "id" => "input_iugu_suspended", "disabled"=>true]) !!}
                            </div>
                        </div>
                        {{--<div size="2" class="inputbox col-md-2">
                            <div class="form-group">
                                {!! Form::label('','Ativo') !!}
                                {!! Form::select('iugu_active', \App\Models\MRAGIuguAssinaturas::Get_options_iugu_active([""]), ($acao=='edit'?$MRAGIuguAssinaturas->iugu_active:null), ['class' => 'form-control select_single_no_trigger' , "id" => "input_iugu_active"]) !!}
                            </div>
                        </div>--}}
                        @endif
                        {{--<div size="2" class="inputbox col-md-2">
                            <div class="form-group">
                                {!! Form::label('','Cobrança Ignorada') !!}
                                {!! Form::select('skip_charge', \App\Models\MRAGIuguAssinaturas::Get_options_skip_charge([""]), ($acao=='edit'?$MRAGIuguAssinaturas->skip_charge:null), ['class' => 'form-control select_single_no_trigger' , "id" => "input_skip_charge", "disabled"=>$disabled]) !!}
                            </div>
                        </div>--}}
                        {{--<div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','Suspender quando Expirar') !!}
                                {!! Form::select('iugu_suspend_on_invoice_expired', \App\Models\MRAGIuguAssinaturas::Get_options_iugu_suspend_on_invoice_expired(), ($acao=='edit'?$MRAGIuguAssinaturas->iugu_suspend_on_invoice_expired:null), ['class' => 'form-control select_single_no_trigger' , "id" => "input_iugu_suspend_on_invoice_expired", "disabled"=>$disabled]) !!}
                            </div>
                        </div>--}}
                    </div>
                </div>
                <div size="12" class="inputbox col-md-12">
                    <h2 class="page-header" style="font-size:20px;">
                        <i class="glyphicon glyphicon-th-large"></i> Cliente
                    </h2>
                </div>
                <div size="12" class="inputbox col-md-12">
                    <div class="row">
                        {{--@if($acao=='edit')--}}
                        <div size="4" class="inputbox col-md-4">
                            <div class="form-group">
                                {!! Form::label('','ID Iugu Cliente',['class'=>'text-warning']) !!}
                                {!! Form::text('iugu_customer_id', (($MRAGIuguClientes and $acao=='edit')?$MRAGIuguClientes->iugu_customer_id:null), ['class' => 'form-control', "id" => "input_iugu_customer_id", "disabled"=>true]) !!}
                            </div>
                        </div>
                        {{--@endif--}}
                        <div size="2" class="inputbox col-md-2">
                            <div class="form-group">
                                {!! Form::label('','Tipo de Pessoa') !!} <span style="color: #ff0500;">*</span>
                                {!! Form::select('tipo', \App\Http\Controllers\MRA\MRAListas::Get_options_tipo_pessoa(), (($MRAGIuguClientes and $acao=='edit')?$MRAGIuguClientes->tipo:null), ['class' => 'form-control select_single_no_trigger' , "id" => "input_tipo", "disabled"=>true]) !!}
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div id="box_cnpj" size="4" class="inputbox col-md-4" style="display:none;">
                            <div class="form-group">
                                {!! Form::label('','CNPJ') !!} <span style="color: #ff0500;">*</span>
                                <div class="input-group mb_15p">
                                    {!! Form::text('cnpj', (($MRAGIuguClientes and $acao=='edit')?$MRAGIuguClientes->cnpj:null), ['class' => 'form-control cnpj_v2', "placeholder"=>"__.___.___/____-__", "id" => "input_cnpj", "maxlength"=>50, "disabled"=>true]) !!}
                                    <span class="input-group-btn">
                                        <button class="btn btn-primary" type="button" onclick="javascript:$('#input_cnpj').trigger('change');"><i class="glyphicon glyphicon-refresh"></i></button>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div id="box_cpf" size="4" class="inputbox col-md-4"  style="display:none;">
                            <div class="form-group">
                                {!! Form::label('','CPF') !!} <span style="color: #ff0500;">*</span>
                                {!! Form::text('cpf', (($MRAGIuguClientes and $acao=='edit')?$MRAGIuguClientes->cpf:null), ['class' => 'form-control cpf', "placeholder"=>"___.___.___-__", "id" => "input_cpf", "maxlength"=>50, "disabled"=>true]) !!}
                            </div>
                        </div>
                        <div id="box_inscricao_estadual" size="4" class="inputbox col-md-4"  style="display:none;">
                            <div class="form-group">
                                {!! Form::label('','Inscrição Estadual (IE)') !!}
                                {!! Form::text('inscricao_estadual', (($MRAGIuguClientes and $acao=='edit')?$MRAGIuguClientes->inscricao_estadual:null), ['class' => 'form-control', "id" => "input_inscricao_estadual", "maxlength"=>50, "disabled"=>true]) !!}
                            </div>
                        </div>
                        <div id="box_inscricao_municipal" size="4" class="inputbox col-md-4"  style="display:none;">
                            <div class="form-group">
                                {!! Form::label('','Inscrição Municipal (IE)') !!}
                                {!! Form::text('inscricao_municipal', (($MRAGIuguClientes and $acao=='edit')?$MRAGIuguClientes->inscricao_municipal:null), ['class' => 'form-control', "id" => "input_inscricao_municipal", "maxlength"=>50, "disabled"=>true]) !!}
                            </div>
                        </div>
                        <div size="4" class="inputbox col-md-4">
                            <div class="form-group">
                                {!! Form::label('','Telefone') !!}
                                <div class="input-group">
                                    <div class="input-group-addon"><i class="fa fa-phone"></i></div>
                                    {!! Form::text('cont_telefone', (($MRAGIuguClientes and $acao=='edit')?$MRAGIuguClientes->cont_telefone:null), ['class' => 'form-control telefone',"placeholder"=>"(__) ____-____", "id" => "input_cont_telefone", "maxlength"=>200, "disabled"=>true]) !!}
                                </div>
                            </div>
                        </div>
                        <div size="4" class="inputbox col-md-4">
                            <div class="form-group">
                                {!! Form::label('','E-mail') !!} <span style="color: #ff0500;">*</span>
                                <div class="input-group">
                                    <div class="input-group-addon"><i class="fa fa-envelope"></i></div>
                                    {!! Form::text('cont_email', (($MRAGIuguClientes and $acao=='edit')?$MRAGIuguClientes->cont_email:null), ['class' => 'form-control' , "id" => "input_cont_email", "maxlength"=>200, "disabled"=>true]) !!}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div size="2" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','CEP') !!}
                                {!! Form::text('end_cep', (($MRAGIuguClientes and $acao=='edit')?$MRAGIuguClientes->end_cep:null), ['class' => 'form-control cep_v2', "placeholder"=>"_____-___", "id" => "input_end_cep", "maxlength"=>50, "disabled"=>true]) !!}
                            </div>
                        </div>
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','Logradouro / Rua') !!}
                                {!! Form::text('end_rua', (($MRAGIuguClientes and $acao=='edit')?$MRAGIuguClientes->end_rua:null), ['class' => 'form-control' , "id" => "input_end_rua", "maxlength"=>200, "disabled"=>true]) !!}
                            </div>
                        </div>
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','Número') !!}
                                {!! Form::text('end_numero', (($MRAGIuguClientes and $acao=='edit')?$MRAGIuguClientes->end_numero:null), ['class' => 'form-control' , "id" => "input_end_numero", "maxlength"=>200, "disabled"=>true]) !!}
                            </div>
                        </div>
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','Bairro') !!}
                                {!! Form::text('end_bairro', (($MRAGIuguClientes and $acao=='edit')?$MRAGIuguClientes->end_bairro:null), ['class' => 'form-control' , "id" => "input_end_bairro", "maxlength"=>200, "disabled"=>true]) !!}
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div size="5" class="inputbox col-md-4">
                            <div class="form-group">
                                {!! Form::label('','Complemento') !!}
                                {!! Form::text('end_complemento', (($MRAGIuguClientes and $acao=='edit')?$MRAGIuguClientes->end_complemento:null), ['class' => 'form-control' , "id" => "input_end_complemento", "maxlength"=>200, "disabled"=>true]) !!}
                            </div>
                        </div>
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','Estado') !!}
                                {!! Form::select('end_estado', \App\Http\Controllers\MRA\MRAListas::Get_options_estados(), (($MRAGIuguClientes and $acao=='edit')?$MRAGIuguClientes->end_estado:null), ['class' => 'form-control select_single_no_trigger', 'data-live-search' => 'true', "id" => "input_end_estado", "disabled"=>true]) !!}
                            </div>
                        </div>
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','Cidade') !!}
                                {!! Form::text('end_cidade', (($MRAGIuguClientes and $acao=='edit')?$MRAGIuguClientes->end_cidade:null), ['class' => 'form-control' , "id" => "input_end_cidade", "maxlength"=>50, "disabled"=>true]) !!}
                            </div>
                        </div>
                        <div size="2" class="inputbox col-md-2">
                            <div class="form-group">
                                {!! Form::label('','País') !!}
                                {!! Form::select('end_pais', \App\Http\Controllers\MRA\MRAListas::Get_options_paises(), ((($MRAGIuguClientes and $acao=='edit') and !is_null($MRAGIuguClientes->end_pais))?$MRAGIuguClientes->end_pais:1058), ['class' => 'form-control select_single_no_trigger', 'data-live-search' => 'true', "dropdown-menu-right"=>"", "id" => "input_end_pais", "disabled"=>true]) !!}
                            </div>
                        </div>
                    </div>
                </div>
                <div size="12" class="inputbox col-md-12">
                    <h2 class="page-header" style="font-size:20px;">
                        <i class="glyphicon glyphicon-th-large"></i> Plano
                    </h2>
                </div>
                <div size="12" class="inputbox col-md-12">
                    <div class="row">
                        {{--@if($acao=='edit')--}}
                        <div size="4" class="inputbox col-md-4">
                            <div class="form-group">
                                {!! Form::label('','ID Iugu Plano',['class'=>'text-warning']) !!}
                                {!! Form::text('iugu_plan_id', (($MRAGIuguPlanos and $acao=='edit')?$MRAGIuguPlanos->iugu_plan_id:null), ['class' => 'form-control', "id" => "input_iugu_plan_id", "disabled"=>true]) !!}
                            </div>
                        </div>
                        {{--@endif--}}
                    </div>
                    <div class="row">
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','Valor') !!} <span style="color: #ff0500;">*</span>
                                <div class="input-group">
                                    <div class="input-group-addon"><strong>R$</strong></div>
                                    {!! Form::text('valor', (($MRAGIuguPlanos and $acao=='edit')?\App\Helper\Helper::H_Decimal_DB_ptBR($MRAGIuguPlanos->valor):null), ['class' => 'form-control money_v2', "placeholder" => "0,00", "id" => "input_valor", "disabled"=>true]) !!}
                                </div>
                            </div>
                        </div>
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','Intervalo') !!} <span style="color: #ff0500;">*</span>
                                {!! Form::number('intervalo', (($MRAGIuguPlanos and $acao=='edit')?$MRAGIuguPlanos->intervalo:null), ['class' => 'form-control' , "id" => "input_intervalo", "min"=>1, "max"=>12, "disabled"=>true]) !!}
                            </div>
                        </div>
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','Tipo de Intervalo') !!} <span style="color: #ff0500;">*</span>
                                {!! Form::select('intervalo_tipo', \App\Http\Controllers\MRA\MRAGIugu::Get_options_tipo_intervalo([""]), (($MRAGIuguPlanos and $acao=='edit')?$MRAGIuguPlanos->intervalo_tipo:null), ['class' => 'form-control select_single_no_trigger', 'data-live-search' => 'true', "id" => "input_intervalo_tipo", "disabled"=>true]) !!}
                            </div>
                        </div>
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','Dias de Faturamento') !!} <span style="color: #ff0500;">*</span>
                                {!! Form::number('dias_ger_faturamento', (($MRAGIuguPlanos and $acao=='edit')?$MRAGIuguPlanos->dias_ger_faturamento:null), ['class' => 'form-control' , "id" => "input_dias_ger_faturamento", "min"=>1, "max"=>30, "disabled"=>true]) !!}
                                <p class="help-block"><i>Dias antes de vencer gera uma nova fatura.</i></p>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div size="12" class="inputbox col-md-12">
                            <div class="form-group">
                                {!! Form::label('','Formas de Pagamento') !!} <span style="color: #ff0500;">*</span>
                                <div class="l_checkbox_radio">
                                    <label>{!! Form::checkbox('fp_todos', 1, (($MRAGIuguPlanos and $acao=='edit')?$MRAGIuguPlanos->fp_todos:null), ['class' => '' , "id" => "input_fp_todos", "disabled"=>true]) !!} Todos</label>
                                    <label>{!! Form::checkbox('fp_cartao_credito', 1, (($MRAGIuguPlanos and $acao=='edit')?$MRAGIuguPlanos->fp_cartao_credito:null), ['class' => '' , "id" => "input_fp_cartao_credito", "disabled"=>true]) !!} Cartão de Crédito</label>
                                    <label>{!! Form::checkbox('fp_boleto', 1, (($MRAGIuguPlanos and $acao=='edit')?$MRAGIuguPlanos->fp_boleto:null), ['class' => '' , "id" => "input_fp_boleto", "disabled"=>true]) !!} Boleto</label>
                                    <label>{!! Form::checkbox('fp_pix', 1, (($MRAGIuguPlanos and $acao=='edit')?$MRAGIuguPlanos->fp_pix:null), ['class' => '' , "id" => "input_fp_pix", "disabled"=>true]) !!} Pix</label>
                                </div>
                            </div>
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
                        @if($acao == 'edit' and App\Models\Permissions::permissaoUsuario(\Auth::user(), "$controller@update"))
                            <button type="submit" class="btn btn-default right form-group-btn-edit-salvar" style="float:right;"><span class="glyphicon glyphicon-ok"></span> Salvar</button>
                        @endif
                        @if($acao == 'edit' and App\Models\Permissions::permissaoUsuario(\Auth::user(), "$controller@suspender") and !$MRAGIuguAssinaturas->iugu_suspended)
                            <button type="submit" name="suspender_assinatura" value="1" class="btn btn-warning right form-group-btn-edit-suspender" style="float:right; margin: 0px; margin-right:15px;"><span class="glyphicon glyphicon-alert"></span>&nbsp; Suspender</button>
                        @endif
                        @if($acao == 'edit' and App\Models\Permissions::permissaoUsuario(\Auth::user(), "$controller@ativar") and $MRAGIuguAssinaturas->iugu_suspended)
                            <button type="submit" name="ativar_assinatura" value="1" class="btn btn-success right form-group-btn-edit-ativar" style="float:right; margin: 0px; margin-right:15px;"><span class="glyphicon glyphicon-ok"></span>&nbsp; Ativar</button>
                        @endif
                        @if($acao == 'add' and App\Models\Permissions::permissaoUsuario(\Auth::user(), "$controller@store"))
                            <button type="submit" class="btn btn-default right form-group-btn-add-cadastrar"><span class="glyphicon glyphicon-plus"></span> Cadastrar</button>
                        @endif
                        @if($acao == 'edit' and App\Models\Permissions::permissaoUsuario(\Auth::user(), "$controller@destroy"))
                            <button type="submit" name="destroy_forcado" value="1" class="btn btn-danger right form-group-btn-edit-destroy-forcado" onclick="javascript: return (confirm('A Assinatura será excluída dentro do sistema! Tem certeza?')?true:false);" style="float:right; margin: 0px; margin-right:15px;"><i class="glyphicon glyphicon-alert"></i>&nbsp; Forçar Exclusão</button>
                        @endif
                    </div>
                </div>
                @if($acao == 'edit')
                <div class="col-md-12">
                    <p class="text-warning text-right">
                        <strong>** Atenção!</strong> <i class="glyphicon glyphicon-alert"></i><br/>
                        - <strong class="text-danger">Forçar Exclusão</strong> apenas será removido <strong>dentro do sistema</strong>!</strong>
                    </p>
                </div>
                @endif
            </div>
        </div>
    </section>
    {!! Form::close() !!}

    @section('script')
        <script type="text/javascript">
            // :: Tipo de Pessoa
            $("#input_tipo").on("change",function(){
                switch($("#input_tipo").val()) {
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

            // :: Cliente
            $("#input_mra_g_iugu_clientes_id").on('change', async function(){
                let _this = $(this);
                if((_this.attr('af') != undefined && _this.attr('af')=='true') || _this.val() == ''){ return false; }
                _this.attr('af','true');
                setTimeout(function(){ _this.attr('af','false'); },1000); // Fix*
                try {
                    $.get('{{URL('mra_g_iugu/mra_g_iugu_clientes')}}/'+_this.val()+'/ajax', function(d,s){
                        if(d!=undefined && Object.keys(d).length){
                            $("#input_iugu_customer_id").val(d.iugu_customer_id);
                            $("#input_tipo").val(d.tipo).trigger('change');
                            $("#input_cnpj").val(d.cnpj);
                            $("#input_cpf").val(d.cpf);
                            $("#input_inscricao_estadual").val(d.inscricao_estadual);
                            $("#input_inscricao_municipal").val(d.inscricao_municipal);
                            $("#input_cont_telefone").val(d.cont_telefone);
                            $("#input_cont_email").val(d.cont_email);
                            $("#input_end_cep").val(d.end_cep);
                            $("#input_end_rua").val(d.end_rua);
                            $("#input_end_numero").val(d.end_numero);
                            $("#input_end_bairro").val(d.end_bairro);
                            $("#input_end_complemento").val(d.end_complemento);
                            $("#input_end_estado").val(d.end_estado).trigger('change');
                            $("#input_end_cidade").val(d.end_cidade);
                            $("#input_end_pais").val(d.end_pais);
                        }
                    });
                }catch(e){
                    //console.log('Erro: '+e);
                }
            });

            // :: Plano
            $("#input_mra_g_iugu_planos_id").on('change', async function(){
                let _this = $(this);
                if((_this.attr('af') != undefined && _this.attr('af')=='true') || _this.val() == ''){ return false; }
                _this.attr('af','true');
                setTimeout(function(){ _this.attr('af','false'); },1000); // Fix*
                try {
                    $.get('{{URL('mra_g_iugu/mra_g_iugu_planos')}}/'+_this.val()+'/ajax', function(d,s){
                        if(d!=undefined && Object.keys(d).length){
                            $("#input_iugu_plan_id").val(d.iugu_plan_id);
                            $("#input_valor").val(RA.format.Decimal_DB_ptBR(d.valor));
                            $("#input_intervalo").val(d.intervalo);
                            $("#input_intervalo_tipo").val(d.intervalo_tipo);
                            $("#input_dias_ger_faturamento").val(d.dias_ger_faturamento);
                            $("#input_fp_todos").prop('checked',d.fp_todos);
                            $("#input_fp_cartao_credito").prop('checked',d.fp_cartao_credito);
                            $("#input_fp_boleto").prop('checked',d.fp_boleto);
                            $("#input_fp_pix").prop('checked',d.fp_pix);
                        }
                    });
                }catch(e){
                    //console.log('Erro: '+e);
                }
            });

            // :: Consultar Assinaturas
            $(".consultar-assinatura").on("click", function() {
                let modulo     = ($(this).attr('modulo')!=undefined && $(this).attr('modulo') != ""?$(this).attr('modulo'):undefined);
                let modulo_id  = ($(this).attr('modulo_id')!=undefined && $(this).attr('modulo_id') != ""?$(this).attr('modulo_id'):undefined);

                if(modulo == undefined || modulo_id == undefined){ return false; }

                let form    = document.createElement('form');
                form.method = 'GET';
                form.action = base+'/'+modulo+'/consultar_assinatura/'+modulo_id
                let input   = document.createElement('input');
                input.type  = 'hidden';
                input.name  = '_token';
                input.value = $("meta[name=\'csrf-token\']").attr('content');
                form.appendChild(input);
                $('body').append(form);
                form.submit();
            });
        </script>
    @endsection

@endsection
