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
        <h1>Financeiro - Tranferência entre Contas</h1>
    </section>
    <section class="content">
       {{-- @if($exibe_filtros)--}}
        <div class="box-header" style="background-color: #fff; padding-top: 30px">
            <form action="{{ URL('/') }}/mra_fluxo_financeiro/mra_f_transf_contas/filter" method="POST">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <div class="row">
                    <div size="3" class="inputbox col-md-3">
                        <div class="form-group ss-btn">
                            {!! Form::label('', 'Data da Movimentação') !!}
                            <div class='row'>
                                <div class='col-md-6'>
                                    {!! Form::select(
                                        'operador[created_at]',
                                        ['contem' => 'Contém', 'entre' => 'Entre', '=' => '=', '>' => '>', '>=' => '>=', '<' => '<', '<=' => '<='],
                                        (Request::get('operador')?Request::get('operador')['created_at']:null),
                                        ['class' => 'form-control operador'],
                                    ) !!}
                                </div>
                                <div class='col-md-6' style='padding-left: 0px;'>
                                    {!! Form::text('created_at', (Request::get('created_at')?Request::get('created_at'):null), ['autocomplete' => 'off', 'class' => 'form-control componenteData', 'placeholder'=>'__/__/____']) !!}
                                    <p class='between' style='top: 16px; position:relative; display: none;'>
                                        {!! Form::label('', '&') !!} {!! Form::text('between[created_at]', (Request::get('between')?Request::get('between')['created_at']:null), [
                                            'autocomplete' => 'off',
                                            'class' => 'form-control componenteData','placeholder'=>'__/__/____'
                                        ]) !!}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div size="3" class="inputbox col-md-3">
                        <div class="form-group">
                            {!! Form::label('','Conta de Origem') !!}
                            {!! Form::select('mra_f_conta_ori_id', \App\Models\MRAFContasBancarias::Get_ContasBancarias_options(), (Request::get('mra_f_conta_ori_id')?Request::get('mra_f_conta_ori_id'):null), ['class' => 'form-control select_single_no_trigger', 'data-live-search' => 'true', "id" => "input_mra_f_conta_ori_id"]) !!}
                        </div>
                    </div>
                    <div size="3" class="inputbox col-md-3">
                        <div class="form-group">
                            {!! Form::label('','Conta de Destino') !!}
                            {!! Form::select('mra_f_conta_des_id', \App\Models\MRAFContasBancarias::Get_ContasBancarias_options(), (Request::get('mra_f_conta_des_id')?Request::get('mra_f_conta_des_id'):null), ['class' => 'form-control select_single_no_trigger', 'data-live-search' => 'true', "id" => "input_mra_f_conta_des_id"]) !!}
                        </div>
                    </div>
                    <div size="3" class="inputbox col-md-3">
                        <div class="form-group">
                            {!! Form::label('','Descrição') !!}
                            {!! Form::text('descricao', (Request::get('descricao')?Request::get('descricao'):null), ['class' => 'form-control',"id" => "input_descricao"]) !!}
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3 btnsFiltro">
                        <a href="{{ URL('/mra_fluxo_financeiro/mra_f_transf_contas') }}" class="btn btn-default" style="float: left; margin-right: 5px;"><i class="glyphicon glyphicon-trash"></i> Limpar</a>
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
                            <th style="white-space: nowrap;">Data da Movimentação</th>
                            <th style="white-space: nowrap;">Descrição</th>
                            <th style="white-space: nowrap;">Conta Origem</th>
                            <th style="white-space: nowrap;">Conta Destino</th>
                            <th style="white-space: nowrap;">Valor</th>
                            @if($auth_user__actions_enable_btns)
                            <th style="border: none; <?php /*echo (0) ? 'min-width: 160px;' : 'min-width: 130px;'*/ ?>"></th>
                            @endif
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($MRAFTransferenciaContas as $value)
                            <tr>
                                {{--<td>{{$value->id}}</td>--}}
                                <td>{{ date('d/m/Y H:i',strtotime($value->created_at)) }}</td>
                                <td>{{(!empty($value->descricao)?$value->descricao:'---')}}</td>
                                <td><i class="glyphicon glyphicon-arrow-up text-danger"></i> {{((!empty($value->mra_f_conta_ori_id) and $value->MRAFContasBancarias_origem)?$value->MRAFContasBancarias_origem->nome:'---')}}</td>
                                <td><i class="glyphicon glyphicon-arrow-down text-success"></i> {{((!empty($value->mra_f_conta_des_id) and $value->MRAFContasBancarias_destino)?$value->MRAFContasBancarias_destino->nome:'---')}}</td>
                                <td>{{(!empty($value->valor)?\App\Helper\Helper::H_Decimal_DB_ptBR($value->valor):'0,00')}}</td>
                                @if($auth_user__actions_enable_btns)
                                    <td>
                                        {{--@if($permissaoUsuario_auth_user__controller_update)
                                            <a style="float: left; margin-right: 5px;" href="{{ URL('/') }}/mra_fluxo_financeiro/mra_f_transf_contas/{{$value->id}}/edit" alt="Editar" title="Editar" class="btn btn-default">
                                                <span class="glyphicon glyphicon-edit"></span>
                                            </a>
                                        @endif--}}
                                        {{--@if(0)
                                            @if($permissaoUsuario_auth_user__controller_copy)
                                                <a style="float: left; margin-right: 5px;" href="{{ URL('/') }}/mra_fluxo_financeiro/mra_f_transf_contas/{{$value->id}}/copy" alt="Duplicar linha" title="Duplicar linha" class="btn btn-default" onclick="return confirm('Tem certeza que quer duplicar a linha?')" >
                                                    <span class="glyphicon glyphicon-copy"></span>
                                                </a>
                                            @endif
                                        @endif--}}
                                        {{--@if($permissaoUsuario_auth_user__controller_show)
                                            <a style="float: left; margin-right: 5px;" href="{{ URL('/') }}/mra_fluxo_financeiro/mra_f_transf_contas/{{$value->id}}" alt="Visualizar" title="Visualizar" class="btn btn-default">
                                                <span class="glyphicon glyphicon-eye-open"></span>
                                            </a>
                                        @endif--}}
                                        @if($permissaoUsuario_auth_user__controller_destroy)
                                            <form style="float: left; margin-right: 5px;" method="POST" action="{{ route('mra_f_transf_contas.destroy', $value->id) }}" accept-charset="UTF-8">
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
                        <a href="{{ URL('/') }}/mra_fluxo_financeiro/mra_f_transf_contas/create" class="btn btn-default right form-group-btn-index-cadastrar" style="margin-left:15px;"><i class="glyphicon glyphicon-plus"></i> Cadastrar</a>
                    @endif
                    {{--<a href="#" class="btn btn-info right form-group-btn-index-cadastrar" style="margin-left:15px;"><i class="glyphicon glyphicon-refresh"></i> Sincronizar Notazz</a>--}}
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
