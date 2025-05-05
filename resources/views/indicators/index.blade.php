@extends('layouts.app')

@section('content')

@php

    $controller = get_class(\Request::route()->getController());

@endphp

<h3 class="box-title" style="margin-left: 15px;">Indicadores</h3>

<section class="content">

<div class="box">

    <div class="box-body table-responsive">

        <table id="datatable" class="display table table-striped table-bordered" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Indicador</th>
                    <th>Cor</th>
                    <th>Tamanho</th>
                    <th>Icon</th>
                    <th>Link</th>
                    <th></th>
                </tr>
            </thead>

            <tbody>

                @foreach($indicators as $value)

                <tr>
                    <td> {{$value->id}} </td>

                    <td> {{$value->name}} </td>

                    <td>
                        <div style="width: 10px; height: 10px; background-color: {{$value->color}};"></div>
                    </td>

                    <td> {{$value->size}} </td>

                    <td> <i class="{{$value->glyphicon}}"></i> </td>

                    <td> {{$value->link}} </td>

                    <td style="width:100px; white-space: nowrap;">

                        @if(\App\Models\Permissions::permissaoUsuario(\Auth::user(), 'App\Http\Controllers\IndicatorsController@edit'))
                            <a style="float:none;" href="{{ URL('/') }}/indicators/{{$value->id}}/edit" alt="Editar" title="Editar" class="btn btn-xs btn-default">
                                <span class="glyphicon glyphicon-edit"></span>
                            </a>
                        @endif

                        @if(\App\Models\Permissions::permissaoUsuario(\Auth::user(), 'App\Http\Controllers\IndicatorsController@destroy'))
                            <form style="display:inline-block;" method="POST" action="{{ route('indicators.destroy', $value->id) }}" accept-charset="UTF-8">
                                {!! csrf_field() !!}
                                {!! method_field('DELETE') !!}
                                <button style="float:none;" type="submit" onclick="return confirm('Tem certeza que quer deletar?')" class="btn btn-xs btn-danger glyphicon glyphicon-trash">
                            </form>
                        @endif

                    </td>

                </tr>

                @endforeach

            </tbody>

        </table>

        <br>
        <br>

        <div class="form-group form-group-btn-index">
            <a href="{{ URL::previous() }}" class="btn btn-xs btn-default form-group-btn-index-voltar"><i class="glyphicon glyphicon-backward"></i> Voltar</a>
            @if(\App\Models\Permissions::permissaoUsuario(\Auth::user(), "$controller@store"))
                <a href="{{ URL::current() }}/create" class="btn btn-xs btn-default right form-group-btn-index-cadastrar"><i class="glyphicon glyphicon-plus"></i> Cadastrar</a>
            @endif
        </div>

    </div>

</div>

</section>

@endsection

@section('script')

    @include('datatable', ['key' => 0, 'order' => 'DESC'])

@endsection
