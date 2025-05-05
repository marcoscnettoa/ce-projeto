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

<section class="content-header Fluxo de caixa_edit">
    <h1>Fluxo de caixa </h1>
    <!--@if(!$isPublic)
    <ol class="breadcrumb">
        <li><a href="{{ URL('/') }}">Home</a></li>
        <li><a href="{{ URL('/') }}/fluxo_de_caixa">Fluxo de caixa</a></li>
        <li class="active">#{{$fluxo_de_caixa->id}}</li>
    </ol>
    @endif-->
</section>

<section class="content Fluxo de caixa_edit">

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
                <form id="form-destroy" method="POST" action="{{ route('fluxo_de_caixa.destroy', $fluxo_de_caixa->id) }}" accept-charset="UTF-8">
                    {!! csrf_field() !!}
                    {!! method_field('DELETE') !!}
                    <button type="submit" style="margin-left: 10px; margin-top: -1px;" onclick="return confirm('Tem certeza que quer deletar?')" class="btn btn-danger glyphicon glyphicon-trash"></button>
                </form>
            </div>
        </div>
    @endif

    {!! Form::open(['url' => "fluxo_de_caixa/$fluxo_de_caixa->id", 'method' => 'put', 'novalidate'=> true, 'enctype' => 'multipart/form-data', 'accept-charset' => 'utf-8', 'id' => 'form_edit_fluxo_de_caixa']) !!}

        @if(\Request::get('modal'))
            {!! Form::hidden('modal-close', 1) !!}
        @endif
        {!! Form::hidden('id', $fluxo_de_caixa->id) !!}

        <div class="box-body" id="div_fluxo_de_caixa">

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                            <div class='input-group'>
                        <div class='input-group-addon'>
                            <i class='glyphicon glyphicon-usd'></i>
                        </div>
                    {!! Form::hidden('saldo_inicial', $fluxo_de_caixa->saldo_inicial) !!}
                    </div>
                </div>
            </div>

            <div size="10" class="inputbox col-md-10">
                <div class="form-group">
                    {!! Form::label('','Movimentação') !!}
                    {!! Form::text('movimentacao', $fluxo_de_caixa->movimentacao, ['class' => 'form-control' , "id" => "input_movimentacao"]) !!}
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
                    {!! Form::hidden('data_do_recebimento', $fluxo_de_caixa->data_do_recebimento) !!}
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
                    {!! Form::text('valor_recebido', $fluxo_de_caixa->valor_recebido, ['class' => 'form-control money' , "id" => "input_valor_recebido"]) !!}
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
                    {!! Form::hidden('data_do_pagamento', $fluxo_de_caixa->data_do_pagamento) !!}
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
                    {!! Form::text('total_a_pagar', $fluxo_de_caixa->total_a_pagar, ['class' => 'form-control money' , "id" => "input_total_a_pagar"]) !!}
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
                    {!! Form::hidden('data_atual', $fluxo_de_caixa->data_atual) !!}
                    </div>
                </div>
            </div>

            <div size="4" class="inputbox col-md-4">
                <div class="form-group">
                            {!! Form::hidden('ghost_camp', $fluxo_de_caixa->ghost_camp) !!}
                </div>
            </div>

            <div size="4" class="inputbox col-md-4">
                <div class="form-group">
                            {!! Form::hidden('saldo_da_transacao', $fluxo_de_caixa->saldo_da_transacao) !!}
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

                            {!! Form::select('r_auth', $parserList, $fluxo_de_caixa->r_auth, ['class' => 'form-control']) !!}
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
