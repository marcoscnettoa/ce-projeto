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

<section class="content-header Cadastro de Empresas_add">
    <h1>Cadastro de Empresas</h1>
    <!--@if(!$isPublic)
    <ol class="breadcrumb">
        <li><a href="{{ URL('/') }}">Home</a></li>
        <li><a href="{{ URL('/') }}/cadastro_de_empresas">Cadastro de Empresas</a></li>
        <li class="active">Cadastro de Empresas</li>
    </ol>
    @endif-->
</section>

<section class="content Cadastro de Empresas_add">

<div class="box">

    {!! Form::open(['url' => "cadastro_de_empresas", 'method' => 'post', 'novalidate'=> true, 'enctype' => 'multipart/form-data', 'accept-charset' => 'utf-8', 'id' => 'form_add_cadastro_de_empresas']) !!}

        <div class="box-body" id="div_cadastro_de_empresas">

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','Logotipo Empresa') !!}
                    {!! Form::file('logotipo_empresa', ['class' => 'form-control isFile' , "id" => "input_logotipo_empresa"]) !!}
        <div class='row'>
            <div class='col-md-12'>
                <img src='' id='file_input_logotipo_empresa' style='height: 100px; display: none;'>
            </div>
        </div>
                </div>
            </div>

<div size="12" class="inputbox col-md-12">
    <h2 class="page-header" style="font-size:20px;">
        <i class="glyphicon glyphicon-th-large"></i> Dados da Empresa
    </h2>
</div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','Codigo Empresa') !!}
                    {!! Form::number('codigo_empresa', null, ['class' => 'form-control' , "id" => "input_codigo_empresa"]) !!}
                </div>
            </div>

            <div size="10" class="inputbox col-md-10">
                <div class="form-group">
                    {!! Form::label('','Nome da Empresa') !!}
                    {!! Form::text('nome_da_empresa', null, ['class' => 'form-control' , "id" => "input_nome_da_empresa"]) !!}
                </div>
            </div>

            <div size="3" class="inputbox col-md-3">
                <div class="form-group">
                    {!! Form::label('','N° Junta Comercial') !!}
                    {!! Form::text('njuntacomercial_empresa', null, ['class' => 'form-control' , "id" => "input_njuntacomercial_empresa"]) !!}
                </div>
            </div>

            <div size="3" class="inputbox col-md-3">
                <div class="form-group">
                    {!! Form::label('','Perfil Fiscal') !!}
                    {!! Form::text('perfil_fiscal', null, ['class' => 'form-control' , "id" => "input_perfil_fiscal"]) !!}
                </div>
            </div>

            <div size="6" class="inputbox col-md-6">
                <div class="form-group">
                    {!! Form::label('','Nome Fantasia') !!}
                    {!! Form::text('nome_fantasia_empresa', null, ['class' => 'form-control' , "id" => "input_nome_fantasia_empresa"]) !!}
                </div>
            </div>

            <div size="3" class="inputbox col-md-3">
                <div class="form-group">
                    {!! Form::label('','CNPJ') !!}
                    {!! Form::text('cnpj_da_empresa', null, ['class' => 'form-control cnpj' , "id" => "input_cnpj_da_empresa"]) !!}
                </div>
            </div>

            <div size="3" class="inputbox col-md-3">
                <div class="form-group">
                    {!! Form::label('','Inscrição Estadual') !!}
                    {!! Form::text('inscricao_estadual', null, ['class' => 'form-control' , "id" => "input_inscricao_estadual"]) !!}
                </div>
            </div>

            <div size="6" class="inputbox col-md-6">
                <div class="form-group">
                    {!! Form::label('','Email') !!}
                    <div class='input-group'>
                        <div class='input-group-addon'>
                            <i class='fa fa-envelope'></i>
                        </div>
                    {!! Form::email('email_empresa', null, ['class' => 'form-control' , "id" => "input_email_empresa"]) !!}
                    </div>
                </div>
            </div>

<div size="12" class="inputbox col-md-12">
    <h2 class="page-header" style="font-size:20px;">
        <i class="glyphicon glyphicon-th-large"></i> Dados de Endereço
    </h2>
</div>

            <div size="3" class="inputbox col-md-3">
                <div class="form-group">
                    {!! Form::label('','CEP') !!}
                    {!! Form::text('cep_empresa', null, ['class' => 'form-control cep' , "id" => "input_cep_empresa"]) !!}
                </div>
            </div>

            <div size="7" class="inputbox col-md-7">
                <div class="form-group">
                    {!! Form::label('','Endereço') !!}
                    {!! Form::text('endereco_empresa', null, ['class' => 'form-control' , "id" => "input_endereco_empresa"]) !!}
                </div>
            </div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','Número') !!}
                    {!! Form::text('numero_empresa', null, ['class' => 'form-control' , "id" => "input_numero_empresa"]) !!}
                </div>
            </div>

            <div size="3" class="inputbox col-md-3">
                <div class="form-group">
                    {!! Form::label('','Bairro') !!}
                    {!! Form::text('bairro_empresa', null, ['class' => 'form-control' , "id" => "input_bairro_empresa"]) !!}
                </div>
            </div>

            <div size="3" class="inputbox col-md-3">
                <div class="form-group">
                    {!! Form::label('','País') !!}
                    {!! Form::text('pais_empresa', null, ['class' => 'form-control' , "id" => "input_pais_empresa"]) !!}
                </div>
            </div>

            <div size="3" class="inputbox col-md-3">
                <div class="form-group">
                    {!! Form::label('','UF') !!}
                    {!! Form::text('uf_empresa', null, ['class' => 'form-control' , "id" => "input_uf_empresa"]) !!}
                </div>
            </div>

            <div size="3" class="inputbox col-md-3">
                <div class="form-group">
                    {!! Form::label('','Cidade') !!}
                    {!! Form::text('cidade_empresa', null, ['class' => 'form-control' , "id" => "input_cidade_empresa"]) !!}
                </div>
            </div>

<div size="12" class="inputbox col-md-12">
    <h2 class="page-header" style="font-size:20px;">
        <i class="glyphicon glyphicon-th-large"></i> Dados para Contato
    </h2>
</div>

            <div size="3" class="inputbox col-md-3">
                <div class="form-group">
                    {!! Form::label('','Telefone') !!}
                    <div class='input-group'>
                        <div class='input-group-addon'>
                            <i class='fa fa-phone'></i>
                        </div>
                    {!! Form::text('telefone_empresa', null, ['class' => 'form-control telefone' , "id" => "input_telefone_empresa"]) !!}
                    </div>
                </div>
            </div>

            <div size="3" class="inputbox col-md-3">
                <div class="form-group">
                    {!! Form::label('','Fax') !!}
                    <div class='input-group'>
                        <div class='input-group-addon'>
                            <i class='fa fa-phone'></i>
                        </div>
                    {!! Form::text('fax_empresa', null, ['class' => 'form-control telefone' , "id" => "input_fax_empresa"]) !!}
                    </div>
                </div>
            </div>

            <div size="4" class="inputbox col-md-4">
                <div class="form-group">
                    {!! Form::label('','Site') !!}
                    {!! Form::text('site_empresa', null, ['class' => 'form-control' , "id" => "input_site_empresa"]) !!}
                </div>
            </div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','NRE') !!}
                    {!! Form::text('nre_empresa', null, ['class' => 'form-control' , "id" => "input_nre_empresa"]) !!}
                </div>
            </div>

<div size="12" class="inputbox col-md-12">
    <h2 class="page-header" style="font-size:20px;">
        <i class="glyphicon glyphicon-th-large"></i> Segmento
    </h2>
</div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','Comércio') !!}
                    {!! Form::checkbox('comercio', null, null, ['class' => '' , "id" => "input_comercio"]) !!}
                </div>
            </div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','Serviço') !!}
                    {!! Form::checkbox('servico', null, null, ['class' => '' , "id" => "input_servico"]) !!}
                </div>
            </div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','Indústria') !!}
                    {!! Form::checkbox('industria', null, null, ['class' => '' , "id" => "input_industria"]) !!}
                </div>
            </div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','Importador') !!}
                    {!! Form::checkbox('importador', null, null, ['class' => '' , "id" => "input_importador"]) !!}
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
