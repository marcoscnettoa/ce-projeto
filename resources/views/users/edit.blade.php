@extends('layouts.app')

@section('content')

@php

    $controller = get_class(\Request::route()->getController());

    if(env('FILESYSTEM_DRIVER') == 's3')
    {
        $fileurlbase = env('URLS3') . '/' . env('FILEKEY') . '/';
    }
    else
    {
        $fileurlbase = env('APP_URL') . '/';
    }
@endphp

<h3 class="box-title" style="margin-left: 15px;">Editar usuário</h3>

<section class="content">

<div class="box">

    {!! Form::open(['url' => 'users/'.$user->id, 'method' => 'put', 'enctype' => 'multipart/form-data', 'accept-charset' => 'utf-8']) !!}

        <div class="box-body">

            {!! Form::hidden('id', $user->id) !!}

            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <div class="input text">
                            {!! Form::label('Nome') !!}
                            {!! Form::text('name', $user->name, ['class' => 'form-control']) !!}
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <div class="input email required">
                            {!! Form::label('E-mail') !!}
                            {!! Form::email('email', $user->email, ['class' => 'form-control']) !!}
                        </div>
                    </div>
                </div>
            </div>

            {{-- # - --}}
            @if($user->image)
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            @if($user->image && count(explode(".", $user->image)) >= 2)
                                <a class="fancybox" rel="gallery1" target="_blank" href="{{ $fileurlbase . "images/" . $user->image }}">
                                    <img src="{{ $fileurlbase . "images/" . $user->image }}" width="100">
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

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
                            {!! Form::text('profession', $user->profession, ['class' => 'form-control']) !!}
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <div class="input text required">
                            {!! Form::label('Perfil') !!}
                            {!! Form::select('profile_id', $profiles, $user->profile_id, ['class' => 'form-control', 'required' => 'required']) !!}
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <div class="input text required">
                            {!! Form::label('Usuário') !!}
                            {!! Form::text('username', $user->username, ['class' => 'form-control', 'required' => 'required']) !!}
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
                @if(\App\Models\Permissions::permissaoUsuario(\Auth::user(), "$controller@update"))
                    <button type="submit" class="btn btn-default right form-group-btn-edit-salvar">
                        <span class="glyphicon glyphicon-ok"></span> Salvar
                    </button>
                @endif
            </div>

        </div>

    {!! Form::close() !!}

</div>

</section>

@endsection
