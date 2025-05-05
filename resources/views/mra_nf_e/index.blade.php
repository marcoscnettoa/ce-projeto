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

    $config_empresa                                     = App\Models\MRANfConfiguracoes::config_empresa();
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
        <h1>Nota Fiscal - Nota Fiscal de Produto</h1>
    </section>
    <section class="content">
        {{--@if($exibe_filtros)--}}
            <div class="box-header">
                <div class="row">
                    <div class="col-md-2">
                        <div class="small-box v2 bg-default">
                            <div class="inner" style="color: #000;">
                                <h3>{{ \App\Models\MRANfNfe::Get_QtStatus_Null() }}</h3>
                                <p>Salvos</p>
                                <i class="glyphicon glyphicon-save flo"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="small-box v2 bg-info">
                            <div class="inner" style="color: #000;">
                                <h3>{{ \App\Models\MRANfNfe::Get_QtStatus_Pendente() }}</h3>
                                <p>Pendentes</p>
                                <i class="glyphicon glyphicon-alert flo"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="small-box v2 bg-success">
                            <div class="inner" style="color: #000;">
                                <h3>{{ \App\Models\MRANfNfe::Get_QtStatus_Autorizada() }}</h3>
                                <p>Autorizadas</p>
                                <i class="glyphicon glyphicon-eye-open flo"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="small-box v2 bg-warning">
                            <div class="inner" style="color: #000;">
                                @php
                                    $autorizando_cancelando  = \App\Models\MRANfNfe::Get_QtStatus_AguardandoAutorizacao();
                                    $autorizando_cancelando += \App\Models\MRANfNfe::Get_QtStatus_EmProcessoDeCancelamento();
                                    $autorizando_cancelando += \App\Models\MRANfNfe::Get_QtStatus_AguardandoCancelamento();
                                @endphp
                                <h3>{{ $autorizando_cancelando }}</h3>
                                <p>Autorizando / Cancelando</p>
                                <i class="glyphicon glyphicon-time flo"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="small-box v2 bg-danger">
                            <div class="inner" style="color: #000;">
                                <h3>{{ \App\Models\MRANfNfe::Get_QtStatus_Rejeitada() }}</h3>
                                <p>Rejeitadas</p>
                                <i class="glyphicon glyphicon-remove-circle flo"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="small-box v2 bg-danger">
                            <div class="inner" style="color: #000;">
                                <h3>{{ \App\Models\MRANfNfe::Get_QtStatus_Cancelada() }}</h3>
                                <p>Canceladas</p>
                                <i class="glyphicon glyphicon-trash flo"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="box-header" style="background-color: #fff; padding-top: 30px">
                <form action="{{ URL('/') }}/mra_nota_fiscal/mra_nf_e/filter" method="POST">
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
                                {!! Form::select('mra_nf_cliente_id', \App\Models\MRANfClientes::lista_clientes(), (Request::get('mra_nf_cliente_id')?Request::get('mra_nf_cliente_id'):null), ['class' => 'form-control select_single_no_trigger', "dropdown-menu-right"=>"", 'data-live-search' => 'true', 'id' => 'input_mra_nf_cliente_id']) !!}
                            </div>
                        </div>
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','Tipo de Operação') !!}
                                {!! Form::select('nfe_tipo_operacao', \App\Http\Controllers\MRA\MRANotasFiscais::Get_options_nf_e_tipo_operacao(), (!empty(Request::get('nfe_tipo_operacao') || Request::get('nfe_tipo_operacao') == 0)?Request::get('nfe_tipo_operacao'):null), ['class' => 'form-control select_single_no_trigger', "dropdown-menu-right"=>"", 'data-live-search' => 'true', 'id' => 'input_nfe_tipo_operacao']) !!}
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
                                {!! Form::select('notazz_status', \App\Http\Controllers\MRA\MRANotasFiscais::Get_options_nf_status_nota_fiscal(), (Request::get('notazz_status')?Request::get('notazz_status'):null), ['class' => 'form-control select_single_no_trigger', 'data-live-search' => 'true', 'id' => 'input_notazz_status']) !!}
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-<?php echo (!$isPublic ? '3' : '6'); ?> btnsFiltro" style="margin-top: 23px;">
                            <a href="{{ URL('mra_nota_fiscal/mra_nf_e') }}" class="btn btn-default" style="float: left; margin-right: 5px;"><i class="glyphicon glyphicon-trash"></i> Limpar</a>
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
                            <th style="white-space: nowrap;">Data de Competência</th>
                            <th style="white-space: nowrap;">Data de Emissão</th>
                            <th style="white-space: nowrap;">Cliente</th>
                            <th style="white-space: nowrap;">Tipo de Operação</th>
                            <th style="white-space: nowrap;">Valor da Nota</th>
                            <th style="white-space: nowrap;">Status</th>
                            <th style="white-space: nowrap;">Transferido</th>
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
                                <td style="white-space: nowrap;">{{(!empty($value->nfe_data_competencia)?\App\Helper\Helper::H_DataHora_DB_ptBR($value->nfe_data_competencia):'---')}}</td>
                                <td style="white-space: nowrap;">{{(!empty($value->nf_emissao)?\App\Helper\Helper::H_DataHora_DB_ptBR($value->nf_emissao):'---')}}</td>
                                <td style="white-space: nowrap;">{{(!empty($value->des_nome_razao_social)?$value->des_nome_razao_social:'---')}}</td>
                                <td style="white-space: nowrap;">{{((!empty($value->nfe_tipo_operacao) || $value->nfe_tipo_operacao == 0)?\App\Http\Controllers\MRA\MRANotasFiscais::Get_nf_e_tipo_operacao($value->nfe_tipo_operacao):'---')}}</td>
                                {{--<td style="white-space: nowrap;">{{(!empty($value->nfe_valor_total)?\App\Helper\Helper::H_Decimal_DB_ptBR($value->nfe_valor_total):'---')}}</td>--}}
                                <td style="white-space: nowrap;">{{(!empty($value->MRANfNfeProdutosItensQtValorTotal())?\App\Helper\Helper::H_Decimal_DB_ptBR($value->MRANfNfeProdutosItensQtValorTotal()):'0,00')}}</td>
                                {{--@php
                                    $cliente = '---';
                                    if($value->Cliente and !empty($value->Cliente->nome)){
                                        $cliente = $value->Cliente->nome;
                                    }elseif(!empty($value->tomador_nome)){
                                        $cliente = $value->tomador_nome;
                                    }
                                @endphp
                                <td>{{ $cliente }}</td>
                                <td>{!! ($value->tomador?"<span class='badge badge-success fw-600'>Sim</span>":"<span class='badge badge-danger fw-600'>Não</span>")!!}</td>
                                <td>{{(!empty($value->cfg_valor_nota)?\App\Helper\Helper::H_Decimal_DB_ptBR($value->cfg_valor_nota):'---')}}</td>--}}
                                @php
                                    $nf_badge_status = 'badge-default';
                                    switch($value->notazz_status){
                                        case 'Pendente':              $nf_badge_status = 'badge-info'; break;
                                        case 'Autorizada':            $nf_badge_status = 'badge-success'; break;
                                        case 'EmProcessoDeCancelamento':
                                        case 'AguardandoCancelamento':
                                        case 'EmConflito':
                                        case 'AguardandoAutorizacao': $nf_badge_status = 'badge-warning'; break;
                                        case 'Rejeitada':
                                        case 'Cancelada':
                                        case 'Denegada':             $nf_badge_status  = 'badge-danger'; break;
                                    }

                                    $notazz_status  = (!empty($value->notazz_status)?\App\Http\Controllers\MRA\MRANotasFiscais::Get_nf_status_nota_fiscal($value->notazz_status):'---');
                                    // ! Caso o Status foi Cancelada + Cancelamento Forçado
                                    if($value->notazz_status == 'Cancelada' and $value->notazz_status_forcado){
                                        $notazz_status = 'Cancelada no Sistema';
                                    }
                                @endphp
                                <td style="white-space: nowrap;"><span class="badge {{$nf_badge_status}} fw-600">{{ $notazz_status }}</span></td>
                                @php
                                    $nf_badge_status = 'badge-default';
                                    if(!empty($value->notazz_id_documento)){
                                        $nf_badge_status = 'badge-success';
                                    }else {
                                        $nf_badge_status = 'badge-danger';
                                    }
                                @endphp
                                <td style="white-space: nowrap;"><span class="badge {{$nf_badge_status}} fw-600">{{(!empty($value->notazz_id_documento)?'Sim':'Não')}}</span></td>
                                <td style="white-space: nowrap;">{{((in_array($value->notazz_status,['Autorizada']) and !empty($value->nf_numero))?str_pad($value->nf_numero, 8, '0', STR_PAD_LEFT):'---')}}</td>
                                <td style="white-space: nowrap;">{{((in_array($value->notazz_status,['Autorizada']) and !empty($value->nf_chave))?$value->nf_chave:'---')}}</td>
                                <td style="white-space: nowrap;">
                                    @if(
                                        in_array($value->notazz_status,['Autorizada']) and
                                        (
                                            !empty($value->nf_pdf) ||
                                            !empty($value->nf_pdf_prefeitura) ||
                                            !empty($value->nf_xml)
                                        )
                                    )
                                        @if(!empty($value->nf_pdf))
                                            <a href="{{$value->nf_pdf}}" target="_blank" class="op75_h"><img src="{{URL('')}}/pdf-icon.png" height="34" title="PDF - Nota Fiscal - Notazz"></a>
                                        @endif
                                        @if(!empty($value->nf_pdf_prefeitura))
                                            <a href="{{$value->nf_pdf_prefeitura}}" target="_blank" class="op75_h"><img src="{{URL('')}}/pdfnfev2-icon.png" height="34" title="PDF - Nota Fiscal - Prefeitura"></a>
                                        @endif
                                        @if(!empty($value->nf_xml))
                                            <a href="{{$value->nf_xml}}" target="_blank" class="op75_h"><img src="{{URL('')}}/xml-icon.png" height="34" title="XML - Nota Fiscal"></a>
                                        @endif
                                    @else
                                        ---
                                    @endif
                                </td>
                                @if($auth_user__actions_enable_btns)
                                    <td>
                                        @if($permissaoUsuario_auth_user__controller_update)
                                            <a style="float: left; margin-right: 5px;" href="{{ URL('/') }}/mra_nota_fiscal/mra_nf_e/{{$value->id}}/edit" alt="Editar" title="Editar" class="btn btn-default">
                                                <span class="glyphicon glyphicon-edit"></span>
                                            </a>
                                        @endif
                                        {{--@if(0)
                                            @if($permissaoUsuario_auth_user__controller_copy)
                                                <a style="float: left; margin-right: 5px;" href="{{ URL('/') }}/mra_nota_fiscal/mra_nf_e/{{$value->id}}/copy" alt="Duplicar linha" title="Duplicar linha" class="btn btn-default" onclick="return confirm('Tem certeza que quer duplicar a linha?')" >
                                                    <span class="glyphicon glyphicon-copy"></span>
                                                </a>
                                            @endif
                                        @endif--}}
                                        {{--@if($permissaoUsuario_auth_user__controller_show)
                                            <a style="float: left; margin-right: 5px;" href="{{ URL('/') }}/mra_nota_fiscal/mra_nf_e/{{$value->id}}" alt="Visualizar" title="Visualizar" class="btn btn-default">
                                                <span class="glyphicon glyphicon-eye-open"></span>
                                            </a>
                                        @endif--}}
                                        @if($permissaoUsuario_auth_user__controller_destroy and empty($value->notazz_id_documento))
                                            <form style="float: left; margin-right: 5px;" method="POST" action="{{ route('mra_nf_e.destroy', $value->id) }}" accept-charset="UTF-8">
                                                {!! csrf_field() !!}
                                                {!! method_field('DELETE') !!}
                                                <button type="submit" onclick="return confirm('Tem certeza que quer deletar?')" class="btn btn-danger"><i class="glyphicon glyphicon-trash"></i></button>
                                            </form>
                                        @endif
                                        @if($config_empresa_token_api and $permissaoUsuario_auth_user__controller_destroy and in_array($value->notazz_status,['Autorizada']))
                                            <form style="float: left; margin-right: 5px;" method="POST" action="{{ URL('/') }}/mra_nota_fiscal/mra_nf_e/{{$value->id}}" accept-charset="UTF-8">
                                                {!! csrf_field() !!}
                                                {!! method_field('PUT') !!}
                                                {!! Form::hidden('id', $value->id) !!}
                                                <button type="submit" name="cancelar_nf" value="1" onclick="javascript: return confirm('A Nota Fiscal será cancelada! Tem certeza?\n** Atenção! Em caso da Nota Fiscal não seja cancelada só poderá ser realizada pelo sistema da prefeitura.');" class="btn btn-danger"><i class="glyphicon glyphicon-floppy-remove"></i></button>
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
                        <a href="{{ URL('/') }}/mra_nota_fiscal/mra_nf_e/create" class="btn btn-default right form-group-btn-index-cadastrar"><i class="glyphicon glyphicon-plus"></i> Cadastrar</a>
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
