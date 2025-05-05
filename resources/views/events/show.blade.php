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

    .noprint {
        display: none !important;
    }

    @media print {
        a[href]::after {
            content: none !important;
        }
    }

</style>

@section('style')

    <style type="text/css">

    </style>

@endsection

<section class="content-header Events_show">
    <h1>Events </h1>
    @if(!$isPublic)
    <ol class="breadcrumb">
        <li><a href="{{ URL('/') }}">Home</a></li>
        <li><a href="{{ URL('/') }}/events">Events</a></li>
        <li class="active">#{{$events->id}}</li>
    </ol>
    @endif
</section>

<section class="content">

    <div class="box">

        <div class="box-logo">
            <img src="https://dashboard.xxxxrxxxapps.com/images/b-6b387ebbcb8020ce186644d4a4669c6a.png" style="height: 100px; margin-left: 10px;">
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

            <div size="12" class="inputbox col-md-12">
                <div class="form-group">
                    {!! Form::label('','Evento') !!}
                    {!! Form::text('title', $events->title, ['class' => 'form-control' , "id" => "input_title",'disabled' => 'disabled',]) !!}
                </div>
            </div>

            <div size="12" class="inputbox col-md-12">
                <div class="form-group">
                    {!! Form::label('','Data Início') !!}
                    {!! Form::text('start_date', $events->start_date, ['class' => 'form-control componenteDataHora' , "id" => "input_start_date",'disabled' => 'disabled',]) !!}
                </div>
            </div>

            <div size="12" class="inputbox col-md-12">
                <div class="form-group">
                    {!! Form::label('','Data Fim') !!}
                    {!! Form::text('end_date', $events->end_date, ['class' => 'form-control componenteDataHora' , "id" => "input_end_date",'disabled' => 'disabled',]) !!}
                </div>
            </div>

            <div size="12" class="inputbox col-md-12">
                <div class="form-group">
                    {!! Form::label('','Dia inteiro?') !!}
                    {!! Form::checkbox('is_all_day', null, $events->is_all_day, ['class' => '' , "id" => "input_is_all_day",'disabled' => 'disabled',]) !!}
                </div>
            </div>

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
