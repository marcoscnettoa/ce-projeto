@extends('layouts.app')

@php
    $controller = get_class(\Request::route()->getController());
@endphp

@section('content')

<section class="content-header Events_index">
    <h1>Calendário</h1>
</section>

<section class="content">

    <div class="box">

        <div class="box-body table-responsive">

            <table id="datatable-no-buttons" class="display table-striped table-bordered stripe" cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th style="white-space: nowrap;">Evento</th>
                        <th style="white-space: nowrap;">Início</th>
                        <th style="white-space: nowrap;">Fim</th>
                        <th style="white-space: nowrap;">Dia inteiro</th>

                        <th></th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($events as $value)

                        <tr>

                            <td >{{$value->title}}</td>

                            <td data-order="{{ $value->start_date }}">{{(isset($value->start_date)) ? date("d/m/Y H:i", strtotime($value->start_date)) : ""}}</td>

                            <td data-order="{{ $value->end_date }}">{{(isset($value->end_date)) ? date("d/m/Y H:i", strtotime($value->end_date)) : ""}}</td>

                            <td >{{(isset($value->is_all_day) && $value->is_all_day) ? "Sim" : "Não"}}</td>

                            <td style="width:100px; white-space: nowrap;">

                                @if(\App\Models\Permissions::permissaoUsuario(\Auth::user(), "$controller@update"))
                                    <a style="float:none;" href="{{ URL('/') }}/events/{{$value->id}}/edit" alt="Editar" title="Editar" class="btn btn-default">
                                        <span class="glyphicon glyphicon-edit"></span>
                                    </a>
                                @endif

                                @if(\App\Models\Permissions::permissaoUsuario(\Auth::user(), "$controller@destroy"))
                                    <form style="display:inline-block;" method="POST" action="{{ route('events.destroy', $value->id) }}" accept-charset="UTF-8">
                                        {!! csrf_field() !!}
                                        {!! method_field('DELETE') !!}
                                        <button style="float:none;" type="submit" onclick="return confirm('Tem certeza que quer deletar?')" class="btn btn-danger glyphicon glyphicon-trash">
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
                <a href="{{ URL('/') }}/events/create" class="btn btn-xs btn-default right form-group-btn-index-cadastrar"><i class="glyphicon glyphicon-plus"></i> Cadastrar</a>
            </div>

        </div>

    </div>

</section>

@endsection

@section('script')

    @include('datatable', ['key' => 0, 'order' => 'desc'])

@endsection
