@php
    $isPublic               = 0;
    $controller             = get_class(\Request::route()->getController());
    $enable_kanban          = 0;
    $kanban_field           = '';
    $import_enable_btns     = 0;
    $export_enable_btns     = 1;
    $actions_enable_btns    = 1;
    $kanban_list            = array();

    if(env('FILESYSTEM_DRIVER') == 's3'){
        $fileurlbase = env('URLS3') . '/' . env('FILEKEY') . '/';
    }else {
        $fileurlbase = env('APP_URL') . '/';
    }
@endphp
@php
    $auth_user__actions_enable_btns                     =   false;
    $permissaoUsuario_auth_user__controller_update      =   false;
    $permissaoUsuario_auth_user__controller_copy        =   false;
    $permissaoUsuario_auth_user__controller_show        =   false;
    $permissaoUsuario_auth_user__controller_destroy     =   false;
    if(\Auth::user() && $actions_enable_btns){ $auth_user__actions_enable_btns = true; }
    if(\App\Models\Permissions::permissaoUsuario(\Auth::user(), "$controller@update")){  $permissaoUsuario_auth_user__controller_update     = true; }
    if(\App\Models\Permissions::permissaoUsuario(\Auth::user(), "$controller@copy")){    $permissaoUsuario_auth_user__controller_copy       = true; }
    if(\App\Models\Permissions::permissaoUsuario(\Auth::user(), "$controller@show")){    $permissaoUsuario_auth_user__controller_show       = true; }
    if(\App\Models\Permissions::permissaoUsuario(\Auth::user(), "$controller@destroy")){ $permissaoUsuario_auth_user__controller_destroy    = true; }
@endphp
@extends('layouts.app')
@section('content')
    @section('style')
        <style type="text/css">
            #datatable_wrapper .dataTables_paginate .paginate_button {
                margin-top: 20px;
                font-size: 12px;
            }
            #datatable_wrapper .dataTables_info {
                margin-top: 20px;
                font-size: 12px;
            }
            #datatable_wrapper .dataTables_filter {
                margin-bottom: 20px;
                font-size: 12px;
            }
            #datatable_wrapper .dataTables_length {
                margin-bottom: 10px;
                font-size: 12px;
            }
        </style>
    @endsection
    <section class="content-header">
        <h1>Financeiro - Fornecedores</h1>
    </section>
    <section class="content">
        {{--@if($exibe_filtros)--}}
        <div class="box-header" style="background-color: #fff; padding-top: 30px">
            <form action="{{ URL('/') }}/mra_fluxo_financeiro/mra_f_fornecedores/filter" method="POST">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <div class="row">
                    <div size="3" class="inputbox col-md-3">
                        <div class="form-group">
                            {!! Form::label('','Tipo de Pessoa') !!}
                            {!! Form::select('tipo', \App\Http\Controllers\MRA\MRAListas::Get_options_tipo_pessoa(), (Request::get('tipo')?Request::get('tipo'):null), ['class' => 'form-control select_single_no_trigger' , "id" => "input_tipo"]) !!}
                        </div>
                    </div>
                    <div size="3" class="inputbox col-md-3">
                        <div class="form-group">
                            {!! Form::label('','CNPJ') !!}
                            {!! Form::text('cnpj', (Request::get('cnpj')?Request::get('cnpj'):null), ['class' => 'form-control cnpj_v2', "placeholder"=>"__.___.___/____-__", "id" => "input_cnpj", "maxlength"=>50]) !!}
                        </div>
                    </div>
                    <div size="3" class="inputbox col-md-3">
                        <div class="form-group">
                            {!! Form::label('','CPF') !!}
                            {!! Form::text('cpf', (Request::get('cpf')?Request::get('cpf'):null), ['class' => 'form-control cpf', "placeholder"=>"___.___.___-__", "id" => "input_cpf", "maxlength"=>50]) !!}
                        </div>
                    </div>
                    <div size="3" class="inputbox col-md-3">
                        <div class="form-group">
                            {!! Form::label('','Status') !!}
                            {!! Form::select('status', \App\Http\Controllers\MRA\MRAListas::Get_options_status_ai(), ((Request::get('status') || Request::get('status') == 0)?Request::get('status'):null), ['class' => 'form-control select_single_no_trigger' , "id" => "input_status"]) !!}
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3 btnsFiltro">
                        <a href="{{ URL('/mra_fluxo_financeiro/mra_f_fornecedores') }}" class="btn btn-default" style="float: left; margin-right: 5px;"><i class="glyphicon glyphicon-trash"></i> Limpar</a>
                        <button type="submit" class="btn btn-default submitbtn" style="float: left;">
                            <span class="glyphicon glyphicon-search"></span> Pesquisar
                        </button>
                    </div>
                </div>
            </form>
        </div>
        {{--@endif--}}
        <div class="box">
            <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content"></div>
                </div>
            </div>
            <div class="box-body table-responsive">

                <table id="<?php echo (!$export_enable_btns ? 'datatable-no-buttons' : 'datatable'); ?>" class="display table-striped table-bordered stripe" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            {{--<th style="white-space: nowrap;">Código</th>--}}
                            <th style="white-space: nowrap;">Cliente</th>
                            <th style="white-space: nowrap;">Tipo</th>
                            <th style="white-space: nowrap;">CNPJ/CPF</th>
                            <th style="white-space: nowrap;">Status</th>
                            @if($auth_user__actions_enable_btns)
                            <th style="border: none; <?php echo (0) ? 'min-width: 160px;' : 'min-width: 130px;' ?>"></th>
                            @endif
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($MRAFFornecedores as $value)
                            <tr>
                                {{--<td>{{$value->id}}</td>--}}
                                <td>{{(!empty($value->nome)?$value->nome:'---')}}</td>
                                <td>{{(!empty($value->tipo)?\App\Http\Controllers\MRA\MRAListas::Get_tipo_pessoa($value->tipo):'---')}}</td>
                                @php
                                    $cnpj_cpf = '---';
                                    if($value->tipo=='F'){
                                        $cnpj_cpf = $value->cpf;
                                    }elseif($value->tipo=='J'){
                                        $cnpj_cpf = $value->cnpj;
                                    }
                                @endphp
                                <td>{{ $cnpj_cpf }}</td>
                                <td>{!! ($value->status?"<span class='badge badge-success fw-600'>Ativo</span>":"<span class='badge badge-danger fw-600'>Inativo</span>")!!}</td>
                                @if($auth_user__actions_enable_btns)
                                    <td>
                                        @if($permissaoUsuario_auth_user__controller_update)
                                            <a style="float: left; margin-right: 5px;" href="{{ URL('/') }}/mra_fluxo_financeiro/mra_f_fornecedores/{{$value->id}}/edit" alt="Editar" title="Editar" class="btn btn-default">
                                                <span class="glyphicon glyphicon-edit"></span>
                                            </a>
                                        @endif
                                        {{--@if(0)
                                            @if($permissaoUsuario_auth_user__controller_copy)
                                                <a style="float: left; margin-right: 5px;" href="{{ URL('/') }}/mra_fluxo_financeiro/mra_f_fornecedores/{{$value->id}}/copy" alt="Duplicar linha" title="Duplicar linha" class="btn btn-default" onclick="return confirm('Tem certeza que quer duplicar a linha?')" >
                                                    <span class="glyphicon glyphicon-copy"></span>
                                                </a>
                                            @endif
                                        @endif--}}
                                        {{--@if($permissaoUsuario_auth_user__controller_show)
                                            <a style="float: left; margin-right: 5px;" href="{{ URL('/') }}/mra_fluxo_financeiro/mra_f_fornecedores/{{$value->id}}" alt="Visualizar" title="Visualizar" class="btn btn-default">
                                                <span class="glyphicon glyphicon-eye-open"></span>
                                            </a>
                                        @endif--}}
                                        @if($permissaoUsuario_auth_user__controller_destroy)
                                            <form style="float: left; margin-right: 5px;" method="POST" action="{{ route('mra_f_fornecedores.destroy', $value->id) }}" accept-charset="UTF-8">
                                                {!! csrf_field() !!}
                                                {!! method_field('DELETE') !!}
                                                <button type="submit" onclick="return confirm('Tem certeza que quer deletar?')" class="btn btn-danger glyphicon glyphicon-trash"></button>
                                            </form>
                                        @endif
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <br>
                <br>
                <div class="form-group form-group-btn-index">
                    <a href="{{ URL::previous() }}" class="btn btn-default form-group-btn-index-voltar"><i class="glyphicon glyphicon-backward"></i> Voltar</a>
                    @if(\App\Models\Permissions::permissaoUsuario(\Auth::user(), "$controller@store"))
                        <a href="{{ URL('/') }}/mra_fluxo_financeiro/mra_f_fornecedores/create" class="btn btn-default right form-group-btn-index-cadastrar"><i class="glyphicon glyphicon-plus"></i> Cadastrar</a>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection
@section('script')
    @include('datatable', ['key' => 0, 'order' => 'desc'])
    <script type="text/javascript">
    </script>
@endsection
