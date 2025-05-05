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
        <h1>Financeiro - Contas a Receber</h1>
    </section>
    <section class="content">
        <div class="box-header">
            <div class="row">
                <div class="col-md-3">
                    <div class="small-box v2 bg-success">
                        <div class="inner" style="color: #000;">
                            <h3>{{ \App\Models\MRAFContasReceber::Get_QtStatus_Concluidos() }}</h3>
                            <p>Contas Concluídas</p>
                            <i class="glyphicon glyphicon-eye-open flo"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="small-box v2 bg-warning">
                        <div class="inner" style="color: #000;">
                            <h3>{{ \App\Models\MRAFContasReceber::Get_QtStatus_Pendentes() }}</h3>
                            <p>Contas Pendentes</p>
                            <i class="glyphicon glyphicon-eye-open flo"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="small-box v2 bg-success">
                        <div class="inner" style="color: #000;">
                            @php
                                $valor_total_pagos = \App\Helper\Helper::H_Decimal_DB_ptBR(\App\Models\MRAFContasReceber::Get_ValorTotalPagamentos_Pagos());
                                $valor_total_pagos = (!empty($valor_total_pagos)?$valor_total_pagos:'0,00');
                            @endphp
                            <h3>R$ {{ $valor_total_pagos }}</h3>
                            <p>Valor Pagos</p>
                            <i class="glyphicon glyphicon-check flo"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="small-box v2 bg-warning">
                        <div class="inner" style="color: #000;">
                            @php
                                $valor_total_pendentes = \App\Helper\Helper::H_Decimal_DB_ptBR(\App\Models\MRAFContasReceber::Get_ValorTotalPagamentos_Pendentes());
                                $valor_total_pendentes = (!empty($valor_total_pendentes)?$valor_total_pendentes:'0,00');
                            @endphp
                            <h3>R$ {{ $valor_total_pendentes }}</h3>
                            <p>Valor Pendentes</p>
                            <i class="glyphicon glyphicon-alert flo"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {{--@if($exibe_filtros)--}}
            <div class="box-header" style="background-color: #fff; padding-top: 30px">
                <form action="{{ URL('/') }}/mra_fluxo_financeiro/mra_f_contas_receber/filter" method="POST">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <div class="row">
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group ss-btn">
                                {!! Form::label('', 'Data de Competência') !!}
                                <div class='row'>
                                    <div class='col-md-6'>
                                        {!! Form::select(
                                            'operador[data_competencia]',
                                            ['contem' => 'Contém', 'entre' => 'Entre', '=' => '=', '>' => '>', '>=' => '>=', '<' => '<', '<=' => '<='],
                                            (Request::get('operador')?Request::get('operador')['data_competencia']:null),
                                            ['class' => 'form-control operador'],
                                        ) !!}
                                    </div>
                                    <div class='col-md-6' style='padding-left: 0px;'>
                                        {!! Form::text('data_competencia', (Request::get('data_competencia')?Request::get('data_competencia'):null), ['autocomplete' => 'off', 'class' => 'form-control componenteData', 'placeholder'=>'__/__/____']) !!}
                                        <p class='between' style='top: 16px; position:relative; display: none;'>
                                            {!! Form::label('', '&') !!} {!! Form::text('between[data_competencia]', (Request::get('between')?Request::get('between')['data_competencia']:null), [
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
                                {!! Form::label('', 'Vencimento') !!}
                                <div class='row'>
                                    <div class='col-md-6'>
                                        {!! Form::select(
                                            'operador[vencimento]',
                                            ['contem' => 'Contém', 'entre' => 'Entre', '=' => '=', '>' => '>', '>=' => '>=', '<' => '<', '<=' => '<='],
                                            (Request::get('operador')?Request::get('operador')['vencimento']:null),
                                            ['class' => 'form-control operador'],
                                        ) !!}
                                    </div>
                                    <div class='col-md-6' style='padding-left: 0px;'>
                                        {!! Form::text('vencimento', (Request::get('vencimento')?Request::get('vencimento'):null), ['autocomplete' => 'off', 'class' => 'form-control componenteData', 'placeholder'=>'__/__/____']) !!}
                                        <p class='between' style='top: 16px; position:relative; display: none;'>
                                            {!! Form::label('', '&') !!} {!! Form::text('between[vencimento]', (Request::get('between')?Request::get('between')['vencimento']:null), [
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
                                {!! Form::label('','Tipo de Pagamento') !!}
                                {!! Form::select('tipo_pagamento', \App\Http\Controllers\MRA\MRAListas::Get_options_tipo_pagamento(), (Request::get('tipo_pagamento')?Request::get('tipo_pagamento'):null), ['class' => 'form-control select_single_no_trigger', 'data-live-search' => 'true', "id" => "input_tipo_pagamento"]) !!}
                            </div>
                        </div>
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','Anexo(s)') !!}
                                {!! Form::select('anexos', ["" => "---", 1 => "Sim", 0 => "Não"], (!empty(Request::get('anexos') || Request::get('anexos') == 0)?Request::get('anexos'):null), ['class' => 'form-control select_single_no_trigger', 'data-live-search' => 'true', "id" => "input_anexos"]) !!}
                            </div>
                        </div>
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','Status') !!}
                                {!! Form::select('status', \App\Http\Controllers\MRA\MRAListas::Get_options_status_conclusao(), (Request::get('status')?Request::get('status'):null), ['class' => 'form-control select_single_no_trigger', 'data-live-search' => 'true', "id" => "input_status"]) !!}
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3 btnsFiltro">
                            <a href="{{ URL('/mra_fluxo_financeiro/mra_f_contas_receber') }}" class="btn btn-default" style="float: left; margin-right: 5px;"><i class="glyphicon glyphicon-trash"></i> Limpar</a>
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
                            <th style="white-space: nowrap;">Data Competência</th>
                            <th style="white-space: nowrap;">Cliente</th>
                            {{--<th style="white-space: nowrap;">Centro de Custo</th>
                            <th style="white-space: nowrap;">Plano de Conta</th>
                            <th style="white-space: nowrap;">Conta Recebimento</th>--}}
                            <th style="white-space: nowrap;">Vencimento</th>
                            <th style="white-space: nowrap;">Valor Serviço</th>
                            <th style="white-space: nowrap;">Juros</th>
                            <th style="white-space: nowrap;">Multa</th>
                            <th style="white-space: nowrap;">Valor Entrada</th>
                            <th style="white-space: nowrap;">Valor Total a Receber</th>
                            <th style="white-space: nowrap;">Tipo Pagamento</th>
                            <th style="white-space: nowrap;">Valor a Parcelar</th>
                            <th style="white-space: nowrap;">Anexos</th>
                            <th style="white-space: nowrap;">Status</th>
                            @if($auth_user__actions_enable_btns)
                            <th style="border: none; <?php echo (0) ? 'min-width: 160px;' : 'min-width: 130px;' ?>"></th>
                            @endif
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($MRAFContasReceber as $value)
                            @php
                                $badge_status = 'badge-default';
                                $status_tr    = '';
                                switch($value->status){
                                    case 1: $badge_status = 'badge-success'; $status_tr = 'bg-success'; break;
                                    case 2: $badge_status = 'badge-warning'; $status_tr = 'bg-warning'; break;
                                }
                            @endphp
                            <tr class="{{$status_tr}}" _id="{{$value->id}}">
                                <td style="white-space: nowrap;">{{(!empty($value->data_competencia)?\App\Helper\Helper::H_Data_DB_ptBR($value->data_competencia):'---')}}</td>
                                <td style="white-space: nowrap;">{{(($value->MRAFClientes and !empty($value->MRAFClientes->nome))?$value->MRAFClientes->nome:'---')}}</td>
                                {{--<td style="white-space: nowrap;">{{(($value->MRAFCentroCusto and !empty($value->MRAFCentroCusto->nome))?$value->MRAFCentroCusto->nome:'---')}}</td>
                                <td style="white-space: nowrap;">{{(($value->MRAFPlanoContas and !empty($value->MRAFPlanoContas->nome))?$value->MRAFPlanoContas->nome:'---')}}</td>
                                <td style="white-space: nowrap;">{{(($value->MRAFContasBancarias and !empty($value->MRAFContasBancarias->nome))?$value->MRAFContasBancarias->nome:'---')}}</td>--}}
                                <td style="white-space: nowrap;">{{(!empty($value->vencimento)?\App\Helper\Helper::H_Data_DB_ptBR($value->vencimento):'---')}}</td>
                                <td style="white-space: nowrap;">{{(!empty($value->valor)?\App\Helper\Helper::H_Decimal_DB_ptBR($value->valor):'---')}}</td>
                                <td style="white-space: nowrap;">{{(!empty($value->juros)?\App\Helper\Helper::H_Decimal_DB_ptBR($value->juros):'---')}}</td>
                                <td style="white-space: nowrap;">{{(!empty($value->multa)?\App\Helper\Helper::H_Decimal_DB_ptBR($value->multa):'---')}}</td>
                                <td style="white-space: nowrap;">{{(!empty($value->valor_entrada)?\App\Helper\Helper::H_Decimal_DB_ptBR($value->valor_entrada):'---')}}</td>
                                @php
                                    $valor_total_a_receber  = \App\Helper\Helper::H_Decimal_DB_ptBR($value->valor + $value->juros + $value->multa);
                                    $valor_total_a_receber  = (!empty($valor_total_a_receber)?$valor_total_a_receber:'0,00');
                                @endphp
                                <td style="white-space: nowrap;">{{ $valor_total_a_receber }}</td>
                                <td style="white-space: nowrap;"><strong>{{ \App\Http\Controllers\MRA\MRAListas::Get_tipo_pagamento($value->tipo_pagamento) }}</strong></td>
                                @php
                                    $valor_total_a_parcelar  = '---';
                                    if($value->tipo_pagamento == 2){
                                        $valor_total_a_parcelar  = \App\Helper\Helper::H_Decimal_DB_ptBR($value->valor + $value->juros + $value->multa - $value->valor_entrada);
                                        $valor_total_a_parcelar  = (!empty($valor_total_a_parcelar)?$valor_total_a_parcelar:'0,00');
                                    }
                                @endphp
                                <td style="white-space: nowrap;">{{ $valor_total_a_parcelar }}</td>
                                <td style="white-space: nowrap;">{!! ((!empty($value->anexo) || !empty($value->anexo2))?"<span class='badge badge-success fw-600'>Sim</span>":"<span class='badge badge-danger fw-600'>Não</span>")!!}</td>
                                <td style="white-space: nowrap;"><span class='badge {{ $badge_status }} fw-600'>{{ \App\Http\Controllers\MRA\MRAListas::Get_status_conclusao($value->status) }}</span></td>
                                @if($auth_user__actions_enable_btns)
                                    <td>
                                        @if($permissaoUsuario_auth_user__controller_update)
                                            <a style="float: left; margin-right: 5px;" href="{{ URL('/') }}/mra_fluxo_financeiro/mra_f_contas_receber/{{$value->id}}/edit" alt="Editar" title="Editar" class="btn btn-default">
                                                <span class="glyphicon glyphicon-edit"></span>
                                            </a>
                                        @endif
                                        {{--@if(0)
                                            @if($permissaoUsuario_auth_user__controller_copy)
                                                <a style="float: left; margin-right: 5px;" href="{{ URL('/') }}/mra_fluxo_financeiro/mra_f_contas_receber/{{$value->id}}/copy" alt="Duplicar linha" title="Duplicar linha" class="btn btn-default" onclick="return confirm('Tem certeza que quer duplicar a linha?')" >
                                                    <span class="glyphicon glyphicon-copy"></span>
                                                </a>
                                            @endif
                                        @endif--}}
                                        {{--@if($permissaoUsuario_auth_user__controller_show)
                                            <a style="float: left; margin-right: 5px;" href="{{ URL('/') }}/mra_fluxo_financeiro/mra_f_contas_receber/{{$value->id}}" alt="Visualizar" title="Visualizar" class="btn btn-default">
                                                <span class="glyphicon glyphicon-eye-open"></span>
                                            </a>
                                        @endif--}}
                                        @if($permissaoUsuario_auth_user__controller_destroy)
                                            <form style="float: left; margin-right: 5px;" method="POST" action="{{ route('mra_f_contas_receber.destroy', $value->id) }}" accept-charset="UTF-8">
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
                        <a href="{{ URL('/') }}/mra_fluxo_financeiro/mra_f_contas_receber/create" class="btn btn-default right form-group-btn-index-cadastrar"><i class="glyphicon glyphicon-plus"></i> Cadastrar</a>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection
@section('script')
    @include('datatable', ["columnDefs"=>"[
                {targets: [0],type: 'ptBRDate'},
                {targets: [2],type: 'ptBRDate'},
            ]"])
    <script type="text/javascript">
    </script>
@endsection
