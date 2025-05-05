@extends('layouts.app')

@section('content')

@php
    
    $controller = get_class(\Request::route()->getController());

@endphp

<section class="content">

<div class="box">

    <div class="box-header">
        <h3 class="box-title">Perfil #{{$profiles->id}}</h3>
    </div>

    <div class="box-body">

        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <div class="input text">
                        {!! Form::label('Perfil') !!}
                        {!! Form::text('name', $profiles->name, ['class' => 'form-control', 'disabled' => true]) !!}
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <div class="input text">
                {!! Form::Label('admin', 'Acesso de administrador') !!}
                {!! Form::select('administrator', [ 0 => 'Não', 1 => 'Sim' ], $profiles->administrator, ['class' => 'form-control', 'disabled' => true]) !!}
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <div class="input text">
                        {!! Form::Label('master', 'Pode ver registros de outros usuários') !!}
                        {!! Form::select('moderator', [ 0 => 'Não', 1 => 'Sim' ], $profiles->moderator, ['class' => 'form-control', 'disabled' => true]) !!}
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