@php
     $acao       = ((isset($MRANfServicos) and !is_null($MRANfServicos))?'edit':'add');
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
        <h1>Nota Fiscal - Serviços</h1>
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
    {!! Form::open(['url' => "nota_fiscal/servicos/ts".($acao=='edit'?'/'.$MRANfServicos->id:''), 'method' => ($acao=='edit'?'put':'post'), 'novalidate'=> true, 'enctype' => 'multipart/form-data', 'accept-charset' => 'utf-8', 'id' => ($acao=='edit'?'form_edit':'form_add').'_servicos']) !!}
    <section class="content">
        <div class="box" style="margin-bottom: 0; margin-top: 0;">
            @if($acao=='edit')
                {!! Form::hidden('id', $MRANfServicos->id) !!}
                @if(\App\Models\Permissions::permissaoUsuario(\Auth::user(), "$controller@destroy"))
                    <div class="row" style="position: absolute; right: 0; padding: 5px; z-index: 100;">
                        <div class="col-md-12">
                            <a href="javascript:void(0);" type="button" class="btn btn-danger excluir-auto-confirma" modulo="nota_fiscal/servicos/ts" modulo_id="{{$MRANfServicos->id}}"><i class="glyphicon glyphicon-trash"></i></a>
                        </div>
                    </div>
                @endif
            @endif
            <div class="box-body" id="div_servicos_ts">

                <div size="12" class="inputbox col-md-12">
                    <h2 class="page-header" style="font-size:20px;">
                        <i class="glyphicon glyphicon-th-large"></i> Informações do Serviço
                    </h2>
                </div>

                <div size="12" class="inputbox col-md-12">
                    <div class="row">
                        <div size="2" class="inputbox col-md-2">
                            <div class="form-group">
                                {!! Form::label('','Status') !!}
                                {!! Form::select('status', \App\Http\Controllers\MRA\MRAListas::Get_options_status_ai([""]), ($acao=='edit'?$MRANfServicos->status:null), ['class' => 'form-control select_single_no_trigger' , "id" => "input_status"]) !!}
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div size="6" class="inputbox col-md-6">
                            <div class="form-group">
                                {!! Form::label('','Nome') !!} <span style="color: #ff0500;">*</span>
                                {!! Form::text('nome', ($acao=='edit'?$MRANfServicos->nome:null), ['class' => 'form-control' , "id" => "input_nome", "maxlength"=>250]) !!}
                            </div>
                        </div>
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','Código do Serviço') !!} <span style="color: #ff0500;">*</span>
                                {!! Form::text('codigo', ($acao=='edit'?$MRANfServicos->codigo:null), ['class' => 'form-control', "id" => "input_codigo", "maxlength"=>250]) !!}
                            </div>
                        </div>
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','Valor') !!} <span style="color: #ff0500;">*</span>
                                <div class="input-group">
                                    <div class="input-group-addon"><strong>R$</strong></div>
                                    {!! Form::text('valor', ($acao=='edit'?\App\Helper\Helper::H_Decimal_DB_ptBR($MRANfServicos->valor):null), ['class' => 'form-control money_v2', "placeholder" => "0,00", "id" => "input_valor"]) !!}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div size="8" class="inputbox col-md-8">
                            <div class="form-group">
                                {!! Form::label('','Descrição do Serviço') !!}
                                {!! Form::textarea('descricao_servico', ($acao=='edit'?$MRANfServicos->descricao_servico:null), ['class' => 'form-control' , "id" => "input_descricao_servico", "rows" => 4]) !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="content">
        <div class="box" style="margin-bottom: 0; margin-top: -25px">
            <div class="box-body" style="margin-top: 0;">
                <div size="12" class="inputbox col-md-12">
                    <h2 class="page-header" style="font-size:20px;">
                        <i class="glyphicon glyphicon-th-large"></i> Configurações
                    </h2>
                </div>
                <div size="12" class="inputbox col-md-12">
                    <div class="row">
                        <div size="12" class="inputbox col-md-12">
                            <div class="form-group">
                                {!! Form::label('','Atividade / CNAE') !!}
                                {!! Form::select('imp_atividade_cnae', \App\Http\Controllers\MRA\RNfListasTsController::Get_options_nf_cnae_23(), ($acao=='edit'?$MRANfServicos->imp_atividade_cnae:null), ['class' => 'form-control select_single_no_trigger bootstrap-select-st2', 'data-live-search' => 'true', "dropdown-menu-right"=>"", "id" => "input_imp_atividade_cnae"]) !!}
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div size="2" class="inputbox col-md-2">
                            <div class="form-group">
                                {!! Form::label('','Cofins %') !!}
                                <div class="input-group">
                                    <div class="input-group-addon"><strong>%</strong></div>
                                    {!! Form::text('imp_confis', ($acao=='edit'?\App\Helper\Helper::H_Decimal_DB_ptBR($MRANfServicos->imp_confis):null), ['class' => 'form-control money_v2', "placeholder" => "0,00", "id" => "input_imp_confis"]) !!}
                                </div>
                            </div>
                        </div>
                        <div size="2" class="inputbox col-md-2">
                            <div class="form-group">
                                {!! Form::label('','CSLL %') !!}
                                <div class="input-group">
                                    <div class="input-group-addon"><strong>%</strong></div>
                                    {!! Form::text('imp_csll', ($acao=='edit'?\App\Helper\Helper::H_Decimal_DB_ptBR($MRANfServicos->imp_csll):null), ['class' => 'form-control money_v2', "placeholder" => "0,00", "id" => "input_imp_csll"]) !!}
                                </div>
                            </div>
                        </div>
                        <div size="2" class="inputbox col-md-2">
                            <div class="form-group">
                                {!! Form::label('','INSS %') !!}
                                <div class="input-group">
                                    <div class="input-group-addon"><strong>%</strong></div>
                                    {!! Form::text('imp_inss', ($acao=='edit'?\App\Helper\Helper::H_Decimal_DB_ptBR($MRANfServicos->imp_inss):null), ['class' => 'form-control money_v2', "placeholder" => "0,00", "id" => "input_imp_inss"]) !!}
                                </div>
                            </div>
                        </div>
                        <div size="2" class="inputbox col-md-2">
                            <div class="form-group">
                                {!! Form::label('','IR %') !!}
                                <div class="input-group">
                                    <div class="input-group-addon"><strong>%</strong></div>
                                    {!! Form::text('imp_ir', ($acao=='edit'?\App\Helper\Helper::H_Decimal_DB_ptBR($MRANfServicos->imp_ir):null), ['class' => 'form-control money_v2', "placeholder" => "0,00", "id" => "input_imp_ir"]) !!}
                                </div>
                            </div>
                        </div>
                        <div size="2" class="inputbox col-md-2">
                            <div class="form-group">
                                {!! Form::label('','PIS %') !!}
                                <div class="input-group">
                                    <div class="input-group-addon"><strong>%</strong></div>
                                    {!! Form::text('imp_pis', ($acao=='edit'?\App\Helper\Helper::H_Decimal_DB_ptBR($MRANfServicos->imp_pis):null), ['class' => 'form-control money_v2', "placeholder" => "0,00", "id" => "input_imp_pis"]) !!}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div size="2" class="inputbox col-md-2">
                            <div class="form-group">
                                {!! Form::label('','ISS %') !!}
                                <div class="input-group">
                                    <div class="input-group-addon"><strong>%</strong></div>
                                    {!! Form::text('imp_iss', ($acao=='edit'?\App\Helper\Helper::H_Decimal_DB_ptBR($MRANfServicos->imp_iss,4):null), ['class' => 'form-control money_v2', "placeholder" => "0,0000", "id" => "input_imp_iss", "maskMoney_precision"=>4]) !!}
                                </div>
                            </div>
                        </div>
                        <div size="2" class="inputbox col-md-2">
                            <div class="form-group">
                                {!! Form::label('','ISS Retido na Fonte') !!}
                                {!! Form::select('imp_iss_retido_fonte', [1=>'Sim',0=>'Não'], ($acao=='edit'?$MRANfServicos->imp_iss_retido_fonte:0), ['class' => 'form-control select_single_no_trigger' , "id" => "input_imp_iss_retido_fonte"]) !!}
                            </div>
                        </div>
                    </div>
                </div>
                <div size="12" class="inputbox col-md-12">
                    <div class="row">
                        <div size="12" class="inputbox col-md-12">
                            <div class="form-group">
                                {!! Form::label('','Item da Lista de Serviço (LC116)') !!}
                                @php
                                    $imp_servico_lc116 = null;
                                    if(old('imp_servico_lc116')){
                                        $imp_servico_lc116 = old('imp_servico_lc116');
                                    }else {
                                        $imp_servico_lc116 = ($acao=='edit'?$MRANfServicos->imp_servico_lc116:null);
                                    }
                                @endphp
                                <select id="input_imp_servico_lc116" name="imp_servico_lc116" class="form-control select_single_no_trigger bootstrap-select-st2" data-live-search="true">
                                    {!! App\Models\RNfListasTs::Get_options_nf_servicos_lc116($imp_servico_lc116) !!}
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div size="4" class="inputbox col-md-4">
                            <div class="form-group">
                                {!! Form::label('','Código de Serviço no Município') !!}
                                {!! Form::text('imp_cod_servico_municip', ($acao=='edit'?$MRANfServicos->imp_cod_servico_municip:null), ['class' => 'form-control', "id" => "input_imp_cod_servico_municip", "maxlength"=>200]) !!}
                            </div>
                        </div>
                        <div size="4" class="inputbox col-md-4">
                            <div class="form-group">
                                {!! Form::label('','Descrição do Serviço no Município') !!}
                                {!! Form::text('imp_desc_servico_municip', ($acao=='edit'?$MRANfServicos->imp_desc_servico_municip:null), ['class' => 'form-control', "id" => "input_imp_desc_servico_municip", "maxlength"=>200]) !!}
                            </div>
                        </div>
                        {{-- <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','Enviar no e-mail?') !!}
                                {!! Form::select('cfg_enviar_email', [1=>'Sim',0=>'Não'], ($acao=='edit'?$MRANfServicos->cfg_enviar_email:0), ['class' => 'form-control select_single_no_trigger' , "id" => "input_cfg_enviar_email"]) !!}
                            </div>
                        </div> --}}
                    </div>
                    <div class="row">
                        <div id="box_cfg_emails" size="9" class="inputbox col-md-9" style="display:none;">
                            <div class="form-group">
                                {!! Form::label('','E-mail(s)') !!}
                                {!! Form::text('cfg_emails', ($acao=='edit'?$MRANfServicos->cfg_emails:null), ['class' => 'form-control select_tags', "id" => "input_cfg_emails", "maxTags"=>3, "maxlength"=>600]) !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="content" >
        <div class="box" style="margin-top: -25px">
            <div class="box-body" style="">
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
                <div class="col-md-12" style="">
                    <div class="form-group form-group-btn-{{($acao=='edit'?'edit':'add')}}">
                        <a href="{{ URL::previous() }}" class="btn btn-default form-group-btn-add-voltar"><i class="glyphicon glyphicon-backward"></i> Voltar</a>
                        @if($acao == 'add' and App\Models\Permissions::permissaoUsuario(\Auth::user(), "$controller@store"))
                            <button type="submit" class="btn btn-default right form-group-btn-add-cadastrar"><span class="glyphicon glyphicon-plus"></span> Cadastrar</button>
                        @endif
                        @if($acao == 'edit' and App\Models\Permissions::permissaoUsuario(\Auth::user(), "$controller@update"))
                            <button type="submit" class="btn btn-default right form-group-btn-edit-salvar"><span class="glyphicon glyphicon-ok"></span> Salvar</button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
    {!! Form::close() !!}

    @section('script')
        <script type="text/javascript">
            $("#input_cfg_enviar_email").on('change',function(){
                if($(this).val() == 1){
                    $("#box_cfg_emails").show();
                }else {
                    $("#box_cfg_emails").hide();
                }
            }).trigger('change');
        </script>
    @endsection

@endsection
