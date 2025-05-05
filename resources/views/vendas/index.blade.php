@php

    $isPublic                   = 0;
    $controller                 = get_class(\Request::route()->getController());
    $enable_kanban              = 0;
    $kanban_field               = '';
    $import_enable_btns         = 1;
    $export_enable_btns         = 1;
    $export_options_size        = 'custom'; // # -
    $export_options_size_height = 3000; // # -
    $export_options_size_width  = 3000; // # -
    $actions_enable_btns        = 1;

    //$kanban_list = array();

    /*foreach($vendas as $val) {

        if(array_key_exists($kanban_field, $val->toArray())){

            if (method_exists($val, 'Get_' . $kanban_field)){

                $field = 'Get_' . $kanban_field;

                $val->$kanban_field = $val->$field();
            }

            $kanban_list[$val[$kanban_field]][] = $val;

        }else{
            $kanban_list[""][] = $val;
        }
    }*/

    if(env('FILESYSTEM_DRIVER') == 's3')
    {
        $fileurlbase = env('URLS3') . '/' . env('FILEKEY') . '/';
    }
    else
    {
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

@extends($isPublic ? 'layouts.app-public' : 'layouts.app')

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
    <section class="content-header Vendas_index">
        <h1>Vendas</h1>
    </section>
    <section class="content">
        @if($exibe_filtros)
            <div class="box-header" style="background-color: #fff; padding-top: 15px">
                <form action="{{ URL('/') }}/vendas/filter" class="form_filter form_filter_vendas" method="POST">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <div class="row">
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','N° da Fatura') !!}
                                <div class='input-group'>
                                    <div class='input-group-addon'>
                                        <i class='glyphicon glyphicon-pencil'></i>
                                    </div>
                                    {!! Form::text('faturamento', (!empty(\Request::post('faturamento'))?\Request::post('faturamento'):null), ['class' => 'form-control' ]) !!}
                                </div>
                            </div>
                        </div>
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','Data') !!}
                                <div class='row'>
                                    <div class='col-md-4'>
                                        {!! Form::select('operador[data]', ['contem' => 'Contém', 'entre' => 'Entre', '=' => '=', '>' => '>', '>=' => '>=', '<' => '<', '<=' => '<='], (!empty(\Request::post('operador')['data'])?\Request::post('operador')['data']:null), ['class' => 'form-control operador' ]) !!}
                                    </div>
                                    <div class='col-md-8' style='padding-left: 0px;'>
                                        <div class='input-group'>
                                            <div class='input-group-addon'>
                                                <i class='glyphicon glyphicon-calendar'></i>
                                            </div>
                                            {!! Form::text('data', (!empty(\Request::post('data'))?\Request::post('data'):null), ['autocomplete' =>'off', 'class' => 'form-control componenteData' ]) !!}
                                        </div>
                                        <div class='between' style='margin-bottom:0px; margin-top: 5px; display: none;'>
                                            {!! Form::label('','&') !!}                    <div class='input-group'>
                                                <div class='input-group-addon'>
                                                    <i class='glyphicon glyphicon-calendar'></i>
                                                </div>
                                                {!! Form::text('between[data]', (!empty(\Request::post('between')['data'])?\Request::post('between')['data']:null), ['autocomplete' =>'off', 'class' => 'form-control componenteData' ]) !!}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','Dt. Embarque') !!}
                                <div class='row'>
                                    <div class='col-md-4'>
                                        {!! Form::select('operador[data_embarque]', ['contem' => 'Contém', 'entre' => 'Entre', '=' => '=', '>' => '>', '>=' => '>=', '<' => '<', '<=' => '<='], (!empty(\Request::post('operador')['data_embarque'])?\Request::post('operador')['data_embarque']:null), ['class' => 'form-control operador' ]) !!}
                                    </div>
                                    <div class='col-md-8' style='padding-left: 0px;'>
                                        <div class='input-group'>
                                            <div class='input-group-addon'><i class='glyphicon glyphicon-calendar'></i></div>
                                            {!! Form::text('data_embarque', (!empty(\Request::post('data_embarque'))?\Request::post('data_embarque'):null), ['autocomplete' =>'off', 'class' => 'form-control componenteData' ]) !!}
                                        </div>
                                        <div class='between' style='margin-bottom:0px; margin-top: 5px; display: none;'>
                                            {!! Form::label('','&') !!}                    <div class='input-group'>
                                                <div class='input-group-addon'>
                                                    <i class='glyphicon glyphicon-calendar'></i>
                                                </div>
                                                {!! Form::text('between[data_embarque]', (!empty(\Request::post('between')['data_embarque'])?\Request::post('between')['data_embarque']:null), ['autocomplete' =>'off', 'class' => 'form-control componenteData' ]) !!}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','Dt. Retorno') !!}
                                <div class='row'>
                                    <div class='col-md-4'>
                                        {!! Form::select('operador[data_retorno]', ['contem' => 'Contém', 'entre' => 'Entre', '=' => '=', '>' => '>', '>=' => '>=', '<' => '<', '<=' => '<='], (!empty(\Request::post('operador')['data_retorno'])?\Request::post('operador')['data_retorno']:null), ['class' => 'form-control operador' ]) !!}
                                    </div>
                                    <div class='col-md-8' style='padding-left: 0px;'>
                                        <div class='input-group'>
                                            <div class='input-group-addon'><i class='glyphicon glyphicon-calendar'></i></div>
                                            {!! Form::text('data_retorno', (!empty(\Request::post('data_retorno'))?\Request::post('data_retorno'):null), ['autocomplete' =>'off', 'class' => 'form-control componenteData' ]) !!}
                                        </div>
                                        <div class='between' style='margin-bottom:0px; margin-top: 5px; display: none;'>
                                            {!! Form::label('','&') !!}                    <div class='input-group'>
                                                <div class='input-group-addon'>
                                                    <i class='glyphicon glyphicon-calendar'></i>
                                                </div>
                                                {!! Form::text('between[data_retorno]', (!empty(\Request::post('between')['data_retorno'])?\Request::post('between')['data_retorno']:null), ['autocomplete' =>'off', 'class' => 'form-control componenteData' ]) !!}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','Cliente') !!}
                                {!! Form::select('cliente[]', $clientes_nome_do_cliente, (!empty(\Request::post('cliente'))?\Request::post('cliente'):null), ['class' => 'form-control  select_relationship ', 'data-sub' => '', 'data-live-search' => 'true',  'data-none-selected-text' => true, 'controller' => 'clientes', 'multiple' => '' ]) !!}
                            </div>
                        </div>
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','Vendedor') !!}
                                {!! Form::select('vendedor[]', $vendedores_nome_do_vendedor, (!empty(\Request::post('vendedor'))?\Request::post('vendedor'):null), ['class' => 'form-control  select_relationship ', 'data-sub' => '', 'data-live-search' => 'true',  'data-none-selected-text' => true, 'controller' => 'vendedores', 'multiple' => '' ]) !!}
                            </div>
                        </div>
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','Localizador') !!}
                                <div class='input-group'>
                                    <div class='input-group-addon'>
                                        <i class='glyphicon glyphicon-search'></i>
                                    </div>
                                    {!! Form::text('localizador', (!empty(\Request::post('localizador'))?\Request::post('localizador'):null), ['class' => 'form-control' ]) !!}
                                </div>
                            </div>
                        </div>
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','Produto') !!}
                                {!! Form::select('produto[]', $produtos_produto, (!empty(\Request::post('produto'))?\Request::post('produto'):null), ['class' => 'form-control  select_relationship ', 'data-sub' => '', 'data-live-search' => 'true',  'data-none-selected-text' => true, 'controller' => 'produtos', 'multiple' => '' ]) !!}
                            </div>
                        </div>
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','Serviço') !!}
                                {!! Form::select('servico[]', $servicos_servico, (!empty(\Request::post('servico'))?\Request::post('servico'):null), ['class' => 'form-control  select_relationship ', 'data-sub' => '', 'data-live-search' => 'true',  'data-none-selected-text' => true, 'controller' => 'servicos', 'multiple' => '' ]) !!}
                            </div>
                        </div>
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','Fornecedor') !!}
                                {!! Form::select('fornecedor[]', $fornecedores_fornecedor, (!empty(\Request::post('fornecedor'))?\Request::post('fornecedor'):null), ['class' => 'form-control  select_relationship ', 'data-sub' => '', 'data-live-search' => 'true',  'data-none-selected-text' => true, 'controller' => 'fornecedores', 'multiple' => '' ]) !!}
                            </div>
                        </div>
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','Companhia') !!}
                                {!! Form::select('companhia[]', $companhias_companhia, (!empty(\Request::post('companhia'))?\Request::post('companhia'):null), ['class' => 'form-control  select_relationship ', 'data-sub' => '', 'data-live-search' => 'true',  'data-none-selected-text' => true, 'controller' => 'companhias', 'multiple' => '' ]) !!}
                            </div>
                        </div>
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','Passageiros') !!}
                                {!! Form::select('grid_fil[vendas__grid_passageiros][passageiros][]', $passageiro_nome, (!empty(\Request::post('grid_fil')['vendas__grid_passageiros']['passageiros'])?\Request::post('grid_fil')['vendas__grid_passageiros']['passageiros']:null), ['class' => 'form-control  select_relationship ', 'data-sub' => '', 'data-live-search' => 'true',  'data-none-selected-text' => true, 'controller' => 'passageiro', 'multiple' => '' ]) !!}
                            </div>
                        </div>
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','Forma de Pagamento') !!}
                                {!! Form::select('grid_fil[vendas__grid_pagamentos][forma_de_pagamento][]', $formas_de_pagamentos_forma_de_pa, (!empty(\Request::post('grid_fil[vendas__grid_pagamentos][forma_de_pagamento][]'))?\Request::post('grid_fil[vendas__grid_pagamentos][forma_de_pagamento][]'):null), ['class' => 'form-control  select_relationship ', 'data-sub' => '', 'data-live-search' => 'true',  'data-none-selected-text' => true, 'controller' => 'formas_de_pagamentos', 'multiple' => '' ]) !!}
                            </div>
                        </div>
                        <div class="col-md-12 btnsFiltro">
                            <a href="{{ URL('/vendas') }}" class="btn btn-xs btn-default" style="float: left; margin-right: 5px;"><i class="glyphicon glyphicon-trash"></i> Limpar</a>
                            <button type="submit" class="btn btn-xs btn-info submitbtn" style="float: left;"><span class="glyphicon glyphicon-search"></span> Pesquisar</button>
                        </div>
                    </div>
                </form>
            </div>
        @endif

        @if(!$enable_kanban)

            <div class="box">

                <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content"></div>
                    </div>
                </div>
                <div class="box-body" style="margin-top:0;">
                    <div class="table-responsive">
                        <table id="vendas-relatorio" class="table-st1 table-striped table-bordered stripe">
                            <tr>
                                <th class="text-center ws_nowrap">Tarifa</th>
                                <th class="text-center ws_nowrap">Taxa Embarque</th>
                                <th class="text-center ws_nowrap">Outras Taxas</th>
                                <th class="text-center ws_nowrap">Desconto</th>
                                <th class="text-center ws_nowrap">Comissão</th>
                                <th class="text-center ws_nowrap">Incentivo</th>
                                <th class="text-center ws_nowrap">Total sem Taxas</th>
                                <th class="text-center ws_nowrap">Valor Total</th>
                                <th class="text-center ws_nowrap bg-warning"><span class="text-warning">Saldo a Pagar</span></th>
                                <th class="text-center ws_nowrap bg-success"><span class="">Valor Pago</span></th>
                                <th class="text-center ws_nowrap">Saldo a Faturar</th>
                            </tr>
                            <tr>
                                <td class="text-center"><span class="cifrao">R$</span> {{ number_format($RV_tarifa['valor_tarifa'],2,',','.') }}</td>
                                <td class="text-center"><span class="cifrao">R$</span> {{ number_format($RV_tarifa['tx_embarque'],2,',','.') }}</td>
                                <td class="text-center"><span class="cifrao">R$</span> {{ number_format($RV_tarifa['outras_taxas'],2,',','.') }}</td>
                                <td class="text-center"><span class="cifrao">R$</span> {{ number_format($RV_tarifa['desconto'],2,',','.') }}</td>
                                <td class="text-center"><span class="cifrao">R$</span> {{ number_format($RV_tarifa['comissao'],2,',','.') }}</td>
                                <td class="text-center"><span class="cifrao">R$</span> {{ number_format($RV_tarifa['incentivo'],2,',','.') }}</td>
                                <td class="text-center"><span class="cifrao">R$</span> {{ number_format($RV_tarifa['valor_total_sem_taxa'],2,',','.') }}</td>
                                <td class="text-center"><span class="cifrao">R$</span> {{ number_format($RV_tarifa['valor_total'],2,',','.') }}</td>
                                <td class="text-center text-warning"><span class="cifrao">R$</span> {{ number_format($RV_tarifa['saldo_a_pagar'],2,',','.') }}</td>
                                <td class="text-center text-success"><span class="cifrao">R$</span> {{ number_format($RV_tarifa['vlr_pago_'],2,',','.') }}</td>
                                <td class="text-center"><span class="cifrao">R$</span> {{ number_format($RV_tarifa['valor_a_faturar'],2,',','.') }}</td>
                            </tr>
                        </table>
                    </div>
                    <br/>
                    @if(($import_enable_btns || $export_enable_btns) && !$isPublic && !$enable_kanban)
                        @include('import', [
                            'model'                       => 'Vendas',
                            'export_options_size'         => $export_options_size,
                            'export_options_size_width'   => $export_options_size_width,
                            'export_options_size_height'  => $export_options_size_height,
                            'import_enable_btns'          => $import_enable_btns,
                            'export_enable_btns'          => $export_enable_btns,
                            'exibe_filtros'               => $exibe_filtros,
                            'lote_count'                  => $vendas_count,
                        ])
                    @endif
                    <div class="table-responsive">
                        @php
                            $defaultPositionActions = 'left';
                            function LOAD_ACTIONS_TD(
                                $auth_user__actions_enable_btns,
                                $permissaoUsuario_auth_user__controller_update,
                                $permissaoUsuario_auth_user__controller_copy,
                                $permissaoUsuario_auth_user__controller_show,
                                $permissaoUsuario_auth_user__controller_destroy,
                                $value
                            ){
                                if($auth_user__actions_enable_btns) {
                        @endphp
                        <td class="ws_nowrap">

                            @if($permissaoUsuario_auth_user__controller_update)
                                <a style="float:none;" href="{{ URL('/') }}/vendas/{{$value->id}}/edit" alt="Editar" title="Editar" class="btn btn-xs btn-default">
                                    <span class="glyphicon glyphicon-edit"></span>
                                </a>
                            @endif

                            @if(1)
                                @if($permissaoUsuario_auth_user__controller_copy)
                                    <a style="float:none;" href="{{ URL('/') }}/vendas/{{$value->id}}/copy" alt="Duplicar linha" title="Duplicar linha" class="btn btn-xs btn-default" onclick="return confirm('Tem certeza que quer duplicar a linha?')" >
                                        <span class="glyphicon glyphicon-copy"></span>
                                    </a>
                                @endif
                            @endif

                            @if($permissaoUsuario_auth_user__controller_show)
                                <a style="float:none;" href="{{ URL('/') }}/vendas/{{$value->id}}" target="_blank" alt="Visualizar" title="Visualizar" class="btn btn-xs btn-default">
                                    <span class="glyphicon glyphicon-eye-open"></span>
                                </a>
                            @endif

                            @if($permissaoUsuario_auth_user__controller_destroy)
                                <form style="display:inline-block;" method="POST" action="{{ route('vendas.destroy', $value->id) }}" accept-charset="UTF-8">
                                    {!! csrf_field() !!}
                                    {!! method_field('DELETE') !!}
                                    <button style="float:none;" type="submit" onclick="return confirm('Tem certeza que quer deletar?')" class="btn btn-xs btn-danger glyphicon glyphicon-trash"></button>
                                </form>
                            @endif
                        </td>
                        @php
                            }
                        }
                        @endphp
                        {{-- <table id="<?php echo (!$export_enable_btns ? 'datatable-no-buttons' : 'datatable'); ?>" class="display table-striped table-bordered stripe" cellspacing="0" width="100%"> --}}
                        <table id="datatable-no-buttons" class="display table-striped table-bordered stripe" cellspacing="0" width="100%">
                            <thead>
                            <tr>
                                @if($auth_user__actions_enable_btns && $defaultPositionActions == 'left')
                                    <th class="text-center" style="border: none;"> # </th>
                                @endif
                                <th class="ws_nowrap vendas_td_data" style="">Data</th>
                                <th class="ws_nowrap vendas_td_id" style="">N&deg; (Venda / Recibo)</th>
                                <th class="ws_nowrap vendas_td_tipo_de_venda bg-warning" style=""><span class="text-warning"><i class="glyphicon glyphicon-star"></i> Tipo</span></th>
                                <th class="ws_nowrap vendas_td_faturamento" style="">N° Fatura</th>
                                <th class="ws_nowrap vendas_td_forma_de_pagamento" style="">F. Pgto</th>
                                <th class="ws_nowrap vendas_td_cliente" style="">Cliente</th>
                                <th class="ws_nowrap vendas_td_localizador" style="">Localizador</th>
                                <th class="ws_nowrap no-export th_grid_table vendas_td_passageiros" style="" data-orderable="false">Passageiros</th>
                                <th class="ws_nowrap vendas_td_trecho" style="">Trecho</th>
                                <th class="ws_nowrap vendas_td_data_embarque" style="">Dt. Embarque</th>
                                <th class="ws_nowrap vendas_td_data_retorno" style="">Dt. Retorno</th>
                                <th class="ws_nowrap vendas_td_valor_tarifa" style="">Tarifa</th>
                                <th class="ws_nowrap vendas_td_comissao" style="">Comissão (DU)</th>
                                <th class="ws_nowrap vendas_td_valor_total" style="">Valor Total</th>
                                <th class="ws_nowrap vendas_td_valor_total bg-warning" style=""><span class="text-warning">Saldo a Pagar</span></th>
                                <th class="ws_nowrap vendas_td_fornecedor" style="">Fornecedor</th>
                                <th class="ws_nowrap vendas_td_dt_pgto_" style="">Dt. Pagamento Forn.</th>
                                <th class="ws_nowrap vendas_td_nr_documento_" style="">Nr. Documento:</th>
                                {{--
                                <th class="ws_nowrap vendas_td_vendedor" style="">Vendedor</th>
                                <th class="ws_nowrap vendas_td_produto" style="">Produto</th>
                                <th class="ws_nowrap vendas_td_servico" style="">Serviço</th>
                                <th class="ws_nowrap vendas_td_companhia" style="">Companhia</th>
                                <th class="ws_nowrap vendas_td_observacoes_da_venda_" style="">Observações Venda:</th>
                                <th class="ws_nowrap no-export th_grid_table vendas_td_forma_de_pgto" style="" data-orderable="false">Forma de Pagamento</th>
                                <th class="ws_nowrap vendas_td_vlr_pago_" style="">Valor Pago:</th>
                                <th class="ws_nowrap vendas_td_observacoes_" style="">Observações:</th>--}}
                                @if($auth_user__actions_enable_btns && $defaultPositionActions == 'right')
                                    <th class="text-center" style="border: none;"> # </th>
                                @endif

                            </tr>
                            </thead>
                            <tbody>
                            @foreach($vendas as $value)
                                <tr>
                                    @if($auth_user__actions_enable_btns && $defaultPositionActions == 'left')
                                        {{
                                            LOAD_ACTIONS_TD(
                                                $auth_user__actions_enable_btns,
                                                $permissaoUsuario_auth_user__controller_update,
                                                $permissaoUsuario_auth_user__controller_copy,
                                                $permissaoUsuario_auth_user__controller_show,
                                                $permissaoUsuario_auth_user__controller_destroy,
                                                $value
                                            )
                                        }}
                                    @endif
                                    <td style="" data-order="{{ $value->data }}" class='va_top ws_nowrap vendas_td_data'>{{(isset($value->data)) ? date("d/m/Y", strtotime($value->data)) : '---'}}</td>
                                    <td style="" class='va_top ws_nowrap vendas_td_id'><strong class="fw-600">{{ $value->id }}</strong></td>
                                    <td style="" class='va_top ws_nowrap vendas_td_tipo_de_venda'><strong class="fw-600">{{(!empty($value->Get_tipo_de_venda())?mb_strtoupper($value->Get_tipo_de_venda()):'---')}}</strong></td>
                                    <td style="" class='va_top ws_nowrap vendas_td_faturamento'><strong class="fw-600">{{!empty($value->faturamento)?$value->faturamento:'---'}}</strong></td>
                                    <td style=""  class='va_top ws_nowrap vendas_td_forma_de_pagamento td_grid_table'>
                                        @if($value->VendasGridPagamentos and count($value->VendasGridPagamentos))
                                            <table class="grid_table" cellspacing="0" width="100%">
                                                @foreach($value->VendasGridPagamentos as $k => $vgp)
                                                    <tr>
                                                        <td>{{ ($vgp->FormaDePagamento?$vgp->FormaDePagamento->forma_de_pagamento:'---') }}</td>
                                                        <td>R$&nbsp;&nbsp;{{ (!empty($vgp->valor_pago)?number_format($vgp->valor_pago,2,',','.'):'---') }}</td>
                                                        <td>{{(isset($vgp->data_pagamento)) ? date("d/m/Y", strtotime($vgp->data_pagamento)) : '---'}}</td>
                                                    </tr>
                                                @endforeach
                                            </table>
                                        @else
                                            ---
                                        @endif
                                    </td>
                                    <td style="" class='va_top ws_nowrap vendas_td_cliente'>
                                        @if($value->Cliente && isset($value->Cliente->nome_do_cliente))
                                            {{--<a data-toggle="modal" href="{{ route('clientes.modal', $value->Cliente->id) }}" data-target="#myModal">{{$value->Cliente->nome_do_cliente}}</a>--}}
                                            {{$value->Cliente->nome_do_cliente}}
                                        @else
                                            ---
                                        @endif
                                    </td>
                                    <td style="text-transform: uppercase;"  class="va_top ws_nowrap vendas_td_localizador"><strong class="text-warning">{{!empty($value->localizador)?$value->localizador:'---'}}</strong></td>
                                    <td style="" class='va_top ws_nowrap vendas_td_passageiros td_grid_table'>
                                        @if($value->VendasGridPassageiros and count($value->VendasGridPassageiros))
                                            <table class="grid_table" cellspacing="0" width="100%">
                                                <!--<tr><th>Passageiros</th></tr>-->
                                                @foreach($value->VendasGridPassageiros as $k => $vgrid)
                                                    <tr>
                                                        <td>
                                                            @if($vgrid->Passageiros && isset($vgrid->Passageiros->nome))
                                                                {!!'<strong class="fw-600">*</strong>'.($k+1).': '.$vgrid->Passageiros->nome!!}
                                                            @else
                                                                ---
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </table>
                                        @else
                                            ---
                                        @endif
                                    </td>
                                    <td style=""  class='va_top ws_nowrap vendas_td_trecho'>
                                        @if($value->Trecho && isset($value->Trecho->trechos))
                                            {{--<a data-toggle="modal" href="{{ route('trechos.modal', $value->Trecho->id) }}" data-target="#myModal">{{$value->Trecho->trechos}}</a>--}}
                                            {{$value->Trecho->trechos}}
                                        @else
                                            ---
                                        @endif
                                    </td>
                                    <td style="" data-order="{{ $value->data_embarque }}" class='va_top ws_nowrap vendas_td_data_embarque'>{{(isset($value->data_embarque)) ? date("d/m/Y", strtotime($value->data_embarque)) : '---'}}</td>
                                    <td style="" data-order="{{ $value->data_retorno }}" class='va_top ws_nowrap vendas_td_data_retorno'>{{(isset($value->data_retorno)) ? date("d/m/Y", strtotime($value->data_retorno)) : '---'}}</td>
                                    <td style=""  class='va_top ws_nowrap vendas_td_valor_tarifa'>{{(!empty($value->valor_tarifa)?number_format($value->valor_tarifa,2,',','.'):'---')}}</td>
                                    <td style=""  class='va_top ws_nowrap vendas_td_comissao'>{{(!empty($value->comissao)?number_format($value->comissao,2,',','.'):'---')}}</td>
                                    <td style=""  class='va_top ws_nowrap vendas_td_valor_total'>{{!empty($value->valor_total)?$value->valor_total:'---'}}</td>
                                    <td style=""  class='va_top ws_nowrap vendas_td_valor_total text-warning'>{{!empty($value->saldo_a_pagar)?$value->saldo_a_pagar:'---'}}</td>
                                    <td style=""  class='va_top ws_nowrap vendas_td_fornecedor'>
                                        @if($value->Fornecedor && isset($value->Fornecedor->fornecedor))
                                            {{--<a data-toggle="modal" href="{{ route('fornecedores.modal', $value->Fornecedor->id) }}" data-target="#myModal">{{$value->Fornecedor->fornecedor}}</a>--}}
                                            {{$value->Fornecedor->fornecedor}}
                                        @else
                                            ---
                                        @endif
                                    </td>
                                    <td style="" data-order="{{ $value->dt_pgto_ }}" class='va_top ws_nowrap vendas_td_dt_pgto_'>{{(isset($value->dt_pgto_)) ? date("d/m/Y", strtotime($value->dt_pgto_)) : '---'}}</td>
                                    <td style=""  class='va_top ws_nowrap vendas_td_nr_documento_'>{{!empty($value->nr_documento_)?$value->nr_documento_:'---'}}</td>
                                    {{--
                                    <td style=""  class='ws_nowrap vendas_td_vendedor'>
                                         @if($value->Vendedor && isset($value->Vendedor->nome_do_vendedor))
                                             <a data-toggle="modal" href="{{ route('vendedores.modal', $value->Vendedor->id) }}" data-target="#myModal">{{$value->Vendedor->nome_do_vendedor}}</a>
                                         @endif
                                    </td>
                                    <td style=""  class='ws_nowrap vendas_td_produto'>
                                         @if($value->Produto && isset($value->Produto->produto))
                                             <a data-toggle="modal" href="{{ route('produtos.modal', $value->Produto->id) }}" data-target="#myModal">{{$value->Produto->produto}}</a>
                                         @endif
                                    </td>
                                    <td style=""  class='ws_nowrap vendas_td_servico'>
                                         @if($value->Servico && isset($value->Servico->servico))
                                             <a data-toggle="modal" href="{{ route('servicos.modal', $value->Servico->id) }}" data-target="#myModal">{{$value->Servico->servico}}</a>
                                         @endif
                                    </td>
                                    <td style=""  class='ws_nowrap vendas_td_companhia'>
                                         @if($value->Companhia && isset($value->Companhia->companhia))
                                             <a data-toggle="modal" href="{{ route('companhias.modal', $value->Companhia->id) }}" data-target="#myModal">{{$value->Companhia->companhia}}</a>
                                         @endif
                                    </td>
                                    <td style=""  class='ws_nowrap vendas_td_observacoes_da_venda_'>{{$value->observacoes_da_venda_}}</td>
                                    <td style="" class='ws_nowrap vendas_td_forma_de_pgto td_grid_table'>@if($value->VendasGridPagamentos and count($value->VendasGridPagamentos))<table class="grid_table" cellspacing="0" width="100%">--}}{{--<tr><th>Forma de Pagamento</th><th>Valor Pago</th></tr>--}}{{--@foreach($value->VendasGridPagamentos as $vgrid)<tr><td>
                                         @if($vgrid->FormaDePagamento && isset($vgrid->FormaDePagamento->forma_de_pagamento))
                                            {{$vgrid->FormaDePagamento->forma_de_pagamento}}
                                         @endif
                                    </td><td>{{(!empty($vgrid->valor_pago)?number_format($vgrid->valor_pago,2,',','.'):'0,00')}}</td></tr>@endforeach</table>@endif</td>
                                    <td style=""  class='ws_nowrap vendas_td_vlr_pago_'>{{(!empty($value->vlr_pago_)?number_format($value->vlr_pago_,2,',','.'):'0,00')}}</td>
                                    <td style=""  class='ws_nowrap vendas_td_observacoes_'>{{$value->observacoes_}}</td>--}}
                                    @if($auth_user__actions_enable_btns && $defaultPositionActions == 'right')
                                        {{
                                            LOAD_ACTIONS_TD(
                                                $auth_user__actions_enable_btns,
                                                $permissaoUsuario_auth_user__controller_update,
                                                $permissaoUsuario_auth_user__controller_copy,
                                                $permissaoUsuario_auth_user__controller_show,
                                                $permissaoUsuario_auth_user__controller_destroy,
                                                $value
                                            )
                                        }}
                                    @endif
                                </tr>
                            @endforeach
                            </tbody>

                        </table>
                    </div>
                    <br>
                    <br>

                    <div class="form-group form-group-btn-index">
                        @if(!$isPublic)
                            <a href="{{ URL::previous() }}" class="btn btn-xs btn-default form-group-btn-index-voltar"><i class="glyphicon glyphicon-backward"></i> Voltar</a>
                        @endif
                        @if(\App\Models\Permissions::permissaoUsuario(\Auth::user(), "$controller@store") OR $isPublic)
                            <a href="{{ URL('/') }}/vendas/create" class="btn btn-xs btn-default right form-group-btn-index-cadastrar"><i class="glyphicon glyphicon-plus"></i> Cadastrar</a>
                        @endif
                    </div>
                </div>
            </div>
        @else
            @include('vendas.kanban', [
                'vendas' => $vendas, // # -
                'kanban_field'    => $kanban_field,
                //'kanban_list' => $kanban_list, // # -
                'controller' => $controller,
                'controller_model' => $controller_model, // # -
                'isPublic' => $isPublic
            ])
        @endif

    </section>

@endsection

@section('script')

    @include('datatable', ['key' => 0, 'order' => 'desc'])

    <script type="text/javascript">
        $("select#input_tipo_de_venda").closest(".form-group").addClass('inpsel-destaque-st1');
        $(
            `#input_valor_tarifa,
            #input_tx_embarque,
            #input_outras_taxas,
            #input_desconto,
            #input_comissao`
        )
            .on('change keyup',function(){
                venda_calculo_total();
            });
        function venda_calculo_total(){
            console.log('foi');
            let valor_tarifa  = parseFloat(($("#input_valor_tarifa").val()!="")?$("#input_valor_tarifa").val():0);
            let tx_embarque   = parseFloat(($("#input_tx_embarque").val()!="")?$("#input_tx_embarque").val():0);
            let outras_taxas  = parseFloat(($("#input_outras_taxas").val()!="")?$("#input_outras_taxas").val():0);
            let desconto  	  = parseFloat(($("#input_desconto").val()!="")?$("#input_desconto").val():0);
            let comissao  	  = parseFloat(($("#input_comissao").val()!="")?$("#input_comissao").val():0);
            let valor_total   = ((valor_tarifa + tx_embarque + outras_taxas + comissao) - desconto);
            $("#input_valor_total").val(valor_total.toFixed(2));
        }

        // # -
        $('[open-iframe]').on('click',function(){
            var _this = $(this);
            $('#myModal').addClass('open-iframe');
            $('#myModal .modal-content').html(
                '<div class="box-modal-btns"><button type="button" class="btn btn-xs btn-danger btn-fechar">Fechar</button></div>'+
                '<iframe class="iframe-modal" width="100%" height="100%" src="'+_this.attr('href')+'?modal=true"></iframe>'
            );
            $('#myModal .modal-content').on('click',function(){
                $('#myModal').modal('hide');
            });
            $('#myModal').modal('show');
            return false;
        });
        $('#myModal').on('shown.bs.modal', function () { /*...*/ });
        $('#myModal').on('hidden.bs.modal', function () {
            $('#myModal').removeClass('open-iframe');
            $('#myModal .modal-content').html('');
        });
        function modal_hide(){
            $('#myModal').modal('hide');
            if($(".form_filter").length){
                $("form.form_filter").submit();
            }else {
                window.location.reload(true);
            }
        }

        $(".select_relationship option[value='']").prop('selected',false);
        $(".select_relationship").selectpicker('refresh');
    </script>

@endsection
