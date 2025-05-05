@php
    $acao       = ((isset($MRAGIuguPlanos) and !is_null($MRAGIuguPlanos))?'edit':'add');
    $isPublic   = 0;
    $controller = get_class(\Request::route()->getController());
@endphp
@extends($isPublic ? 'layouts.app-public' : 'layouts.app')
@section('content')
    @section('style')
        <style type="text/css">
        </style>
    @endsection
    <section class="content-header">
        <h1>Gateway Iugu - Planos</h1>
        {{--
        @if(!$isPublic)
            <ol class="breadcrumb">
                <li><a href="{{ URL('/') }}">Home</a></li>
                <li><a href="{{ URL('/') }}/emissao_nota_fiscal">Emissão Nota Fiscal</a></li>
                <li class="active">Emissão Nota Fiscal</li>
            </ol>
        @endif
        --}}
    </section>
    {!! Form::open(['url' => "mra_g_iugu/mra_g_iugu_planos".($acao=='edit'?'/'.$MRAGIuguPlanos->id:''), 'method' => ($acao=='edit'?'put':'post'), 'novalidate'=> true, 'enctype' => 'multipart/form-data', 'accept-charset' => 'utf-8', 'id' => ($acao=='edit'?'form_edit':'form_add').'_mra_g_iugu_planos']) !!}
    <section class="content">
        <div class="box">
            @if($acao=='edit')
                {!! Form::hidden('id', $MRAGIuguPlanos->id) !!}
                @if(\App\Models\Permissions::permissaoUsuario(\Auth::user(), "$controller@destroy"))
                    <div class="row" style="position: absolute; right: 0; padding: 5px; z-index: 100;">
                        <div class="col-md-12">
                            <a href="javascript:void(0);" type="button" class="btn btn-info consultar-plano" modulo="mra_g_iugu/mra_g_iugu_planos" modulo_id="{{$MRAGIuguPlanos->id}}" style="margin:2px;"><i class="glyphicon glyphicon-refresh"></i> Recarregar Plano Iugu</a>
                            <a href="javascript:void(0);" type="button" class="btn btn-danger excluir-auto-confirma" modulo="mra_g_iugu/mra_g_iugu_planos" modulo_id="{{$MRAGIuguPlanos->id}}" style="float:none;"><i class="glyphicon glyphicon-trash"></i></a>
                        </div>
                    </div>
                @endif
            @endif
            <div class="box-body" id="div_mra_g_iugu_planos">

                <div size="12" class="inputbox col-md-12">
                    <h2 class="page-header" style="font-size:20px;">
                        <i class="glyphicon glyphicon-th-large"></i> Informações do Plano
                    </h2>
                </div>

                <div size="12" class="inputbox col-md-12">
                    <div class="row">
                        {{--<div size="2" class="inputbox col-md-2">
                            <div class="form-group">
                                {!! Form::label('','Status') !!}
                                {!! Form::select('status', \App\Http\Controllers\MRA\MRAListas::Get_options_status_ai([""]), ($acao=='edit'?$MRAGIuguPlanos->status:null), ['class' => 'form-control select_single_no_trigger' , "id" => "input_status"]) !!}
                            </div>
                        </div>--}}
                        @if($acao=='edit')
                            <div size="4" class="inputbox col-md-4">
                                <div class="form-group">
                                    {!! Form::label('','ID Iugu Plano',['class'=>'text-warning']) !!}
                                    {!! Form::text('iugu_plan_id', ($acao=='edit'?$MRAGIuguPlanos->iugu_plan_id:null), ['class' => 'form-control', "id" => "input_iugu_plan_id", "disabled"=>true]) !!}
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="row">
                        <div size="4" class="inputbox col-md-4">
                            <div class="form-group">
                                {!! Form::label('','Nome') !!} <span style="color: #ff0500;">*</span>
                                {!! Form::text('nome', ($acao=='edit'?$MRAGIuguPlanos->nome:null), ['class' => 'form-control' , "id" => "input_nome", "maxlength"=>100]) !!}
                            </div>
                        </div>
                        <div size="4" class="inputbox col-md-4">
                            <div class="form-group">
                                {!! Form::label('','Identificador Plano Iugu') !!} <span style="color: #ff0500;">*</span>
                                {!! Form::text('iugu_plan_identifier', ($acao=='edit'?$MRAGIuguPlanos->iugu_plan_identifier:null), ['class' => 'form-control', "id" => "input_iugu_plan_identifier", "disabled"=>($acao=='edit'?true:false)]) !!}
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','Valor') !!} <span style="color: #ff0500;">*</span>
                                <div class="input-group">
                                    <div class="input-group-addon"><strong>R$</strong></div>
                                    {!! Form::text('valor', ($acao=='edit'?\App\Helper\Helper::H_Decimal_DB_ptBR($MRAGIuguPlanos->valor):null), ['class' => 'form-control money_v2', "placeholder" => "0,00", "id" => "input_valor"]) !!}
                                </div>
                            </div>
                        </div>
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','Intervalo') !!} <span style="color: #ff0500;">*</span>
                                {!! Form::number('intervalo', ($acao=='edit'?$MRAGIuguPlanos->intervalo:null), ['class' => 'form-control' , "id" => "input_intervalo", "min"=>1, "max"=>12]) !!}
                            </div>
                        </div>
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','Tipo de Intervalo') !!} <span style="color: #ff0500;">*</span>
                                {!! Form::select('intervalo_tipo', \App\Http\Controllers\MRA\MRAGIugu::Get_options_tipo_intervalo([""]), ($acao=='edit'?$MRAGIuguPlanos->intervalo_tipo:null), ['class' => 'form-control select_single_no_trigger', 'data-live-search' => 'true', "id" => "input_intervalo_tipo"]) !!}
                            </div>
                        </div>
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','Dias de Faturamento') !!} <span style="color: #ff0500;">*</span>
                                {!! Form::number('dias_ger_faturamento', ($acao=='edit'?$MRAGIuguPlanos->dias_ger_faturamento:null), ['class' => 'form-control' , "id" => "input_dias_ger_faturamento", "min"=>1, "max"=>30]) !!}
                                <p class="help-block"><i>Dias antes de vencer gera uma nova fatura.</i></p>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div size="12" class="inputbox col-md-12">
                            <div class="form-group">
                                {!! Form::label('','Formas de Pagamento') !!} <span style="color: #ff0500;">*</span>
                                <div class="l_checkbox_radio">
                                    <label>{!! Form::checkbox('fp_todos', 1, ($acao=='edit'?$MRAGIuguPlanos->fp_todos:null), ['class' => '' , "id" => "input_fp_todos"]) !!} Todos</label>
                                    <label>{!! Form::checkbox('fp_cartao_credito', 1, ($acao=='edit'?$MRAGIuguPlanos->fp_cartao_credito:null), ['class' => '' , "id" => "input_fp_cartao_credito"]) !!} Cartão de Crédito</label>
                                    <label>{!! Form::checkbox('fp_boleto', 1, ($acao=='edit'?$MRAGIuguPlanos->fp_boleto:null), ['class' => '' , "id" => "input_fp_boleto"]) !!} Boleto</label>
                                    <label>{!! Form::checkbox('fp_pix', 1, ($acao=='edit'?$MRAGIuguPlanos->fp_pix:null), ['class' => '' , "id" => "input_fp_pix"]) !!} Pix</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                {{--@if(0)
                    @if(App\Models\Permissions::permissaoModerador(\Auth::user()))
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="">Para quem essa informação ficará disponível? Selecione um
                                    usuário. </label>
                                @php
                                    $parserList = array();
                                    $userlist = App\Models\User::get()->toArray();
                                    array_unshift($userlist, array('id' => '',  'name' => ''));
                                    array_unshift($userlist, array('id' => 0,  'name' => 'Disponível para todos'));
                                    foreach($userlist as $u)
                                    {
                                        $parserList[$u['id']] = $u['name'];
                                    }
                                @endphp
                                {!! Form::select('r_auth', $parserList, null, ['class' => 'form-control']) !!}
                            </div>
                        </div>
                    @endif
                @endif--}}
                <div class="col-md-12" style="margin-top: 20px; margin-bottom: 20px;">
                    <div class="form-group form-group-btn-{{($acao=='edit'?'edit':'add')}}">
                        <a href="{{ URL::previous() }}" class="btn btn-default form-group-btn-add-voltar"><i class="glyphicon glyphicon-backward"></i> Voltar</a>
                        @if($acao == 'add' and App\Models\Permissions::permissaoUsuario(\Auth::user(), "$controller@store"))
                            <button type="submit" class="btn btn-default right form-group-btn-add-cadastrar"><span class="glyphicon glyphicon-plus"></span> Cadastrar</button>
                        @endif
                        @if($acao == 'edit' and App\Models\Permissions::permissaoUsuario(\Auth::user(), "$controller@update"))
                            <button type="submit" class="btn btn-default right form-group-btn-edit-salvar" style="float:right;"><span class="glyphicon glyphicon-ok"></span> Salvar</button>
                        @endif
                        @if($acao == 'edit' and App\Models\Permissions::permissaoUsuario(\Auth::user(), "$controller@destroy"))
                            <button type="submit" name="destroy_forcado" value="1" class="btn btn-danger right form-group-btn-edit-destroy-forcado" onclick="javascript: return (confirm('O Cliente será excluído dentro do sistema! Tem certeza?')?true:false);" style="float:right; margin: 0px; margin-right:15px;"><i class="glyphicon glyphicon-alert"></i>&nbsp; Forçar Exclusão</button>
                        @endif
                    </div>
                </div>
                @if($acao == 'edit')
                    <div class="col-md-12">
                        <p class="text-warning text-right">
                            <strong>** Atenção!</strong> <i class="glyphicon glyphicon-alert"></i><br/>
                            - <strong class="text-danger">Forçar Exclusão</strong> apenas será removido <strong>dentro do sistema</strong>!</strong>
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </section>
    {!! Form::close() !!}

    @section('script')
        <script type="text/javascript">
            $("#input_fp_todos").on('change',function(){
                if($(this).prop('checked')){
                    $("#input_fp_cartao_credito").prop('checked',true);
                    $("#input_fp_boleto").prop('checked',true);
                    $("#input_fp_pix").prop('checked',true);
                }else {
                    $("#input_fp_cartao_credito").prop('checked',false);
                    $("#input_fp_boleto").prop('checked',false);
                    $("#input_fp_pix").prop('checked',false);
                }
            });
            $("#input_fp_cartao_credito,#input_fp_boleto,#input_fp_pix").on('change',function(){
                if($("#input_fp_cartao_credito").prop('checked') && $("#input_fp_boleto").prop('checked') && $("#input_fp_pix").prop('checked')){
                    $("#input_fp_todos").prop('checked',true);
                }else {
                    $("#input_fp_todos").prop('checked',false);
                }
            });

            // :: Consultar Assinaturas
            $(".consultar-plano").on("click", function() {
                let modulo     = ($(this).attr('modulo')!=undefined && $(this).attr('modulo') != ""?$(this).attr('modulo'):undefined);
                let modulo_id  = ($(this).attr('modulo_id')!=undefined && $(this).attr('modulo_id') != ""?$(this).attr('modulo_id'):undefined);

                if(modulo == undefined || modulo_id == undefined){ return false; }

                let form    = document.createElement('form');
                form.method = 'GET';
                form.action = base+'/'+modulo+'/consultar_plano/'+modulo_id
                let input   = document.createElement('input');
                input.type  = 'hidden';
                input.name  = '_token';
                input.value = $("meta[name=\'csrf-token\']").attr('content');
                form.appendChild(input);
                $('body').append(form);
                form.submit();
            });
        </script>
    @endsection

@endsection
