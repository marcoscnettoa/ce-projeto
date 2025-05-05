@extends('layouts.app')

@section('content')

@php

    $controller = get_class(\Request::route()->getController());

@endphp

<h3 class="box-title" style="margin-left: 15px;">Perfis de acesso</h3>

<section class="content">

<div class="box">

    <div class="box-body table-responsive">

        @if(\App\Models\Permissions::permissaoUsuario(\Auth::user(), '$controller@defaultProfile') && env('ENV_ENABLE_CADASTRO'))
            <div class="form-group">
                <div class="col-md-12">
                    <div class="col-md-12">
                        <label for="">Informe um perfil padrão</label>
                        <p> Este perfil será usado para os cadastros através do formulário público (se estiver habilitado). </p>


                            {!! Form::open(['url' => 'profiles/default', 'method' => 'post', 'enctype' => 'multipart/form-data', 'accept-charset' => 'utf-8']) !!}

                            <div class="row">

                                <div class="col-md-2">
                                    <select name="default" class="form-control">
                                        <?php  foreach ($profiles as $key => $value) {  ?>
                                            <option value="<?php echo $value->id; ?>" <?php echo ($value->default) ? 'selected="selected"': ''; ?> ><?php  echo $value->name; ?></option>
                                        <?php } ?>
                                    </select>
                                </div>

                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-default form-group-btn-edit-salvar">
                                        <span class="glyphicon glyphicon-ok"></span> Salvar
                                    </button>
                                </div>

                            </div>

                            <hr>

                            {!! Form::close() !!}

                    </div>
                    <div class="col-md-2"></div>
                </div>
            </div>

            <br>
            <br>
        @endif

        <table id="datatable" class="display table table-striped table-bordered" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Perfil</th>
                    <th>Acesso de administrador</th>
                    <th>Pode ver dados de outros usuários</th>
                    <!--<th>Padrão</th>-->
                    <th></th>
                </tr>
            </thead>

            <tbody>

                @foreach($profiles as $value)

                <tr>
                    <td> {{$value->id}} </td>

                    <td> {{$value->name}} </td>
                    <td> {{ ( $value->administrator ? 'Sim' : 'Não') }} </td>
                    <td> {{ ( $value->moderator ? 'Sim' : 'Não') }} </td>
                    <!--<td> {{ ( $value->default ? 'Sim' : 'Não') }} </td>-->

                    <td style="width:100px; white-space: nowrap;">

                        @if(\App\Models\Permissions::permissaoUsuario(\Auth::user(), "$controller@edit"))
                            <a style="float:none;" href="{{ URL('/') }}/profiles/{{$value->id}}/edit" alt="Editar" title="Editar" class="btn btn-xs btn-default">
                                <span class="glyphicon glyphicon-edit"></span>
                            </a>
                        @endif

                        @if(\App\Models\Permissions::permissaoUsuario(\Auth::user(), "App\Http\Controllers\PermissionsController@store"))
                            <a style="float:none;" href="{{ URL('/') }}/permissions/profile/{{$value->id}}" alt="Permissões" title="Permissões" class="btn btn-xs btn-default">
                                <span class="glyphicon glyphicon-lock"></span>
                            </a>
                        @endif

                        @if(\App\Models\Permissions::permissaoUsuario(\Auth::user(), "$controller@destroy"))
                            <form style="display:inline-block;" method="POST" action="{{ route('profiles.destroy', $value->id) }}" accept-charset="UTF-8">
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
