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

<h3 class="box-title" style="margin-left: 15px;">Usuários</h3>

<section class="content">

<div class="box">

    <div class="box-body table-responsive">

        <table id="datatable" class="display table table-striped table-bordered" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Nome</th>
                    <th>E-mail</th>
                    <th>Usuário</th>
                    <th>Perfil</th>
                    <th>Imagem</th>
                    <th></th>
                </tr>
            </thead>

            <tbody>

                @foreach($users as $value)

                <tr>
                    <td> {{$value->id}} </td>

                    <td> {{$value->name}} </td>

                    <td> {{$value->email}} </td>

                    <td> {{$value->username}} </td>

                    <td> {{ (isset($value->perfil) ? $value->perfil->name : '') }} </td>

                    <td>
                        @if($value->image && count(explode(".", $value->image)) >= 2)
                            <a class="fancybox" rel="gallery1" target="_blank" href="{{in_array(explode(".", $value->image)[1], array("jpg", "jpeg", "gif", "png", "bmp", "mp4", "pdf", "doc", "docx", "rar", "zip", "txt", "7zip", "csv", "xls", "xlsx")) ? $fileurlbase . "images/" . $value->image : "javascript:void(0);"}}">
                                <img src="{{in_array(explode(".", $value->image)[1], array("mp4", "pdf", "doc", "docx", "rar", "zip", "txt", "7zip", "csv", "xls", "xlsx")) ? explode(".", $value->image)[1] . "-icon.png" : $fileurlbase . "images/" . $value->image}}" width="30">
                            </a>
                        @endif
                    </td>

                    <td style="width:100px; white-space: nowrap;">

                        @if(\App\Models\Permissions::permissaoUsuario(\Auth::user(), "$controller@edit"))
                            <a style="float:none;" href="{{ URL('/') }}/users/{{$value->id}}/edit" alt="Editar" title="Editar" class="btn btn-xs btn-default">
                                <span class="glyphicon glyphicon-edit"></span>
                            </a>
                        @endif

                        @if(\App\Models\Permissions::permissaoUsuario(\Auth::user(), "App\Http\Controllers\PermissionsController@store"))
                            <a style="float:none;" href="{{ URL('/') }}/permissions/user/{{$value->id}}" alt="Permissões" title="Permissões" class="btn btn-xs btn-default">
                                <span class="glyphicon glyphicon-lock"></span>
                            </a>
                        @endif

                        @if(\App\Models\Permissions::permissaoUsuario(\Auth::user(), "$controller@destroy"))
                            <form style="display:inline-block;"  method="POST" action="{{ route('users.destroy', $value->id) }}" accept-charset="UTF-8">
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
