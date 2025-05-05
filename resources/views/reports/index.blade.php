@extends('layouts.app')

@section('content')

@php

    $controller = get_class(\Request::route()->getController());

@endphp

<h3 class="box-title" style="margin-left: 15px;">Relatórios</h3>

<section class="content">

<div class="box">

    <div class="box-body table-responsive">

        <table id="datatable-master" class="display table table-striped table-bordered" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nome do Relatório</th>
                    <th>Tamanho</th>
                    <th>Altura</th>
                    <th>Largura</th>
                    <th></th>
                </tr>
            </thead>

            <tbody>

                @foreach($reports as $value)

                <tr>
                    <td> {{$value->id}} </td>
                    <td> {{$value->name}} </td>
                    <td> {{$value->Get_tipo_size()}} </td>
                    <td> {{(!empty($value->size_height)?$value->size_height:'---')}} </td>
                    <td> {{(!empty($value->size_width)?$value->size_width:'---')}} </td>
                    <td style="width:100px; white-space: nowrap;">

                        @if(\App\Models\Permissions::permissaoUsuario(\Auth::user(), "$controller@generate"))
                            <a style="float:none;" href="{{ URL('/') }}/reports/generate/{{$value->id}}?tipo=pdf" alt="Gerar Relatório PDF" title="Gerar Relatório PDF" class="btn btn-xs btn-default">
                                <span class="fa fa-file-pdf-o"></span>
                            </a>
                        @endif

                        @if(\App\Models\Permissions::permissaoUsuario(\Auth::user(), "$controller@generate"))
                            <a style="float:none;" href="{{ URL('/') }}/reports/generate/{{$value->id}}?tipo=excel" alt="Gerar Relatório Excel" title="Gerar Relatório Excel" class="btn btn-xs btn-default">
                                <span class="fa fa-file-excel-o"></span>
                            </a>
                        @endif

                        @if(\App\Models\Permissions::permissaoUsuario(\Auth::user(), "$controller@generate"))
                            <a style="float:none;" href="{{ URL('/') }}/reports/{{$value->id}}" alt="Gerar relatório" title="Gerar relatório" class="btn btn-xs btn-default">
                                <span class="glyphicon glyphicon-print"></span>
                            </a>
                        @endif

                        @if(\App\Models\Permissions::permissaoUsuario(\Auth::user(), "$controller@edit"))
                            <a style="float:none;" href="{{ URL('/') }}/reports/{{$value->id}}/edit" alt="Editar" title="Editar" class="btn btn-xs btn-default">
                                <span class="glyphicon glyphicon-edit"></span>
                            </a>
                        @endif

                        @if(\App\Models\Permissions::permissaoUsuario(\Auth::user(), "$controller@destroy"))
                            <form style="display:inline-block;" method="POST" action="{{ route('reports.destroy', $value->id) }}" accept-charset="UTF-8">
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
