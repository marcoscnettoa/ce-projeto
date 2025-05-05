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

<section class="content-header Contas a Pagar_edit">
    <h1>Contas a Pagar </h1>
    <!--@if(!$isPublic)
    <ol class="breadcrumb">
        <li><a href="{{ URL('/') }}">Home</a></li>
        <li><a href="{{ URL('/') }}/contas_a_pagar">Contas a Pagar</a></li>
        <li class="active">#{{$contas_a_pagar->id}}</li>
    </ol>
    @endif-->
</section>

<section class="content Contas a Pagar_edit">

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
                <form id="form-destroy" method="POST" action="{{ route('contas_a_pagar.destroy', $contas_a_pagar->id) }}" accept-charset="UTF-8">
                    {!! csrf_field() !!}
                    {!! method_field('DELETE') !!}
                    <button type="submit" style="margin-left: 10px; margin-top: -1px;" onclick="return confirm('Tem certeza que quer deletar?')" class="btn btn-danger glyphicon glyphicon-trash"></button>
                </form>
            </div>
        </div>
    @endif

    {!! Form::open(['url' => "contas_a_pagar/$contas_a_pagar->id", 'method' => 'put', 'novalidate'=> true, 'enctype' => 'multipart/form-data', 'accept-charset' => 'utf-8', 'id' => 'form_edit_contas_a_pagar']) !!}

        @if(\Request::get('modal'))
            {!! Form::hidden('modal-close', 1) !!}
        @endif
        {!! Form::hidden('id', $contas_a_pagar->id) !!}

        <div class="box-body" id="div_contas_a_pagar">

            <div size="4" class="inputbox col-md-4">
                {{--<div class="form-group">
                    {!! Form::label('','Empresa') !!}
                    {!! Form::select('referente_a', $cadastro_de_empresas_nome_fantas, $contas_a_pagar->referente_a, ['class' => 'form-control  select_relationship ', 'data-sub' => '', 'data-live-search' => 'true',  'data-none-selected-text' => true, 'controller' => 'cadastro_de_empresas' , "id" => "input_referente_a","placeholder" => html_entity_decode("Nome da Empresa"),]) !!}
                </div>--}}
                <div class="form-group">
                    {!! Form::label('','Empresa') !!}
                    <div class='input-group input-add-edit'>
                        {!! Form::select('referente_a', $cadastro_de_empresas_nome_fantas, $contas_a_pagar->referente_a, ['no-trigger-change'=>'','class' => 'form-control  select_relationship ', 'data-sub' => '', 'data-live-search' => 'true',  'data-none-selected-text' => true, 'controller' => 'cadastro_de_empresas' , "id" => "input_referente_a"]) !!}
                        <div class='input-group-btn'>
                            <button type="button" class="btn btn-xs btn-info" modal-create modal-input-refresh="#input_referente_a" modal-modulo="cadastro_de_empresas" modal-url="{{URL('cadastro_de_empresas/create/modal')}}"><i class='glyphicon glyphicon-plus-sign'></i></button>
                        </div>
                    </div>
                </div>
            </div>

            <div size="4" class="inputbox col-md-4">
                {{--<div class="form-group">
                    {!! Form::label('','Fornecedor') !!}
                    {!! Form::select('fornecedor', $fornecedores_fornecedor, $contas_a_pagar->fornecedor, ['class' => 'form-control  select_relationship ', 'data-sub' => '', 'data-live-search' => 'true',  'data-none-selected-text' => true, 'controller' => 'fornecedores' , "id" => "input_fornecedor","placeholder" => html_entity_decode("Nome do Fornecedor"),]) !!}
                </div>--}}
                <div class="form-group">
                    {!! Form::label('','Fornecedor') !!}
                    <div class='input-group input-add-edit'>
                        {!! Form::select('fornecedor', $fornecedores_fornecedor, $contas_a_pagar->fornecedor, ['no-trigger-change'=>'','class' => 'form-control  select_relationship ', 'data-sub' => '', 'data-live-search' => 'true',  'data-none-selected-text' => true, 'controller' => 'fornecedores' , "id" => "input_fornecedor"]) !!}
                        <div class='input-group-btn'>
                            <button type="button" class="btn btn-xs btn-info" modal-create modal-input-refresh="#input_fornecedor" modal-modulo="fornecedores" modal-url="{{URL('fornecedores/create/modal')}}"><i class='glyphicon glyphicon-plus-sign'></i></button>
                        </div>
                    </div>
                </div>
            </div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','Tipo de documento') !!}
                    {!! Form::text('tipo_de_documento', $contas_a_pagar->tipo_de_documento, ['class' => 'form-control' , "id" => "input_tipo_de_documento"]) !!}
                </div>
            </div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','N° do documento') !!}
                    {!! Form::text('n_do_documento', $contas_a_pagar->n_do_documento, ['class' => 'form-control' , "id" => "input_n_do_documento"]) !!}
                </div>
            </div>

            <div size="4" class="inputbox col-md-4">
                <div class="form-group">
                    {!! Form::label('','Portador') !!}
                    {!! Form::text('portador', $contas_a_pagar->portador, ['class' => 'form-control' , "id" => "input_portador"]) !!}
                </div>
            </div>

            <div size="4" class="inputbox col-md-4">
                <div class="form-group">
                    {!! Form::label('','Descrição do Pagamento') !!}
                    {!! Form::text('descricao_do_pagamento', $contas_a_pagar->descricao_do_pagamento, ['class' => 'form-control' , "id" => "input_descricao_do_pagamento","placeholder" => html_entity_decode("Motivo do Pagamento"),]) !!}
                </div>
            </div>

            <div size="4" class="inputbox col-md-4">
                <div class="form-group">
                    {!! Form::label('','N° da ordem de compra') !!}
                    {!! Form::text('n_da_ordem_de_compra', $contas_a_pagar->n_da_ordem_de_compra, ['class' => 'form-control' , "id" => "input_n_da_ordem_de_compra"]) !!}
                </div>
            </div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','Data do Vencimento') !!}
                    <div class='input-group'>
                        <div class='input-group-addon'>
                            <i class='glyphicon glyphicon-calendar'></i>
                        </div>
                    {!! Form::text('data_do_vencimento', $contas_a_pagar->data_do_vencimento, ['class' => 'form-control data' , "id" => "input_data_do_vencimento","placeholder" => html_entity_decode("Vencimento"),]) !!}
                    </div>
                </div>
            </div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','Valor à Pagar') !!}
                    <div class='input-group'>
                        <div class='input-group-addon'>
                            <i class='glyphicon glyphicon-usd'></i>
                        </div>
                    {!! Form::text('valor_a_pagar', $contas_a_pagar->valor_a_pagar, ['class' => 'form-control money' , "id" => "input_valor_a_pagar"]) !!}
                    </div>
                </div>
            </div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','Tipo do valor') !!}
                    {!! Form::select('tipo_do_valor', \App\Models\ContasAPagar::Get_options_tipo_do_valor(), $contas_a_pagar->tipo_do_valor, ['class' => 'form-control select_single' , "id" => "input_tipo_do_valor"]) !!}
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
                    <div class='input-group'>
                        <div class='input-group-addon'>
                            <i class='glyphicon glyphicon-sort-by-order'></i>
                        </div>
                    {!! Form::number('n_de_parcelas', $contas_a_pagar->n_de_parcelas, ['class' => 'form-control' , "id" => "input_n_de_parcelas",'conditional' => true, 'conditional_field' => 'tipo_do_valor', 'conditional_field_value' => 'Parcelas',]) !!}
                    </div>
                </div>
            </div>

            <div size="2" class="inputbox col-md-2" style="display: none;" hide-by="input_tipo_do_valor">
                <div class="form-group">
                    {!! Form::label('','Primeira Data') !!}
                    <div class='input-group'>
                        <div class='input-group-addon'>
                            <i class='glyphicon glyphicon-calendar'></i>
                        </div>
                    {!! Form::text('primeira_data', $contas_a_pagar->primeira_data, ['class' => 'form-control data' , "id" => "input_primeira_data",'conditional' => true, 'conditional_field' => 'tipo_do_valor', 'conditional_field_value' => 'Parcelas',]) !!}
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
                    {!! Form::label('','Data do Pagamento') !!}
                    <div class='input-group'>
                        <div class='input-group-addon'>
                            <i class='fa fa-calendar'></i>
                        </div>
                    {!! Form::text('data_do_pagamento', $contas_a_pagar->data_do_pagamento, ['class' => 'form-control data' , "id" => "input_data_do_pagamento","placeholder" => html_entity_decode("Envio da Cobrança"),]) !!}
                    </div>
                </div>
            </div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','Valor Pago') !!}
                    <div class='input-group'>
                        <div class='input-group-addon'>
                            <i class='glyphicon glyphicon-usd'></i>
                        </div>
                    {!! Form::text('valor_pago', $contas_a_pagar->valor_pago, ['class' => 'form-control money' , "id" => "input_valor_pago"]) !!}
                    </div>
                </div>
            </div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','Forma de pagamento') !!}
                    <div class='input-group'>
                        <div class='input-group-addon'>
                            <i class='glyphicon glyphicon-briefcase'></i>
                        </div>
                    {!! Form::select('forma_de_pagamento', $formas_de_pagamentos_forma_de_pa, $contas_a_pagar->forma_de_pagamento, ['class' => 'form-control  select_relationship ', 'data-sub' => '', 'data-live-search' => 'true',  'data-none-selected-text' => true, 'controller' => 'formas_de_pagamentos' , "id" => "input_forma_de_pagamento"]) !!}
                    </div>
                </div>
            </div>

            <div size="3" class="inputbox col-md-3">
                <div class="form-group">
                    {!! Form::label('','Status') !!}
                    <div class='input-group'>
                        <div class='input-group-addon'>
                            <i class='glyphicon glyphicon-hourglass'></i>
                        </div>
                    {!! Form::select('status', \App\Models\ContasAPagar::Get_options_status(), $contas_a_pagar->status, ['class' => 'form-control select_single' , "id" => "input_status"]) !!}
                    </div>
                </div>
            </div>

            <div size="3" class="inputbox col-md-3">
                <div class="form-group">
                    {!! Form::label('','Comprovante de Pagamento') !!}
                    <div class='input-group'>
                        <div class='input-group-addon'>
                            <i class='glyphicon glyphicon-hdd'></i>
                        </div>
@if($contas_a_pagar->comprovante_de_pagamento && pathinfo($contas_a_pagar->comprovante_de_pagamento, PATHINFO_EXTENSION))
        <ol style="margin:0px;padding:0px;">
            <a class="fancybox" rel="gallery1" target="_blank" href="{{$fileurlbase . "images"}}/{{$contas_a_pagar->comprovante_de_pagamento}}">
                <img src="{{in_array(explode(".", $contas_a_pagar->comprovante_de_pagamento)[1], array("mp4", "pdf", "doc", "docx", "rar", "zip", "txt", "7zip", "csv", "xls", "xlsx")) ? URL("/") . "/" . explode(".", $contas_a_pagar->comprovante_de_pagamento)[1] . "-icon.png" : $fileurlbase . "images/" . $contas_a_pagar->comprovante_de_pagamento}}" height="100">
            </a>
        </ol>
{!! Form::hidden("comprovante_de_pagamento", $contas_a_pagar->comprovante_de_pagamento) !!}
@endif
                    {!! Form::file('comprovante_de_pagamento', ['class' => 'form-control isFile' , "id" => "input_comprovante_de_pagamento"]) !!}
        <div class='row'>
            <div class='col-md-12'>
                <img src='' id='file_input_comprovante_de_pagamento' style='height: 100px; display: none;'>
            </div>
        </div>
        <i class='glyphicon glyphicon-trash input_remove' style='margin-top: 5px;'></i>
                    </div>
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

                            {!! Form::select('r_auth', $parserList, $contas_a_pagar->r_auth, ['class' => 'form-control']) !!}
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
