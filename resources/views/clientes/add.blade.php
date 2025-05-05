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

<section class="content-header Clientes_add">
    <h1>Clientes</h1>
    <!--@if(!$isPublic)
    <ol class="breadcrumb">
        <li><a href="{{ URL('/') }}">Home</a></li>
        <li><a href="{{ URL('/') }}/clientes">Clientes</a></li>
        <li class="active">Clientes</li>
    </ol>
    @endif-->
</section>

<section class="content Clientes_add">

<div class="box">

    {!! Form::open(['url' => "clientes", 'method' => 'post', 'novalidate'=> true, 'enctype' => 'multipart/form-data', 'accept-charset' => 'utf-8', 'id' => 'form_add_clientes']) !!}

        <div class="box-body" id="div_clientes">

<div size="12" class="inputbox col-md-12">
    <h2 class="page-header" style="font-size:20px;">
        <i class="glyphicon glyphicon-th-large"></i> Dados do Cliente
    </h2>
</div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','Código do Cliente') !!}
                    {!! Form::number('codigo_do_cliente', null, ['class' => 'form-control' , "id" => "input_codigo_do_cliente",'disabled' => 'disabled',]) !!}
                </div>
            </div>

            <div size="3" class="inputbox col-md-3">
                <div class="form-group">
                    {!! Form::label('','CNPJ do Cliente') !!}
                    {!! Form::text('cnpj_do_cliente', null, ['class' => 'form-control cnpj' , "id" => "input_cnpj_do_cliente"]) !!}
                </div>
            </div>

            <div size="3" class="inputbox col-md-3">
                <div class="form-group">
                    {!! Form::label('','CPF do Cliente') !!}
                    {!! Form::text('cpf_do_cliente', null, ['class' => 'form-control cpf' , "id" => "input_cpf_do_cliente"]) !!}
                </div>
            </div>

            <div size="3" class="inputbox col-md-3">
                <div class="form-group">
                    {!! Form::label('','Inscrição Estadual/RG') !!}
                    {!! Form::text('inscricao_estadual_rg', null, ['class' => 'form-control' , "id" => "input_inscricao_estadual_rg"]) !!}
                </div>
            </div>

            <div size="7" class="inputbox col-md-7">
                <div class="form-group">
                    {!! Form::label('','Nome do Cliente') !!}
                    {!! Form::text('nome_do_cliente', null, ['class' => 'form-control' , "id" => "input_nome_do_cliente", "style" => "text-transform: uppercase;", "oninput" => "this.value = this.value.toUpperCase();"]) !!}
                </div>
            </div>

            <div size="5" class="inputbox col-md-5">
                <div class="form-group">
                    {!! Form::label('','Fantasia') !!}
                    {!! Form::text('fantasia', null, ['class' => 'form-control' , "id" => "input_fantasia"]) !!}
                </div>
            </div>

            <div size="6" class="inputbox col-md-6">
                <div class="form-group">
                    {!! Form::label('','E-mail') !!}
                    {!! Form::text('e_mail', null, ['class' => 'form-control' , "id" => "input_e_mail"]) !!}
                </div>
            </div>

            <div size="3" class="inputbox col-md-3">
                <div class="form-group">
                    {!! Form::label('','Data de Nascimento') !!}
                    {!! Form::text('data_nascimento', null, ['autocomplete' =>'off', 'class' => 'form-control componenteData' , "id" => "input_data_nascimento", "placeholder" => "__/__/____"]) !!}
                </div>
            </div>

            <div size="12" class="inputbox col-md-12">
                <div class="form-group">
                    {!! Form::label('','Observação') !!}
                    {!! Form::text('observacao', null, ['class' => 'form-control' , "id" => "input_observacao"]) !!}
                </div>
            </div>

            <div size="3" class="inputbox col-md-3">
                <div class="form-group">
                    {!! Form::label('','Telefone 1') !!}
                    <div class='input-group'>
                        <div class='input-group-addon'>
                            <i class='fa fa-phone'></i>
                        </div>
                    {!! Form::text('telefone_1', null, ['class' => 'form-control telefone' , "id" => "input_telefone_1"]) !!}
                    </div>
                </div>
            </div>

            <div size="3" class="inputbox col-md-3">
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

<div size="12" class="inputbox col-md-12">
    <h2 class="page-header" style="font-size:20px;">
        <i class="glyphicon glyphicon-th-large"></i> Endereço do Cliente
    </h2>
</div>

            <div size="3" class="inputbox col-md-3">
                <div class="form-group">
                    {!! Form::label('','CEP do Cliente') !!}
                    {!! Form::text('cep_do_cliente', null, ['class' => 'form-control cep' , "id" => "input_cep_do_cliente"]) !!}
                </div>
            </div>

            <div size="7" class="inputbox col-md-7">
                <div class="form-group">
                    {!! Form::label('','Endereço') !!}
                    {!! Form::text('endereco', null, ['class' => 'form-control' , "id" => "input_endereco"]) !!}
                </div>
            </div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','Número:') !!}
                    {!! Form::text('numero_', null, ['class' => 'form-control' , "id" => "input_numero_"]) !!}
                </div>
            </div>

            <div size="8" class="inputbox col-md-8">
                <div class="form-group">
                    {!! Form::label('','Complemento') !!}
                    {!! Form::text('complemento', null, ['class' => 'form-control' , "id" => "input_complemento"]) !!}
                </div>
            </div>

            <div size="4" class="inputbox col-md-4">
                <div class="form-group">
                    {!! Form::label('','Bairro') !!}
                    {!! Form::text('bairro', null, ['class' => 'form-control' , "id" => "input_bairro"]) !!}
                </div>
            </div>

            <div size="4" class="inputbox col-md-4">
                <div class="form-group">
                    {!! Form::label('','Cidade') !!}
                    {!! Form::text('cidade', null, ['class' => 'form-control' , "id" => "input_cidade"]) !!}
                </div>
            </div>

            <div size="4" class="inputbox col-md-4">
                <div class="form-group">
                    {!! Form::label('','Estado') !!}
                    {!! Form::text('estado', null, ['class' => 'form-control' , "id" => "input_estado"]) !!}
                </div>
            </div>

            <div size="4" class="inputbox col-md-4">
                <div class="form-group">
                    {!! Form::label('','Ponto de Referência') !!}
                    {!! Form::text('ponto_de_referencia', null, ['class' => 'form-control' , "id" => "input_ponto_de_referencia"]) !!}
                </div>
            </div>

<div size="12" class="inputbox col-md-12">
    <h2 class="page-header" style="font-size:20px;">
        <i class="glyphicon glyphicon-th-large"></i> Anexos
    </h2>
</div>

<div class="col-md-12">
                    {!! Form::label('','Documentos') !!}
</div>

<div class="documentos_multiplos">
    <div class="divdefault">
        <div size="12" class="inputbox col-md-12">
                <ol>
                                        {!! Form::file('documentos[]', ['class' => 'form-control isFile' , "id" => "input_documentos"]) !!}
        <div class='row'>
            <div class='col-md-12'>
                <img src='' id='file_input_documentos[]' style='height: 100px; display: none;'>
            </div>
        </div>
                <i class="glyphicon glyphicon-trash multiple_remove" style="margin-top: 5px;"></i>
                </ol>
        </div>
    </div>
</div>

<div class="col-md-12" style="margin: 20px 0 20px 0;">
    <div class="form-group">
        <i class="glyphicon glyphicon-plus multiple_add" data="documentos_multiplos"></i>
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
