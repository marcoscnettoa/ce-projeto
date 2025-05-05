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
        <h1>Financeiro - Contas Bancárias</h1>
    </section>
    <section class="content">
        <div class="box-header">
            <div class="row">
                <div class="col-md-4">
                    <div class="small-box v2 bg-success">
                        <div class="inner" style="color: #000;">
                            @php
                                $valor_atual_total = \App\Helper\Helper::H_Decimal_DB_ptBR(\App\Models\MRAFContasBancarias::Get_ValorAtualTotal());
                                $valor_atual_total = (!empty($valor_atual_total)?$valor_atual_total:'0,00');
                            @endphp
                            <h3>R$ {{ $valor_atual_total }}</h3>
                            <p>Valor Atual - Total</p>
                            <i class="glyphicon glyphicon-eye-open flo"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {{--@if($exibe_filtros)--}}
        <div class="box-header" style="background-color: #fff; padding-top: 30px">
            <form action="{{ URL('/') }}/mra_fluxo_financeiro/mra_f_contas_bancarias/filter" method="POST">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <div class="row">
                    <div size="3" class="inputbox col-md-3">
                        <div class="form-group">
                            {!! Form::label('','Tipo de Conta') !!}
                            {!! Form::select('tipo_conta',\App\Models\MRAFContasBancarias::Get_options_tipos_de_conta(), (Request::get('tipo_conta')?Request::get('tipo_conta'):null), ['class' => 'form-control select_single_no_trigger', 'data-live-search' => 'true', "id" => "input_tipo_conta"]) !!}
                        </div>
                    </div>
                    <div size="3" class="inputbox col-md-3">
                        <div class="form-group">
                            {!! Form::label('','Banco') !!}
                            {!! Form::select('mra_f_bancos_id', \App\Models\MRAFBancos::Get_Bancos_options(), (Request::get('mra_f_bancos_id')?Request::get('mra_f_bancos_id'):null), ['class' => 'form-control select_single_no_trigger', 'data-live-search' => 'true', "id" => "input_mra_f_bancos_id"]) !!}
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
                        <a href="{{ URL('/mra_fluxo_financeiro/mra_f_contas_bancarias') }}" class="btn btn-default" style="float: left; margin-right: 5px;"><i class="glyphicon glyphicon-trash"></i> Limpar</a>
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
                            <th style="white-space: nowrap;">Conta Bancária</th>
                            <th style="white-space: nowrap;">Tipo de Conta</th>
                            <th style="white-space: nowrap;">Banco</th>
                            <th style="white-space: nowrap;">Agência</th>
                            <th style="white-space: nowrap;">N&deg; da Conta</th>
                            {{--<th style="white-space: nowrap;">Valor Inicial</th>--}}
                            <th style="white-space: nowrap;">Valor Atual</th>
                            <th style="white-space: nowrap;">Status</th>
                            @if($auth_user__actions_enable_btns)
                            <th style="border: none; <?php echo (0) ? 'min-width: 160px;' : 'min-width: 130px;' ?>"></th>
                            @endif
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($MRAFContasBancarias as $value)
                            <tr>
                                {{--<td>{{$value->id}}</td>--}}
                                <td>{{(!empty($value->nome)?$value->nome:'---')}}</td>
                                <td>{{(!empty($value->tipo_conta)?\App\Models\MRAFContasBancarias::Get_tipos_de_conta($value->tipo_conta):'---')}}</td>
                                <td>{{($value->MRAFBancos?$value->MRAFBancos->nome:'---')}}</td>
                                <td>{{(!empty($value->agencia)?$value->agencia:'---')}}</td>
                                <td>{{(!empty($value->conta)?$value->conta:'---')}}</td>
                                {{--<td>{{(!empty($value->valor_inicial)?\App\Helper\Helper::H_Decimal_DB_ptBR($value->valor_inicial):'0,00')}}</td>--}}
                                <td>{{(!empty($value->valor_atual)?\App\Helper\Helper::H_Decimal_DB_ptBR($value->valor_atual):'0,00')}}</td>
                                <td>{!! ($value->status?"<span class='badge badge-success fw-600'>Ativo</span>":"<span class='badge badge-danger fw-600'>Inativo</span>")!!}</td>
                                @if($auth_user__actions_enable_btns)
                                    <td>
                                        @if($permissaoUsuario_auth_user__controller_update)
                                            <a style="float: left; margin-right: 5px;" href="{{ URL('/') }}/mra_fluxo_financeiro/mra_f_contas_bancarias/{{$value->id}}/edit" alt="Editar" title="Editar" class="btn btn-default">
                                                <span class="glyphicon glyphicon-edit"></span>
                                            </a>
                                        @endif
                                        {{--@if(0)
                                            @if($permissaoUsuario_auth_user__controller_copy)
                                                <a style="float: left; margin-right: 5px;" href="{{ URL('/') }}/mra_fluxo_financeiro/mra_f_contas_bancarias/{{$value->id}}/copy" alt="Duplicar linha" title="Duplicar linha" class="btn btn-default" onclick="return confirm('Tem certeza que quer duplicar a linha?')" >
                                                    <span class="glyphicon glyphicon-copy"></span>
                                                </a>
                                            @endif
                                        @endif--}}
                                        {{--@if($permissaoUsuario_auth_user__controller_show)
                                            <a style="float: left; margin-right: 5px;" href="{{ URL('/') }}/mra_fluxo_financeiro/mra_f_contas_bancarias/{{$value->id}}" alt="Visualizar" title="Visualizar" class="btn btn-default">
                                                <span class="glyphicon glyphicon-eye-open"></span>
                                            </a>
                                        @endif--}}
                                        @if($permissaoUsuario_auth_user__controller_destroy)
                                            <form style="float: left; margin-right: 5px;" method="POST" action="{{ route('mra_f_contas_bancarias.destroy', $value->id) }}" accept-charset="UTF-8">
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
                        <a href="{{ URL('/') }}/mra_fluxo_financeiro/mra_f_contas_bancarias/create" class="btn btn-default right form-group-btn-index-cadastrar" style="margin-left:15px;"><i class="glyphicon glyphicon-plus"></i> Cadastrar</a>
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
