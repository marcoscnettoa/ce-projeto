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

<section class="content-header Fluxo de caixa_add">
    <h1>Fluxo de caixa</h1>
    <!--@if(!$isPublic)
    <ol class="breadcrumb">
        <li><a href="{{ URL('/') }}">Home</a></li>
        <li><a href="{{ URL('/') }}/fluxo_de_caixa">Fluxo de caixa</a></li>
        <li class="active">Fluxo de caixa</li>
    </ol>
    @endif-->
</section>

<section class="content Fluxo de caixa_add">

<div class="box">

    {!! Form::open(['url' => "fluxo_de_caixa", 'method' => 'post', 'novalidate'=> true, 'enctype' => 'multipart/form-data', 'accept-charset' => 'utf-8', 'id' => 'form_add_fluxo_de_caixa']) !!}

        <div class="box-body" id="div_fluxo_de_caixa">

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                            <div class='input-group'>
                        <div class='input-group-addon'>
                            <i class='glyphicon glyphicon-usd'></i>
                        </div>
                    {!! Form::hidden('saldo_inicial', null) !!}
                    </div>
                </div>
            </div>

            <div size="10" class="inputbox col-md-10">
                <div class="form-group">
                    {!! Form::label('','Movimentação') !!}
                    {!! Form::text('movimentacao', null, ['class' => 'form-control' , "id" => "input_movimentacao"]) !!}
                </div>
            </div>

<div size="12" class="inputbox col-md-12">
    <h2 class="page-header" style="font-size:20px;">
        <i class="glyphicon glyphicon-th-large"></i> Recebimentos
    </h2>
</div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                            <div class='input-group'>
                        <div class='input-group-addon'>
                            <i class='glyphicon glyphicon-calendar'></i>
                        </div>
                    {!! Form::hidden('data_do_recebimento', null) !!}
                    </div>
                </div>
            </div>

            <div size="3" class="inputbox col-md-3">
                <div class="form-group">
                    {!! Form::label('','Valor Recebido') !!}
                    <div class='input-group'>
                        <div class='input-group-addon'>
                            <i class='glyphicon glyphicon-usd'></i>
                        </div>
                    {!! Form::text('valor_recebido', null, ['class' => 'form-control money' , "id" => "input_valor_recebido"]) !!}
                    </div>
                </div>
            </div>

<div size="12" class="inputbox col-md-12">
    <h2 class="page-header" style="font-size:20px;">
        <i class="glyphicon glyphicon-th-large"></i> Pagamentos
    </h2>
</div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                            <div class='input-group'>
                        <div class='input-group-addon'>
                            <i class='glyphicon glyphicon-calendar'></i>
                        </div>
                    {!! Form::hidden('data_do_pagamento', null) !!}
                    </div>
                </div>
            </div>

            <div size="3" class="inputbox col-md-3">
                <div class="form-group">
                    {!! Form::label('','Total a Pagar') !!}
                    <div class='input-group'>
                        <div class='input-group-addon'>
                            <i class='glyphicon glyphicon-usd'></i>
                        </div>
                    {!! Form::text('total_a_pagar', null, ['class' => 'form-control money' , "id" => "input_total_a_pagar"]) !!}
                    </div>
                </div>
            </div>

<div size="12" class="inputbox col-md-12">
    <h2 class="page-header" style="font-size:20px;">
        <i class="glyphicon glyphicon-th-large"></i> Total
    </h2>
</div>

            <div size="4" class="inputbox col-md-4">
                <div class="form-group">
                            <div class='input-group'>
                        <div class='input-group-addon'>
                            <i class='glyphicon glyphicon-calendar'></i>
                        </div>
                    {!! Form::hidden('data_atual', null) !!}
                    </div>
                </div>
            </div>

            <div size="4" class="inputbox col-md-4">
                <div class="form-group">
                            {!! Form::hidden('ghost_camp', null) !!}
                </div>
            </div>

            <div size="4" class="inputbox col-md-4">
                <div class="form-group">
                            {!! Form::hidden('saldo_da_transacao', null) !!}
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
