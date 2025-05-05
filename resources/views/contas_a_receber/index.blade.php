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

    /*foreach($contas_a_receber as $val) {

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

<section class="content-header Contas a Receber_index">
    <h1>Contas a Receber</h1>
</section>

<section class="content">

    @if($exibe_filtros)
        <div class="box-header" style="background-color: #fff; padding-top: 15px">
            <form action="{{ URL('/') }}/contas_a_receber/filter" class="form_filter form_filter_contas_a_receber" method="POST">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <div class="row">

                    <div size="3" class="inputbox col-md-3">
<div class="form-group">
                    {!! Form::label('','Empresa') !!}
                    {!! Form::select('empresa', $cadastro_de_empresas_nome_fantas, (!empty(\Request::post('empresa'))?\Request::post('empresa'):null), ['class' => 'form-control  select_relationship ', 'data-sub' => '', 'data-live-search' => 'true',  'data-none-selected-text' => true, 'controller' => 'cadastro_de_empresas' ]) !!}
</div>
</div>
<div size="3" class="inputbox col-md-3">
<div class="form-group">
                    {!! Form::label('','Cliente') !!}
                    {!! Form::select('cliente', $clientes_nome_do_cliente, (!empty(\Request::post('cliente'))?\Request::post('cliente'):null), ['class' => 'form-control  select_relationship ', 'data-sub' => '', 'data-live-search' => 'true',  'data-none-selected-text' => true, 'controller' => 'clientes' ]) !!}
</div>
</div>
<div size="3" class="inputbox col-md-3">
<div class="form-group">
                    {!! Form::label('','Tipo de documento') !!}
                    {!! Form::text('tipo_de_documento', (!empty(\Request::post('tipo_de_documento'))?\Request::post('tipo_de_documento'):null), ['class' => 'form-control' ]) !!}
</div>
</div>
<div size="3" class="inputbox col-md-3">
<div class="form-group">
                    {!! Form::label('','N° do documento') !!}
                    {!! Form::text('n_do_documento', (!empty(\Request::post('n_do_documento'))?\Request::post('n_do_documento'):null), ['class' => 'form-control' ]) !!}
</div>
</div>
<div size="3" class="inputbox col-md-3">
<div class="form-group">
                    {!! Form::label('','Vendedor') !!}
                    {!! Form::select('vendedor', $vendedores_nome_do_vendedor, (!empty(\Request::post('vendedor'))?\Request::post('vendedor'):null), ['class' => 'form-control  select_relationship ', 'data-sub' => '', 'data-live-search' => 'true',  'data-none-selected-text' => true, 'controller' => 'vendedores' ]) !!}
</div>
</div>
<div size="3" class="inputbox col-md-3">
<div class="form-group">
                    {!! Form::label('','Data do Vencimento') !!}
                    <div class='input-group'>
                        <div class='input-group-addon'>
                            <i class='fa fa-calendar'></i>
                        </div>
                    {!! Form::text('data_do_vencimento', (!empty(\Request::post('data_do_vencimento'))?\Request::post('data_do_vencimento'):null), ['class' => 'form-control data' ]) !!}
                    </div>
</div>
</div>
<div size="3" class="inputbox col-md-3">
<div class="form-group">
                    {!! Form::label('','Primeira Data') !!}
                    <div class='input-group'>
                        <div class='input-group-addon'>
                            <i class='fa fa-calendar'></i>
                        </div>
                    {!! Form::text('primeira_data', (!empty(\Request::post('primeira_data'))?\Request::post('primeira_data'):null), ['class' => 'form-control data' ]) !!}
                    </div>
</div>
</div>
<div size="3" class="inputbox col-md-3">
<div class="form-group">
                    {!! Form::label('','Data do Recebimento') !!}
                    <div class='input-group'>
                        <div class='input-group-addon'>
                            <i class='fa fa-calendar'></i>
                        </div>
                    {!! Form::text('data_do_recebimento', (!empty(\Request::post('data_do_recebimento'))?\Request::post('data_do_recebimento'):null), ['class' => 'form-control data' ]) !!}
                    </div>
</div>
</div>
<div size="3" class="inputbox col-md-3">
<div class="form-group">
                    {!! Form::label('','Forma de pagamento') !!}
                    {!! Form::select('forma_de_pagamento', $formas_de_pagamentos_forma_de_pa, (!empty(\Request::post('forma_de_pagamento'))?\Request::post('forma_de_pagamento'):null), ['class' => 'form-control  select_relationship ', 'data-sub' => '', 'data-live-search' => 'true',  'data-none-selected-text' => true, 'controller' => 'formas_de_pagamentos' ]) !!}
</div>
</div>

                    <div class="col-md-12 btnsFiltro">
                        <a href="{{ URL('/contas_a_receber') }}" class="btn btn-xs btn-default" style="float: left; margin-right: 5px;"><i class="glyphicon glyphicon-trash"></i> Limpar</a>
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
                        'model'                       => 'ContasAReceber',
                        'export_options_size'         => $export_options_size,
                        'export_options_size_width'   => $export_options_size_width,
                        'export_options_size_height'  => $export_options_size_height,
                        'import_enable_btns'          => $import_enable_btns,
                        'export_enable_btns'          => $export_enable_btns,
                        'exibe_filtros'               => $exibe_filtros,
                        'lote_count'                  => $contas_a_receber_count
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
                                    <a style="float:none;" href="{{ URL('/') }}/contas_a_receber/{{$value->id}}/edit" alt="Editar" title="Editar" class="btn btn-xs btn-default">
                                        <span class="glyphicon glyphicon-edit"></span>
                                    </a>
                                @endif

                                @if(1)
                                    @if($permissaoUsuario_auth_user__controller_copy)
                                        <a style="float:none;" href="{{ URL('/') }}/contas_a_receber/{{$value->id}}/copy" alt="Duplicar linha" title="Duplicar linha" class="btn btn-xs btn-default" onclick="return confirm('Tem certeza que quer duplicar a linha?')" >
                                            <span class="glyphicon glyphicon-copy"></span>
                                        </a>
                                    @endif
                                @endif

                                @if($permissaoUsuario_auth_user__controller_show)
                                    <a style="float:none;" href="{{ URL('/') }}/contas_a_receber/{{$value->id}}" alt="Visualizar" title="Visualizar" class="btn btn-xs btn-default">
                                        <span class="glyphicon glyphicon-eye-open"></span>
                                    </a>
                                @endif

                                @if($permissaoUsuario_auth_user__controller_destroy)
                                    <form style="display:inline-block;" method="POST" action="{{ route('contas_a_receber.destroy', $value->id) }}" accept-charset="UTF-8">
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

                            <th class="contas_a_receber_td_empresa" style="white-space: nowrap;">Empresa</th>
                            <th class="contas_a_receber_td_cliente" style="white-space: nowrap;">Cliente</th>
                            <th class="contas_a_receber_td_tipo_de_documento" style="white-space: nowrap;">Tipo de documento</th>
                            <th class="contas_a_receber_td_n_do_documento" style="white-space: nowrap;">N° do documento</th>
                            <th class="contas_a_receber_td_vendedor" style="white-space: nowrap;">Vendedor</th>
                            <th class="contas_a_receber_td_descricao_do_recebimento" style="white-space: nowrap;">Descrição do Recebimento</th>
                            <th class="contas_a_receber_td_data_do_vencimento" style="white-space: nowrap;">Data do Vencimento</th>
                            <th class="sum">Valor à Receber</th>
                            <th class="contas_a_receber_td_tipo_do_valor" style="white-space: nowrap;">Tipo do valor</th>
                            <th class="contas_a_receber_td_n_de_parcelas" style="white-space: nowrap;">N° de Parcelas</th>
                            <th class="contas_a_receber_td_primeira_data" style="white-space: nowrap;">Primeira Data</th>
                            <th class="contas_a_receber_td_data_do_recebimento" style="white-space: nowrap;">Data do Recebimento</th>
                            <th class="contas_a_receber_td_valor_recebido" style="white-space: nowrap;">Valor Recebido</th>
                            <th class="contas_a_receber_td_forma_de_pagamento" style="white-space: nowrap;">Forma de pagamento</th>
                            <th class="contas_a_receber_td_status" style="white-space: nowrap;">Status</th>
                            <th class="contas_a_receber_td_comprovante" style="white-space: nowrap;">Comprovante</th>

                            @if($auth_user__actions_enable_btns && $defaultPositionActions == 'right')
                                <th class="text-center" style="border: none;"> # </th>
                            @endif

                        </tr>
                    </thead>

                    <tbody>
                        @foreach($contas_a_receber as $value)

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

                                <td style=' white-space: nowrap; '  class='contas_a_receber_td_empresa'>
     @if($value->Empresa && isset($value->Empresa->nome_fantasia_empresa))
         <a data-toggle="modal" href="{{ route('cadastro_de_empresas.modal', $value->Empresa->id) }}" data-target="#myModal">{{$value->Empresa->nome_fantasia_empresa}}</a>
     @endif
</td>

                                <td style=' white-space: nowrap; '  class='contas_a_receber_td_cliente'>
     @if($value->Cliente && isset($value->Cliente->nome_do_cliente))
         <a data-toggle="modal" href="{{ route('clientes.modal', $value->Cliente->id) }}" data-target="#myModal">{{$value->Cliente->nome_do_cliente}}</a>
     @endif
</td>

                                <td style=' white-space: nowrap; '  class='contas_a_receber_td_tipo_de_documento'>{{$value->tipo_de_documento}}</td>

                                <td style=' white-space: nowrap; '  class='contas_a_receber_td_n_do_documento'>{{$value->n_do_documento}}</td>

                                <td style=' white-space: nowrap; '  class='contas_a_receber_td_vendedor'>
     @if($value->Vendedor && isset($value->Vendedor->nome_do_vendedor))
         <a data-toggle="modal" href="{{ route('vendedores.modal', $value->Vendedor->id) }}" data-target="#myModal">{{$value->Vendedor->nome_do_vendedor}}</a>
     @endif
</td>

                                <td style=' white-space: nowrap; '  class='contas_a_receber_td_descricao_do_recebimento'>{{$value->descricao_do_recebimento}}</td>

                                <td style=' white-space: nowrap; ' data-order="{{ implode('-', array_reverse(explode('/', $value->data_do_vencimento))) }}" class='contas_a_receber_td_data_do_vencimento'>{{$value->data_do_vencimento}}</td>

                                <td style=' white-space: nowrap; '  class='contas_a_receber_td_valor_a_receber'>{{$value->valor_a_receber}}</td>

                                <td style=' white-space: nowrap; '  class='contas_a_receber_td_tipo_do_valor'>{{ $value->Get_tipo_do_valor() }}</td>

                                <td style=' white-space: nowrap; '  class='contas_a_receber_td_n_de_parcelas'>{{$value->n_de_parcelas}}</td>

                                <td style=' white-space: nowrap; ' data-order="{{ implode('-', array_reverse(explode('/', $value->primeira_data))) }}" class='contas_a_receber_td_primeira_data'>{{$value->primeira_data}}</td>

                                <td style=' white-space: nowrap; ' data-order="{{ implode('-', array_reverse(explode('/', $value->data_do_recebimento))) }}" class='contas_a_receber_td_data_do_recebimento'>{{$value->data_do_recebimento}}</td>

                                <td style=' white-space: nowrap; '  class='contas_a_receber_td_valor_recebido'>{{$value->valor_recebido}}</td>

                                <td style=' white-space: nowrap; '  class='contas_a_receber_td_forma_de_pagamento'>
     @if($value->FormaDePagamento && isset($value->FormaDePagamento->forma_de_pagamento))
         <a data-toggle="modal" href="{{ route('formas_de_pagamentos.modal', $value->FormaDePagamento->id) }}" data-target="#myModal">{{$value->FormaDePagamento->forma_de_pagamento}}</a>
     @endif
</td>

                                <td style=' white-space: nowrap; '  class='contas_a_receber_td_status'>{{ $value->Get_status() }}</td>

                                <td style=' white-space: nowrap; '  class='contas_a_receber_td_comprovante'>

@if($value->comprovante && count(explode(".", $value->comprovante)) >= 2)

<a class="fancybox" rel="gallery1" target="_blank" href="{{in_array(explode(".", $value->comprovante)[1], array("jpg", "jpeg", "gif", "png", "bmp", "mp4", "pdf", "doc", "docx", "rar", "zip", "txt", "7zip", "csv", "xls", "xlsx")) ? $fileurlbase . "images/" . $value->comprovante : "javascript:void(0);"}}">
                                    <img src="{{in_array(explode(".", $value->comprovante)[1], array("mp4", "pdf", "doc", "docx", "rar", "zip", "txt", "7zip", "csv", "xls", "xlsx")) ? explode(".", $value->comprovante)[1] . "-icon.png" : $fileurlbase . "images/" . $value->comprovante}}" width="30">
                                </a>
@endif
</td>

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
<th>&nbsp</th>
<th>&nbsp</th>
<th>&nbsp</th>
<th>&nbsp</th>
<th>&nbsp</th>
<th>&nbsp</th>
<th>&nbsp</th>
<th>&nbsp</th>
<th>&nbsp</th>
<th>&nbsp</th>
<th>&nbsp</th>
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
                        <a href="{{ URL('/') }}/contas_a_receber/create" class="btn btn-xs btn-default right form-group-btn-index-cadastrar"><i class="glyphicon glyphicon-plus"></i> Cadastrar</a>
                    @endif
                </div>
            </div>
        </div>
    @else
        @include('contas_a_receber.kanban', [
            'contas_a_receber' => $contas_a_receber, // # -
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
