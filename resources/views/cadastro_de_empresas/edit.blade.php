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

<section class="content-header Cadastro de Empresas_edit">
    <h1>Cadastro de Empresas </h1>
    <!--@if(!$isPublic)
    <ol class="breadcrumb">
        <li><a href="{{ URL('/') }}">Home</a></li>
        <li><a href="{{ URL('/') }}/cadastro_de_empresas">Cadastro de Empresas</a></li>
        <li class="active">#{{$cadastro_de_empresas->id}}</li>
    </ol>
    @endif-->
</section>

<section class="content Cadastro de Empresas_edit">

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
                <form id="form-destroy" method="POST" action="{{ route('cadastro_de_empresas.destroy', $cadastro_de_empresas->id) }}" accept-charset="UTF-8">
                    {!! csrf_field() !!}
                    {!! method_field('DELETE') !!}
                    <button type="submit" style="margin-left: 10px; margin-top: -1px;" onclick="return confirm('Tem certeza que quer deletar?')" class="btn btn-danger glyphicon glyphicon-trash"></button>
                </form>
            </div>
        </div>
    @endif

    {!! Form::open(['url' => "cadastro_de_empresas/$cadastro_de_empresas->id", 'method' => 'put', 'novalidate'=> true, 'enctype' => 'multipart/form-data', 'accept-charset' => 'utf-8', 'id' => 'form_edit_cadastro_de_empresas']) !!}

        @if(\Request::get('modal'))
            {!! Form::hidden('modal-close', 1) !!}
        @endif
        {!! Form::hidden('id', $cadastro_de_empresas->id) !!}

        <div class="box-body" id="div_cadastro_de_empresas">

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','Logotipo Empresa') !!}
@if($cadastro_de_empresas->logotipo_empresa && pathinfo($cadastro_de_empresas->logotipo_empresa, PATHINFO_EXTENSION))
        <ol style="margin:0px;padding:0px;">
            <a class="fancybox" rel="gallery1" target="_blank" href="{{$fileurlbase . "images"}}/{{$cadastro_de_empresas->logotipo_empresa}}">
                <img src="{{in_array(explode(".", $cadastro_de_empresas->logotipo_empresa)[1], array("mp4", "pdf", "doc", "docx", "rar", "zip", "txt", "7zip", "csv", "xls", "xlsx")) ? URL("/") . "/" . explode(".", $cadastro_de_empresas->logotipo_empresa)[1] . "-icon.png" : $fileurlbase . "images/" . $cadastro_de_empresas->logotipo_empresa}}" height="100">
            </a>
        </ol>
{!! Form::hidden("logotipo_empresa", $cadastro_de_empresas->logotipo_empresa) !!}
@endif
                    {!! Form::file('logotipo_empresa', ['class' => 'form-control isFile' , "id" => "input_logotipo_empresa"]) !!}
        <div class='row'>
            <div class='col-md-12'>
                <img src='' id='file_input_logotipo_empresa' style='height: 100px; display: none;'>
            </div>
        </div>
        <i class='glyphicon glyphicon-trash input_remove' style='margin-top: 5px;'></i>
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
                    {!! Form::number('codigo_empresa', $cadastro_de_empresas->codigo_empresa, ['class' => 'form-control' , "id" => "input_codigo_empresa"]) !!}
                </div>
            </div>

            <div size="10" class="inputbox col-md-10">
                <div class="form-group">
                    {!! Form::label('','Nome da Empresa') !!}
                    {!! Form::text('nome_da_empresa', $cadastro_de_empresas->nome_da_empresa, ['class' => 'form-control' , "id" => "input_nome_da_empresa"]) !!}
                </div>
            </div>

            <div size="3" class="inputbox col-md-3">
                <div class="form-group">
                    {!! Form::label('','N° Junta Comercial') !!}
                    {!! Form::text('njuntacomercial_empresa', $cadastro_de_empresas->njuntacomercial_empresa, ['class' => 'form-control' , "id" => "input_njuntacomercial_empresa"]) !!}
                </div>
            </div>

            <div size="3" class="inputbox col-md-3">
                <div class="form-group">
                    {!! Form::label('','Perfil Fiscal') !!}
                    {!! Form::text('perfil_fiscal', $cadastro_de_empresas->perfil_fiscal, ['class' => 'form-control' , "id" => "input_perfil_fiscal"]) !!}
                </div>
            </div>

            <div size="6" class="inputbox col-md-6">
                <div class="form-group">
                    {!! Form::label('','Nome Fantasia') !!}
                    {!! Form::text('nome_fantasia_empresa', $cadastro_de_empresas->nome_fantasia_empresa, ['class' => 'form-control' , "id" => "input_nome_fantasia_empresa"]) !!}
                </div>
            </div>

            <div size="3" class="inputbox col-md-3">
                <div class="form-group">
                    {!! Form::label('','CNPJ') !!}
                    {!! Form::text('cnpj_da_empresa', $cadastro_de_empresas->cnpj_da_empresa, ['class' => 'form-control cnpj' , "id" => "input_cnpj_da_empresa"]) !!}
                </div>
            </div>

            <div size="3" class="inputbox col-md-3">
                <div class="form-group">
                    {!! Form::label('','Inscrição Estadual') !!}
                    {!! Form::text('inscricao_estadual', $cadastro_de_empresas->inscricao_estadual, ['class' => 'form-control' , "id" => "input_inscricao_estadual"]) !!}
                </div>
            </div>

            <div size="6" class="inputbox col-md-6">
                <div class="form-group">
                    {!! Form::label('','Email') !!}
                    <div class='input-group'>
                        <div class='input-group-addon'>
                            <i class='fa fa-envelope'></i>
                        </div>
                    {!! Form::email('email_empresa', $cadastro_de_empresas->email_empresa, ['class' => 'form-control' , "id" => "input_email_empresa"]) !!}
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
                    {!! Form::text('cep_empresa', $cadastro_de_empresas->cep_empresa, ['class' => 'form-control cep' , "id" => "input_cep_empresa"]) !!}
                </div>
            </div>

            <div size="7" class="inputbox col-md-7">
                <div class="form-group">
                    {!! Form::label('','Endereço') !!}
                    {!! Form::text('endereco_empresa', $cadastro_de_empresas->endereco_empresa, ['class' => 'form-control' , "id" => "input_endereco_empresa"]) !!}
                </div>
            </div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','Número') !!}
                    {!! Form::text('numero_empresa', $cadastro_de_empresas->numero_empresa, ['class' => 'form-control' , "id" => "input_numero_empresa"]) !!}
                </div>
            </div>

            <div size="3" class="inputbox col-md-3">
                <div class="form-group">
                    {!! Form::label('','Bairro') !!}
                    {!! Form::text('bairro_empresa', $cadastro_de_empresas->bairro_empresa, ['class' => 'form-control' , "id" => "input_bairro_empresa"]) !!}
                </div>
            </div>

            <div size="3" class="inputbox col-md-3">
                <div class="form-group">
                    {!! Form::label('','País') !!}
                    {!! Form::text('pais_empresa', $cadastro_de_empresas->pais_empresa, ['class' => 'form-control' , "id" => "input_pais_empresa"]) !!}
                </div>
            </div>

            <div size="3" class="inputbox col-md-3">
                <div class="form-group">
                    {!! Form::label('','UF') !!}
                    {!! Form::text('uf_empresa', $cadastro_de_empresas->uf_empresa, ['class' => 'form-control' , "id" => "input_uf_empresa"]) !!}
                </div>
            </div>

            <div size="3" class="inputbox col-md-3">
                <div class="form-group">
                    {!! Form::label('','Cidade') !!}
                    {!! Form::text('cidade_empresa', $cadastro_de_empresas->cidade_empresa, ['class' => 'form-control' , "id" => "input_cidade_empresa"]) !!}
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
                    {!! Form::text('telefone_empresa', $cadastro_de_empresas->telefone_empresa, ['class' => 'form-control telefone' , "id" => "input_telefone_empresa"]) !!}
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
                    {!! Form::text('fax_empresa', $cadastro_de_empresas->fax_empresa, ['class' => 'form-control telefone' , "id" => "input_fax_empresa"]) !!}
                    </div>
                </div>
            </div>

            <div size="4" class="inputbox col-md-4">
                <div class="form-group">
                    {!! Form::label('','Site') !!}
                    {!! Form::text('site_empresa', $cadastro_de_empresas->site_empresa, ['class' => 'form-control' , "id" => "input_site_empresa"]) !!}
                </div>
            </div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','NRE') !!}
                    {!! Form::text('nre_empresa', $cadastro_de_empresas->nre_empresa, ['class' => 'form-control' , "id" => "input_nre_empresa"]) !!}
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
                    {!! Form::checkbox('comercio', null, $cadastro_de_empresas->comercio, ['class' => '' , "id" => "input_comercio"]) !!}
                </div>
            </div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','Serviço') !!}
                    {!! Form::checkbox('servico', null, $cadastro_de_empresas->servico, ['class' => '' , "id" => "input_servico"]) !!}
                </div>
            </div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','Indústria') !!}
                    {!! Form::checkbox('industria', null, $cadastro_de_empresas->industria, ['class' => '' , "id" => "input_industria"]) !!}
                </div>
            </div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','Importador') !!}
                    {!! Form::checkbox('importador', null, $cadastro_de_empresas->importador, ['class' => '' , "id" => "input_importador"]) !!}
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

                            {!! Form::select('r_auth', $parserList, $cadastro_de_empresas->r_auth, ['class' => 'form-control']) !!}
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
