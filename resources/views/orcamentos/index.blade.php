@php

    $isPublic                   = 0;
    $controller                 = get_class(\Request::route()->getController());
    $enable_kanban              = 0;
    $kanban_field               = '';
    $import_enable_btns         = 0;
    $export_enable_btns         = 1;
    $export_options_size        = 'custom'; // # -
    $export_options_size_height = 3000; // # -
    $export_options_size_width  = 3000; // # -
    $actions_enable_btns        = 1;

    //$kanban_list = array();

    /*foreach($orcamentos as $val) {

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

<section class="content-header Orçamentos_index">
    <h1>Orçamentos</h1>
</section>

<section class="content">

    @if($exibe_filtros)
        <div class="box-header" style="background-color: #fff; padding-top: 15px">
            <form action="{{ URL('/') }}/orcamentos/filter" class="form_filter form_filter_orcamentos" method="POST">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <div class="row">

                    <div size="3" class="inputbox col-md-3">
<div class="form-group">
                    {!! Form::label('','Número') !!}
                    {!! Form::text('numero', (!empty(\Request::post('numero'))?\Request::post('numero'):null), ['class' => 'form-control' ]) !!}
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
                    {!! Form::label('','Clilente') !!}
                    {!! Form::text('clilente', (!empty(\Request::post('clilente'))?\Request::post('clilente'):null), ['class' => 'form-control' ]) !!}
</div>
</div>
<div size="3" class="inputbox col-md-3">
<div class="form-group">
                    {!! Form::label('','Vendedor') !!}
                    {!! Form::text('vendedor', (!empty(\Request::post('vendedor'))?\Request::post('vendedor'):null), ['class' => 'form-control' ]) !!}
</div>
</div>
<div size="3" class="inputbox col-md-3">
<div class="form-group">
                    {!! Form::label('','Produto') !!}
                    {!! Form::text('produto', (!empty(\Request::post('produto'))?\Request::post('produto'):null), ['class' => 'form-control' ]) !!}
</div>
</div>

                    <div class="col-md-12 btnsFiltro">
                        <a href="{{ URL('/orcamentos') }}" class="btn btn-xs btn-default" style="float: left; margin-right: 5px;"><i class="glyphicon glyphicon-trash"></i> Limpar</a>
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
                        'model'                       => 'Orcamentos',
                        'export_options_size'         => $export_options_size,
                        'export_options_size_width'   => $export_options_size_width,
                        'export_options_size_height'  => $export_options_size_height,
                        'import_enable_btns'          => $import_enable_btns,
                        'export_enable_btns'          => $export_enable_btns,
                        'exibe_filtros'               => $exibe_filtros,
                        'lote_count'                  => $orcamentos_count
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
                                    <a style="float:none;" href="{{ URL('/') }}/orcamentos/{{$value->id}}/edit" alt="Editar" title="Editar" class="btn btn-xs btn-default">
                                        <span class="glyphicon glyphicon-edit"></span>
                                    </a>
                                @endif

                                @if(1)
                                    @if($permissaoUsuario_auth_user__controller_copy)
                                        <a style="float:none;" href="{{ URL('/') }}/orcamentos/{{$value->id}}/copy" alt="Duplicar linha" title="Duplicar linha" class="btn btn-xs btn-default" onclick="return confirm('Tem certeza que quer duplicar a linha?')" >
                                            <span class="glyphicon glyphicon-copy"></span>
                                        </a>
                                    @endif
                                @endif

                                @if($permissaoUsuario_auth_user__controller_show)
                                    <a style="float:none;" href="{{ URL('/') }}/orcamentos/{{$value->id}}" alt="Visualizar" title="Visualizar" class="btn btn-xs btn-default">
                                        <span class="glyphicon glyphicon-eye-open"></span>
                                    </a>
                                @endif

                                @if($permissaoUsuario_auth_user__controller_destroy)
                                    <form style="display:inline-block;" method="POST" action="{{ route('orcamentos.destroy', $value->id) }}" accept-charset="UTF-8">
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

                            <th class="orcamentos_td_numero" style="white-space: nowrap;">Número</th>
                            <th class="orcamentos_td_data" style="white-space: nowrap;">Data</th>
                            <th class="orcamentos_td_clilente" style="white-space: nowrap;">Clilente</th>
                            <th class="orcamentos_td_vendedor" style="white-space: nowrap;">Vendedor</th>
                            <th class="orcamentos_td_produto" style="white-space: nowrap;">Produto</th>
                            <th class="orcamentos_td_periodo_de" style="white-space: nowrap;">Período de</th>
                            <th class="orcamentos_td_a" style="white-space: nowrap;">A</th>
                            <th class="orcamentos_td_viajantes" style="white-space: nowrap;">Viajantes</th>
                            <th class="orcamentos_td_criancas" style="white-space: nowrap;">Crianças</th>
                            <th class="orcamentos_td_idade" style="white-space: nowrap;">Idade</th>
                            <th class="orcamentos_td_inclui" style="white-space: nowrap;">Inclui</th>
                            <th class="orcamentos_td_nao_inclui" style="white-space: nowrap;">Não inclui</th>
                            <th class="orcamentos_td_passageiros" style="white-space: nowrap;">Passageiros</th>
                            <th class="orcamentos_td_quantidade" style="white-space: nowrap;">Quantidade</th>
                            <th class="orcamentos_td_valor_por_pessoa" style="white-space: nowrap;">Valor por pessoa</th>
                            <th class="orcamentos_td_valor_total" style="white-space: nowrap;">Valor total</th>

                            @if($auth_user__actions_enable_btns && $defaultPositionActions == 'right')
                                <th class="text-center" style="border: none;"> # </th>
                            @endif

                        </tr>
                    </thead>

                    <tbody>
                        @foreach($orcamentos as $value)

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

                                <td style=' white-space: nowrap; '  class='orcamentos_td_numero'>{{$value->numero}}</td>

                                <td style=' white-space: nowrap; ' data-order="{{ $value->data }}" class='orcamentos_td_data'>{{(isset($value->data)) ? date("d/m/Y", strtotime($value->data)) : ""}}</td>

                                <td style=' white-space: nowrap; '  class='orcamentos_td_clilente'>{{$value->clilente}}</td>

                                <td style=' white-space: nowrap; '  class='orcamentos_td_vendedor'>{{$value->vendedor}}</td>

                                <td style=' white-space: nowrap; '  class='orcamentos_td_produto'>{{$value->produto}}</td>

                                <td style=' white-space: nowrap; ' data-order="{{ $value->periodo_de }}" class='orcamentos_td_periodo_de'>{{(isset($value->periodo_de)) ? date("d/m/Y", strtotime($value->periodo_de)) : ""}}</td>

                                <td style=' white-space: nowrap; ' data-order="{{ $value->a }}" class='orcamentos_td_a'>{{(isset($value->a)) ? date("d/m/Y", strtotime($value->a)) : ""}}</td>

                                <td style=' white-space: nowrap; '  class='orcamentos_td_viajantes'>{{$value->viajantes}}</td>

                                <td style=' white-space: nowrap; '  class='orcamentos_td_criancas'>{{$value->criancas}}</td>

                                <td style=' white-space: nowrap; '  class='orcamentos_td_idade'>{{$value->idade}}</td>

                                <td style=' white-space: nowrap; '  class='orcamentos_td_inclui'>{{$value->inclui}}</td>

                                <td style=' white-space: nowrap; '  class='orcamentos_td_nao_inclui'>{{$value->nao_inclui}}</td>

                                <td style=' white-space: nowrap; '  class='orcamentos_td_passageiros'>{{$value->passageiros}}</td>

                                <td style=' white-space: nowrap; '  class='orcamentos_td_quantidade'>{{$value->quantidade}}</td>

                                <td style=' white-space: nowrap; '  class='orcamentos_td_valor_por_pessoa'>{{$value->valor_por_pessoa}}</td>

                                <td style=' white-space: nowrap; '  class='orcamentos_td_valor_total'>{{$value->valor_total}}</td>

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
                        <a href="{{ URL('/') }}/orcamentos/create" class="btn btn-xs btn-default right form-group-btn-index-cadastrar"><i class="glyphicon glyphicon-plus"></i> Cadastrar</a>
                    @endif
                </div>
            </div>
        </div>
    @else
        @include('orcamentos.kanban', [
            'orcamentos' => $orcamentos, // # -
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
