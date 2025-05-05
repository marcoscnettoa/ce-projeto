@extends('layouts.app')

@section('content')

@php

    $controller = get_class(\Request::route()->getController());

@endphp

<section class="content">

<section class="content-header">
    <h1>Permissões de acesso (Módulos/Ações)</h1>
</section>

<div class="boxcontent">

    <div class="box-body">

        {!! Form::open(['url' => 'permissions', 'method' => 'post']) !!}

        {!! Form::hidden('user_id', $user_id) !!}
        {!! Form::hidden('profile_id', $profile_id) !!}

        <div class="col-md-12">

            @foreach($lista as $key => $value)

                @php
                    // #
                    $label = str_replace('App\Http\Controllers', '', $value);
                    $label = explode('Controller@', $label);
                    if(!isset($label[1])) { continue; }
                    // - #

                    if($label[0] == 'Laravel\Sanctum\Http\Controllers\CsrfCookie' OR $label[0] == '\Backups'){
                        continue;
                    }

                @endphp

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">

                            @php

                                if(!isset($modulo) or (isset($modulo) && $modulo != $label[0]))
                                {
                                    $modulo = $label[0];

                                    switch ($modulo) {

                                        case '\Users':
                                            $title = 'Usuário';
                                            break;

                                        case '\Reports':
                                            $title = 'Relatórios';
                                            break;

                                        case '\Profiles':
                                            $title = 'Perfis de acesso';
                                            break;

                                        case '\Permissions':
                                            $title = 'Permissões';
                                            break;

                                        case '\Logs':
                                            $title = 'Logs';
                                            break;

                                        case '\Indicators':
                                            $title = 'Indicadores';
                                            break;

                                        case '\LinkPagamento':
                                            $title = 'Link de Pagamento';
                                            break;

                                        default:
                                            $title = $modulo;
                                            break;
                                    }

                                    echo '<div class="alert alert-warning" role="alert">';
                                    echo '<h4>' . $title . '</h4>';
                                    echo '</div>';
                                }

                                switch ($label[1]) {

                                    case 'index':
                                        $label[1] = 'Listar';
                                        break;

                                    case 'store':
                                        $label[1] = 'Cadastrar';
                                        break;

                                    case 'update':
                                        $label[1] = 'Editar';
                                        break;

                                    case 'destroy':
                                        $label[1] = 'Deletar';
                                        break;

                                    case 'show':
                                        $label[1] = 'Visualizar';
                                        break;

                                    case 'generate':
                                        $label[1] = 'Gerar relatório';
                                        break;

                                    case 'perfil':
                                        $label[1] = 'Editar próprio perfil';
                                        break;

                                    case 'defaultProfile':
                                        $label[1] = 'Mudar perfil padrão';
                                        break;

                                    case 'copy':
                                        $label[1] = 'Duplicar linha';
                                        break;

                                    case 'pdf':
                                        $label[1] = 'Gerar PDF';
                                        break;

                                    case 'ajax':
                                        $label[1] = 'Auto Completar';
                                        break;

                                    case 'filter':
                                        $label[1] = 'Utilizar filtros de pesquisa';
                                        break;

                                    case 'modal':
                                        $label[1] = 'Modal';
                                        break;
                                }

                            @endphp

                            <ol>
                                <div class="alert alert-info" role="alert">

                                @if (in_array($value, $perms->toArray()))
                                    {!! Form::checkbox('permissions[]', $value, true, ['class' => 'simple']) !!} {{$label[1]}} <br>
                                @else
                                    {!! Form::checkbox('permissions[]', $value, false, ['class' => 'simple']) !!} {{$label[1]}} <br>
                                @endif

                                </div>

                            </ol>

                        </div>
                    </div>
                </div>
            @endforeach

        </div>

        <br>
        <br>

        <div class="form-group">
            @if(\App\Models\Permissions::permissaoUsuario(\Auth::user(), "$controller@store"))
                <button type="submit" class="btn btn-xs btn-default right form-group-btn-add-cadastrar"><span class="glyphicon glyphicon-plus"></span> Cadastrar</button>
            @endif
        </div>

        {!! Form::close() !!}

    </div>

</div>

</section>

@endsection
