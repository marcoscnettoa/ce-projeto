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

<section class="content-header Orçamentos_add">
    <h1>Orçamentos</h1>
    <!--@if(!$isPublic)
    <ol class="breadcrumb">
        <li><a href="{{ URL('/') }}">Home</a></li>
        <li><a href="{{ URL('/') }}/orcamentos">Orçamentos</a></li>
        <li class="active">Orçamentos</li>
    </ol>
    @endif-->
</section>

<section class="content Orçamentos_add">

<div class="box">

    {!! Form::open(['url' => "orcamentos", 'method' => 'post', 'novalidate'=> true, 'enctype' => 'multipart/form-data', 'accept-charset' => 'utf-8', 'id' => 'form_add_orcamentos']) !!}

        <div class="box-body" id="div_orcamentos">

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','Número') !!}
                    {!! Form::text('numero', null, ['class' => 'form-control' , "id" => "input_numero"]) !!}
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

            <div size="5" class="inputbox col-md-5">
                <div class="form-group">
                    {!! Form::label('','Clilente') !!}
                    {!! Form::text('clilente', null, ['class' => 'form-control' , "id" => "input_clilente"]) !!}
                </div>
            </div>

            <div size="3" class="inputbox col-md-3">
                <div class="form-group">
                    {!! Form::label('','Vendedor') !!}
                    {!! Form::text('vendedor', null, ['class' => 'form-control' , "id" => "input_vendedor"]) !!}
                </div>
            </div>

            <div size="12" class="inputbox col-md-12">
                <div class="form-group">
                    {!! Form::label('','Produto') !!}
                    {!! Form::text('produto', null, ['class' => 'form-control' , "id" => "input_produto"]) !!}
                </div>
            </div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','Período de') !!}
                    <div class='input-group'>
                        <div class='input-group-addon'>
                            <i class='glyphicon glyphicon-calendar'></i>
                        </div>
                    {!! Form::text('periodo_de', null, ['autocomplete' =>'off', 'class' => 'form-control componenteData' , "id" => "input_periodo_de"]) !!}
                    </div>
                </div>
            </div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','A') !!}
                    {!! Form::text('a', null, ['autocomplete' =>'off', 'class' => 'form-control componenteData' , "id" => "input_a"]) !!}
                </div>
            </div>

            <div size="4" class="inputbox col-md-4">
                <div class="form-group">
                    {!! Form::label('','Viajantes') !!}
                    {!! Form::text('viajantes', null, ['class' => 'form-control' , "id" => "input_viajantes"]) !!}
                </div>
            </div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','Crianças') !!}
                    {!! Form::text('criancas', null, ['class' => 'form-control' , "id" => "input_criancas"]) !!}
                </div>
            </div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','Idade') !!}
                    {!! Form::text('idade', null, ['class' => 'form-control' , "id" => "input_idade"]) !!}
                </div>
            </div>

<div size="12" class="inputbox col-md-12">
    <h2 class="page-header" style="font-size:20px;">
        <i class="glyphicon glyphicon-th-large"></i> MALAS DESPACHADAS
    </h2>
</div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','Inclui') !!}
                    {!! Form::text('inclui', null, ['class' => 'form-control' , "id" => "input_inclui"]) !!}
                </div>
            </div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','Não inclui') !!}
                    {!! Form::text('nao_inclui', null, ['class' => 'form-control' , "id" => "input_nao_inclui"]) !!}
                </div>
            </div>

<div size="12" class="inputbox col-md-12">
    <h2 class="page-header" style="font-size:20px;">
        <i class="glyphicon glyphicon-th-large"></i> VALOR POR PASSAGEIRO
    </h2>
</div>

            <div size="3" class="inputbox col-md-3">
                <div class="form-group">
                    {!! Form::label('','Passageiros') !!}
                    {!! Form::text('passageiros', null, ['class' => 'form-control' , "id" => "input_passageiros"]) !!}
                </div>
            </div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','Quantidade') !!}
                    {!! Form::number('quantidade', null, ['class' => 'form-control' , "id" => "input_quantidade"]) !!}
                </div>
            </div>

            <div size="3" class="inputbox col-md-3">
                <div class="form-group">
                    {!! Form::label('','Valor por pessoa') !!}
                    {!! Form::text('valor_por_pessoa', null, ['class' => 'form-control money' , "id" => "input_valor_por_pessoa"]) !!}
                </div>
            </div>

            <div size="3" class="inputbox col-md-3">
                <div class="form-group">
                    {!! Form::label('','Valor total') !!}
                    {!! Form::text('valor_total', null, ['class' => 'form-control money' , "id" => "input_valor_total"]) !!}
                </div>
            </div>

<div size="12" class="inputbox col-md-12">
    <h2 class="page-header" style="font-size:20px;">
        <i class="glyphicon glyphicon-th-large"></i> OUTROS SERVIÇOS
    </h2>
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
