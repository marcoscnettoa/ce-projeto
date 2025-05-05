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

    /*foreach($clientes as $val) {

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

<section class="content-header Clientes_index">
    <h1>Clientes</h1>
</section>

<section class="content">

    @if($exibe_filtros)
        <div class="box-header" style="background-color: #fff; padding-top: 15px">
            <form action="{{ URL('/') }}/clientes/filter" class="form_filter form_filter_clientes" method="POST">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <div class="row">

                    <div size="3" class="inputbox col-md-3">
<div class="form-group">
                    {!! Form::label('','CNPJ do Cliente') !!}
                    {!! Form::text('cnpj_do_cliente', (!empty(\Request::post('cnpj_do_cliente'))?\Request::post('cnpj_do_cliente'):null), ['class' => 'form-control cnpj' ]) !!}
</div>
</div>
<div size="3" class="inputbox col-md-3">
<div class="form-group">
                    {!! Form::label('','CPF do Cliente') !!}
                    {!! Form::text('cpf_do_cliente', (!empty(\Request::post('cpf_do_cliente'))?\Request::post('cpf_do_cliente'):null), ['class' => 'form-control cpf' ]) !!}
</div>
</div>
<div size="3" class="inputbox col-md-3">
<div class="form-group">
                    {!! Form::label('','Nome do Cliente') !!}
                    {!! Form::text('nome_do_cliente', (!empty(\Request::post('nome_do_cliente'))?\Request::post('nome_do_cliente'):null), ['class' => 'form-control' ]) !!}
</div>
</div>
<div size="3" class="inputbox col-md-3">
<div class="form-group">
                    {!! Form::label('','Fantasia') !!}
                    {!! Form::text('fantasia', (!empty(\Request::post('fantasia'))?\Request::post('fantasia'):null), ['class' => 'form-control' ]) !!}
</div>
</div>
<div size="3" class="inputbox col-md-3">
<div class="form-group">
                    {!! Form::label('','E-mail') !!}
                    {!! Form::text('e_mail', (!empty(\Request::post('e_mail'))?\Request::post('e_mail'):null), ['class' => 'form-control' ]) !!}
</div>
</div>
<div size="3" class="inputbox col-md-3">
<div class="form-group">
                    {!! Form::label('','Telefone 1') !!}
                    <div class='input-group'>
                        <div class='input-group-addon'>
                            <i class='fa fa-phone'></i>
                        </div>
                    {!! Form::text('telefone_1', (!empty(\Request::post('telefone_1'))?\Request::post('telefone_1'):null), ['class' => 'form-control telefone' ]) !!}
                    </div>
</div>
</div>
<div size="3" class="inputbox col-md-3">
<div class="form-group">
                    {!! Form::label('','Cidade') !!}
                    {!! Form::text('cidade', (!empty(\Request::post('cidade'))?\Request::post('cidade'):null), ['class' => 'form-control' ]) !!}
</div>
</div>

                    <div class="col-md-12 btnsFiltro">
                        <a href="{{ URL('/clientes') }}" class="btn btn-xs btn-default" style="float: left; margin-right: 5px;"><i class="glyphicon glyphicon-trash"></i> Limpar</a>
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
                        'model'                       => 'Clientes',
                        'export_options_size'         => $export_options_size,
                        'export_options_size_width'   => $export_options_size_width,
                        'export_options_size_height'  => $export_options_size_height,
                        'import_enable_btns'          => $import_enable_btns,
                        'export_enable_btns'          => $export_enable_btns,
                        'exibe_filtros'               => $exibe_filtros,
                        'lote_count'                  => $clientes_count
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
                                    <a style="float:none;" href="{{ URL('/') }}/clientes/{{$value->id}}/edit" alt="Editar" title="Editar" class="btn btn-xs btn-default">
                                        <span class="glyphicon glyphicon-edit"></span>
                                    </a>
                                @endif

                                @if(1)
                                    @if($permissaoUsuario_auth_user__controller_copy)
                                        <a style="float:none;" href="{{ URL('/') }}/clientes/{{$value->id}}/copy" alt="Duplicar linha" title="Duplicar linha" class="btn btn-xs btn-default" onclick="return confirm('Tem certeza que quer duplicar a linha?')" >
                                            <span class="glyphicon glyphicon-copy"></span>
                                        </a>
                                    @endif
                                @endif

                                @if($permissaoUsuario_auth_user__controller_show)
                                    <a style="float:none;" href="{{ URL('/') }}/clientes/{{$value->id}}" alt="Visualizar" title="Visualizar" class="btn btn-xs btn-default">
                                        <span class="glyphicon glyphicon-eye-open"></span>
                                    </a>
                                @endif

                                @if($permissaoUsuario_auth_user__controller_destroy)
                                    <form style="display:inline-block;" method="POST" action="{{ route('clientes.destroy', $value->id) }}" accept-charset="UTF-8">
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

                            <th class="clientes_td_codigo_do_cliente" style="white-space: nowrap;">Código do Cliente</th>
                            <th class="clientes_td_cnpj_do_cliente" style="white-space: nowrap;">CNPJ do Cliente</th>
                            <th class="clientes_td_cpf_do_cliente" style="white-space: nowrap;">CPF do Cliente</th>
                            <th class="clientes_td_inscricao_estadual_rg" style="white-space: nowrap;">Inscrição Estadual/RG</th>
                            <th class="clientes_td_nome_do_cliente" style="white-space: nowrap;">Nome do Cliente</th>
                            <th class="clientes_td_fantasia" style="white-space: nowrap;">Fantasia</th>
                            <th class="clientes_td_e_mail" style="white-space: nowrap;">E-mail</th>
                            <th class="clientes_td_data_de_nascimento" style="white-space: nowrap;">Data de Nascimento</th>
                            <th class="clientes_td_observacao" style="white-space: nowrap;">Observação</th>
                            <th class="clientes_td_telefone_1" style="white-space: nowrap;">Telefone 1</th>
                            <th class="clientes_td_telefone_2" style="white-space: nowrap;">Telefone 2</th>
                            <th class="clientes_td_cep_do_cliente" style="white-space: nowrap;">CEP do Cliente</th>
                            <th class="clientes_td_endereco" style="white-space: nowrap;">Endereço</th>
                            <th class="clientes_td_numero_" style="white-space: nowrap;">Número:</th>
                            <th class="clientes_td_complemento" style="white-space: nowrap;">Complemento</th>
                            <th class="clientes_td_bairro" style="white-space: nowrap;">Bairro</th>
                            <th class="clientes_td_cidade" style="white-space: nowrap;">Cidade</th>
                            <th class="clientes_td_estado" style="white-space: nowrap;">Estado</th>
                            <th class="clientes_td_ponto_de_referencia" style="white-space: nowrap;">Ponto de Referência</th>
                            <th class="clientes_td_documentos" style="white-space: nowrap;">Documentos</th>

                            @if($auth_user__actions_enable_btns && $defaultPositionActions == 'right')
                                <th class="text-center" style="border: none;"> # </th>
                            @endif

                        </tr>
                    </thead>

                    <tbody>
                        @foreach($clientes as $value)

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

                                <td style=' white-space: nowrap; '  class='clientes_td_codigo_do_cliente'>{{$value->codigo_do_cliente}}</td>

                                <td style=' white-space: nowrap; '  class='clientes_td_cnpj_do_cliente'>{{$value->cnpj_do_cliente}}</td>

                                <td style=' white-space: nowrap; '  class='clientes_td_cpf_do_cliente'>{{$value->cpf_do_cliente}}</td>

                                <td style=' white-space: nowrap; '  class='clientes_td_inscricao_estadual_rg'>{{$value->inscricao_estadual_rg}}</td>

                                <td style="white-space: nowrap; text-transform: uppercase;"  class="clientes_td_nome_do_cliente">{{$value->nome_do_cliente}}</td>

                                <td style=' white-space: nowrap; '  class='clientes_td_fantasia'>{{$value->fantasia}}</td>

                                <td style=' white-space: nowrap; '  class='clientes_td_e_mail'>{{$value->e_mail}}</td>

                                <td style=' white-space: nowrap;'  class='clientes_td_data_de_nascimento'>{{(!empty($value->data_nascimento)?\App\Helper\Helper::H_Data_DB_ptBR($value->data_nascimento):'---')}}</td>

                                <td style=' white-space: nowrap; '  class='clientes_td_observacao'>{{$value->observacao}}</td>

                                <td style=' white-space: nowrap; '  class='clientes_td_telefone_1'><a href="https://api.whatsapp.com/send?phone=+55{{ preg_replace('/[^0-9]/', '', $value->telefone_1) }}&text={{ isset($value->texto_whatsapp) ? $value->texto_whatsapp : '__SUA_MENSAGEM_AQUI__' }}" target="_blank">{{ $value->telefone_1 }}</a></td>

                                <td style=' white-space: nowrap; '  class='clientes_td_telefone_2'>{{$value->telefone_2}}</td>

                                <td style=' white-space: nowrap; '  class='clientes_td_cep_do_cliente'>{{$value->cep_do_cliente}}</td>

                                <td style=' white-space: nowrap; '  class='clientes_td_endereco'>{{$value->endereco}}</td>

                                <td style=' white-space: nowrap; '  class='clientes_td_numero_'>{{$value->numero_}}</td>

                                <td style=' white-space: nowrap; '  class='clientes_td_complemento'>{{$value->complemento}}</td>

                                <td style=' white-space: nowrap; '  class='clientes_td_bairro'>{{$value->bairro}}</td>

                                <td style=' white-space: nowrap; '  class='clientes_td_cidade'>{{$value->cidade}}</td>

                                <td style=' white-space: nowrap; '  class='clientes_td_estado'>{{$value->estado}}</td>

                                <td style=' white-space: nowrap; '  class='clientes_td_ponto_de_referencia'>{{$value->ponto_de_referencia}}</td>

                                <td style=' white-space: nowrap; '  class='clientes_td_documentos'><a href="{{ URL("/") }}/clientes/{{$value->id}}"><i class="glyphicon glyphicon-th-list"></i></a>
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

                </table>
                </div>
                <br>
                <br>

                <div class="form-group form-group-btn-index">
                    @if(!$isPublic)
                        <a href="{{ URL::previous() }}" class="btn btn-xs btn-default form-group-btn-index-voltar"><i class="glyphicon glyphicon-backward"></i> Voltar</a>
                    @endif
                    @if(\App\Models\Permissions::permissaoUsuario(\Auth::user(), "$controller@store") OR $isPublic)
                        <a href="{{ URL('/') }}/clientes/create" class="btn btn-xs btn-default right form-group-btn-index-cadastrar"><i class="glyphicon glyphicon-plus"></i> Cadastrar</a>
                    @endif
                </div>
            </div>
        </div>
    @else
        @include('clientes.kanban', [
            'clientes' => $clientes, // # -
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
