@php

    $controller = get_class(\Request::route()->getController());

@endphp

@extends('layouts.app')

@section('content')

<section class="content-header Events_edit">
    <h1>Calendário </h1>
</section>

<section class="content">

    <div class="box">

        @if(\App\Models\Permissions::permissaoUsuario(\Auth::user(), "$controller@destroy"))
            <div class="row" style="position: absolute; right: 0; padding: 5px;">
                <div class="col-md-12">
                    <form method="POST" action="{{ route('events.destroy', $events->id) }}" accept-charset="UTF-8">
                        {!! csrf_field() !!}
                        {!! method_field('DELETE') !!}
                        <button type="submit" style="margin-left: 10px; margin-top: -1px;" onclick="return confirm('Tem certeza que quer deletar?')" class="btn btn-danger glyphicon glyphicon-trash"></button>
                    </form>
                </div>
            </div>
        @endif

        {!! Form::open(['url' => "events/$events->id", 'method' => 'put', 'novalidate'=> true, 'enctype' => 'multipart/form-data', 'accept-charset' => 'utf-8', 'id' => 'form_add_events']) !!}

            {!! Form::hidden('id', $events->id) !!}

            <div class="box-body" id="div_events">

                <div size="12" class="inputbox col-md-12">
                    <div class="form-group">
                        {!! Form::label('','Evento') !!}
                        {!! Form::text('title', $events->title, ['class' => 'form-control' , "id" => "input_title"]) !!}
                    </div>
                </div>

                <div size="6" class="inputbox col-md-6">
                    <div class="form-group">
                        {!! Form::label('','Início') !!}
                        {!! Form::text('start_date', $events->start_date, ['class' => 'form-control componenteDataHora' , "id" => "input_start_date"]) !!}
                    </div>
                </div>

                <div size="6" class="inputbox col-md-6">
                    <div class="form-group">
                        {!! Form::label('','Fim') !!}
                        {!! Form::text('end_date', $events->end_date, ['class' => 'form-control componenteDataHora' , "id" => "input_end_date"]) !!}
                    </div>
                </div>

                <div size="12" class="inputbox col-md-12">
                    <div class="form-group">
                        {!! Form::label('','Dia inteiro') !!}
                        {!! Form::checkbox('is_all_day', null, $events->is_all_day, ['class' => '' , "id" => "input_is_all_day"]) !!}
                    </div>
                </div>

                <div class="col-md-12" style="margin-top: 20px; margin-bottom: 20px;">

                    <div class="form-group form-group-btn-edit">

                        <a href="{{ URL::previous() }}" class="btn btn-default form-group-btn-add-voltar" style="float: left;"><i class="glyphicon glyphicon-backward"></i> Voltar</a>

                        @if(\App\Models\Permissions::permissaoUsuario(\Auth::user(), "$controller@update"))

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

@endsection