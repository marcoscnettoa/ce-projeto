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

    /*foreach($fluxo_de_caixa as $val) {

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

<section class="content-header Fluxo de caixa_index">
    <h1>Fluxo de caixa</h1>
</section>

<section class="content">

    @if($exibe_filtros)
        <div class="box-header" style="background-color: #fff; padding-top: 15px">
            <form action="{{ URL('/') }}/fluxo_de_caixa/filter" class="form_filter form_filter_fluxo_de_caixa" method="POST">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <div class="row">

                    <div size="3" class="inputbox col-md-3">
<div class="form-group">
                    {!! Form::label('','Movimentação') !!}
                    {!! Form::text('movimentacao', (!empty(\Request::post('movimentacao'))?\Request::post('movimentacao'):null), ['class' => 'form-control' ]) !!}
</div>
</div>
<div size="3" class="inputbox col-md-3">
<div class="form-group">
                    {!! Form::label('','Data do Recebimento') !!}
                    <div class='input-group'>
                        <div class='input-group-addon'>
                            <i class='glyphicon glyphicon-calendar'></i>
                        </div>
                    {!! Form::text('data_do_recebimento', (!empty(\Request::post('data_do_recebimento'))?\Request::post('data_do_recebimento'):null), ['class' => 'form-control data' ]) !!}
                    </div>
</div>
</div>
<div size="3" class="inputbox col-md-3">
<div class="form-group">
                    {!! Form::label('','Valor Recebido') !!}
                    <div class='input-group'>
                        <div class='input-group-addon'>
                            <i class='glyphicon glyphicon-usd'></i>
                        </div>
                    {!! Form::text('valor_recebido', (!empty(\Request::post('valor_recebido'))?\Request::post('valor_recebido'):null), ['class' => 'form-control money' ]) !!}
                    </div>
</div>
</div>
<div size="3" class="inputbox col-md-3">
<div class="form-group">
                    {!! Form::label('','Data do Pagamento') !!}
                    <div class='input-group'>
                        <div class='input-group-addon'>
                            <i class='glyphicon glyphicon-calendar'></i>
                        </div>
                    {!! Form::text('data_do_pagamento', (!empty(\Request::post('data_do_pagamento'))?\Request::post('data_do_pagamento'):null), ['class' => 'form-control data' ]) !!}
                    </div>
</div>
</div>
<div size="3" class="inputbox col-md-3">
<div class="form-group">
                    {!! Form::label('','Total a Pagar') !!}
                    <div class='input-group'>
                        <div class='input-group-addon'>
                            <i class='glyphicon glyphicon-usd'></i>
                        </div>
                    {!! Form::text('total_a_pagar', (!empty(\Request::post('total_a_pagar'))?\Request::post('total_a_pagar'):null), ['class' => 'form-control money' ]) !!}
                    </div>
</div>
</div>
<div size="3" class="inputbox col-md-3">
<div class="form-group">
                    {!! Form::label('','Data Atual') !!}
<div class='row'>
    <div class='col-md-4'>
 {!! Form::select('operador[data_atual]', ['contem' => 'Contém', 'entre' => 'Entre', '=' => '=', '>' => '>', '>=' => '>=', '<' => '<', '<=' => '<='], (!empty(\Request::post('operador')['data_atual'])?\Request::post('operador')['data_atual']:null), ['class' => 'form-control operador' ]) !!}
    </div>
    <div class='col-md-8' style='padding-left: 0px;'>
                    <div class='input-group'>
                        <div class='input-group-addon'>
                            <i class='glyphicon glyphicon-calendar'></i>
                        </div>
                    {!! Form::text('data_atual', (!empty(\Request::post('data_atual'))?\Request::post('data_atual'):null), ['class' => 'form-control data' ]) !!}
                    </div>
        <div class='between' style='margin-bottom:0px; margin-top: 5px; display: none;'>
                    {!! Form::label('','&') !!}                    <div class='input-group'>
                        <div class='input-group-addon'>
                            <i class='glyphicon glyphicon-calendar'></i>
                        </div>
                    {!! Form::text('between[data_atual]', (!empty(\Request::post('between')['data_atual'])?\Request::post('between')['data_atual']:null), ['class' => 'form-control data' ]) !!}
                    </div>
        </div>
            </div>
        </div>
</div>
</div>

                    <div class="col-md-12 btnsFiltro">
                        <a href="{{ URL('/fluxo_de_caixa') }}" class="btn btn-xs btn-default" style="float: left; margin-right: 5px;"><i class="glyphicon glyphicon-trash"></i> Limpar</a>
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

            <div class="box-body">
                @if(($import_enable_btns || $export_enable_btns) && !$isPublic && !$enable_kanban)
                    @include('import', [
                        'model'                       => 'FluxoDeCaixa',
                        'export_options_size'         => $export_options_size,
                        'export_options_size_width'   => $export_options_size_width,
                        'export_options_size_height'  => $export_options_size_height,
                        'import_enable_btns'          => $import_enable_btns,
                        'export_enable_btns'          => $export_enable_btns,
                        'exibe_filtros'               => $exibe_filtros,
                        'lote_count'                  => $fluxo_de_caixa_count
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
                             <td style="width:100px; white-space: nowrap;">

                                @if($permissaoUsuario_auth_user__controller_update)
                                    <a style="float:none;" href="{{ URL('/') }}/fluxo_de_caixa/{{$value->id}}/edit" alt="Editar" title="Editar" class="btn btn-xs btn-default">
                                        <span class="glyphicon glyphicon-edit"></span>
                                    </a>
                                @endif

                                @if(1)
                                    @if($permissaoUsuario_auth_user__controller_copy)
                                        <a style="float:none;" href="{{ URL('/') }}/fluxo_de_caixa/{{$value->id}}/copy" alt="Duplicar linha" title="Duplicar linha" class="btn btn-xs btn-default" onclick="return confirm('Tem certeza que quer duplicar a linha?')" >
                                            <span class="glyphicon glyphicon-copy"></span>
                                        </a>
                                    @endif
                                @endif

                                @if($permissaoUsuario_auth_user__controller_show)
                                    <a style="float:none;" href="{{ URL('/') }}/fluxo_de_caixa/{{$value->id}}" alt="Visualizar" title="Visualizar" class="btn btn-xs btn-default">
                                        <span class="glyphicon glyphicon-eye-open"></span>
                                    </a>
                                @endif

                                @if($permissaoUsuario_auth_user__controller_destroy)
                                    <form style="display:inline-block;" method="POST" action="{{ route('fluxo_de_caixa.destroy', $value->id) }}" accept-charset="UTF-8">
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

                            <th class="fluxo_de_caixa_td_movimentacao" style="white-space: nowrap;">Movimentação</th>
                                                        <th class="sum">Valor Recebido</th>
                                                        <th class="sum">Total a Pagar</th>
                            <th class="fluxo_de_caixa_td_data_atual" style="white-space: nowrap;">Data Atual</th>
                            <th class="fluxo_de_caixa_td_ghost_camp" style="white-space: nowrap;">Calculando</th>
                            <th class="sum">Saldo da Transação</th>

                            @if($auth_user__actions_enable_btns && $defaultPositionActions == 'right')
                                <th class="text-center" style="border: none;"> # </th>
                            @endif

                        </tr>
                    </thead>

                    <tbody>
                        @foreach($fluxo_de_caixa as $value)

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

                                <td style=' white-space: nowrap; '  class='fluxo_de_caixa_td_movimentacao'>{{$value->movimentacao}}</td>

                                                                <td style=' white-space: nowrap; '  class='fluxo_de_caixa_td_valor_recebido'>{{$value->valor_recebido}}</td>

                                                                <td style=' white-space: nowrap; '  class='fluxo_de_caixa_td_total_a_pagar'>{{$value->total_a_pagar}}</td>

                                <td style=' white-space: nowrap; ' data-order="{{ implode('-', array_reverse(explode('/', $value->data_atual))) }}" class='fluxo_de_caixa_td_data_atual'>{{$value->data_atual}}</td>

                                <td style=' white-space: nowrap; '  class='fluxo_de_caixa_td_ghost_camp'>{{$value->ghost_camp}}</td>

                                <td style=' white-space: nowrap; '  class='fluxo_de_caixa_td_saldo_da_transacao'>{{$value->saldo_da_transacao}}</td>

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

                    <tfoot class='tfoot' style='display: none;'>
    <tr>
        <th>&nbsp</th>
<th>&nbsp</th>
<th>&nbsp</th>
<th>&nbsp</th>
<th>Saldo Final  -></th>
<th>&nbsp</th>

        <th>&nbsp</th>
    </tr>
</tfoot>

                </table>
                </div>
                <br>
                <br>

                <div class="form-group form-group-btn-index">
                    @if(!$isPublic)
                        <a href="{{ URL::previous() }}" class="btn btn-xs btn-default form-group-btn-index-voltar"><i class="glyphicon glyphicon-backward"></i> Voltar</a>
                    @endif
                    @if(\App\Models\Permissions::permissaoUsuario(\Auth::user(), "$controller@store") OR $isPublic)
                        <a href="{{ URL('/') }}/fluxo_de_caixa/create" class="btn btn-xs btn-default right form-group-btn-index-cadastrar"><i class="glyphicon glyphicon-plus"></i> Cadastrar</a>
                    @endif
                </div>
            </div>
        </div>
    @else
        @include('fluxo_de_caixa.kanban', [
            'fluxo_de_caixa' => $fluxo_de_caixa, // # -
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
    </script>

@endsection
