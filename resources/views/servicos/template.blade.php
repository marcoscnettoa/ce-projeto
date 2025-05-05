@extends('layouts.app')

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
        .box-title{
            display: none;
        }
    }

</style>

<h3 class="box-title Serviços_add" style="margin-left: 15px;">Serviços</h3>

<section class="content">

    <div class="box">

        <div style="padding: 40px;">

            <?php

                eval("?> $template <?php ");
            ?>
        </div>

        <div class="box-body">

            <div class="col-md-12">
                <div class="form-group text-right no-print form-group-btn-show">
                    <a href="javascript:void(0);" onclick="printScreen();" class="btn btn-default form-group-btn-show-imprimir"><i class="glyphicon glyphicon-print"></i> Imprimir</a>
                </div>
            </div>

        </div>

    </div>

</section>

<script type="text/javascript">

    function printScreen(){
        window.print();
    }

</script>

@endsection
