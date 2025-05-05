@php

    $isPublic = 0;

    $controller = get_class(\Request::route()->getController());

@endphp

@extends($isPublic ? 'layouts.app-public' : 'layouts.app')

@section('content')

    @section('style')
        <style type="text/css">
        </style>
    @endsection

    <section class="content-header Vendas_add">
        <h1>Vendas</h1>
        <!--@if(!$isPublic)
            <ol class="breadcrumb">
                <li><a href="{{ URL('/') }}">Home</a></li>
            <li><a href="{{ URL('/') }}/vendas">Vendas</a></li>
            <li class="active">Vendas</li>
        </ol>
        @endif-->
    </section>

    <section class="content Vendas_add">
        <div class="box">
            {!! Form::open(['url' => "vendas", 'method' => 'post', 'novalidate'=> true, 'enctype' => 'multipart/form-data', 'accept-charset' => 'utf-8', 'id' => 'form_add_vendas']) !!}
            <div class="box-body" id="div_vendas">
                <div size="3" class="inputbox col-md-3">
                    <div class="form-group">
                        {!! Form::label('','Tipo de Venda') !!}
                        <div class='input-group'>
                            <div class='input-group-addon'>
                                <i class='glyphicon glyphicon-star'></i>
                            </div>
                            {!! Form::select('tipo_de_venda', \App\Models\Vendas::Get_options_tipo_de_venda(), null, ['no-trigger-change'=>'','class' => 'form-control select_single' , "id" => "input_tipo_de_venda"]) !!}
                        </div>
                    </div>
                </div>
                <div size="3" class="inputbox col-md-3">
                    <div class="form-group">
                        {!! Form::label('','N° da Fatura') !!}
                        <div class='input-group'>
                            <div class='input-group-addon'>
                                <i class='glyphicon glyphicon-pencil'></i>
                            </div>
                            {!! Form::text('faturamento', null, ['class' => 'form-control' , "id" => "input_faturamento",'disabled' => 'disabled',]) !!}
                        </div>
                    </div>
                </div>
                <div size="3" class="inputbox col-md-3">
                    <div class="form-group">
                        {!! Form::label('','Foi Faturado?') !!}
                        <div class='input-group'>
                            <div class='input-group-addon'>
                                <i class='glyphicon glyphicon-ok-circle'></i>
                            </div>
                            {!! Form::select('foi_faturado', \App\Models\Vendas::Get_options_foi_faturado(), null, ['no-trigger-change'=>'','class' => 'form-control select_single' , "id" => "input_foi_faturado",'disabled' => 'disabled',]) !!}
                        </div>
                    </div>
                </div>
                <div size="12" class="inputbox col-md-12">
                    <h2 class="page-header" style="font-size:20px;">
                        <i class="glyphicon glyphicon-th-large"></i> INFORMAÇÕES DA VENDA:
                    </h2>
                </div>
                <div size="2" class="inputbox col-md-2">
                    <div class="form-group">
                        {!! Form::label('','N&deg; Venda/Recibo') !!}
                        {!! Form::text('id', null, ['class' => 'form-control' , "id" => "input_id",'disabled' => 'disabled',]) !!}
                    </div>
                </div>
                <div size="2" class="inputbox col-md-2">
                    <div class="form-group">
                        {!! Form::label('','Data') !!}
                        <div class='input-group'>
                            <div class='input-group-addon'>
                                <i class='glyphicon glyphicon-calendar'></i>
                            </div>
                            {!! Form::text('data', null, ['autocomplete' =>'off', 'class' => 'form-control componenteData' , "id" => "input_data"]) !!}
                        </div>
                    </div>
                </div>
                <div size="4" class="inputbox col-md-4">
                    {{--<div class="form-group">
                        {!! Form::label('','Cliente') !!}
                        {!! Form::select('cliente', $clientes_nome_do_cliente, null, ['class' => 'form-control  select_relationship ', 'data-sub' => '', 'data-live-search' => 'true',  'data-none-selected-text' => true, 'controller' => 'clientes' , "id" => "input_cliente"]) !!}
                    </div>--}}
                    <div class="form-group">
                        {!! Form::label('','Cliente') !!}
                        <div class='input-group input-add-edit'>
                            {!! Form::select('cliente', $clientes_nome_do_cliente, null, ['no-trigger-change'=>'','class' => 'form-control  select_relationship ', 'data-sub' => '', 'data-live-search' => 'true',  'data-none-selected-text' => true, 'controller' => 'clientes' , "id" => "input_cliente"]) !!}
                            <div class='input-group-btn'>
                                <button type="button" class="btn btn-xs btn-info" modal-create modal-input-refresh="#input_cliente" modal-modulo="clientes" modal-url="{{URL('clientes/create/modal')}}"><i class='glyphicon glyphicon-plus-sign'></i></button>
                            </div>
                        </div>
                    </div>
                </div>
                <div size="4" class="inputbox col-md-4">
                    {{--<div class="form-group">
                        {!! Form::label('','Vendedor') !!}
                        {!! Form::select('vendedor', $vendedores_nome_do_vendedor, null, ['class' => 'form-control  select_relationship ', 'data-sub' => '', 'data-live-search' => 'true',  'data-none-selected-text' => true, 'controller' => 'vendedores' , "id" => "input_vendedor"]) !!}
                    </div>--}}
                    <div class="form-group">
                        {!! Form::label('','Vendedor') !!}
                        <div class='input-group input-add-edit'>
                            {!! Form::select('vendedor', $vendedores_nome_do_vendedor, null, ['no-trigger-change'=>'','class' => 'form-control  select_relationship ', 'data-sub' => '', 'data-live-search' => 'true',  'data-none-selected-text' => true, 'controller' => 'vendedores' , "id" => "input_vendedor"]) !!}
                            <div class='input-group-btn'>
                                <button type="button" class="btn btn-xs btn-info" modal-create modal-input-refresh="#input_vendedor" modal-modulo="vendedores" modal-url="{{URL('vendedores/create/modal')}}"><i class='glyphicon glyphicon-plus-sign'></i></button>
                            </div>
                        </div>
                    </div>
                </div>
                <div size="3" class="inputbox col-md-3">
                    <div class="form-group">
                        {!! Form::label('','Localizador') !!}
                        <div class='input-group'>
                            <div class='input-group-addon'>
                                <i class='glyphicon glyphicon-search'></i>
                            </div>
                            {!! Form::text('localizador', null, ['class' => 'form-control' , "id" => "input_localizador", "style" => "text-transform: uppercase;", "oninput" => "this.value = this.value.toUpperCase();"]) !!}
                        </div>
                    </div>
                </div>
                <div size="3" class="inputbox col-md-3">
                    {{--<div class="form-group">
                        {!! Form::label('','Produto') !!}
                        {!! Form::select('produto', $produtos_produto, null, ['class' => 'form-control  select_relationship ', 'data-sub' => '', 'data-live-search' => 'true',  'data-none-selected-text' => true, 'controller' => 'produtos' , "id" => "input_produto"]) !!}
                    </div>--}}
                    <div class="form-group">
                        {!! Form::label('','Produto') !!}
                        <div class='input-group input-add-edit'>
                            {!! Form::select('produto', $produtos_produto, null, ['no-trigger-change'=>'','class' => 'form-control  select_relationship ', 'data-sub' => '', 'data-live-search' => 'true',  'data-none-selected-text' => true, 'controller' => 'produtos' , "id" => "input_produto"]) !!}
                            <div class='input-group-btn'>
                                <button type="button" class="btn btn-xs btn-info" modal-create modal-input-refresh="#input_produto" modal-modulo="produtos" modal-url="{{URL('produtos/create/modal')}}"><i class='glyphicon glyphicon-plus-sign'></i></button>
                            </div>
                        </div>
                    </div>
                </div>
                <div size="3" class="inputbox col-md-3">
                    {{--<div class="form-group">
                        {!! Form::label('','Serviço') !!}
                        {!! Form::select('servico', $servicos_servico, null, ['class' => 'form-control  select_relationship ', 'data-sub' => '', 'data-live-search' => 'true',  'data-none-selected-text' => true, 'controller' => 'servicos' , "id" => "input_servico"]) !!}
                    </div>--}}
                    <div class="form-group">
                        {!! Form::label('','Serviço') !!}
                        <div class='input-group input-add-edit'>
                            {!! Form::select('servico', $servicos_servico, null, ['no-trigger-change'=>'','class' => 'form-control  select_relationship ', 'data-sub' => '', 'data-live-search' => 'true',  'data-none-selected-text' => true, 'controller' => 'servicos' , "id" => "input_servico"]) !!}
                            <div class='input-group-btn'>
                                <button type="button" class="btn btn-xs btn-info" modal-create modal-input-refresh="#input_servico" modal-modulo="servicos" modal-url="{{URL('servicos/create/modal')}}"><i class='glyphicon glyphicon-plus-sign'></i></button>
                            </div>
                        </div>
                    </div>
                </div>
                <div size="3" class="inputbox col-md-3">
                    {{--<div class="form-group">
                        {!! Form::label('','Fornecedor') !!}
                        {!! Form::select('fornecedor', $fornecedores_fornecedor, null, ['class' => 'form-control  select_relationship ', 'data-sub' => '', 'data-live-search' => 'true',  'data-none-selected-text' => true, 'controller' => 'fornecedores' , "id" => "input_fornecedor"]) !!}
                    </div>--}}
                    <div class="form-group">
                        {!! Form::label('','Fornecedor') !!}
                        <div class='input-group input-add-edit'>
                            {!! Form::select('fornecedor', $fornecedores_fornecedor, null, ['no-trigger-change'=>'','class' => 'form-control  select_relationship ', 'data-sub' => '', 'data-live-search' => 'true',  'data-none-selected-text' => true, 'controller' => 'fornecedores' , "id" => "input_fornecedor"]) !!}
                            <div class='input-group-btn'>
                                <button type="button" class="btn btn-xs btn-info" modal-create modal-input-refresh="#input_fornecedor" modal-modulo="fornecedores" modal-url="{{URL('fornecedores/create/modal')}}"><i class='glyphicon glyphicon-plus-sign'></i></button>
                            </div>
                        </div>
                    </div>
                </div>
                <div size="3" class="inputbox col-md-3">
                    {{--<div class="form-group">
                        {!! Form::label('','Companhia') !!}
                        {!! Form::select('companhia', $companhias_companhia, null, ['class' => 'form-control  select_relationship ', 'data-sub' => '', 'data-live-search' => 'true',  'data-none-selected-text' => true, 'controller' => 'companhias' , "id" => "input_companhia"]) !!}
                    </div>--}}
                    <div class="form-group">
                        {!! Form::label('','Companhia') !!}
                        <div class='input-group input-add-edit'>
                            {!! Form::select('companhia', $companhias_companhia, null, ['no-trigger-change'=>'','class' => 'form-control  select_relationship ', 'data-sub' => '', 'data-live-search' => 'true',  'data-none-selected-text' => true, 'controller' => 'companhias' , "id" => "input_companhia"]) !!}
                            <div class='input-group-btn'>
                                <button type="button" class="btn btn-xs btn-info" modal-create modal-input-refresh="#input_companhia" modal-modulo="companhias" modal-url="{{URL('companhias/create/modal')}}"><i class='glyphicon glyphicon-plus-sign'></i></button>
                            </div>
                        </div>
                    </div>
                </div>
                <div size="5" class="inputbox col-md-5">
                    {{--<div class="form-group">
                        {!! Form::label('','Trecho') !!}
                        {!! Form::select('trecho', $trechos_trechos, null, ['class' => 'form-control  select_relationship ', 'data-sub' => '', 'data-live-search' => 'true',  'data-none-selected-text' => true, 'controller' => 'trechos' , "id" => "input_trecho"]) !!}
                    </div>--}}
                    <div class="form-group">
                        {!! Form::label('','Trecho') !!}
                        <div class='input-group input-add-edit'>
                            {!! Form::select('trecho', $trechos_trechos, null, ['no-trigger-change'=>'','class' => 'form-control  select_relationship ', 'data-sub' => '', 'data-live-search' => 'true',  'data-none-selected-text' => true, 'controller' => 'trechos' , "id" => "input_trecho"]) !!}
                            <div class='input-group-btn'>
                                <button type="button" class="btn btn-xs btn-info" modal-create modal-input-refresh="#input_trecho" modal-modulo="trechos" modal-url="{{URL('trechos/create/modal')}}"><i class='glyphicon glyphicon-plus-sign'></i></button>
                            </div>
                        </div>
                    </div>
                </div>
                <div size="2" class="inputbox col-md-2">
                    <div class="form-group">
                        {!! Form::label('','Dt. Embarque') !!}
                        {!! Form::text('data_embarque', null, ['autocomplete' =>'off', 'class' => 'form-control componenteData' , "id" => "input_data_embarque"]) !!}
                    </div>
                </div>
                <div size="2" class="inputbox col-md-2">
                    <div class="form-group">
                        {!! Form::label('','Dt. Retorno') !!}
                        {!! Form::text('data_retorno', null, ['autocomplete' =>'off', 'class' => 'form-control componenteData' , "id" => "input_data_retorno"]) !!}
                    </div>
                </div>
                <div class="row">
                    <div class="GridPassageiros_grid">
                        <div class="divdefault">
                            <div class="grid">
                                <div class="col-md-11" style="margin-bottom: 10px;">
                                    <div size="4" class="inputbox col-md-4">
                                        {{--<div class="form-group">
                                            {!! Form::label('','Passageiros') !!}
                                            {!! Form::select('grid[VendasGridPassageiros][passageiros][]', $passageiro_nome, null, ['class' => 'form-control  select_relationship ', 'data-sub' => '', 'data-live-search' => 'true',  'data-none-selected-text' => true, 'controller' => 'passageiro' , "id" => "input_passageiros", "style" => "text-transform: uppercase;", "oninput" => "this.value = this.value.toUpperCase();"]) !!}
                                        </div>--}}
                                        <div class="form-group">
                                            {!! Form::label('','Passageiros') !!}
                                            <div class='input-group input-add-edit'>
                                                {!! Form::select('grid[VendasGridPassageiros][passageiros][]', $passageiro_nome, null, ['no-trigger-change'=>'','class' => 'form-control  select_relationship input_passageiros', 'data-sub' => '', 'data-live-search' => 'true',  'data-none-selected-text' => true, 'controller' => 'passageiro' , "style" => "text-transform: uppercase;", "oninput" => "this.value = this.value.toUpperCase();"]) !!}
                                                <div class='input-group-btn'>
                                                    <button type="button" class="btn btn-xs btn-info" modal-create modal-grid modal-input-refresh=".input_passageiros" modal-modulo="passageiro" modal-url="{{URL('passageiro/create/modal')}}"><i class='glyphicon glyphicon-plus-sign'></i></button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-1">
                                    <i class="glyphicon glyphicon-trash grid_remove" data="GridPassageiros_grid" style="margin-top: 30px;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-1" style="margin: 40px 0 10px 0; float: right;">
                        <div class="form-group">
                            <i class="glyphicon glyphicon-plus multiple_add" data="GridPassageiros_grid"></i>
                        </div>
                    </div>
                </div>
                <div size="2" class="inputbox col-md-2">
                    <div class="form-group">
                        {!! Form::label('','Tarifa') !!}
                        <div class='input-group'>
                            <div class='input-group-addon'>
                                <i class='glyphicon glyphicon-usd'></i>
                            </div>
                            {!! Form::text('valor_tarifa', null, ['class' => 'form-control money' , "id" => "input_valor_tarifa"]) !!}
                        </div>
                    </div>
                </div>
                <div size="2" class="inputbox col-md-2">
                    <div class="form-group">
                        {!! Form::label('','Tx. Embarque') !!}
                        <div class='input-group'>
                            <div class='input-group-addon'>
                                <i class='glyphicon glyphicon-usd'></i>
                            </div>
                            {!! Form::text('tx_embarque', null, ['class' => 'form-control money' , "id" => "input_tx_embarque"]) !!}
                        </div>
                    </div>
                </div>
                <div size="2" class="inputbox col-md-2">
                    <div class="form-group">
                        {!! Form::label('','Outras Taxas') !!}
                        <div class='input-group'>
                            <div class='input-group-addon'>
                                <i class='glyphicon glyphicon-usd'></i>
                            </div>
                            {!! Form::text('outras_taxas', null, ['class' => 'form-control money' , "id" => "input_outras_taxas"]) !!}
                        </div>
                    </div>
                </div>
                <div size="2" class="inputbox col-md-2">
                    <div class="form-group">
                        {!! Form::label('','Desconto') !!}
                        <div class='input-group'>
                            <div class='input-group-addon'>
                                <i class='glyphicon glyphicon-usd'></i>
                            </div>
                            {!! Form::text('desconto', null, ['class' => 'form-control money' , "id" => "input_desconto"]) !!}
                        </div>
                    </div>
                </div>
                <div size="2" class="inputbox col-md-2">
                    <div class="form-group">
                        {!! Form::label('','Comissão (DU)') !!}
                        <div class='input-group'>
                            <div class='input-group-addon'>
                                <i class='glyphicon glyphicon-usd'></i>
                            </div>
                            {!! Form::text('comissao', null, ['class' => 'form-control money' , "id" => "input_comissao"]) !!}
                        </div>
                    </div>
                </div>
                <div size="2" class="inputbox col-md-2">
                    <div class="form-group">
                        {!! Form::label('','Incentivo') !!}
                        <div class='input-group'>
                            <div class='input-group-addon'>
                                <i class='glyphicon glyphicon-usd'></i>
                            </div>
                            {!! Form::text('incentivo', null, ['class' => 'form-control money' , "id" => "input_incentivo"]) !!}
                        </div>
                    </div>
                </div>
                <div size="2" class="inputbox col-md-2">
                    <div class="form-group">
                        {!! Form::label('','Valor Total') !!}
                        <div class='input-group'>
                            <div class='input-group-addon'>
                                <i class='glyphicon glyphicon-usd'></i>
                            </div>
                            {!! Form::text('valor_total', '0', ['class' => 'form-control' , "id" => "input_valor_total",'disabled' => 'disabled',]) !!}
                        </div>
                    </div>
                </div>
                <div size="12" class="inputbox col-md-12">
                    <div class="form-group">
                        {!! Form::label('','OBSERVACÕES DA VENDA:') !!}
                        {!! Form::text('observacoes_da_venda_', null, ['class' => 'form-control' , "id" => "input_observacoes_da_venda_"]) !!}
                    </div>
                </div>
                <div size="12" class="inputbox col-md-12">
                    <h2 class="page-header" style="font-size:20px;">
                        <i class="glyphicon glyphicon-th-large"></i> FORMAS DE PAGAMENTO:
                    </h2>
                </div>
                <div class="row">
                    <div size="2" class="inputbox col-md-2">
                        <div class="form-group bg-warning">
                            {!! Form::label('','+ Saldo a Pagar',['class'=>'text-warning']) !!}
                            <div class='input-group'>
                                <div class='input-group-addon'><i class='glyphicon glyphicon-usd'></i></div>
                                {!! Form::text('saldo_a_pagar', null, ['class' => 'form-control' , "id" => "input_saldo_a_pagar",'readonly' => 'readonly',]) !!}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="GridPagamentos_grid">
                        <div class="divdefault">
                            <div class="grid">
                                <div class="col-md-11" style="margin-bottom: 10px;">
                                    <div size="4" class="inputbox col-md-4">
                                        {{--<div class="form-group">
                                            {!! Form::label('','Forma de Pagamento') !!}
                                            {!! Form::select('grid[VendasGridPagamentos][forma_de_pagamento][]', $formas_de_pagamentos_forma_de_pa, null, ['class' => 'form-control  select_relationship ', 'data-sub' => '', 'data-live-search' => 'true',  'data-none-selected-text' => true, 'controller' => 'formas_de_pagamentos' , "id" => "input_forma_de_pagamento"]) !!}
                                        </div>--}}
                                        <div class="form-group">
                                            {!! Form::label('','Forma de Pagamento') !!}
                                            <div class='input-group input-add-edit'>
                                                {!! Form::select('grid[VendasGridPagamentos][forma_de_pagamento][]', $formas_de_pagamentos_forma_de_pa, null, ['no-trigger-change'=>'','class' => 'form-control  select_relationship input_forma_de_pagamento', 'data-sub' => '', 'data-live-search' => 'true',  'data-none-selected-text' => true, 'controller' => 'formas_de_pagamentos']) !!}
                                                <div class='input-group-btn'>
                                                    <button type="button" class="btn btn-xs btn-info" modal-create modal-grid modal-input-refresh=".input_forma_de_pagamento" modal-modulo="formas_de_pagamentos" modal-url="{{URL('formas_de_pagamentos/create/modal')}}"><i class='glyphicon glyphicon-plus-sign'></i></button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div size="2" class="inputbox col-md-2">
                                        <div class="form-group">
                                            {!! Form::label('','Valor Pago') !!}
                                            {!! Form::text('grid[VendasGridPagamentos][valor_pago][]', null, ['class' => 'form-control money input_valor_pago' , "id" => "input_valor_pago"]) !!}
                                        </div>
                                    </div>

                                    <div size="2" class="inputbox col-md-2">
                                        <div class="form-group">
                                            {!! Form::label('','Dt. de Pagamento') !!}
                                            {!! Form::text('grid[VendasGridPagamentos][data_pagamento][]', null, ['autocomplete' =>'off', 'class' => 'form-control componenteData' , "id" => "input_data_pagamento", "placeholder"=>"__/__/____"]) !!}
                                        </div>
                                    </div>

                                </div>
                                <div class="col-md-1">
                                    <i class="glyphicon glyphicon-trash grid_remove" data="GridPagamentos_grid" style="margin-top: 30px;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-1" style="margin: 40px 0 10px 0; float: right;">
                        <div class="form-group">
                            <i class="glyphicon glyphicon-plus multiple_add" data="GridPagamentos_grid"></i>
                        </div>
                    </div>
                </div>
                {{--<div size="12" class="inputbox col-md-12">
                    <h2 class="page-header" style="font-size:20px;">
                        <i class="glyphicon glyphicon-th-large"></i> PGTO AO FORNECEDOR
                    </h2>
                </div>
                <div size="2" class="inputbox col-md-2">
                    <div class="form-group">
                        {!! Form::label('','Dt. Pgto:') !!}
                        {!! Form::text('dt_pgto_', null, ['autocomplete' =>'off', 'class' => 'form-control componenteData' , "id" => "input_dt_pgto_"]) !!}
                    </div>
                </div>
                <div size="2" class="inputbox col-md-2">
                    <div class="form-group">
                        {!! Form::label('','Nr. Documento:') !!}
                        {!! Form::text('nr_documento_', null, ['class' => 'form-control' , "id" => "input_nr_documento_"]) !!}
                    </div>
                </div>
                <div size="2" class="inputbox col-md-2">
                    <div class="form-group">
                        {!! Form::label('','Valor:') !!}
                        {!! Form::text('valor_', null, ['class' => 'form-control money' , "id" => "input_valor_"]) !!}
                    </div>
                </div>
                <div size="2" class="inputbox col-md-2">
                    <div class="form-group">
                        {!! Form::label('','Acréscimo:') !!}
                        {!! Form::text('acrescimo_', null, ['class' => 'form-control money' , "id" => "input_acrescimo_"]) !!}
                    </div>
                </div>
                <div size="2" class="inputbox col-md-2">
                    <div class="form-group">
                        {!! Form::label('','Desconto:') !!}
                        {!! Form::text('desconto_', null, ['class' => 'form-control money' , "id" => "input_desconto_"]) !!}
                    </div>
                </div>
                <div size="2" class="inputbox col-md-2">
                    <div class="form-group">
                        {!! Form::label('','Valor Pago:') !!}
                        {!! Form::text('vlr_pago_', null, ['class' => 'form-control' , "id" => "input_vlr_pago_"]) !!}
                    </div>
                </div>
                <div size="12" class="inputbox col-md-12">
                    <div class="form-group">
                        {!! Form::label('','OBSERVAÇÕES:') !!}
                        {!! Form::text('observacoes_', null, ['class' => 'form-control' , "id" => "input_observacoes_"]) !!}
                    </div>
                </div>--}}
                {{--<div size="12" class="inputbox col-md-12">
                    <div class="form-group">
                        {!! Form::label('','Template') !!}
                        {!! Form::select('template', $templates_nome_do_template, null, ['class' => 'form-control  select_relationship ', 'data-sub' => '', 'data-live-search' => 'true',  'data-none-selected-text' => true, 'controller' => 'templates' , "id" => "input_template"]) !!}
                    </div>
                </div>--}}
                {{-- 2 = Venda - Recibo --}}
                {!! Form::hidden('template', 2) !!}
                @if(0)
                    @if(App\Models\Permissions::permissaoModerador(\Auth::user()))
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="">Para quem essa informação ficará disponível? Selecione um usuário. </label>
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
                @endif
                <div class="col-md-12" style="margin-top: 20px;">
                    <div class="form-group form-group-btn-add">
                        @if(!$isPublic)
                            <a href="{{ URL::previous() }}" class="btn btn-default form-group-btn-add-voltar"><i class="glyphicon glyphicon-backward"></i> Voltar</a>
                        @endif
                        @if(App\Models\Permissions::permissaoUsuario(\Auth::user(), "$controller@store") OR $isPublic)

                            <button type="submit" class="btn btn-default right form-group-btn-add-cadastrar">
                                <span class="glyphicon glyphicon-plus"></span> Cadastrar
                            </button>
                        @endif
                    </div>
                </div>
            </div>
            {!! Form::close() !!}
        </div>
    </section>
    @section('script')
        <script type="text/javascript">
            $("select#input_tipo_de_venda").closest(".form-group").addClass('inpsel-destaque-st1');
            $(
                `#input_valor_tarifa,
                #input_tx_embarque,
                #input_outras_taxas,
                #input_desconto,
                #input_comissao,
                .input_valor_pago`
            )
                .on('change keyup',function(){
                    venda_calculo_total();
                });
            function venda_calculo_total(){
                console.log('foi');
                let valor_tarifa  = parseFloat(($("#input_valor_tarifa").val()!="")?$("#input_valor_tarifa").val():0);
                let tx_embarque   = parseFloat(($("#input_tx_embarque").val()!="")?$("#input_tx_embarque").val():0);
                let outras_taxas  = parseFloat(($("#input_outras_taxas").val()!="")?$("#input_outras_taxas").val():0);
                let desconto  	  = parseFloat(($("#input_desconto").val()!="")?$("#input_desconto").val():0);
                let comissao  	  = parseFloat(($("#input_comissao").val()!="")?$("#input_comissao").val():0);
                let valor_total   = ((valor_tarifa + tx_embarque + outras_taxas + comissao) - desconto);
                // :: Forma de Pagamento - Itens
                let valor_pago      = 0;
                $(".input_valor_pago").each(function(i,e){
                    if($(e).val()!=''){
                        valor_pago  += parseFloat(($(e).val()!="")?$(e).val():0);
                    }
                });
                let saldo_a_pagar    = (valor_total - valor_pago);
                $("#input_saldo_a_pagar").val(RA.format.Decimal_DB_ptBR(saldo_a_pagar.toFixed(2)));
                // ::
                $("#input_valor_total").val(valor_total.toFixed(2));
            }
            $(".multiple_add").on('click',function(){
                setTimeout(function(){
                    $(".GridPagamentos_grid .grid").last().find('.input_valor_pago').on('change keyup',function(){  venda_calculo_total(); });
                    $(".GridPagamentos_grid .grid").last().find('.grid_remove').on('click', function(){
                        setTimeout(function(){ venda_calculo_total(); },1000);
                    });
                },1000);
            });
            $(".grid_remove").on('click', function(){
                setTimeout(function(){ venda_calculo_total(); },1000);
            });
        </script>
    @endsection
@endsection
