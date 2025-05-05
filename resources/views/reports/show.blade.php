@extends('layouts.app')

@section('content')

@php

    $controller = get_class(\Request::route()->getController());

@endphp

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

    @media print {
        a[href]::after {
            content: none !important;
        }
        .listar { clear:both; }
    }
</style>

<section class="content">

    <div class="row">
        <div class="invoice" style="padding: 20px;">

            <img src="http://ffj_brasil.ffj_brasil.uxw.xxxxrxxx.com.br/images/{{ env('LOGO') }}" height="50" style="float: right;">

            <div class="row" style="padding: 20px;">
                <div class="col-12">
                    <h4>
                        {{$report->name}}
                    </h4>
                    <small class="float-right">Data: {{ date('d/m/Y') }}</small>
                </div>
            </div>
            <!-- Table row -->
            <div class="row" style="padding: 20px;">
                <div class="col-12 table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                @foreach($columns as $column)
                                    <th>{{$column}}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($query as $column)
                                <tr>
                                    @php
                                        $column = array_values((array)$column);
                                        echo '<td>' . implode('</td><td>', $column) . '</td>';
                                    @endphp
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <hr>

                <br>
                <br>

                <div class="form-group text-left">
                    <a href="{{ URL::previous() }}" class="btn btn-default form-group-btn-add-voltar"><i class="glyphicon glyphicon-backward"></i> Voltar</a>
                    <a href="javascript:void(0);" onclick="printScreen();" class="btn btn-default right form-group-btn-show-imprimir"><i class="glyphicon glyphicon-print"></i> Imprimir</a>
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
        window.print();
    }

</script>

@endsection
