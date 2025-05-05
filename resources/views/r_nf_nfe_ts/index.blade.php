@php
    $isPublic               = 0;
    $controller             = get_class(\Request::route()->getController());
    $enable_kanban          = 0;
    $kanban_field           = '';
    $import_enable_btns     = 0;
    $export_enable_btns     = 0;
    $actions_enable_btns    = 1;
    $kanban_list            = array();

    if(env('FILESYSTEM_DRIVER') == 's3'){
        $fileurlbase = env('URLS3').'/'.env('FILEKEY');
    }else {
        $fileurlbase = env('APP_URL').'/storage';
    }

    $config_empresa                                     = App\Models\RNfConfiguracoesTs::config_empresa();
    $envios_disponiveis                                 = App\Models\RNfConfiguracoesTs::envios_disponiveis('nfe');
    $config_empresa_token_api                           = ($config_empresa and !empty($config_empresa->token_api)?true:false);
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
        <h1>
            Nota Fiscal - Nota Fiscal de Produto
            <span class="pull-right badge badge badge-warning" style="font-size: 17px; padding: 5px 10px;">
                Excedente {{$envios_disponiveis < 0 ? abs($envios_disponiveis) : 0}}
            </span>
            <span class="pull-right badge badge badge-success" style="font-size: 17px; padding: 5px 10px; margin-right: 5px">
                Disponível {{$envios_disponiveis > 0 ? $envios_disponiveis : 0}}
            </span>
        </h1>
    </section>
    
    <section class="content">
        <div class="box-header">
            <div class="row">
                <div class="col-md-2">
                    <div class="small-box v2 bg-default">
                        <div class="inner" style="color: #000;">
                            <h3>{{ \App\Models\RNfNfeTs::Get_QtStatus_Nao_Emitida() }}</h3>
                            <p>Não emitida</p>
                            <i class="glyphicon glyphicon-save flo"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="small-box v2 bg-info">
                        <div class="inner" style="color: #000;">
                            <h3>{{ \App\Models\RNfNfeTs::Get_QtStatus_Pendente() }}</h3>
                            <p>Pendentes</p>
                            <i class="glyphicon glyphicon-alert flo"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="small-box v2 bg-warning">
                        <div class="inner" style="color: #000;">
                            <h3>{{ \App\Models\RNfNfeTs::Get_QtStatus_Processando() }}</h3>
                            <p>Processando</p>
                            <i class="glyphicon glyphicon-time flo"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="small-box v2 bg-success">
                        <div class="inner" style="color: #000;">
                            <h3>{{ \App\Models\RNfNfeTs::Get_QtStatus_Autorizada() }}</h3>
                            <p>Autorizadas</p>
                            <i class="glyphicon glyphicon-check flo"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="small-box v2 bg-danger">
                        <div class="inner" style="color: #000;">
                            <h3>{{ \App\Models\RNfNfeTs::Get_QtStatus_Rejeitada() + \App\Models\RNfNfeTs::Get_QtStatus_Denegada() }}</h3>
                            <p>Rejeitadas / Denegadas</p>
                            <i class="glyphicon glyphicon-remove-circle flo"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="small-box v2 bg-danger">
                        <div class="inner" style="color: #000;">
                            <h3>{{ \App\Models\RNfNfeTs::Get_QtStatus_Cancelada() }}</h3>
                            <p>Canceladas</p>
                            <i class="glyphicon glyphicon-trash flo"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if($exibe_filtros)
            <div class="box-header" style="background-color: #fff; padding-top: 30px">
                <form action="{{ URL('/') }}/nota_fiscal/nfe/ts/filter" method="POST">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <div class="row">
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group ss-btn">
                                {!! Form::label('', 'Data de Competência') !!}
                                <div class='row'>
                                    <div class='col-md-6'>
                                        {!! Form::select(
                                            'operador[nfe_data_competencia]',
                                            ['contem' => 'Contém', 'entre' => 'Entre', '=' => '=', '>' => '>', '>=' => '>=', '<' => '<', '<=' => '<='],
                                            (Request::get('operador')?Request::get('operador')['nfe_data_competencia']:null),
                                            ['class' => 'form-control operador'],
                                        ) !!}
                                    </div>
                                    <div class='col-md-6 fix_pl_0px' style='padding-left: 0px;'>
                                        {!! Form::text('nfe_data_competencia', (Request::get('nfe_data_competencia')?Request::get('nfe_data_competencia'):null), ['autocomplete' => 'off', 'class' => 'form-control componenteData', 'placeholder'=>'__/__/____']) !!}
                                        <p class='between' style='top: 16px; position:relative; display: none;'>
                                            {!! Form::label('', '&') !!} {!! Form::text('between[nfe_data_competencia]', (Request::get('between')?Request::get('between')['nfe_data_competencia']:null), [
                                            'autocomplete' => 'off',
                                            'class' => 'form-control componenteData','placeholder'=>'__/__/____'
                                        ]) !!}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group ss-btn">
                                {!! Form::label('', 'Data de Emissão') !!}
                                <div class='row'>
                                    <div class='col-md-6'>
                                        {!! Form::select(
                                            'operador[nf_emissao]',
                                            ['contem' => 'Contém', 'entre' => 'Entre', '=' => '=', '>' => '>', '>=' => '>=', '<' => '<', '<=' => '<='],
                                            (Request::get('operador')?Request::get('operador')['nf_emissao']:null),
                                            ['class' => 'form-control operador'],
                                        ) !!}
                                    </div>
                                    <div class='col-md-6 fix_pl_0px' style='padding-left: 0px;'>
                                        {!! Form::text('nf_emissao', (Request::get('nf_emissao')?Request::get('nf_emissao'):null), ['autocomplete' => 'off', 'class' => 'form-control componenteData', 'placeholder'=>'__/__/____']) !!}
                                        <p class='between' style='top: 16px; position:relative; display: none;'>
                                            {!! Form::label('', '&') !!} {!! Form::text('between[nf_emissao]', (Request::get('between')?Request::get('between')['nf_emissao']:null), [
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
                                {!! Form::label('','Cliente / Tomador') !!}
                                {!! Form::select('mra_nf_cliente_id', \App\Models\RNfClientesTs::lista_clientes(), (Request::get('mra_nf_cliente_id')?Request::get('mra_nf_cliente_id'):null), ['class' => 'form-control select_single_no_trigger', "dropdown-menu-right"=>"", 'data-live-search' => 'true', 'id' => 'input_mra_nf_cliente_id']) !!}
                            </div>
                        </div>
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','Número da Nota') !!}
                                {!! Form::text('nf_numero', (Request::get('nf_numero')?Request::get('nf_numero'):null), ['class' => 'form-control', 'id' => 'input_nf_numero']) !!}
                            </div>
                        </div>
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','Chave') !!}
                                {!! Form::text('nf_chave', (Request::get('nf_chave')?Request::get('nf_chave'):null), ['class' => 'form-control', 'id' => 'input_nf_codigoVerificacao']) !!}
                            </div>
                        </div>
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','Status') !!}
                                {!! Form::select('nf_status', [
                                    ''            => '---',
                                    'NÃO EMITIDA' => 'NÃO EMITIDAS',
                                    'PENDENTE'    => 'PENDENTES',
                                    'CONCLUIDO'   => 'CONCLUIDAS',
                                    'REJEITADO'   => 'REJEITADAS',
                                    'DENEGADO'    => 'DENEGADAS',
                                    'CANCELADO'   => 'CANCELADAS',
                                ], (Request::get('nf_status')?Request::get('nf_status'):null), ['class' => 'form-control select_single_no_trigger', 'data-live-search' => 'true', 'id' => 'input_nf_status']) !!}
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-<?php echo (!$isPublic ? '3' : '6'); ?> btnsFiltro" style="margin-top: 23px;">
                            <a href="{{ URL('nota_fiscal/nfe/ts') }}" class="btn btn-default" style="float: left; margin-right: 5px;"><i class="glyphicon glyphicon-trash"></i> Limpar</a>
                            <button type="submit" class="btn btn-default submitbtn" style="float: left;">
                                <span class="glyphicon glyphicon-search"></span> Pesquisar
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        @endif
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
                            <th style="white-space: nowrap;">Data de Competência</th>
                            <th style="white-space: nowrap;">Data de Emissão</th>
                            <th style="white-space: nowrap;">Cliente</th>
                            <th style="white-space: nowrap;">Valor da Nota</th>
                            <th style="white-space: nowrap;">Status</th>
                            <th style="white-space: nowrap;">Número da Nota</th>
                            <th style="white-space: nowrap;">Chave</th>
                            <th style="white-space: nowrap;">Anexos</th>
                            @if($auth_user__actions_enable_btns)
                            <th style="border: none; <?php echo (0) ? 'min-width: 160px;' : 'min-width: 130px;' ?>"></th>
                            @endif
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($MRANfNfe as $value)
                            <tr>
                                <td style="white-space: nowrap;">{{(!empty($value->nfe_data_competencia)?date('d/m/Y', strtotime($value->nfe_data_competencia)):'---')}}</td>
                                <td style="white-space: nowrap;">{{(!empty($value->nf_emissao)?date('d/m/Y', strtotime($value->nf_emissao)):'---')}}</td>
                                <td style="white-space: nowrap;">{{(!empty($value->des_nome_razao_social)?$value->des_nome_razao_social:'---')}}</td>
                                <td style="white-space: nowrap;">{{(!empty($value->MRANfNfeProdutosItensQtValorTotal())?\App\Helper\Helper::H_Decimal_DB_ptBR($value->MRANfNfeProdutosItensQtValorTotal()):'0,00')}}</td>

                                @php
                                    if ($value->nf_status) {
                                        if ($value->nf_status == 'CONCLUIDO') {
                                            $label_color    = 'success';
                                        }elseif ($value->nf_status == 'CANCELADO') {
                                            $label_color    = 'danger';
                                        }elseif ($value->nf_status == 'DENEGADO' || $value->nf_status == 'REJEITADO') {
                                            $label_color    = 'warning';
                                        }else {
                                            $label_color    = 'default';
                                        }
                                    }else {
                                        $label_color    = 'default';
                                    }
                                @endphp

                                <td style="white-space: nowrap;">
                                    <span class="badge badge-{{$label_color}} pull-left"> {{$value->nf_status ? $value->nf_status : 'NÃO EMITIDA' }}</span>
                                </td>

                                @php
                                    $nf_badge_status = 'badge-default';
                                    if(!empty($value->notazz_id_documento)){
                                        $nf_badge_status = 'badge-success';
                                    }else {
                                        $nf_badge_status = 'badge-danger';
                                    }
                                @endphp
                                <td style="white-space: nowrap;">{{$value->nf_numero ?? '---'}}</td>
                                <td style="white-space: nowrap;">{{$value->nf_chave ?? '---'}}</td>
                                <td style="white-space: nowrap;">
                                    @if($value->nf_pdf ||
                                        $value->nf_xml ||
                                        $value->nf_xml_cancelamento
                                    )
                                        @if($value->nf_pdf)
                                            <a href="{{ $fileurlbase.$value->nf_pdf }}" target="_blank" class="op75_h">
                                                <img src="{{URL('')}}/pdf-icon.png" height="34" title="Visualizar PDF" alt="">
                                            </a>
                                        @endif
                                        @if($value->nf_xml)
                                            <a href="{{ $fileurlbase.$value->nf_xml }}" target="_blank" class="op75_h">
                                                <img src="{{URL('')}}/xml-icon.png" height="34" title="Visualizar XML" alt="">
                                            </a>
                                        @endif
                                        @if($value->nf_xml_cancelamento)
                                            <a href="{{ $fileurlbase.$value->nf_xml_cancelamento }}" target="_blank" class="op75_h">
                                                <img src="{{URL('')}}/xml-icon.png" height="34" title="XML de Cancelamento" alt="">
                                            </a>
                                        @endif
                                    @else
                                        ---
                                    @endif
                                </td>
                                @if($auth_user__actions_enable_btns)
                                    <td>
                                        @if($permissaoUsuario_auth_user__controller_update)
                                            <a style="float: left; margin-right: 5px;" href="{{ URL('/') }}/nota_fiscal/nfe/ts/{{$value->id}}/edit" alt="Editar" title="Editar" class="btn btn-default">
                                                <span class="glyphicon glyphicon-edit"></span>
                                            </a>
                                        @endif
                                        {{--@if(0)
                                            @if($permissaoUsuario_auth_user__controller_copy)
                                                <a style="float: left; margin-right: 5px;" href="{{ URL('/') }}/nota_fiscal/nfe/{{$value->id}}/copy" alt="Duplicar linha" title="Duplicar linha" class="btn btn-default" onclick="return confirm('Tem certeza que quer duplicar a linha?')" >
                                                    <span class="glyphicon glyphicon-copy"></span>
                                                </a>
                                            @endif
                                        @endif--}}
                                        {{--@if($permissaoUsuario_auth_user__controller_show)
                                            <a style="float: left; margin-right: 5px;" href="{{ URL('/') }}/nota_fiscal/nfe/{{$value->id}}" alt="Visualizar" title="Visualizar" class="btn btn-default">
                                                <span class="glyphicon glyphicon-eye-open"></span>
                                            </a>
                                        @endif--}}
                                        @if($permissaoUsuario_auth_user__controller_destroy and !$value->nf_response_id)
                                            <form style="float: left; margin-right: 5px;" method="POST" action="{{ route('r_nfe.ts.destroy', $value->id) }}" accept-charset="UTF-8">
                                                {!! csrf_field() !!}
                                                {!! method_field('DELETE') !!}
                                                <button type="submit" onclick="return confirm('Tem certeza que quer deletar?')" class="btn btn-danger"><i class="glyphicon glyphicon-trash"></i></button>
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
                        <a href="{{ URL('/') }}/nota_fiscal/nfe/ts/create" class="btn btn-default right form-group-btn-index-cadastrar"><i class="glyphicon glyphicon-plus"></i> Cadastrar</a>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection
@section('script')
    @include('datatable', ["columnDefs"=>"[
                {targets: [0],type: 'ptBRDate'},
                {targets: [1],type: 'ptBRDate'},
                {targets: [5],type: 'ptBRDecimal'},
            ]"])
    <script type="text/javascript">
    </script>
@endsection
