@extends('layouts.app')

@section('content')

@php
    
    $controller = get_class(\Request::route()->getController());

@endphp

<h3 class="box-title" style="margin-left: 15px;">Cadastrar usuário</h3>

<section class="content">

<div class="box">

    {!! Form::open(['url' => 'users', 'method' => 'post', 'enctype' => 'multipart/form-data', 'accept-charset' => 'utf-8']) !!}

        <div class="box-body">

            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <div class="input text">
                            {!! Form::label('Nome') !!}
                            {!! Form::text('name', null, ['class' => 'form-control', 'required' => 'required']) !!}
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <div class="input email required">
                            {!! Form::label('E-mail') !!}
                            {!! Form::email('email', null, ['class' => 'form-control', 'required' => 'required']) !!}
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <div class="input file">
                            {!! Form::label('image', 'Foto do perfil') !!}
                            {!! Form::file('image', ['class' => 'form-control']) !!}
                        </div>
                    </div>
                </div>
            </div>

            <div class="row" style="display: none;">
                <div class="col-md-12">
                    <div class="form-group">
                        <div class="input text required">
                            {!! Form::label('Profissão') !!}
                            {!! Form::text('profession', null, ['class' => 'form-control']) !!}
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <div class="input text required">
                            {!! Form::label('Perfil') !!}
                            {!! Form::select('profile_id', $profiles, $profile_id, ['class' => 'form-control', 'required' => 'required']) !!}
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <div class="input text required">
                            {!! Form::label('Usuário') !!}
                            {!! Form::text('username', null, ['class' => 'form-control', 'required' => 'required']) !!}
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <div class="input password">
                            {!! Form::label('Senha') !!}
                            {!! Form::password('password', ['class' => 'form-control']) !!}
                        </div>
                    </div>
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