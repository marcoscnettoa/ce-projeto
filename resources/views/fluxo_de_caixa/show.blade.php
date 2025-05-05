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

<section class="content-header Fluxo de caixa_show">
    <h1>Fluxo de caixa </h1>
    <!--@if(!$isPublic)
    <ol class="breadcrumb">
        <li><a href="{{ URL('/') }}">Home</a></li>
        <li><a href="{{ URL('/') }}/fluxo_de_caixa">Fluxo de caixa</a></li>
        <li class="active">#{{$fluxo_de_caixa->id}}</li>
    </ol>
    @endif-->
</section>

<section class="content Fluxo de caixa_show">

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
                    {!! Form::label('','Saldo do dia') !!}
                    <div class='input-group'>
                        <div class='input-group-addon'>
                            <i class='glyphicon glyphicon-usd'></i>
                        </div>
                    {!! Form::text('saldo_inicial', $fluxo_de_caixa->saldo_inicial, ['class' => 'form-control money' , "id" => "input_saldo_inicial",'disabled' => 'disabled',]) !!}
                    </div>
                </div>
            </div>

            <div size="10" class="inputbox col-md-10">
                <div class="form-group">
                    {!! Form::label('','Movimentação') !!}
                    {!! Form::text('movimentacao', $fluxo_de_caixa->movimentacao, ['class' => 'form-control' , "id" => "input_movimentacao",'disabled' => 'disabled',]) !!}
                </div>
            </div>

<div size="12" class="inputbox col-md-12">
    <h2 class="page-header" style="font-size:20px;">
        <i class="glyphicon glyphicon-th-large"></i> Recebimentos
    </h2>
</div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','Data do Recebimento') !!}
                    <div class='input-group'>
                        <div class='input-group-addon'>
                            <i class='glyphicon glyphicon-calendar'></i>
                        </div>
                    {!! Form::text('data_do_recebimento', $fluxo_de_caixa->data_do_recebimento, ['class' => 'form-control data' , "id" => "input_data_do_recebimento",'disabled' => 'disabled',]) !!}
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
                    {!! Form::text('valor_recebido', $fluxo_de_caixa->valor_recebido, ['class' => 'form-control money' , "id" => "input_valor_recebido",'disabled' => 'disabled',]) !!}
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
                    {!! Form::label('','Data do Pagamento') !!}
                    <div class='input-group'>
                        <div class='input-group-addon'>
                            <i class='glyphicon glyphicon-calendar'></i>
                        </div>
                    {!! Form::text('data_do_pagamento', $fluxo_de_caixa->data_do_pagamento, ['class' => 'form-control data' , "id" => "input_data_do_pagamento",'disabled' => 'disabled',]) !!}
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
                    {!! Form::text('total_a_pagar', $fluxo_de_caixa->total_a_pagar, ['class' => 'form-control money' , "id" => "input_total_a_pagar",'disabled' => 'disabled',]) !!}
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
                    {!! Form::label('','Data Atual') !!}
                    <div class='input-group'>
                        <div class='input-group-addon'>
                            <i class='glyphicon glyphicon-calendar'></i>
                        </div>
                    {!! Form::text('data_atual', $fluxo_de_caixa->data_atual, ['class' => 'form-control data' , "id" => "input_data_atual",'disabled' => 'disabled',]) !!}
                    </div>
                </div>
            </div>

            <div size="4" class="inputbox col-md-4">
                <div class="form-group">
                    {!! Form::label('','Calculando') !!}
                    {!! Form::text('ghost_camp', $fluxo_de_caixa->ghost_camp, ['class' => 'form-control' , "id" => "input_ghost_camp",'disabled' => 'disabled',]) !!}
                </div>
            </div>

            <div size="4" class="inputbox col-md-4">
                <div class="form-group">
                    {!! Form::label('','Saldo da Transação') !!}
                    {!! Form::text('saldo_da_transacao', $fluxo_de_caixa->saldo_da_transacao, ['class' => 'form-control' , "id" => "input_saldo_da_transacao",'disabled' => 'disabled',]) !!}
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
