@php

    $isPublic = 0;

    $enable_kanban = 0;

    $controller = get_class(\Request::route()->getController());

@endphp

@extends($isPublic ? 'layouts.app-public' : 'layouts.app')

@section('content')

@section('style')

    <style type="text/css">

    </style>

@endsection

<section class="content-header Contas a Receber_edit">
    <h1>Contas a Receber </h1>
    <!--@if(!$isPublic)
    <ol class="breadcrumb">
        <li><a href="{{ URL('/') }}">Home</a></li>
        <li><a href="{{ URL('/') }}/contas_a_receber">Contas a Receber</a></li>
        <li class="active">#{{$contas_a_receber->id}}</li>
    </ol>
    @endif-->
</section>

<section class="content Contas a Receber_edit">

<div class="box">

    @php

        if(env('FILESYSTEM_DRIVER') == 's3')
        {
            $fileurlbase = env('URLS3') . '/' . env('FILEKEY') . '/';
        }
        else
        {
            $fileurlbase = env('APP_URL') . '/';
        }

    @endphp

    @if(\App\Models\Permissions::permissaoUsuario(\Auth::user(), "$controller@destroy"))
        <div class="row" style="position: absolute; right: 0; padding: 5px;">
            <div class="col-md-12">
                <form id="form-destroy" method="POST" action="{{ route('contas_a_receber.destroy', $contas_a_receber->id) }}" accept-charset="UTF-8">
                    {!! csrf_field() !!}
                    {!! method_field('DELETE') !!}
                    <button type="submit" style="margin-left: 10px; margin-top: -1px;" onclick="return confirm('Tem certeza que quer deletar?')" class="btn btn-danger glyphicon glyphicon-trash"></button>
                </form>
            </div>
        </div>
    @endif

    {!! Form::open(['url' => "contas_a_receber/$contas_a_receber->id", 'method' => 'put', 'novalidate'=> true, 'enctype' => 'multipart/form-data', 'accept-charset' => 'utf-8', 'id' => 'form_edit_contas_a_receber']) !!}

        @if(\Request::get('modal'))
            {!! Form::hidden('modal-close', 1) !!}
        @endif
        {!! Form::hidden('id', $contas_a_receber->id) !!}

        <div class="box-body" id="div_contas_a_receber">

            <div size="4" class="inputbox col-md-4">
                {{--<div class="form-group">
                    {!! Form::label('','Empresa') !!}
                    {!! Form::select('empresa', $cadastro_de_empresas_nome_fantas, $contas_a_receber->empresa, ['class' => 'form-control  select_relationship ', 'data-sub' => '', 'data-live-search' => 'true',  'data-none-selected-text' => true, 'controller' => 'cadastro_de_empresas' , "id" => "input_empresa","placeholder" => html_entity_decode("Nome da empresa"),]) !!}
                </div>--}}
                <div class="form-group">
                    {!! Form::label('','Empresa') !!}
                    <div class='input-group input-add-edit'>
                        {!! Form::select('empresa', $cadastro_de_empresas_nome_fantas, $contas_a_receber->empresa, ['no-trigger-change'=>'','class' => 'form-control  select_relationship ', 'data-sub' => '', 'data-live-search' => 'true',  'data-none-selected-text' => true, 'controller' => 'cadastro_de_empresas' , "id" => "input_empresa"]) !!}
                        <div class='input-group-btn'>
                            <button type="button" class="btn btn-xs btn-info" modal-create modal-input-refresh="#input_empresa" modal-modulo="cadastro_de_empresas" modal-url="{{URL('cadastro_de_empresas/create/modal')}}"><i class='glyphicon glyphicon-plus-sign'></i></button>
                        </div>
                    </div>
                </div>
            </div>

            <div size="4" class="inputbox col-md-4">
                {{--<div class="form-group">
                    {!! Form::label('','Cliente') !!}
                    {!! Form::select('cliente', $clientes_nome_do_cliente, $contas_a_receber->cliente, ['class' => 'form-control  select_relationship ', 'data-sub' => '', 'data-live-search' => 'true',  'data-none-selected-text' => true, 'controller' => 'clientes' , "id" => "input_cliente","placeholder" => html_entity_decode("Nome do Cliente"),]) !!}
                </div>--}}
                <div class="form-group">
                    {!! Form::label('','Cliente') !!}
                    <div class='input-group input-add-edit'>
                        {!! Form::select('cliente', $clientes_nome_do_cliente, $contas_a_receber->cliente, ['no-trigger-change'=>'','class' => 'form-control  select_relationship ', 'data-sub' => '', 'data-live-search' => 'true',  'data-none-selected-text' => true, 'controller' => 'clientes' , "id" => "input_cliente"]) !!}
                        <div class='input-group-btn'>
                            <button type="button" class="btn btn-xs btn-info" modal-create modal-input-refresh="#input_cliente" modal-modulo="clientes" modal-url="{{URL('clientes/create/modal')}}"><i class='glyphicon glyphicon-plus-sign'></i></button>
                        </div>
                    </div>
                </div>
            </div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','Tipo de documento') !!}
                    {!! Form::text('tipo_de_documento', $contas_a_receber->tipo_de_documento, ['class' => 'form-control' , "id" => "input_tipo_de_documento"]) !!}
                </div>
            </div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','N° do documento') !!}
                    {!! Form::text('n_do_documento', $contas_a_receber->n_do_documento, ['class' => 'form-control' , "id" => "input_n_do_documento"]) !!}
                </div>
            </div>

            <div size="4" class="inputbox col-md-4">
                {{--<div class="form-group">
                    {!! Form::label('','Vendedor') !!}
                    {!! Form::select('vendedor', $vendedores_nome_do_vendedor, $contas_a_receber->vendedor, ['class' => 'form-control  select_relationship ', 'data-sub' => '', 'data-live-search' => 'true',  'data-none-selected-text' => true, 'controller' => 'vendedores' , "id" => "input_vendedor","placeholder" => html_entity_decode("Nome do Vendedor"),]) !!}
                </div>--}}
                <div class="form-group">
                    {!! Form::label('','Vendedor') !!}
                    <div class='input-group input-add-edit'>
                        {!! Form::select('vendedor', $vendedores_nome_do_vendedor, $contas_a_receber->vendedor, ['no-trigger-change'=>'','class' => 'form-control  select_relationship ', 'data-sub' => '', 'data-live-search' => 'true',  'data-none-selected-text' => true, 'controller' => 'vendedores' , "id" => "input_vendedor"]) !!}
                        <div class='input-group-btn'>
                            <button type="button" class="btn btn-xs btn-info" modal-create modal-input-refresh="#input_vendedor" modal-modulo="vendedores" modal-url="{{URL('vendedores/create/modal')}}"><i class='glyphicon glyphicon-plus-sign'></i></button>
                        </div>
                    </div>
                </div>
            </div>

            <div size="8" class="inputbox col-md-8">
                <div class="form-group">
                    {!! Form::label('','Descrição do Recebimento') !!}
                    {!! Form::text('descricao_do_recebimento', $contas_a_receber->descricao_do_recebimento, ['class' => 'form-control' , "id" => "input_descricao_do_recebimento","placeholder" => html_entity_decode("Motivo do Recebimento"),]) !!}
                </div>
            </div>

            <div size="3" class="inputbox col-md-3">
                <div class="form-group">
                    {!! Form::label('','Data do Vencimento') !!}
                    <div class='input-group'>
                        <div class='input-group-addon'>
                            <i class='fa fa-calendar'></i>
                        </div>
                    {!! Form::text('data_do_vencimento', $contas_a_receber->data_do_vencimento, ['class' => 'form-control data' , "id" => "input_data_do_vencimento","placeholder" => html_entity_decode("Vencimento"),]) !!}
                    </div>
                </div>
            </div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','Valor à Receber') !!}
                    {!! Form::text('valor_a_receber', $contas_a_receber->valor_a_receber, ['class' => 'form-control money' , "id" => "input_valor_a_receber"]) !!}
                </div>
            </div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','Tipo do valor') !!}
                    {!! Form::select('tipo_do_valor', \App\Models\ContasAReceber::Get_options_tipo_do_valor(), $contas_a_receber->tipo_do_valor, ['class' => 'form-control select_single' , "id" => "input_tipo_do_valor"]) !!}
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
                    {!! Form::number('n_de_parcelas', $contas_a_receber->n_de_parcelas, ['class' => 'form-control' , "id" => "input_n_de_parcelas",'conditional' => true, 'conditional_field' => 'tipo_do_valor', 'conditional_field_value' => 'Parcelas',]) !!}
                </div>
            </div>

            <div size="2" class="inputbox col-md-2" style="display: none;" hide-by="input_tipo_do_valor">
                <div class="form-group">
                    {!! Form::label('','Primeira Data') !!}
                    <div class='input-group'>
                        <div class='input-group-addon'>
                            <i class='fa fa-calendar'></i>
                        </div>
                    {!! Form::text('primeira_data', $contas_a_receber->primeira_data, ['class' => 'form-control data' , "id" => "input_primeira_data",'conditional' => true, 'conditional_field' => 'tipo_do_valor', 'conditional_field_value' => 'Parcelas',]) !!}
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
                    {!! Form::text('data_do_recebimento', $contas_a_receber->data_do_recebimento, ['class' => 'form-control data' , "id" => "input_data_do_recebimento","placeholder" => html_entity_decode("Envio da Cobrança"),]) !!}
                    </div>
                </div>
            </div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','Valor Recebido') !!}
                    {!! Form::text('valor_recebido', $contas_a_receber->valor_recebido, ['class' => 'form-control money' , "id" => "input_valor_recebido"]) !!}
                </div>
            </div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','Forma de pagamento') !!}
                    {!! Form::select('forma_de_pagamento', $formas_de_pagamentos_forma_de_pa, $contas_a_receber->forma_de_pagamento, ['class' => 'form-control  select_relationship ', 'data-sub' => '', 'data-live-search' => 'true',  'data-none-selected-text' => true, 'controller' => 'formas_de_pagamentos' , "id" => "input_forma_de_pagamento"]) !!}
                </div>
            </div>

            <div size="3" class="inputbox col-md-3">
                <div class="form-group">
                    {!! Form::label('','Status') !!}
                    {!! Form::select('status', \App\Models\ContasAReceber::Get_options_status(), $contas_a_receber->status, ['class' => 'form-control select_single' , "id" => "input_status"]) !!}
                </div>
            </div>

            <div size="3" class="inputbox col-md-3">
                <div class="form-group">
                    {!! Form::label('','Comprovante') !!}
@if($contas_a_receber->comprovante && pathinfo($contas_a_receber->comprovante, PATHINFO_EXTENSION))
        <ol style="margin:0px;padding:0px;">
            <a class="fancybox" rel="gallery1" target="_blank" href="{{$fileurlbase . "images"}}/{{$contas_a_receber->comprovante}}">
                <img src="{{in_array(explode(".", $contas_a_receber->comprovante)[1], array("mp4", "pdf", "doc", "docx", "rar", "zip", "txt", "7zip", "csv", "xls", "xlsx")) ? URL("/") . "/" . explode(".", $contas_a_receber->comprovante)[1] . "-icon.png" : $fileurlbase . "images/" . $contas_a_receber->comprovante}}" height="100">
            </a>
        </ol>
{!! Form::hidden("comprovante", $contas_a_receber->comprovante) !!}
@endif
                    {!! Form::file('comprovante', ['class' => 'form-control isFile' , "id" => "input_comprovante"]) !!}
        <div class='row'>
            <div class='col-md-12'>
                <img src='' id='file_input_comprovante' style='height: 100px; display: none;'>
            </div>
        </div>
        <i class='glyphicon glyphicon-trash input_remove' style='margin-top: 5px;'></i>
                </div>
            </div>

            @if(0)

                @if(\App\Models\Permissions::permissaoModerador(\Auth::user()))
                    <div class="col-md-12">
                        <div class="form-group">

                            <label for="">Para quem essa informação ficará disponível? Selecione um usuário. </label>

                            @php

                                $parserList = array();

                                $userlist = \App\Models\User::get()->toArray();

                                array_unshift($userlist, array('id' => '',  'name' => ''));
                                array_unshift($userlist, array('id' => 0,  'name' => 'Disponível para todos'));

                                foreach($userlist as $u)
                                {
                                    $parserList[$u['id']] = $u['name'];
                                }

                            @endphp

                            {!! Form::select('r_auth', $parserList, $contas_a_receber->r_auth, ['class' => 'form-control']) !!}
                        </div>
                    </div>
                @endif

            @endif

            <div class="col-md-12" style="margin-top: 20px; margin-bottom: 20px;">

                <div class="form-group form-group-btn-edit">

                    @if(!$isPublic)
                        <a href="{{ URL::previous() }}" class="btn btn-default form-group-btn-add-voltar" style="float: left;"><i class="glyphicon glyphicon-backward"></i> Voltar</a>
                    @endif

                    @if(\App\Models\Permissions::permissaoUsuario(\Auth::user(), "$controller@update") OR $isPublic)

                        <button type="submit" class="btn btn-default right form-group-btn-edit-salvar">
                            <span class="glyphicon glyphicon-ok"></span> Salvar
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
