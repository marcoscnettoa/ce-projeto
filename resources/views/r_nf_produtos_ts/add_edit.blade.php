@php
    $acao       = ((isset($MRANfProdutos) and !is_null($MRANfProdutos))?'edit':'add');
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
        <h1>Nota Fiscal - Produtos</h1>
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
    {!! Form::open(['url' => "nota_fiscal/produtos/ts".($acao=='edit'?'/'.$MRANfProdutos->id:''), 'method' => ($acao=='edit'?'put':'post'), 'novalidate'=> true, 'enctype' => 'multipart/form-data', 'accept-charset' => 'utf-8', 'id' => ($acao=='edit'?'form_edit':'form_add').'_produtos']) !!}
    <section class="content">
        <div class="box" style="margin-bottom: 0; margin-top: 0;">
            @if($acao=='edit')
                {!! Form::hidden('id', $MRANfProdutos->id) !!}
                @if(\App\Models\Permissions::permissaoUsuario(\Auth::user(), "$controller@destroy"))
                    <div class="row" style="position: absolute; right: 0; padding: 5px; z-index: 100;">
                        <div class="col-md-12">
                            <a href="javascript:void(0);" type="button" class="btn btn-danger excluir-auto-confirma" modulo="nota_fiscal/produtos/ts" modulo_id="{{$MRANfProdutos->id}}"><i class="glyphicon glyphicon-trash"></i></a>
                        </div>
                    </div>
                @endif
            @endif
            <div class="box-body" id="div_produtos">

                <div size="12" class="inputbox col-md-12">
                    <h2 class="page-header" style="font-size:20px;">
                        <i class="glyphicon glyphicon-th-large"></i> Informações do Produto
                    </h2>
                </div>

                <div size="12" class="inputbox col-md-12">
                    <div class="row">
                        <div size="2" class="inputbox col-md-2">
                            <div class="form-group">
                                {!! Form::label('','Status') !!}
                                {!! Form::select('status', \App\Http\Controllers\MRA\MRAListas::Get_options_status_ai([""]), ($acao=='edit'?$MRANfProdutos->status:null), ['class' => 'form-control select_single_no_trigger' , "id" => "input_status"]) !!}
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div size="6" class="inputbox col-md-6">
                            <div class="form-group">
                                {!! Form::label('','Nome') !!} <span style="color: #ff0500;">*</span>
                                {!! Form::text('nome', ($acao=='edit'?$MRANfProdutos->nome:null), ['class' => 'form-control' , "id" => "input_nome", "maxlength"=>250]) !!}
                            </div>
                        </div>
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','Valor') !!} <span style="color: #ff0500;">*</span>
                                <div class="input-group">
                                    <div class="input-group-addon"><strong>R$</strong></div>
                                    {!! Form::text('valor_venda', ($acao=='edit'?\App\Helper\Helper::H_Decimal_DB_ptBR($MRANfProdutos->valor_venda):null), ['class' => 'form-control money_v2', "placeholder" => "0,00", "id" => "input_valor"]) !!}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','Código do Produto') !!} <span style="color: #ff0500;">*</span>
                                {!! Form::text('codigo', ($acao=='edit'?$MRANfProdutos->codigo:null), ['class' => 'form-control', "id" => "input_codigo", "maxlength"=>250]) !!}
                            </div>
                        </div>
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','Código Fiscal / Logística') !!}
                                {!! Form::text('codigo_fiscal', ($acao=='edit'?$MRANfProdutos->codigo_fiscal:null), ['class' => 'form-control', "id" => "input_codigo", "maxlength"=>250]) !!}
                            </div>
                        </div>
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','Código de Barras') !!}
                                {!! Form::text('codigo_barras', ($acao=='edit'?$MRANfProdutos->codigo_barras:null), ['class' => 'form-control', "id" => "input_codigo", "maxlength"=>250]) !!}
                            </div>
                        </div>
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','Unidade de Medida') !!}
                                {!! Form::text('unidade_medida', ($acao=='edit'?$MRANfProdutos->unidade_medida:null), ['class' => 'form-control', "id" => "input_unidade_medida", "maxlength"=>100]) !!}
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div size="8" class="inputbox col-md-8">
                            <div class="form-group">
                                {!! Form::label('','Descrição do Produto') !!}
                                {!! Form::textarea('observacoes', ($acao=='edit'?$MRANfProdutos->observacoes:null), ['class' => 'form-control' , "id" => "input_descricao_servico", "rows" => 4]) !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="content">
        <div class="box" style="margin-bottom: 0;">
            <div class="box-body" style="margin-top: 0;">
                <div size="12" class="inputbox col-md-12">
                    <h2 class="page-header" style="font-size:20px;">
                        <i class="glyphicon glyphicon-th-large"></i> Configurações e Impostos
                    </h2>
                </div>

                <div size="12" class="inputbox col-md-12">
                    <div class="row">
                        <div size="6" class="inputbox col-md-6">
                            <div class="form-group">
                                {!! Form::label('','ORIGEM') !!} <span style="color: #ff0500;">*</span>
                                {!! Form::select('origem', \App\Http\Controllers\MRA\RNfListasTsController::Get_options_origem(), ($acao=='edit'?$MRANfProdutos->origem:null), ['class' => 'form-control select_single_no_trigger bootstrap-select-st2', 'data-live-search' => 'true', "dropdown-menu-right"=>"", "id" => "input_origem"]) !!}
                            </div>
                        </div>
                        <div size="12" class="inputbox col-md-12">
                            <div class="form-group">
                                {!! Form::label('','CST') !!} <span style="color: #ff0500;">*</span>
                                {!! Form::select('cst', \App\Http\Controllers\MRA\RNfListasTsController::Get_options_cst(), ($acao=='edit'?$MRANfProdutos->cst:null), ['class' => 'form-control select_single_no_trigger bootstrap-select-st2', 'data-live-search' => 'true', "dropdown-menu-right"=>"", "id" => "input_cst"]) !!}
                            </div>
                        </div>
                        <div size="12" class="inputbox col-md-12">
                            <div class="form-group">
                                {!! Form::label('','CFOP') !!} <span style="color: #ff0500;">*</span>
                                {!! Form::select('cfop', \App\Http\Controllers\MRA\RNfListasTsController::Get_options_cfop(), ($acao=='edit'?$MRANfProdutos->cfop:null), ['class' => 'form-control select_single_no_trigger bootstrap-select-st2', 'data-live-search' => 'true', "dropdown-menu-right"=>"", "id" => "input_cfop"]) !!}
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','NCM') !!} <span style="color: #ff0500;">*</span>
                                {!! Form::number('ncm', ($acao=='edit'?$MRANfProdutos->ncm:null), ['class' => 'form-control', "id" => "input_ncm", "maxlength"=>8]) !!}
                            </div>
                        </div>
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','CEST') !!}
                                {!! Form::text('cest', ($acao=='edit'?$MRANfProdutos->cest:null), ['class' => 'form-control', "id" => "input_cest", "maxlength"=>50]) !!}
                            </div>
                        </div>
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','Valor de Desconto') !!}
                                <div class="input-group">
                                    <div class="input-group-addon"><strong>R$</strong></div>
                                    {!! Form::text('valor_desconto', ($acao=='edit'?\App\Helper\Helper::H_Decimal_DB_ptBR($MRANfProdutos->valor_desconto):null), ['class' => 'form-control money_v2', "placeholder" => "0,00", "id" => "input_valor_desconto"]) !!}
                                </div>
                            </div>
                        </div>
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','Valor do Seguro') !!}
                                <div class="input-group">
                                    <div class="input-group-addon"><strong>R$</strong></div>
                                    {!! Form::text('valor_seguro', ($acao=='edit'?\App\Helper\Helper::H_Decimal_DB_ptBR($MRANfProdutos->valor_seguro):null), ['class' => 'form-control money_v2', "placeholder" => "0,00", "id" => "input_valor_seguro"]) !!}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div size="3" class="inputbox col-md-3">
                            <div class="row" style="background-color: #f1f1f1;">
                                <div size="12" class="inputbox col-md-12" style="padding-top: 15px;">
                                    <div class="form-group">
                                        {!! Form::label('','ICMS - CST') !!}
                                        {!! Form::select('icms_cst', \App\Http\Controllers\MRA\RNfListasTsController::Get_options_cst_icms(), ($acao=='edit'?$MRANfProdutos->icms_cst:null), ['class' => 'form-control select_single_no_trigger', 'data-live-search' => 'true', "id" => "input_icms_cst"]) !!}
                                    </div>
                                </div>
                                <div size="12" class="inputbox col-md-12">
                                    <div class="form-group">
                                        {!! Form::label('','ICMS %') !!}
                                        <div class="input-group">
                                            <div class="input-group-addon"><strong>%</strong></div>
                                            {!! Form::text('icms_icms', ($acao=='edit'?\App\Helper\Helper::H_Decimal_DB_ptBR($MRANfProdutos->icms_icms):null), ['class' => 'form-control money_v2', "placeholder" => "0,00", "id" => "input_icms_icms"]) !!}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div size="3" class="inputbox col-md-3">
                            <div class="row" style="background-color: #e3e3e3;">
                                <div size="12" class="inputbox col-md-12" style="padding-top: 15px;">
                                    <div class="form-group">
                                        {!! Form::label('','IPI - CST') !!} <span style="color: #ff0500;">*</span>
                                        {!! Form::select('ipi_cst', \App\Http\Controllers\MRA\RNfListasTsController::Get_options_cst_ipi(), ($acao=='edit'?$MRANfProdutos->ipi_cst:null), ['class' => 'form-control select_single_no_trigger', 'data-live-search' => 'true', "id" => "input_ipi_cst"]) !!}
                                    </div>
                                </div>
                                <div size="12" class="inputbox col-md-12">
                                    <div class="form-group">
                                        {!! Form::label('','IPI %') !!}
                                        <div class="input-group">
                                            <div class="input-group-addon"><strong>%</strong></div>
                                            {!! Form::text('ipi_ipi', ($acao=='edit'?\App\Helper\Helper::H_Decimal_DB_ptBR($MRANfProdutos->ipi_ipi):null), ['class' => 'form-control money_v2', "placeholder" => "0,00", "id" => "input_ipi_ipi"]) !!}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div size="3" class="inputbox col-md-3">
                            <div class="row" style="background-color: #f1f1f1;">
                                <div size="12" class="inputbox col-md-12" style="padding-top: 15px;">
                                    <div class="form-group">
                                        {!! Form::label('','PIS - CST') !!} <span style="color: #ff0500;">*</span>
                                        {!! Form::select('pis_cst', \App\Http\Controllers\MRA\RNfListasTsController::Get_options_cst_pis_ou_cofins(), ($acao=='edit'?$MRANfProdutos->pis_cst:null), ['class' => 'form-control select_single_no_trigger', 'data-live-search' => 'true', "dropdown-menu-right"=>"", "id" => "input_pis_cst"]) !!}
                                    </div>
                                </div>
                                <div size="12" class="inputbox col-md-12">
                                    <div class="form-group">
                                        {!! Form::label('','PIS %') !!}
                                        <div class="input-group">
                                            <div class="input-group-addon"><strong>%</strong></div>
                                            {!! Form::text('pis_pis', ($acao=='edit'?\App\Helper\Helper::H_Decimal_DB_ptBR($MRANfProdutos->pis_pis):null), ['class' => 'form-control money_v2', "placeholder" => "0,00", "id" => "input_pis_pis"]) !!}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div size="3" class="inputbox col-md-3">
                            <div class="row" style="background-color: #e3e3e3;">
                                <div size="12" class="inputbox col-md-12" style="padding-top: 15px;">
                                    <div class="form-group">
                                        {!! Form::label('','COFINS - CST - ') !!} <span style="color: #ff0500;">*</span>
                                        {!! Form::select('cofins_cst', \App\Http\Controllers\MRA\RNfListasTsController::Get_options_cst_pis_ou_cofins(), ($acao=='edit'?$MRANfProdutos->cofins_cst:null), ['class' => 'form-control select_single_no_trigger', 'data-live-search' => 'true', "dropdown-menu-right"=>"", "id" => "input_cofins_cst"]) !!}
                                    </div>
                                </div>
                                <div size="12" class="inputbox col-md-12">
                                    <div class="form-group">
                                        {!! Form::label('','COFINS %') !!}
                                        <div class="input-group">
                                            <div class="input-group-addon"><strong>%</strong></div>
                                            {!! Form::text('cofins_cofins', ($acao=='edit'?\App\Helper\Helper::H_Decimal_DB_ptBR($MRANfProdutos->cofins_cofins):null), ['class' => 'form-control money_v2', "placeholder" => "0,00", "id" => "input_cofins_cofins"]) !!}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="content">
        <div class="box">
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
