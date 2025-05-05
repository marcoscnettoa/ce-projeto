@extends('layouts.app')

@section('content')

@php
    
    $controller = get_class(\Request::route()->getController());

@endphp

<h3 class="box-title" style="margin-left: 15px;">Cadastrar perfil</h3>

<section class="content">

<div class="box">

    {!! Form::open(['url' => 'profiles', 'method' => 'post', 'enctype' => 'multipart/form-data', 'accept-charset' => 'utf-8']) !!}

        <div class="box-body">
    
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <div class="input text">
                            {!! Form::label('Perfil') !!}
                            {!! Form::text('name', null, ['class' => 'form-control', 'required' => 'required']) !!}
                        </div>
                    </div>
                </div>
            </div>

            <div class="row" style="margin-top: 0px;">
                <div class="col-md-12">
                    {!! Form::Label('admin', 'Acesso de administrador') !!}
                    {!! Form::select('administrator', [ 0 => 'Não', 1 => 'Sim' ], null, ['class' => 'form-control']) !!}
                </div>
            </div>

            <div class="row" style="margin-top: 10px;">
                <div class="col-md-12">
                    {!! Form::Label('master', 'Pode ver registros de outros usuários') !!}
                    {!! Form::select('moderator', [ 0 => 'Não', 1 => 'Sim' ], null, ['class' => 'form-control']) !!}
                </div>
            </div>

            <br>
            <br>

            <div class="form-group">
                <a href="{{ URL::previous() }}" class="btn btn-default form-group-btn-add-voltar"><i class="glyphicon glyphicon-backward"></i> Voltar</a>
                @if(\App\Models\Permissions::permissaoUsuario(\Auth::user(), "$controller@store"))
                    <button type="submit" class="btn btn-default right form-group-btn-add-cadastrar">
                        <span class="glyphicon glyphicon-plus"></span> Cadastrar
                    </button>
                @endif
            </div>

        </div>

    {!! Form::close() !!}

</div>

</section>

@endsection