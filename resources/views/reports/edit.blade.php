@extends('layouts.app')

@section('content')

@php

    $controller = get_class(\Request::route()->getController());

@endphp

<h3 class="box-title" style="margin-left: 15px;">Editar relatório</h3>

<section class="content">

    <div class="box">

    {!! Form::open(['url' => "reports/$reports->id", 'method' => 'put', 'enctype' => 'multipart/form-data', 'accept-charset' => 'utf-8']) !!}

        <div class="box-body">

            {!! Form::hidden('id', $reports->id) !!}

            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <div class="input text">
                            {!! Form::label('name', 'Nome do Relatório') !!}
                            {!! Form::text('name', $reports->name, ['class' => 'form-control', 'placeholder'=>'Ex: Total de usuários', 'required'=> 'required']) !!}
                        </div>
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="form-group">
                        <div class="input text">
                            {!! Form::label('query', 'Script para o Relatório') !!}
                            {!! Form::textarea('query', $reports->query, ['class' => 'form-control', 'placeholder'=>'Ex: SELECT * FROM users;', 'required'=> 'required']) !!}
                        </div>
                    </div>
                </div>

                {{-- # - --}}
                <div size="3" class="inputbox col-md-3">
                    <div class="form-group">
                        {!! Form::label('','Tamanho') !!}
                        {!! Form::select('size', \App\Models\Reports::Get_options_tipo_size(), $reports->size, ['class' => 'form-control  select_single ', 'data-sub' => '', 'data-live-search' => 'true', 'id' => 'input_size' ]) !!}
                    </div>
                </div>

                <div id="box_size_height" class="col-md-3" style="display:none;">
                    <div class="form-group">
                        <div class="input text">
                            {!! Form::label('', 'Altura') !!}
                            {!! Form::number('size_height', $reports->size_height, ['class' => 'form-control', 'placeholder'=>'0', 'min'=>0]) !!}
                        </div>
                    </div>
                </div>

                <div id="box_size_width" class="col-md-3" style="display:none;">
                    <div class="form-group">
                        <div class="input text">
                            {!! Form::label('', 'Largura') !!}
                            {!! Form::number('size_width', $reports->size_width, ['class' => 'form-control', 'placeholder'=>'0', 'min'=>0]) !!}
                        </div>
                    </div>
                </div>
                {{-- - # --}}

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
@section('script')
    <script type="text/javascript">
        $("#input_size").on('change',function(){
            if($(this).val() == '3'){
                $("#box_size_height").show();
                $("#box_size_width").show();
            }else {
                $("#box_size_height").hide();
                $("#box_size_width").hide();
            }
        }).trigger("change");
    </script>
@endsection
@endsection
