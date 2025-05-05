@extends('layouts.app')

@section('content')

<h3 class="box-title" style="margin-left: 15px;">Nova cobrança</h3>

<section class="content">

    <div class="box">
                        
        {!! Form::open(['url' => "link_pagamento", 'method' => 'post', 'enctype' => 'multipart/form-data', 'accept-charset' => 'utf-8']) !!}

            <div class="box-body">

                <div class="row">
                    
                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::label('', 'Produto / Serviço') !!}
                            {!! Form::text('product', null, ['class' => 'form-control', 'required' => 'required' ]) !!}
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::label('', 'Cliente') !!}
                            {!! Form::email('email', null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => 'Informe o e-mail do cliente' ]) !!}
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::label('', 'Valor') !!}
                            {!! Form::text('price', null, ['class' => 'form-control money', 'required' => 'required' ]) !!}
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('', 'Descrição') !!}
                            {!! Form::text('obs', null, ['class' => 'form-control', 'required' => 'required' ]) !!}
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="form-group">
                            {!! Form::label('', 'Vencimento') !!}
                            {!! Form::text('due_date', null, ['class' => 'form-control data', 'required' => 'required' ]) !!}
                        </div>
                    </div>

                    <div class="col-md-4" style="margin-top: 25px;">
                        <div class="form-group">
                            {!! Form::label('', 'Enviar e-mail para o cliente com link de pagamento') !!}
                            <br>
                            <input type="checkbox" name="sendmail" checked="checked">
                        </div>
                    </div>

                </div>

                <div class="form-group form-group-btn-add" style="float: right;">
                    <a href="{{ URL::previous() }}" class="btn btn-warning mr-1 form-group-btn-add-voltar" style="margin-right: 5px;">Voltar</a>
                    {!! Form::submit('Cadastrar', ['class' => 'btn btn-primary right form-group-btn-add-cadastrar']) !!}
                </div>

            </div>

        {!! Form::close() !!}
        
                    
    </div>

</section>

@endsection