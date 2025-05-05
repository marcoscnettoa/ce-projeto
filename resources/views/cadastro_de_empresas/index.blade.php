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

    /*foreach($cadastro_de_empresas as $val) {

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

<section class="content-header Cadastro de Empresas_index">
    <h1>Cadastro de Empresas</h1>
</section>

<section class="content">

    @if($exibe_filtros)
        <div class="box-header" style="background-color: #fff; padding-top: 15px">
            <form action="{{ URL('/') }}/cadastro_de_empresas/filter" class="form_filter form_filter_cadastro_de_empresas" method="POST">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <div class="row">

                    <div size="3" class="inputbox col-md-3">
<div class="form-group">
                    {!! Form::label('','Nome da Empresa') !!}
                    {!! Form::text('nome_da_empresa', (!empty(\Request::post('nome_da_empresa'))?\Request::post('nome_da_empresa'):null), ['class' => 'form-control' ]) !!}
</div>
</div>
<div size="3" class="inputbox col-md-3">
<div class="form-group">
                    {!! Form::label('','Nome Fantasia') !!}
                    {!! Form::text('nome_fantasia_empresa', (!empty(\Request::post('nome_fantasia_empresa'))?\Request::post('nome_fantasia_empresa'):null), ['class' => 'form-control' ]) !!}
</div>
</div>
<div size="3" class="inputbox col-md-3">
<div class="form-group">
                    {!! Form::label('','CNPJ') !!}
                    {!! Form::text('cnpj_da_empresa', (!empty(\Request::post('cnpj_da_empresa'))?\Request::post('cnpj_da_empresa'):null), ['class' => 'form-control cnpj' ]) !!}
</div>
</div>
<div size="3" class="inputbox col-md-3">
<div class="form-group">
                    {!! Form::label('','Inscrição Estadual') !!}
                    {!! Form::text('inscricao_estadual', (!empty(\Request::post('inscricao_estadual'))?\Request::post('inscricao_estadual'):null), ['class' => 'form-control' ]) !!}
</div>
</div>
<div size="3" class="inputbox col-md-3">
<div class="form-group">
                    {!! Form::label('','Email') !!}
                    <div class='input-group'>
                        <div class='input-group-addon'>
                            <i class='fa fa-envelope'></i>
                        </div>
                    {!! Form::email('email_empresa', (!empty(\Request::post('email_empresa'))?\Request::post('email_empresa'):null), ['class' => 'form-control' ]) !!}
                    </div>
</div>
</div>
<div size="3" class="inputbox col-md-3">
<div class="form-group">
                    {!! Form::label('','UF') !!}
                    {!! Form::text('uf_empresa', (!empty(\Request::post('uf_empresa'))?\Request::post('uf_empresa'):null), ['class' => 'form-control' ]) !!}
</div>
</div>
<div size="3" class="inputbox col-md-3">
<div class="form-group">
                    {!! Form::label('','Cidade') !!}
                    {!! Form::text('cidade_empresa', (!empty(\Request::post('cidade_empresa'))?\Request::post('cidade_empresa'):null), ['class' => 'form-control' ]) !!}
</div>
</div>
<div size="3" class="inputbox col-md-3">
<div class="form-group">
                    {!! Form::label('','Telefone') !!}
                    <div class='input-group'>
                        <div class='input-group-addon'>
                            <i class='fa fa-phone'></i>
                        </div>
                    {!! Form::text('telefone_empresa', (!empty(\Request::post('telefone_empresa'))?\Request::post('telefone_empresa'):null), ['class' => 'form-control telefone' ]) !!}
                    </div>
</div>
</div>

                    <div class="col-md-12 btnsFiltro">
                        <a href="{{ URL('/cadastro_de_empresas') }}" class="btn btn-xs btn-default" style="float: left; margin-right: 5px;"><i class="glyphicon glyphicon-trash"></i> Limpar</a>
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
                        'model'                       => 'CadastroDeEmpresas',
                        'export_options_size'         => $export_options_size,
                        'export_options_size_width'   => $export_options_size_width,
                        'export_options_size_height'  => $export_options_size_height,
                        'import_enable_btns'          => $import_enable_btns,
                        'export_enable_btns'          => $export_enable_btns,
                        'exibe_filtros'               => $exibe_filtros,
                        'lote_count'                  => $cadastro_de_empresas_count
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
                                    <a style="float:none;" href="{{ URL('/') }}/cadastro_de_empresas/{{$value->id}}/edit" alt="Editar" title="Editar" class="btn btn-xs btn-default">
                                        <span class="glyphicon glyphicon-edit"></span>
                                    </a>
                                @endif

                                @if(1)
                                    @if($permissaoUsuario_auth_user__controller_copy)
                                        <a style="float:none;" href="{{ URL('/') }}/cadastro_de_empresas/{{$value->id}}/copy" alt="Duplicar linha" title="Duplicar linha" class="btn btn-xs btn-default" onclick="return confirm('Tem certeza que quer duplicar a linha?')" >
                                            <span class="glyphicon glyphicon-copy"></span>
                                        </a>
                                    @endif
                                @endif

                                @if($permissaoUsuario_auth_user__controller_show)
                                    <a style="float:none;" href="{{ URL('/') }}/cadastro_de_empresas/{{$value->id}}" alt="Visualizar" title="Visualizar" class="btn btn-xs btn-default">
                                        <span class="glyphicon glyphicon-eye-open"></span>
                                    </a>
                                @endif

                                @if($permissaoUsuario_auth_user__controller_destroy)
                                    <form style="display:inline-block;" method="POST" action="{{ route('cadastro_de_empresas.destroy', $value->id) }}" accept-charset="UTF-8">
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

                            <th class="cadastro_de_empresas_td_logotipo_empresa" style="white-space: nowrap;">Logotipo Empresa</th>
                            <th class="cadastro_de_empresas_td_codigo_empresa" style="white-space: nowrap;">Codigo Empresa</th>
                            <th class="cadastro_de_empresas_td_nome_da_empresa" style="white-space: nowrap;">Nome da Empresa</th>
                            <th class="cadastro_de_empresas_td_njuntacomercial_empresa" style="white-space: nowrap;">N° Junta Comercial</th>
                            <th class="cadastro_de_empresas_td_perfil_fiscal" style="white-space: nowrap;">Perfil Fiscal</th>
                            <th class="cadastro_de_empresas_td_nome_fantasia_empresa" style="white-space: nowrap;">Nome Fantasia</th>
                            <th class="cadastro_de_empresas_td_cnpj_da_empresa" style="white-space: nowrap;">CNPJ</th>
                            <th class="cadastro_de_empresas_td_inscricao_estadual" style="white-space: nowrap;">Inscrição Estadual</th>
                            <th class="cadastro_de_empresas_td_email_empresa" style="white-space: nowrap;">Email</th>
                            <th class="cadastro_de_empresas_td_cep_empresa" style="white-space: nowrap;">CEP</th>
                            <th class="cadastro_de_empresas_td_endereco_empresa" style="white-space: nowrap;">Endereço</th>
                            <th class="cadastro_de_empresas_td_numero_empresa" style="white-space: nowrap;">Número</th>
                            <th class="cadastro_de_empresas_td_bairro_empresa" style="white-space: nowrap;">Bairro</th>
                            <th class="cadastro_de_empresas_td_pais_empresa" style="white-space: nowrap;">País</th>
                            <th class="cadastro_de_empresas_td_uf_empresa" style="white-space: nowrap;">UF</th>
                            <th class="cadastro_de_empresas_td_cidade_empresa" style="white-space: nowrap;">Cidade</th>
                            <th class="cadastro_de_empresas_td_telefone_empresa" style="white-space: nowrap;">Telefone</th>
                            <th class="cadastro_de_empresas_td_fax_empresa" style="white-space: nowrap;">Fax</th>
                            <th class="cadastro_de_empresas_td_site_empresa" style="white-space: nowrap;">Site</th>
                            <th class="cadastro_de_empresas_td_nre_empresa" style="white-space: nowrap;">NRE</th>
                            <th class="cadastro_de_empresas_td_comercio" style="white-space: nowrap;">Comércio</th>
                            <th class="cadastro_de_empresas_td_servico" style="white-space: nowrap;">Serviço</th>
                            <th class="cadastro_de_empresas_td_industria" style="white-space: nowrap;">Indústria</th>
                            <th class="cadastro_de_empresas_td_importador" style="white-space: nowrap;">Importador</th>

                            @if($auth_user__actions_enable_btns && $defaultPositionActions == 'right')
                                <th class="text-center" style="border: none;"> # </th>
                            @endif

                        </tr>
                    </thead>

                    <tbody>
                        @foreach($cadastro_de_empresas as $value)

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

                                <td style=' white-space: nowrap; '  class='cadastro_de_empresas_td_logotipo_empresa'>

@if($value->logotipo_empresa && count(explode(".", $value->logotipo_empresa)) >= 2)

<a class="fancybox" rel="gallery1" target="_blank" href="{{in_array(explode(".", $value->logotipo_empresa)[1], array("jpg", "jpeg", "gif", "png", "bmp", "mp4", "pdf", "doc", "docx", "rar", "zip", "txt", "7zip", "csv", "xls", "xlsx")) ? $fileurlbase . "images/" . $value->logotipo_empresa : "javascript:void(0);"}}">
                                    <img src="{{in_array(explode(".", $value->logotipo_empresa)[1], array("mp4", "pdf", "doc", "docx", "rar", "zip", "txt", "7zip", "csv", "xls", "xlsx")) ? explode(".", $value->logotipo_empresa)[1] . "-icon.png" : $fileurlbase . "images/" . $value->logotipo_empresa}}" width="30">
                                </a>
@endif
</td>

                                <td style=' white-space: nowrap; '  class='cadastro_de_empresas_td_codigo_empresa'>{{$value->codigo_empresa}}</td>

                                <td style=' white-space: nowrap; '  class='cadastro_de_empresas_td_nome_da_empresa'>{{$value->nome_da_empresa}}</td>

                                <td style=' white-space: nowrap; '  class='cadastro_de_empresas_td_njuntacomercial_empresa'>{{$value->njuntacomercial_empresa}}</td>

                                <td style=' white-space: nowrap; '  class='cadastro_de_empresas_td_perfil_fiscal'>{{$value->perfil_fiscal}}</td>

                                <td style=' white-space: nowrap; '  class='cadastro_de_empresas_td_nome_fantasia_empresa'>{{$value->nome_fantasia_empresa}}</td>

                                <td style=' white-space: nowrap; '  class='cadastro_de_empresas_td_cnpj_da_empresa'>{{$value->cnpj_da_empresa}}</td>

                                <td style=' white-space: nowrap; '  class='cadastro_de_empresas_td_inscricao_estadual'>{{$value->inscricao_estadual}}</td>

                                <td style=' white-space: nowrap; '  class='cadastro_de_empresas_td_email_empresa'>{{$value->email_empresa}}</td>

                                <td style=' white-space: nowrap; '  class='cadastro_de_empresas_td_cep_empresa'>{{$value->cep_empresa}}</td>

                                <td style=' white-space: nowrap; '  class='cadastro_de_empresas_td_endereco_empresa'>{{$value->endereco_empresa}}</td>

                                <td style=' white-space: nowrap; '  class='cadastro_de_empresas_td_numero_empresa'>{{$value->numero_empresa}}</td>

                                <td style=' white-space: nowrap; '  class='cadastro_de_empresas_td_bairro_empresa'>{{$value->bairro_empresa}}</td>

                                <td style=' white-space: nowrap; '  class='cadastro_de_empresas_td_pais_empresa'>{{$value->pais_empresa}}</td>

                                <td style=' white-space: nowrap; '  class='cadastro_de_empresas_td_uf_empresa'>{{$value->uf_empresa}}</td>

                                <td style=' white-space: nowrap; '  class='cadastro_de_empresas_td_cidade_empresa'>{{$value->cidade_empresa}}</td>

                                <td style=' white-space: nowrap; '  class='cadastro_de_empresas_td_telefone_empresa'>{{$value->telefone_empresa}}</td>

                                <td style=' white-space: nowrap; '  class='cadastro_de_empresas_td_fax_empresa'>{{$value->fax_empresa}}</td>

                                <td style=' white-space: nowrap; '  class='cadastro_de_empresas_td_site_empresa'>{{$value->site_empresa}}</td>

                                <td style=' white-space: nowrap; '  class='cadastro_de_empresas_td_nre_empresa'>{{$value->nre_empresa}}</td>

                                <td style=' white-space: nowrap; '  class='cadastro_de_empresas_td_comercio'>{{(isset($value->comercio) && $value->comercio) ? "Sim" : "Não"}}</td>

                                <td style=' white-space: nowrap; '  class='cadastro_de_empresas_td_servico'>{{(isset($value->servico) && $value->servico) ? "Sim" : "Não"}}</td>

                                <td style=' white-space: nowrap; '  class='cadastro_de_empresas_td_industria'>{{(isset($value->industria) && $value->industria) ? "Sim" : "Não"}}</td>

                                <td style=' white-space: nowrap; '  class='cadastro_de_empresas_td_importador'>{{(isset($value->importador) && $value->importador) ? "Sim" : "Não"}}</td>

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
                        <a href="{{ URL('/') }}/cadastro_de_empresas/create" class="btn btn-xs btn-default right form-group-btn-index-cadastrar"><i class="glyphicon glyphicon-plus"></i> Cadastrar</a>
                    @endif
                </div>
            </div>
        </div>
    @else
        @include('cadastro_de_empresas.kanban', [
            'cadastro_de_empresas' => $cadastro_de_empresas, // # -
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
