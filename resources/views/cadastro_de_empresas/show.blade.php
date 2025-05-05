@php

    $isPublic = 0;

@endphp

@extends($isPublic ? 'layouts.app-public' : 'layouts.app')

@section('content')

<style type="text/css" media="print">

    @page
    {
        size: auto;
        margin: 0mm;
    }

    body
    {
        margin: 0px;
       }

       .noprint,
    {
        display: none !important;
    }

    @media print {
        a[href]::after {
            content: none !important;
        }
        .listar { clear:both; }
        .content { padding-top: 0px; padding-bottom: 0px; }
    }

</style>

@section('style')

    <style type="text/css">

    </style>

@endsection

<section class="content-header Cadastro de Empresas_show">
    <h1>Cadastro de Empresas </h1>
    <!--@if(!$isPublic)
    <ol class="breadcrumb">
        <li><a href="{{ URL('/') }}">Home</a></li>
        <li><a href="{{ URL('/') }}/cadastro_de_empresas">Cadastro de Empresas</a></li>
        <li class="active">#{{$cadastro_de_empresas->id}}</li>
    </ol>
    @endif-->
</section>

<section class="content Cadastro de Empresas_show">

    <div class="box">

        <div class="box-logo">
            <img src="https://lxxxtxx.xxxxrxxxapps.com/images/logo-lxxxtxx.jpg" style="height: 100px; margin-left: 10px;">
        </div>

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

        <div class="box-body">

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
                    {!! Form::number('codigo_empresa', $cadastro_de_empresas->codigo_empresa, ['class' => 'form-control' , "id" => "input_codigo_empresa",'disabled' => 'disabled',]) !!}
                </div>
            </div>

            <div size="10" class="inputbox col-md-10">
                <div class="form-group">
                    {!! Form::label('','Nome da Empresa') !!}
                    {!! Form::text('nome_da_empresa', $cadastro_de_empresas->nome_da_empresa, ['class' => 'form-control' , "id" => "input_nome_da_empresa",'disabled' => 'disabled',]) !!}
                </div>
            </div>

            <div size="3" class="inputbox col-md-3">
                <div class="form-group">
                    {!! Form::label('','N° Junta Comercial') !!}
                    {!! Form::text('njuntacomercial_empresa', $cadastro_de_empresas->njuntacomercial_empresa, ['class' => 'form-control' , "id" => "input_njuntacomercial_empresa",'disabled' => 'disabled',]) !!}
                </div>
            </div>

            <div size="3" class="inputbox col-md-3">
                <div class="form-group">
                    {!! Form::label('','Perfil Fiscal') !!}
                    {!! Form::text('perfil_fiscal', $cadastro_de_empresas->perfil_fiscal, ['class' => 'form-control' , "id" => "input_perfil_fiscal",'disabled' => 'disabled',]) !!}
                </div>
            </div>

            <div size="6" class="inputbox col-md-6">
                <div class="form-group">
                    {!! Form::label('','Nome Fantasia') !!}
                    {!! Form::text('nome_fantasia_empresa', $cadastro_de_empresas->nome_fantasia_empresa, ['class' => 'form-control' , "id" => "input_nome_fantasia_empresa",'disabled' => 'disabled',]) !!}
                </div>
            </div>

            <div size="3" class="inputbox col-md-3">
                <div class="form-group">
                    {!! Form::label('','CNPJ') !!}
                    {!! Form::text('cnpj_da_empresa', $cadastro_de_empresas->cnpj_da_empresa, ['class' => 'form-control cnpj' , "id" => "input_cnpj_da_empresa",'disabled' => 'disabled',]) !!}
                </div>
            </div>

            <div size="3" class="inputbox col-md-3">
                <div class="form-group">
                    {!! Form::label('','Inscrição Estadual') !!}
                    {!! Form::text('inscricao_estadual', $cadastro_de_empresas->inscricao_estadual, ['class' => 'form-control' , "id" => "input_inscricao_estadual",'disabled' => 'disabled',]) !!}
                </div>
            </div>

            <div size="6" class="inputbox col-md-6">
                <div class="form-group">
                    {!! Form::label('','Email') !!}
                    <div class='input-group'>
                        <div class='input-group-addon'>
                            <i class='fa fa-envelope'></i>
                        </div>
                    {!! Form::email('email_empresa', $cadastro_de_empresas->email_empresa, ['class' => 'form-control' , "id" => "input_email_empresa",'disabled' => 'disabled',]) !!}
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
                    {!! Form::text('cep_empresa', $cadastro_de_empresas->cep_empresa, ['class' => 'form-control cep' , "id" => "input_cep_empresa",'disabled' => 'disabled',]) !!}
                </div>
            </div>

            <div size="7" class="inputbox col-md-7">
                <div class="form-group">
                    {!! Form::label('','Endereço') !!}
                    {!! Form::text('endereco_empresa', $cadastro_de_empresas->endereco_empresa, ['class' => 'form-control' , "id" => "input_endereco_empresa",'disabled' => 'disabled',]) !!}
                </div>
            </div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','Número') !!}
                    {!! Form::text('numero_empresa', $cadastro_de_empresas->numero_empresa, ['class' => 'form-control' , "id" => "input_numero_empresa",'disabled' => 'disabled',]) !!}
                </div>
            </div>

            <div size="3" class="inputbox col-md-3">
                <div class="form-group">
                    {!! Form::label('','Bairro') !!}
                    {!! Form::text('bairro_empresa', $cadastro_de_empresas->bairro_empresa, ['class' => 'form-control' , "id" => "input_bairro_empresa",'disabled' => 'disabled',]) !!}
                </div>
            </div>

            <div size="3" class="inputbox col-md-3">
                <div class="form-group">
                    {!! Form::label('','País') !!}
                    {!! Form::text('pais_empresa', $cadastro_de_empresas->pais_empresa, ['class' => 'form-control' , "id" => "input_pais_empresa",'disabled' => 'disabled',]) !!}
                </div>
            </div>

            <div size="3" class="inputbox col-md-3">
                <div class="form-group">
                    {!! Form::label('','UF') !!}
                    {!! Form::text('uf_empresa', $cadastro_de_empresas->uf_empresa, ['class' => 'form-control' , "id" => "input_uf_empresa",'disabled' => 'disabled',]) !!}
                </div>
            </div>

            <div size="3" class="inputbox col-md-3">
                <div class="form-group">
                    {!! Form::label('','Cidade') !!}
                    {!! Form::text('cidade_empresa', $cadastro_de_empresas->cidade_empresa, ['class' => 'form-control' , "id" => "input_cidade_empresa",'disabled' => 'disabled',]) !!}
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
                    {!! Form::text('telefone_empresa', $cadastro_de_empresas->telefone_empresa, ['class' => 'form-control telefone' , "id" => "input_telefone_empresa",'disabled' => 'disabled',]) !!}
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
                    {!! Form::text('fax_empresa', $cadastro_de_empresas->fax_empresa, ['class' => 'form-control telefone' , "id" => "input_fax_empresa",'disabled' => 'disabled',]) !!}
                    </div>
                </div>
            </div>

            <div size="4" class="inputbox col-md-4">
                <div class="form-group">
                    {!! Form::label('','Site') !!}
                    {!! Form::text('site_empresa', $cadastro_de_empresas->site_empresa, ['class' => 'form-control' , "id" => "input_site_empresa",'disabled' => 'disabled',]) !!}
                </div>
            </div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','NRE') !!}
                    {!! Form::text('nre_empresa', $cadastro_de_empresas->nre_empresa, ['class' => 'form-control' , "id" => "input_nre_empresa",'disabled' => 'disabled',]) !!}
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
                    {!! Form::checkbox('comercio', null, $cadastro_de_empresas->comercio, ['class' => '' , "id" => "input_comercio",'disabled' => 'disabled',]) !!}
                </div>
            </div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','Serviço') !!}
                    {!! Form::checkbox('servico', null, $cadastro_de_empresas->servico, ['class' => '' , "id" => "input_servico",'disabled' => 'disabled',]) !!}
                </div>
            </div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','Indústria') !!}
                    {!! Form::checkbox('industria', null, $cadastro_de_empresas->industria, ['class' => '' , "id" => "input_industria",'disabled' => 'disabled',]) !!}
                </div>
            </div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','Importador') !!}
                    {!! Form::checkbox('importador', null, $cadastro_de_empresas->importador, ['class' => '' , "id" => "input_importador",'disabled' => 'disabled',]) !!}
                </div>
            </div>

            <div class="col-md-12" style="margin-top: 20px; margin-bottom: 20px;">
                <div class="form-group no-print form-group-btn-show">
                    @if(!$isPublic)
                        <a href="{{ URL::previous() }}" class="btn btn-default form-group-btn-add-voltar" style="float: left;"><i class="glyphicon glyphicon-backward"></i> Voltar</a>
                    @endif
                    <a href="javascript:void(0);" onclick="printScreen();" class="btn btn-default form-group-btn-show-imprimir" style="float: right;"><i class="glyphicon glyphicon-print"></i> Imprimir</a>
                </div>
            </div>

        </div>

    </div>

</section>

<script type="text/javascript">

    function printScreen(){

        $('.inputbox').each(function(){
            var size = $(this).attr('size');
            var percent = (( size * 100 ) / 12);
            $(this).css({"width": percent + "%", "float": "left"});
        });

        $('.grid_remove').remove();

        window.print();

    }

</script>

@section('script')

    <script type="text/javascript">

    </script>

@endsection

@endsection
