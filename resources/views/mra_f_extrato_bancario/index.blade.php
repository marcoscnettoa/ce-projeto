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
        <h1>Financeiro - Extrato Bancários</h1>
    </section>
    <section class="content">
        <div class="box-header">
            <div class="row">
                <div class="col-md-4">
                    <div class="small-box v2 bg-success">
                        <div class="inner" style="color: #000;">
                            @php
                                $valor_entradas_get = \App\Models\MRAFExtratoBancario::Get_ValorTotal_Entradas();
                                $valor_entradas     = \App\Helper\Helper::H_Decimal_DB_ptBR($valor_entradas_get);
                                $valor_entradas     = (!empty($valor_entradas)?$valor_entradas:'0,00');
                            @endphp
                            <h3>R$ {{ $valor_entradas }}</h3>
                            <p><i class="glyphicon glyphicon-arrow-down text-success"></i> Entradas</p>
                            <i class="glyphicon glyphicon-arrow-down flo"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="small-box v2 bg-danger">
                        <div class="inner" style="color: #000;">
                            @php
                                $valor_saidas_get = \App\Models\MRAFExtratoBancario::Get_ValorTotal_Saidas();
                                $valor_saidas     = \App\Helper\Helper::H_Decimal_DB_ptBR($valor_saidas_get);
                                $valor_saidas     = (!empty($valor_saidas)?$valor_saidas:'0,00');
                            @endphp
                            <h3>R$ {{ $valor_saidas }}</h3>
                            <p><i class="glyphicon glyphicon-arrow-up text-danger"></i> Saídas</p>
                            <i class="glyphicon glyphicon-arrow-up flo"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="small-box v2 bg-default">
                        <div class="inner" style="color: #000;">
                            @php
                                $valor_total = \App\Helper\Helper::H_Decimal_DB_ptBR($valor_entradas_get - $valor_saidas_get);
                                $valor_total = (!empty($valor_total)?$valor_total:'0,00');
                            @endphp
                            <h3>R$ {{ $valor_total }}</h3>
                            <p><i class="glyphicon glyphicon-arrow-down text-success"></i><i class="glyphicon glyphicon-arrow-up text-danger"></i> Total</p>
                            <i class="glyphicon glyphicon-sort flo"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {{--@if($exibe_filtros)--}}
        <div class="box-header" style="background-color: #fff; padding-top: 30px">
            <form action="{{ URL('/') }}/mra_fluxo_financeiro/mra_f_extrato_bancario/filter" method="POST">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <div class="row">
                    <div size="3" class="inputbox col-md-3">
                        <div class="form-group ss-btn">
                            {!! Form::label('', 'Data do Fluxo') !!}
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
                            {!! Form::label('','Cliente') !!}
                            {!! Form::select('mra_f_clientes_id', \App\Models\MRAFClientes::lista_clientes(), (Request::get('mra_f_clientes_id')?Request::get('mra_f_clientes_id'):null), ['class' => 'form-control select_single_no_trigger', 'data-live-search' => 'true', "id" => "input_mra_f_clientes_id"]) !!}
                        </div>
                    </div>
                    <div size="3" class="inputbox col-md-3">
                        <div class="form-group">
                            {!! Form::label('','Fornecedor') !!}
                            {!! Form::select('mra_f_fornecedores_id', \App\Models\MRAFFornecedores::lista_fornecedores(), (Request::get('mra_f_fornecedores_id')?Request::get('mra_f_fornecedores_id'):null), ['class' => 'form-control select_single_no_trigger', 'data-live-search' => 'true', "id" => "input_mra_f_fornecedores_id"]) !!}
                        </div>
                    </div>
                    <div size="3" class="inputbox col-md-3">
                        <div class="form-group">
                            {!! Form::label('','Conta Bancária') !!}
                            {!! Form::select('mra_f_contas_bancarias_id', \App\Models\MRAFContasBancarias::Get_ContasBancarias_options(), (Request::get('mra_f_contas_bancarias_id')?Request::get('mra_f_contas_bancarias_id'):null), ['class' => 'form-control select_single_no_trigger', 'data-live-search' => 'true', "id" => "input_mra_f_contas_bancarias_id"]) !!}
                        </div>
                    </div>
                    <div size="3" class="inputbox col-md-3">
                        <div class="form-group">
                            {!! Form::label('','Tipo de Movimentação') !!}
                            {!! Form::select('tipo', \App\Models\MRAFExtratoBancario::Get_options_tipos(), (Request::get('tipo')?Request::get('tipo'):null), ['class' => 'form-control select_single_no_trigger', 'data-live-search' => 'true', "id" => "input_tipo"]) !!}
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
                        <a href="{{ URL('/mra_fluxo_financeiro/mra_f_extrato_bancario') }}" class="btn btn-default" style="float: left; margin-right: 5px;"><i class="glyphicon glyphicon-trash"></i> Limpar</a>
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

                <table id="<?php echo (!$export_enable_btns ? 'datatable-no-buttons' : 'datatable'); ?>" class="table-st1 display table-striped table-bordered stripe" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            {{--<th style="white-space: nowrap;">Código</th>--}}
                            <th style="white-space: nowrap;">Data do Extrato</th>
                            <th style="white-space: nowrap;">Cliente / Fornecedor</th>
                            <th style="white-space: nowrap;">Conta Bancária</th>
                            <th style="white-space: nowrap;">Descrição</th>
                            <th style="white-space: nowrap;">Tipo</th>
                            <th style="white-space: nowrap;">Valor</th>
                            {{--<th style="white-space: nowrap;">Status</th>--}}
                            @if($auth_user__actions_enable_btns)
                            <th style="border: none; <?php /*echo (0) ? 'min-width: 160px;' : 'min-width: 130px;'*/ ?>"></th>
                            @endif
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($MRAFExtratoBancario as $value)
                            {{--@php
                                $status    = '';
                                $status_tr = '';
                                switch($value->status){
                                    case 1: $status = 'badge-success'; $status_tr = 'bg-success'; break;
                                    case 2: $status = 'badge-warning'; $status_tr = 'bg-warning'; break;
                                }
                            @endphp--}}
                            @php
                                $tipo_i     = '';
                                $status_tr  = '';
                                switch($value->tipo){
                                    case 1: $tipo_i = '<i class="glyphicon glyphicon-arrow-down text-success"></i> '; $status_tr = 'bg-success'; break;
                                    case 2: $tipo_i = '<i class="glyphicon glyphicon-arrow-up text-danger"></i> '; $status_tr = 'bg-danger'; break;
                                }
                            @endphp
                            <tr class="{{$status_tr}}">
                                {{--<td>{{$value->id}}</td>--}}
                                <td>{{ date('d/m/Y H:i',strtotime($value->created_at)) }}</td>
                                @php
                                    $cliente_fornecedor = '---';
                                    if(!is_null($value->mra_f_clientes_id)){
                                        $cliente_fornecedor = ($value->MRAFClientes?$value->MRAFClientes->nome:'---');
                                    }elseif(!is_null($value->mra_f_fornecedores_id)){
                                        $cliente_fornecedor = ($value->MRAFFornecedores?$value->MRAFFornecedores->nome:'---');
                                    }
                                @endphp
                                <td style="white-space: nowrap;">{{$cliente_fornecedor}}</td>
                                <td style="white-space: nowrap;">{{($value->MRAFContasBancarias?$value->MRAFContasBancarias->nome:'---')}}</td>
                                <td>{{(!empty($value->descricao)?$value->descricao:'---')}}</td>
                                <td>{!! $tipo_i . (!empty($value->tipo)?\App\Models\MRAFExtratoBancario::Get_tipos($value->tipo):'---') !!}</td>
                                <td>{{(!empty($value->valor)?\App\Helper\Helper::H_Decimal_DB_ptBR($value->valor):'0,00')}}</td>
                                {{--<td><span class="badge {{$status}} fw-600">{{ \App\Models\MRAFExtratoBancario::Get_status($value->status) }}</span></td>--}}
                                @if($auth_user__actions_enable_btns)
                                    <td>
                                        {{--@if($permissaoUsuario_auth_user__controller_update)
                                            <a style="float: left; margin-right: 5px;" href="{{ URL('/') }}/mra_fluxo_financeiro/mra_f_extrato_bancario/{{$value->id}}/edit" alt="Editar" title="Editar" class="btn btn-default">
                                                <span class="glyphicon glyphicon-edit"></span>
                                            </a>
                                        @endif--}}
                                        {{--@if(0)
                                            @if($permissaoUsuario_auth_user__controller_copy)
                                                <a style="float: left; margin-right: 5px;" href="{{ URL('/') }}/mra_fluxo_financeiro/mra_f_extrato_bancario/{{$value->id}}/copy" alt="Duplicar linha" title="Duplicar linha" class="btn btn-default" onclick="return confirm('Tem certeza que quer duplicar a linha?')" >
                                                    <span class="glyphicon glyphicon-copy"></span>
                                                </a>
                                            @endif
                                        @endif--}}
                                        {{--@if($permissaoUsuario_auth_user__controller_show)
                                            <a style="float: left; margin-right: 5px;" href="{{ URL('/') }}/mra_fluxo_financeiro/mra_f_extrato_bancario/{{$value->id}}" alt="Visualizar" title="Visualizar" class="btn btn-default">
                                                <span class="glyphicon glyphicon-eye-open"></span>
                                            </a>
                                        @endif--}}
                                        @if($permissaoUsuario_auth_user__controller_destroy)
                                            <form style="float: left; margin-right: 5px;" method="POST" action="{{ route('mra_f_extrato_bancario.destroy', $value->id) }}" accept-charset="UTF-8">
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
                    {{--@if(\App\Models\Permissions::permissaoUsuario(\Auth::user(), "$controller@store"))
                        <a href="{{ URL('/') }}/mra_fluxo_financeiro/mra_f_extrato_bancario/create" class="btn btn-default right form-group-btn-index-cadastrar" style="margin-left:15px;"><i class="glyphicon glyphicon-plus"></i> Cadastrar</a>
                    @endif--}}
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
