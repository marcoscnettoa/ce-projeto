@extends('layouts.app')

@section('content')

@php
    
    $controller = get_class(\Request::route()->getController());

@endphp

<section class="content">

<div class="box">

    <div class="box-header">
        <h3 class="box-title">Usuário #{{$user->id}}</h3>
    </div>

    <div class="box-body">
    
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <div class="input text">
                        {!! Form::label('Nome') !!}
                        {!! Form::text('name', $user->name, ['class' => 'form-control', 'disabled' => true]) !!}
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <div class="input email required">
                        {!! Form::label('E-mail') !!}
                        {!! Form::email('email', $user->email, ['class' => 'form-control', 'disabled' => true]) !!}
                    </div>
                </div>
            </div>
        </div>

        @if($user->image)
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <div class="input text required">    
                            <img src="{{ URL('/') }}/images/{{$user->image}}" width="100">
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <div class="input text required">
                        {!! Form::label('Profissão') !!}
                        {!! Form::text('profession', $user->profession, ['class' => 'form-control', 'disabled' => true]) !!}
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <div class="input text required">
                        {!! Form::label('Perfil') !!}
                        {!! Form::select('profile_id', $profiles, $user->profile_id, ['class' => 'form-control', 'disabled' => true]) !!}
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <div class="input text required">
                        {!! Form::label('Usuário') !!}
                        {!! Form::text('username', $user->username, ['class' => 'form-control', 'disabled' => true]) !!}
                    </div>
                </div>
            </div>
        </div>

        <br>
        <br>

        <div class="form-group text-left">
            <a href="{{ URL::previous() }}" class="btn btn-default form-group-btn-add-voltar"><i class="glyphicon glyphicon-backward"></i> Voltar</a>
            <a href="javascript:void(0);" onclick="printScreen();" class="btn btn-default right form-group-btn-show-imprimir"><i class="glyphicon glyphicon-print"></i> Imprimir</a>
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