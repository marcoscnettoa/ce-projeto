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

<section class="content-header Orçamentos_show">
    <h1>Orçamentos </h1>
    <!--@if(!$isPublic)
    <ol class="breadcrumb">
        <li><a href="{{ URL('/') }}">Home</a></li>
        <li><a href="{{ URL('/') }}/orcamentos">Orçamentos</a></li>
        <li class="active">#{{$orcamentos->id}}</li>
    </ol>
    @endif-->
</section>

<section class="content Orçamentos_show">

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
                    {!! Form::label('','Número') !!}
                    {!! Form::text('numero', $orcamentos->numero, ['class' => 'form-control' , "id" => "input_numero",'disabled' => 'disabled',]) !!}
                </div>
            </div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','Data') !!}
                    <div class='input-group'>
                        <div class='input-group-addon'>
                            <i class='glyphicon glyphicon-calendar'></i>
                        </div>
                    {!! Form::text('data', $orcamentos->data, ['autocomplete' =>'off', 'class' => 'form-control componenteData' , "id" => "input_data",'disabled' => 'disabled',]) !!}
                    </div>
                </div>
            </div>

            <div size="5" class="inputbox col-md-5">
                <div class="form-group">
                    {!! Form::label('','Clilente') !!}
                    {!! Form::text('clilente', $orcamentos->clilente, ['class' => 'form-control' , "id" => "input_clilente",'disabled' => 'disabled',]) !!}
                </div>
            </div>

            <div size="3" class="inputbox col-md-3">
                <div class="form-group">
                    {!! Form::label('','Vendedor') !!}
                    {!! Form::text('vendedor', $orcamentos->vendedor, ['class' => 'form-control' , "id" => "input_vendedor",'disabled' => 'disabled',]) !!}
                </div>
            </div>

            <div size="12" class="inputbox col-md-12">
                <div class="form-group">
                    {!! Form::label('','Produto') !!}
                    {!! Form::text('produto', $orcamentos->produto, ['class' => 'form-control' , "id" => "input_produto",'disabled' => 'disabled',]) !!}
                </div>
            </div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','Período de') !!}
                    <div class='input-group'>
                        <div class='input-group-addon'>
                            <i class='glyphicon glyphicon-calendar'></i>
                        </div>
                    {!! Form::text('periodo_de', $orcamentos->periodo_de, ['autocomplete' =>'off', 'class' => 'form-control componenteData' , "id" => "input_periodo_de",'disabled' => 'disabled',]) !!}
                    </div>
                </div>
            </div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','A') !!}
                    {!! Form::text('a', $orcamentos->a, ['autocomplete' =>'off', 'class' => 'form-control componenteData' , "id" => "input_a",'disabled' => 'disabled',]) !!}
                </div>
            </div>

            <div size="4" class="inputbox col-md-4">
                <div class="form-group">
                    {!! Form::label('','Viajantes') !!}
                    {!! Form::text('viajantes', $orcamentos->viajantes, ['class' => 'form-control' , "id" => "input_viajantes",'disabled' => 'disabled',]) !!}
                </div>
            </div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','Crianças') !!}
                    {!! Form::text('criancas', $orcamentos->criancas, ['class' => 'form-control' , "id" => "input_criancas",'disabled' => 'disabled',]) !!}
                </div>
            </div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','Idade') !!}
                    {!! Form::text('idade', $orcamentos->idade, ['class' => 'form-control' , "id" => "input_idade",'disabled' => 'disabled',]) !!}
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
                    {!! Form::text('inclui', $orcamentos->inclui, ['class' => 'form-control' , "id" => "input_inclui",'disabled' => 'disabled',]) !!}
                </div>
            </div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','Não inclui') !!}
                    {!! Form::text('nao_inclui', $orcamentos->nao_inclui, ['class' => 'form-control' , "id" => "input_nao_inclui",'disabled' => 'disabled',]) !!}
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
                    {!! Form::text('passageiros', $orcamentos->passageiros, ['class' => 'form-control' , "id" => "input_passageiros",'disabled' => 'disabled',]) !!}
                </div>
            </div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','Quantidade') !!}
                    {!! Form::number('quantidade', $orcamentos->quantidade, ['class' => 'form-control' , "id" => "input_quantidade",'disabled' => 'disabled',]) !!}
                </div>
            </div>

            <div size="3" class="inputbox col-md-3">
                <div class="form-group">
                    {!! Form::label('','Valor por pessoa') !!}
                    {!! Form::text('valor_por_pessoa', $orcamentos->valor_por_pessoa, ['class' => 'form-control money' , "id" => "input_valor_por_pessoa",'disabled' => 'disabled',]) !!}
                </div>
            </div>

            <div size="3" class="inputbox col-md-3">
                <div class="form-group">
                    {!! Form::label('','Valor total') !!}
                    {!! Form::text('valor_total', $orcamentos->valor_total, ['class' => 'form-control money' , "id" => "input_valor_total",'disabled' => 'disabled',]) !!}
                </div>
            </div>

<div size="12" class="inputbox col-md-12">
    <h2 class="page-header" style="font-size:20px;">
        <i class="glyphicon glyphicon-th-large"></i> OUTROS SERVIÇOS
    </h2>
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
