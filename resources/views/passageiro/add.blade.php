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

<section class="content-header Passageiro_add">
    <h1>Passageiro</h1>
    <!--@if(!$isPublic)
    <ol class="breadcrumb">
        <li><a href="{{ URL('/') }}">Home</a></li>
        <li><a href="{{ URL('/') }}/passageiro">Passageiro</a></li>
        <li class="active">Passageiro</li>
    </ol>
    @endif-->
</section>

<section class="content Passageiro_add">

<div class="box">

    {!! Form::open(['url' => "passageiro", 'method' => 'post', 'novalidate'=> true, 'enctype' => 'multipart/form-data', 'accept-charset' => 'utf-8', 'id' => 'form_add_passageiro']) !!}

        <div class="box-body" id="div_passageiro">

<div size="12" class="inputbox col-md-12">
    <h2 class="page-header" style="font-size:20px;">
        <i class="glyphicon glyphicon-th-large"></i> Dados do Passageiro
    </h2>
</div>

            <div size="4" class="inputbox col-md-4">
                <div class="form-group">
                    {!! Form::label('','Nome') !!}
                    {!! Form::text('nome', null, ['class' => 'form-control' , "id" => "input_nome", "style" => "text-transform: uppercase;", "oninput" => "this.value = this.value.toUpperCase();"]) !!}
                </div>
            </div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','CPF') !!}
                    {!! Form::text('cpf', null, ['class' => 'form-control cpf' , "id" => "input_cpf"]) !!}
                </div>
            </div>

            <div size="3" class="inputbox col-md-3">
                <div class="form-group">
                    {!! Form::label('','E-mail') !!}
                    <div class='input-group'>
                        <div class='input-group-addon'>
                            <i class='fa fa-envelope'></i>
                        </div>
                    {!! Form::email('e_mail', null, ['class' => 'form-control' , "id" => "input_e_mail"]) !!}
                    </div>
                </div>
            </div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','Telefone') !!}
                    <div class='input-group'>
                        <div class='input-group-addon'>
                            <i class='fa fa-phone'></i>
                        </div>
                    {!! Form::text('telefone', null, ['class' => 'form-control telefone' , "id" => "input_telefone"]) !!}
                    </div>
                </div>
            </div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','Telefone 2') !!}
                    <div class='input-group'>
                        <div class='input-group-addon'>
                            <i class='fa fa-phone'></i>
                        </div>
                    {!! Form::text('telefone_2', null, ['class' => 'form-control telefone' , "id" => "input_telefone_2"]) !!}
                    </div>
                </div>
            </div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','Passaporte') !!}
                    {!! Form::text('passaporte', null, ['class' => 'form-control' , "id" => "input_passaporte"]) !!}
                </div>
            </div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','Data de Nascimento') !!}
                    {!! Form::text('data_nascimento', null, ['autocomplete' =>'off', 'class' => 'form-control componenteData' , "id" => "input_data_nascimento", "placeholder" => "__/__/____"]) !!}
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
