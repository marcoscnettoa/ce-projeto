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

<section class="content-header Grid Passageiros_show">
    <h1>Grid Passageiros </h1>
    <!--@if(!$isPublic)
    <ol class="breadcrumb">
        <li><a href="{{ URL('/') }}">Home</a></li>
        <li><a href="{{ URL('/') }}/grid_passageiros">Grid Passageiros</a></li>
        <li class="active">#{{$grid_passageiros->id}}</li>
    </ol>
    @endif-->
</section>

<section class="content Grid Passageiros_show">

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

                        <div size="3" class="inputbox col-md-3">
                <div class="form-group">
                    {!! Form::label('','Passageiros') !!}
                    {!! Form::select('passageiros', $passageiro_nome, $grid_passageiros->passageiros, ['class' => 'form-control  select_relationship ', 'data-sub' => '', 'data-live-search' => 'true',  'data-none-selected-text' => true, 'controller' => 'passageiro' , "id" => "input_passageiros", "style" => "text-transform: uppercase;", "oninput" => "this.value = this.value.toUpperCase();",'disabled' => 'disabled',]) !!}
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
