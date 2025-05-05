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

    /*foreach($ccf_cartao_de_credito as $val) {

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

<section class="content-header CCF Cartão de Crédito_index">
    <h1>CCF Cartão de Crédito</h1>
</section>

<section class="content">

    @if($exibe_filtros)
        <div class="box-header" style="background-color: #fff; padding-top: 15px">
            <form action="{{ URL('/') }}/ccf_cartao_de_credito/filter" class="form_filter form_filter_ccf_cartao_de_credito" method="POST">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <div class="row">

                    <div size="3" class="inputbox col-md-3">
<div class="form-group">
                    {!! Form::label('','Visa:') !!}
                    {!! Form::checkbox('visa_', null, (!empty(\Request::post('visa_'))?\Request::post('visa_'):null), ['class' => '' ]) !!}
</div>
</div>
<div size="3" class="inputbox col-md-3">
<div class="form-group">
                    {!! Form::label('','Mastercard:') !!}
                    {!! Form::checkbox('mastercard_', null, (!empty(\Request::post('mastercard_'))?\Request::post('mastercard_'):null), ['class' => '' ]) !!}
</div>
</div>
<div size="3" class="inputbox col-md-3">
<div class="form-group">
                    {!! Form::label('','Diners:') !!}
                    {!! Form::checkbox('diners_', null, (!empty(\Request::post('diners_'))?\Request::post('diners_'):null), ['class' => '' ]) !!}
</div>
</div>
<div size="3" class="inputbox col-md-3">
<div class="form-group">
                    {!! Form::label('','Outros') !!}
                    {!! Form::checkbox('outros', null, (!empty(\Request::post('outros'))?\Request::post('outros'):null), ['class' => '' ]) !!}
</div>
</div>
<div size="3" class="inputbox col-md-3">
<div class="form-group">
                    {!! Form::label('','Nome do Titular:') !!}
                    {!! Form::text('nome_do_titular_', (!empty(\Request::post('nome_do_titular_'))?\Request::post('nome_do_titular_'):null), ['class' => 'form-control' ]) !!}
</div>
</div>
<div size="3" class="inputbox col-md-3">
<div class="form-group">
                    {!! Form::label('','CPF:') !!}
                    {!! Form::text('cpf_', (!empty(\Request::post('cpf_'))?\Request::post('cpf_'):null), ['class' => 'form-control' ]) !!}
</div>
</div>

                    <div class="col-md-12 btnsFiltro">
                        <a href="{{ URL('/ccf_cartao_de_credito') }}" class="btn btn-xs btn-default" style="float: left; margin-right: 5px;"><i class="glyphicon glyphicon-trash"></i> Limpar</a>
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
                        'model'                       => 'CcfCartaoDeCredito',
                        'export_options_size'         => $export_options_size,
                        'export_options_size_width'   => $export_options_size_width,
                        'export_options_size_height'  => $export_options_size_height,
                        'import_enable_btns'          => $import_enable_btns,
                        'export_enable_btns'          => $export_enable_btns,
                        'exibe_filtros'               => $exibe_filtros,
                        'lote_count'                  => $ccf_cartao_de_credito_count
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
                                    <a style="float:none;" href="{{ URL('/') }}/ccf_cartao_de_credito/{{$value->id}}/edit" alt="Editar" title="Editar" class="btn btn-xs btn-default">
                                        <span class="glyphicon glyphicon-edit"></span>
                                    </a>
                                @endif

                                @if(1)
                                    @if($permissaoUsuario_auth_user__controller_copy)
                                        <a style="float:none;" href="{{ URL('/') }}/ccf_cartao_de_credito/{{$value->id}}/copy" alt="Duplicar linha" title="Duplicar linha" class="btn btn-xs btn-default" onclick="return confirm('Tem certeza que quer duplicar a linha?')" >
                                            <span class="glyphicon glyphicon-copy"></span>
                                        </a>
                                    @endif
                                @endif

                                @if($permissaoUsuario_auth_user__controller_show)
                                    <a style="float:none;" href="{{ URL('/') }}/ccf_cartao_de_credito/{{$value->id}}" alt="Visualizar" title="Visualizar" class="btn btn-xs btn-default">
                                        <span class="glyphicon glyphicon-eye-open"></span>
                                    </a>
                                @endif

                                @if($permissaoUsuario_auth_user__controller_destroy)
                                    <form style="display:inline-block;" method="POST" action="{{ route('ccf_cartao_de_credito.destroy', $value->id) }}" accept-charset="UTF-8">
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

                            <th class="ccf_cartao_de_credito_td_visa_" style="white-space: nowrap;">Visa:</th>
                            <th class="ccf_cartao_de_credito_td_mastercard_" style="white-space: nowrap;">Mastercard:</th>
                            <th class="ccf_cartao_de_credito_td_diners_" style="white-space: nowrap;">Diners:</th>
                            <th class="ccf_cartao_de_credito_td_outros" style="white-space: nowrap;">Outros</th>
                            <th class="ccf_cartao_de_credito_td_numero_do_cartao_" style="white-space: nowrap;">Número do Cartão:</th>
                            <th class="ccf_cartao_de_credito_td_codigo_de_verificacao_" style="white-space: nowrap;">Código de Verificação:</th>
                            <th class="ccf_cartao_de_credito_td_data_de_validade_cartao_" style="white-space: nowrap;">Data de Validade Cartão:</th>
                            <th class="ccf_cartao_de_credito_td_nome_do_titular_" style="white-space: nowrap;">Nome do Titular:</th>
                            <th class="ccf_cartao_de_credito_td_cpf_" style="white-space: nowrap;">CPF:</th>
                            <th class="ccf_cartao_de_credito_td_nro_do_telefone_do_responsavel_" style="white-space: nowrap;">Nro do Telefone do Responsável:</th>
                            <th class="ccf_cartao_de_credito_td_valor_total_" style="white-space: nowrap;">Valor Total:</th>
                            <th class="ccf_cartao_de_credito_td_nro_de_parcelas_" style="white-space: nowrap;">Nro de Parcelas:</th>
                            <th class="ccf_cartao_de_credito_td_valor_da_parcela_" style="white-space: nowrap;">Valor da Parcela:</th>
                            <th class="ccf_cartao_de_credito_td_esta_autorizacao_destina_se_ao_pagamento_em_nome_de_" style="white-space: nowrap;">Esta autorização destina-se ao pagamento em nome de:</th>
                            <th class="ccf_cartao_de_credito_td_nro_telefone_passageiro_" style="white-space: nowrap;">Nro. Telefone Passageiro:</th>
                            <th class="ccf_cartao_de_credito_td_cia_aerea_" style="white-space: nowrap;">Cia Aérea:</th>
                            <th class="ccf_cartao_de_credito_td_data_de_embarque_" style="white-space: nowrap;">Data de Embarque:</th>
                            <th class="ccf_cartao_de_credito_td_destino_" style="white-space: nowrap;">Destino:</th>

                            @if($auth_user__actions_enable_btns && $defaultPositionActions == 'right')
                                <th class="text-center" style="border: none;"> # </th>
                            @endif

                        </tr>
                    </thead>

                    <tbody>
                        @foreach($ccf_cartao_de_credito as $value)

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

                                <td style=' white-space: nowrap; '  class='ccf_cartao_de_credito_td_visa_'>{{(isset($value->visa_) && $value->visa_) ? "Sim" : "Não"}}</td>

                                <td style=' white-space: nowrap; '  class='ccf_cartao_de_credito_td_mastercard_'>{{(isset($value->mastercard_) && $value->mastercard_) ? "Sim" : "Não"}}</td>

                                <td style=' white-space: nowrap; '  class='ccf_cartao_de_credito_td_diners_'>{{(isset($value->diners_) && $value->diners_) ? "Sim" : "Não"}}</td>

                                <td style=' white-space: nowrap; '  class='ccf_cartao_de_credito_td_outros'>{{(isset($value->outros) && $value->outros) ? "Sim" : "Não"}}</td>

                                <td style=' white-space: nowrap; '  class='ccf_cartao_de_credito_td_numero_do_cartao_'>{{$value->numero_do_cartao_}}</td>

                                <td style=' white-space: nowrap; '  class='ccf_cartao_de_credito_td_codigo_de_verificacao_'>{{$value->codigo_de_verificacao_}}</td>

                                <td style=' white-space: nowrap; ' data-order="{{ implode('-', array_reverse(explode('/', $value->data_de_validade_cartao_))) }}" class='ccf_cartao_de_credito_td_data_de_validade_cartao_'>{{$value->data_de_validade_cartao_}}</td>

                                <td style="text-transform: uppercase;"  class="ccf_cartao_de_credito_td_nome_do_titular_">{{$value->nome_do_titular_}}</td>

                                <td style=' white-space: nowrap; '  class='ccf_cartao_de_credito_td_cpf_'>{{$value->cpf_}}</td>

                                <td style=' white-space: nowrap; '  class='ccf_cartao_de_credito_td_nro_do_telefone_do_responsavel_'>{{$value->nro_do_telefone_do_responsavel_}}</td>

                                <td style=' white-space: nowrap; '  class='ccf_cartao_de_credito_td_valor_total_'>{{$value->valor_total_}}</td>

                                <td style=' white-space: nowrap; '  class='ccf_cartao_de_credito_td_nro_de_parcelas_'>{{$value->nro_de_parcelas_}}</td>

                                <td style=' white-space: nowrap; '  class='ccf_cartao_de_credito_td_valor_da_parcela_'>{{$value->valor_da_parcela_}}</td>

                                <td style=' white-space: nowrap; '  class='ccf_cartao_de_credito_td_esta_autorizacao_destina_se_ao_pagamento_em_nome_de_'>{{$value->esta_autorizacao_destina_se_ao_pagamento_em_nome_de_}}</td>

                                <td style=' white-space: nowrap; '  class='ccf_cartao_de_credito_td_nro_telefone_passageiro_'>{{$value->nro_telefone_passageiro_}}</td>

                                <td style=' white-space: nowrap; '  class='ccf_cartao_de_credito_td_cia_aerea_'>{{$value->cia_aerea_}}</td>

                                <td style=' white-space: nowrap; ' data-order="{{ implode('-', array_reverse(explode('/', $value->data_de_embarque_))) }}" class='ccf_cartao_de_credito_td_data_de_embarque_'>{{$value->data_de_embarque_}}</td>

                                <td style=' white-space: nowrap; '  class='ccf_cartao_de_credito_td_destino_'>{{$value->destino_}}</td>

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
                        <a href="{{ URL('/') }}/ccf_cartao_de_credito/create" class="btn btn-xs btn-default right form-group-btn-index-cadastrar"><i class="glyphicon glyphicon-plus"></i> Cadastrar</a>
                    @endif
                </div>
            </div>
        </div>
    @else
        @include('ccf_cartao_de_credito.kanban', [
            'ccf_cartao_de_credito' => $ccf_cartao_de_credito, // # -
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
        $(`
	#input_valor_total_,
	#input_nro_de_parcelas_
`)
.on('change keyup',function(){
	venda_calculo_total();
});
function venda_calculo_total(){
	console.log('foi');
	let valor_total_ 	 	= parseFloat(($("#input_valor_total_").val()!="")?$("#input_valor_total_").val():0);
	let nro_de_parcelas_    = parseFloat(($("#input_nro_de_parcelas_").val()!="")?$("#input_nro_de_parcelas_").val():0);
	let valor_da_parcela_ 	= 0;
	if(valor_total_ > 0 && nro_de_parcelas_ > 0){
		valor_da_parcela_   = (valor_total_ / nro_de_parcelas_);
	}
	$("#input_valor_da_parcela_").val(valor_da_parcela_.toFixed(2));
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
    </script>

@endsection
