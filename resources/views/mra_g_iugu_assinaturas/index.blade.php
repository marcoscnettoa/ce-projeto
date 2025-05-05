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
        <h1>Gateway Iugu - Assinaturas</h1>
    </section>
    <section class="content">
        {{--@if($exibe_filtros)--}}
        <div class="box-header" style="background-color: #fff; padding-top: 15px">
            <div class="row">
                <div class="col-md-12">
                    <form action="{{ URL('/') }}/mra_g_iugu/mra_g_iugu_assinaturas/iugu_load" method="POST">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <input type="hidden" name="iugu_load" value="1">
                        <button type="submit" class="btn btn-info submitbtn" style="float: right;">
                            <span class="glyphicon glyphicon glyphicon-floppy-save"></span>&nbsp; Carregar Assinaturas Iugu
                        </button>
                    </form>
                </div>
            </div>
            <form action="{{ URL('/') }}/mra_g_iugu/mra_g_iugu_assinaturas/filter" method="POST" style="display:none;">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <div class="row">
                    {{--<div size="3" class="inputbox col-md-3">
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
                    </div>--}}
                    {{--<div size="3" class="inputbox col-md-3">
                        <div class="form-group">
                            {!! Form::label('','Status') !!}
                            {!! Form::select('status', \App\Http\Controllers\MRA\MRAListas::Get_options_status_ai(), ((Request::get('status') || Request::get('status') == 0)?Request::get('status'):null), ['class' => 'form-control select_single_no_trigger' , "id" => "input_status"]) !!}
                        </div>
                    </div>--}}
                </div>
                <div class="row">
                    <div class="col-md-3 btnsFiltro">
                        <a href="{{ URL('/mra_g_iugu/mra_g_iugu_assinaturas') }}" class="btn btn-default" style="float: left; margin-right: 5px;"><i class="glyphicon glyphicon-trash"></i> Limpar</a>
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
                            <th style="white-space: nowrap;" class="text-warning">ID Iugu Assinatura</th>
                            <th style="white-space: nowrap;">Cliente</th>
                            <th style="white-space: nowrap;">Plano</th>
                            <th style="white-space: nowrap;">Data de Expiração</th>
                            <th style="white-space: nowrap;">Ciclo/Cobrança</th>
                            {{--<th style="white-space: nowrap;">Suspenso</th>--}}
                            <th style="white-space: nowrap;">Status</th>
                            {{--<th style="white-space: nowrap;">Valor</th>
                            <th style="white-space: nowrap;">Intervalo</th>
                            <th style="white-space: nowrap;">Tipo Intervalo</th>
                            <th style="white-space: nowrap;">Dias Faturamento</th>
                            <th style="white-space: nowrap;">Formas de Pagamento</th>--}}
                            {{--<th style="white-space: nowrap;">Identificador Iugu</th>--}}
                            {{--<th style="white-space: nowrap;">Tipo de Pessoa</th>
                            <th style="white-space: nowrap;">CNPJ/CPF</th>
                            <th style="white-space: nowrap;">Status</th>--}}
                            @if($auth_user__actions_enable_btns)
                            <th style="border: none; <?php echo (0) ? 'min-width: 160px;' : 'min-width: 130px;' ?>"></th>
                            @endif
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($MRAGIuguAssinaturas as $value)
                            <tr>
                                <td>{{$value->iugu_subscriptions_id}}</td>
                                <td style="white-space: nowrap;">{{($value->MRAGIuguClientes?$value->MRAGIuguClientes->nome:'---')}}</td>
                                <td style="white-space: nowrap;">{{($value->MRAGIuguPlanos?$value->MRAGIuguPlanos->nome:'---')}}</td>
                                <td>{{!empty($value->iugu_expires_at)?date('d/m/Y',strtotime($value->iugu_expires_at)):'---'}}</td>
                                <td>{{!empty($value->iugu_cycled_at)?date('d/m/Y',strtotime($value->iugu_cycled_at)):'---'}}</td>
                                <td style="white-space: nowrap;">{!! (($value->iugu_suspended)?"<span class='badge badge-danger fw-600'>Suspenso</span>":"<span class='badge badge-success fw-600'>Ativo</span>") !!}</td>
                                {{--<td style="white-space: nowrap;">{!! (($value->iugu_active)?"<span class='badge badge-success fw-600'>Sim</span>":"<span class='badge badge-danger fw-600'>Não</span>") !!}</td>--}}
                                {{--<td>{{(!empty($value->nome)?$value->nome:'---')}}</td>--}}
                                {{--<td>{{(!empty($value->valor)?\App\Helper\Helper::H_Decimal_DB_ptBR($value->valor):'---')}}</td>
                                <td>{{(!empty($value->intervalo)?$value->intervalo:'---')}}</td>
                                <td>{{(!empty($value->intervalo_tipo)?\App\Http\Controllers\MRA\MRAGIugu::Get_tipo_intervalo($value->intervalo_tipo):'---')}}</td>
                                <td>{{(!empty($value->dias_ger_faturamento)?$value->dias_ger_faturamento:'---')}}</td>--}}
                                @php
                                    /*$formas_de_pagamento = [];
                                    if($value->fp_todos){
                                        $formas_de_pagamento[] = 'Todos';
                                    }else {
                                        if($value->fp_cartao_credito){
                                            $formas_de_pagamento[] = 'Cartão de Crédito';
                                        }
                                        if($value->fp_boleto){
                                            $formas_de_pagamento[] = 'Boleto';
                                        }
                                        if($value->fp_pix){
                                            $formas_de_pagamento[] = 'Pix';
                                        }
                                    }*/
                                @endphp
                                {{--<td>{{(!empty($formas_de_pagamento)?implode(", ",$formas_de_pagamento):'---')}}</td>--}}
                                {{--<td>{{$value->iugu_plan_identifier}}</td>--}}
                                {{--<td>{{(!empty($value->tipo)?\App\Http\Controllers\MRA\MRAListas::Get_tipo_pessoa($value->tipo):'---')}}</td>
                                @php
                                    $cnpj_cpf = '---';
                                    if($value->tipo=='F'){
                                        $cnpj_cpf = $value->cpf;
                                    }elseif($value->tipo=='J'){
                                        $cnpj_cpf = $value->cnpj;
                                    }
                                @endphp
                                <td>{{ $cnpj_cpf }}</td>--}}
                                {{--<td>{!! ($value->status?"<span class='badge badge-success fw-600'>Ativo</span>":"<span class='badge badge-danger fw-600'>Inativo</span>")!!}</td>--}}
                                @if($auth_user__actions_enable_btns)
                                    <td>
                                        @if($permissaoUsuario_auth_user__controller_update)
                                            <a style="float: left; margin-right: 5px;" href="{{ URL('/') }}/mra_g_iugu/mra_g_iugu_assinaturas/{{$value->id}}/edit" alt="Editar" title="Editar" class="btn btn-default">
                                                <span class="glyphicon glyphicon-edit"></span>
                                            </a>
                                        @endif
                                        {{--@if(0)
                                            @if($permissaoUsuario_auth_user__controller_copy)
                                                <a style="float: left; margin-right: 5px;" href="{{ URL('/') }}/mra_g_iugu/mra_g_iugu_assinaturas/{{$value->id}}/copy" alt="Duplicar linha" title="Duplicar linha" class="btn btn-default" onclick="return confirm('Tem certeza que quer duplicar a linha?')" >
                                                    <span class="glyphicon glyphicon-copy"></span>
                                                </a>
                                            @endif
                                        @endif--}}
                                        {{--@if($permissaoUsuario_auth_user__controller_show)
                                            <a style="float: left; margin-right: 5px;" href="{{ URL('/') }}/mra_g_iugu/mra_g_iugu_assinaturas/{{$value->id}}" alt="Visualizar" title="Visualizar" class="btn btn-default">
                                                <span class="glyphicon glyphicon-eye-open"></span>
                                            </a>
                                        @endif--}}
                                        @if($permissaoUsuario_auth_user__controller_destroy)
                                            <form style="float: left; margin-right: 5px;" method="POST" action="{{ route('mra_g_iugu_assinaturas.destroy', $value->id) }}" accept-charset="UTF-8">
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
                        <a href="{{ URL('/') }}/mra_g_iugu/mra_g_iugu_assinaturas/create" class="btn btn-default right form-group-btn-index-cadastrar"><i class="glyphicon glyphicon-plus"></i> Cadastrar</a>
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
