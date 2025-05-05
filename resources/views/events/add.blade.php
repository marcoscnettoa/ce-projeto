@php

    $controller = get_class(\Request::route()->getController());

@endphp

@extends('layouts.app')

@section('content')

<section class="content-header Events_add">
    <h1>Calendário</h1>
</section>

<section class="content">

<div class="box">

    {!! Form::open(['url' => "events", 'method' => 'post', 'novalidate'=> true, 'enctype' => 'multipart/form-data', 'accept-charset' => 'utf-8', 'id' => 'form_add_events']) !!}

        <div class="box-body" id="div_events">

            <div size="12" class="inputbox col-md-12">
                <div class="form-group">
                    {!! Form::label('','Evento') !!}
                    {!! Form::text('title', null, ['class' => 'form-control' , "id" => "input_title"]) !!}
                </div>
            </div>

            <div size="6" class="inputbox col-md-6">
                <div class="form-group">
                    {!! Form::label('','Início') !!}
                    {!! Form::text('start_date', null, ['class' => 'form-control componenteDataHora' , "id" => "input_start_date"]) !!}
                </div>
            </div>

            <div size="6" class="inputbox col-md-6">
                <div class="form-group">
                    {!! Form::label('','Fim') !!}
                    {!! Form::text('end_date', null, ['class' => 'form-control componenteDataHora' , "id" => "input_end_date"]) !!}
                </div>
            </div>

            <div size="12" class="inputbox col-md-12">
                <div class="form-group">
                    {!! Form::label('','Dia inteiro?') !!}
                    {!! Form::checkbox('is_all_day', null, null, ['class' => '' , "id" => "input_is_all_day"]) !!}
                </div>
            </div>

            <div class="col-md-12" style="margin-top: 20px;">

                <div class="form-group form-group-btn-add">

                    <a href="{{ URL::previous() }}" class="btn btn-default form-group-btn-add-voltar"><i class="glyphicon glyphicon-backward"></i> Voltar</a>

                    @if(\App\Models\Permissions::permissaoUsuario(\Auth::user(), "$controller@store"))

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

@endsection