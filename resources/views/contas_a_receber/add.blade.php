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

<section class="content-header Contas a Receber_add">
    <h1>Contas a Receber</h1>
    <!--@if(!$isPublic)
    <ol class="breadcrumb">
        <li><a href="{{ URL('/') }}">Home</a></li>
        <li><a href="{{ URL('/') }}/contas_a_receber">Contas a Receber</a></li>
        <li class="active">Contas a Receber</li>
    </ol>
    @endif-->
</section>

<section class="content Contas a Receber_add">

<div class="box">

    {!! Form::open(['url' => "contas_a_receber", 'method' => 'post', 'novalidate'=> true, 'enctype' => 'multipart/form-data', 'accept-charset' => 'utf-8', 'id' => 'form_add_contas_a_receber']) !!}

        <div class="box-body" id="div_contas_a_receber">

            <div size="4" class="inputbox col-md-4">
                {{--<div class="form-group">
                    {!! Form::label('','Empresa') !!}
                    {!! Form::select('empresa', $cadastro_de_empresas_nome_fantas, null, ['class' => 'form-control  select_relationship ', 'data-sub' => '', 'data-live-search' => 'true',  'data-none-selected-text' => true, 'controller' => 'cadastro_de_empresas' , "id" => "input_empresa","placeholder" => html_entity_decode("Nome da empresa"),]) !!}
                </div>--}}
                <div class="form-group">
                    {!! Form::label('','Empresa') !!}
                    <div class='input-group input-add-edit'>
                        {!! Form::select('empresa', $cadastro_de_empresas_nome_fantas, null, ['no-trigger-change'=>'','class' => 'form-control  select_relationship ', 'data-sub' => '', 'data-live-search' => 'true',  'data-none-selected-text' => true, 'controller' => 'cadastro_de_empresas' , "id" => "input_empresa"]) !!}
                        <div class='input-group-btn'>
                            <button type="button" class="btn btn-xs btn-info" modal-create modal-input-refresh="#input_empresa" modal-modulo="cadastro_de_empresas" modal-url="{{URL('cadastro_de_empresas/create/modal')}}"><i class='glyphicon glyphicon-plus-sign'></i></button>
                        </div>
                    </div>
                </div>
            </div>

            <div size="4" class="inputbox col-md-4">
                {{--<div class="form-group">
                    {!! Form::label('','Cliente') !!}
                    {!! Form::select('cliente', $clientes_nome_do_cliente, null, ['class' => 'form-control  select_relationship ', 'data-sub' => '', 'data-live-search' => 'true',  'data-none-selected-text' => true, 'controller' => 'clientes' , "id" => "input_cliente","placeholder" => html_entity_decode("Nome do Cliente"),]) !!}
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

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','Tipo de documento') !!}
                    {!! Form::text('tipo_de_documento', null, ['class' => 'form-control' , "id" => "input_tipo_de_documento"]) !!}
                </div>
            </div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','N° do documento') !!}
                    {!! Form::text('n_do_documento', null, ['class' => 'form-control' , "id" => "input_n_do_documento"]) !!}
                </div>
            </div>

            <div size="4" class="inputbox col-md-4">
                {{--<div class="form-group">
                    {!! Form::label('','Vendedor') !!}
                    {!! Form::select('vendedor', $vendedores_nome_do_vendedor, null, ['class' => 'form-control  select_relationship ', 'data-sub' => '', 'data-live-search' => 'true',  'data-none-selected-text' => true, 'controller' => 'vendedores' , "id" => "input_vendedor","placeholder" => html_entity_decode("Nome do Vendedor"),]) !!}
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

            <div size="8" class="inputbox col-md-8">
                <div class="form-group">
                    {!! Form::label('','Descrição do Recebimento') !!}
                    {!! Form::text('descricao_do_recebimento', null, ['class' => 'form-control' , "id" => "input_descricao_do_recebimento","placeholder" => html_entity_decode("Motivo do Recebimento"),]) !!}
                </div>
            </div>

            <div size="3" class="inputbox col-md-3">
                <div class="form-group">
                    {!! Form::label('','Data do Vencimento') !!}
                    <div class='input-group'>
                        <div class='input-group-addon'>
                            <i class='fa fa-calendar'></i>
                        </div>
                    {!! Form::text('data_do_vencimento', null, ['class' => 'form-control data' , "id" => "input_data_do_vencimento","placeholder" => html_entity_decode("Vencimento"),]) !!}
                    </div>
                </div>
            </div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','Valor à Receber') !!}
                    {!! Form::text('valor_a_receber', null, ['class' => 'form-control money' , "id" => "input_valor_a_receber"]) !!}
                </div>
            </div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','Tipo do valor') !!}
                    {!! Form::select('tipo_do_valor', \App\Models\ContasAReceber::Get_options_tipo_do_valor(), null, ['class' => 'form-control select_single' , "id" => "input_tipo_do_valor"]) !!}
                </div>
            </div>

<div size="12" class="inputbox col-md-12">
    <h2 class="page-header" style="font-size:20px;">
        <i class="glyphicon glyphicon-th-large"></i> Parcelas
    </h2>
</div>

            <div size="2" class="inputbox col-md-2" style="display: none;" hide-by="input_tipo_do_valor">
                <div class="form-group">
                    {!! Form::label('','N° de Parcelas') !!}
                    {!! Form::number('n_de_parcelas', null, ['class' => 'form-control' , "id" => "input_n_de_parcelas",'conditional' => true, 'conditional_field' => 'tipo_do_valor', 'conditional_field_value' => 'Parcelas',]) !!}
                </div>
            </div>

            <div size="2" class="inputbox col-md-2" style="display: none;" hide-by="input_tipo_do_valor">
                <div class="form-group">
                    {!! Form::label('','Primeira Data') !!}
                    <div class='input-group'>
                        <div class='input-group-addon'>
                            <i class='fa fa-calendar'></i>
                        </div>
                    {!! Form::text('primeira_data', null, ['class' => 'form-control data' , "id" => "input_primeira_data",'conditional' => true, 'conditional_field' => 'tipo_do_valor', 'conditional_field_value' => 'Parcelas',]) !!}
                    </div>
                </div>
            </div>

<div size="12" class="inputbox col-md-12">
    <h2 class="page-header" style="font-size:20px;">
        <i class="glyphicon glyphicon-th-large"></i> Recepção
    </h2>
</div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','Data do Recebimento') !!}
                    <div class='input-group'>
                        <div class='input-group-addon'>
                            <i class='fa fa-calendar'></i>
                        </div>
                    {!! Form::text('data_do_recebimento', null, ['class' => 'form-control data' , "id" => "input_data_do_recebimento","placeholder" => html_entity_decode("Envio da Cobrança"),]) !!}
                    </div>
                </div>
            </div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','Valor Recebido') !!}
                    {!! Form::text('valor_recebido', null, ['class' => 'form-control money' , "id" => "input_valor_recebido"]) !!}
                </div>
            </div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','Forma de pagamento') !!}
                    {!! Form::select('forma_de_pagamento', $formas_de_pagamentos_forma_de_pa, null, ['class' => 'form-control  select_relationship ', 'data-sub' => '', 'data-live-search' => 'true',  'data-none-selected-text' => true, 'controller' => 'formas_de_pagamentos' , "id" => "input_forma_de_pagamento"]) !!}
                </div>
            </div>

            <div size="3" class="inputbox col-md-3">
                <div class="form-group">
                    {!! Form::label('','Status') !!}
                    {!! Form::select('status', \App\Models\ContasAReceber::Get_options_status(), null, ['class' => 'form-control select_single' , "id" => "input_status"]) !!}
                </div>
            </div>

            <div size="3" class="inputbox col-md-3">
                <div class="form-group">
                    {!! Form::label('','Comprovante') !!}
                    {!! Form::file('comprovante', ['class' => 'form-control isFile' , "id" => "input_comprovante"]) !!}
        <div class='row'>
            <div class='col-md-12'>
                <img src='' id='file_input_comprovante' style='height: 100px; display: none;'>
            </div>
        </div>
                </div>
            </div>

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

    </script>

@endsection

@endsection
