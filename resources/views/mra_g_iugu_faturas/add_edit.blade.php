@php
    $acao               = ((isset($MRAGIuguFaturas) and !is_null($MRAGIuguFaturas))?'edit':'add');
    $isPublic           = 0;
    $controller         = get_class(\Request::route()->getController());

    $MRAGIuguClientes   = ($acao=='edit'?$MRAGIuguFaturas->MRAGIuguClientes:null);
    $disabled           = (($acao=='edit')?true:false);
@endphp
@extends($isPublic ? 'layouts.app-public' : 'layouts.app')
@section('content')
    @section('style')
        <style type="text/css">
        </style>
    @endsection
    <section class="content-header">
        <h1>Gateway Iugu - Faturas</h1>
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
    {!! Form::open(['url' => "mra_g_iugu/mra_g_iugu_faturas".($acao=='edit'?'/'.$MRAGIuguFaturas->id:''), 'method' => ($acao=='edit'?'put':'post'), 'novalidate'=> true, 'enctype' => 'multipart/form-data', 'accept-charset' => 'utf-8', 'id' => ($acao=='edit'?'form_edit':'form_add').'_mra_g_iugu_faturas']) !!}
    <section class="content">
        <div class="box">
            @if($acao=='edit')
                {!! Form::hidden('id', $MRAGIuguFaturas->id) !!}
                @if(\App\Models\Permissions::permissaoUsuario(\Auth::user(), "$controller@destroy"))
                    <div class="row" style="position: absolute; right: 0; padding: 5px; z-index: 100;">
                        <div class="col-md-12">
                            <a href="javascript:void(0);" type="button" class="btn btn-info consultar-fatura" modulo="mra_g_iugu/mra_g_iugu_faturas" modulo_id="{{$MRAGIuguFaturas->id}}" style="margin:2px;"><i class="glyphicon glyphicon-refresh"></i> Consultar Fatura Iugu</a>
                            <a href="javascript:void(0);" type="button" class="btn btn-danger excluir-auto-confirma" modulo="mra_g_iugu/mra_g_iugu_faturas" modulo_id="{{$MRAGIuguFaturas->id}}" style="float:none;"><i class="glyphicon glyphicon-trash"></i></a>
                        </div>
                    </div>
                @endif
            @endif
            <div class="box-body" id="div_mra_g_iugu_faturas">

                <div size="12" class="inputbox col-md-12">
                    <h2 class="page-header" style="font-size:20px;">
                        <i class="glyphicon glyphicon-th-large"></i> Informações da Fatura
                    </h2>
                </div>

                <div size="12" class="inputbox col-md-12">
                    @if($acao=='edit')
                    <div class="row">
                        <div size="2" class="inputbox col-md-2">
                            <div class="form-group">
                                {!! Form::label('','Status',['class'=>'text-warning']) !!}
                                {!! Form::select('iugu_status', \App\Http\Controllers\MRA\MRAGIugu::Get_options_invoice_iugu_status([""]), ($acao=='edit'?$MRAGIuguFaturas->iugu_status:null), ['class' => 'form-control select_single_no_trigger' , "id" => "input_iugu_status", "disabled"=>$disabled]) !!}
                            </div>
                        </div>
                        <div size="4" class="inputbox col-md-4">
                            <div class="form-group">
                                {!! Form::label('','ID Iugu Fatura',['class'=>'text-warning']) !!}
                                {!! Form::text('iugu_invoices_id', ($acao=='edit'?$MRAGIuguFaturas->iugu_invoices_id:null), ['class' => 'form-control', "id" => "input_iugu_invoices_id", "disabled"=>$disabled]) !!}
                            </div>
                        </div>
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','N&deg; Fatura') !!}
                                {!! Form::text('iugu_order_id', ($acao=='edit'?$MRAGIuguFaturas->iugu_order_id:null), ['class' => 'form-control', "id" => "input_iugu_order_id", "disabled"=>true]) !!}
                            </div>
                        </div>
                    </div>
                    @endif
                    <div class="row">
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','Data de Vencimento') !!} <span style="color: #ff0500;">*</span>
                                <div class="input-group">
                                    <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                                    {!! Form::text('iugu_due_date', ($acao=='edit'?\App\Helper\Helper::H_Data_DB_ptBR($MRAGIuguFaturas->iugu_due_date):date('d-m-Y')), ['autocomplete' =>'off', 'class' => 'form-control componenteData_v2', "placeholder"=>"__/__/____","id" => "input_iugu_due_date", "disabled"=>$disabled]) !!}
                                </div>
                            </div>
                        </div>
                        <div size="4" class="inputbox col-md-4">
                            {!! Form::label('','Cliente',['class'=>'text-info']) !!}
                            <div class="input-group mb_15p">
                                {!! Form::select('mra_g_iugu_clientes_id', \App\Models\MRAGIuguClientes::lista_clientes(), ($acao=='edit'?$MRAGIuguFaturas->mra_g_iugu_clientes_id:null), ['class' => 'form-control select_single_no_trigger', 'data-live-search' => 'true', "id" => "input_mra_g_iugu_clientes_id", "disabled"=>$disabled]) !!}
                                <span class="input-group-btn">
                                    <button class="btn btn-primary" type="button" {{ ($disabled?'disabled="disabled"':'') }} onclick="javascript:$('#input_mra_g_iugu_clientes_id').trigger('change');"><i class="glyphicon glyphicon-refresh"></i></button>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div size="12" class="inputbox col-md-12">
                    <div class="form-group">
                        {!! Form::label('','Formas de Pagamento') !!} <span style="color: #ff0500;">*</span>
                        <div class="l_checkbox_radio">
                            <label>{!! Form::checkbox('fp_todos', 1, ($acao=='edit'?$MRAGIuguFaturas->fp_todos:null), ['class' => '' , "id" => "input_fp_todos", "disabled"=>$disabled]) !!} Todos</label>
                            <label>{!! Form::checkbox('fp_cartao_credito', 1, ($acao=='edit'?$MRAGIuguFaturas->fp_cartao_credito:null), ['class' => '' , "id" => "input_fp_cartao_credito", "disabled"=>$disabled]) !!} Cartão de Crédito</label>
                            <label>{!! Form::checkbox('fp_boleto', 1, ($acao=='edit'?$MRAGIuguFaturas->fp_boleto:null), ['class' => '' , "id" => "input_fp_boleto", "disabled"=>$disabled]) !!} Boleto</label>
                            <label>{!! Form::checkbox('fp_pix', 1, ($acao=='edit'?$MRAGIuguFaturas->fp_pix:null), ['class' => '' , "id" => "input_fp_pix", "disabled"=>$disabled]) !!} Pix</label>
                        </div>
                    </div>
                </div>
                @if($acao=='edit')
                    @if(!empty($MRAGIuguFaturas->iugu_secure_url))
                        <div size="12" class="inputbox col-md-12">
                            <div class="form-group">
                                {!! Form::label('','Link de Pagamento') !!}<br/>
                                <a href="{{$MRAGIuguFaturas->iugu_secure_url}}" target="_blank">{{$MRAGIuguFaturas->iugu_secure_url}}</a>
                            </div>
                        </div>
                    @endif
                    @if(!empty($MRAGIuguFaturas->iugu_pix_qrcode))
                        <div size="12" class="inputbox col-md-12">
                            <div class="row">
                                <div size="3" class="inputbox col-md-3">
                                    <div class="form-group">
                                        {!! Form::label('','QR Code de Pagamento') !!}<br/>
                                        <a href="{{$MRAGIuguFaturas->iugu_pix_qrcode}}" target="_blank"><img src="{{$MRAGIuguFaturas->iugu_pix_qrcode}}" width="150" /></a>
                                    </div>
                                </div>
                                <div size="9" class="inputbox col-md-9">
                                    <div class="form-group">
                                        {!! Form::label('','QR Code - Chave') !!}<br/>
                                        {!! Form::textarea('iugu_pix_qrcode_text', $MRAGIuguFaturas->iugu_pix_qrcode_text, ['class' => 'form-control', "rows"=>4, "id" => "input_iugu_pix_qrcode_text", "disabled"=>true]) !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                @endif
                <div id="box_faturas_itens_grid" class="col-md-12">
                    <div class="row">
                        <div size="12" class="inputbox col-md-12">
                            <h2 class="page-header" style="font-size:20px;">
                                <i class="glyphicon glyphicon-record"></i> Itens
                            </h2>
                        </div>
                        <div class="GridFaturasItens_grid Grid_grid" count="0">
                            @php
                                function GridFaturasItens($acao,$MRAGIuguFaturas,$Item, $disabled) {
                            @endphp
                            <div class="divdefault item">
                                <div class="col-md-12" style="margin-bottom: 10px;">
                                    <div class="row">
                                        {!! Form::hidden('mra_g_iugu_faturas_i_id[]', ($acao=='edit'?($Item?$Item->id:null):null), []) !!}
                                        <div size="1" class="inputbox col-md-1">
                                            {!! Form::label('','Item') !!}
                                            {!! Form::text('item_posicao[]', null, ['autocomplete' =>'off', 'class' => 'form-control item_posicao', 'placeholder'=>'_', 'disabled'=>true]) !!}
                                        </div>
                                        <div size="2" class="inputbox col-md-2">
                                            <div class="form-group">
                                                {!! Form::label('','Quantidade') !!} <span style="color: #ff0500;">*</span>
                                                {!! Form::number('item_quantidade[]', ($acao=='edit'?($Item?$Item->quantidade:null):null), ['class' => 'form-control item_quantidade', 'placeholder'=>'0', "disabled"=>$disabled]) !!}
                                            </div>
                                        </div>
                                        <div size="3" class="inputbox col-md-3">
                                            <div class="form-group">
                                                {!! Form::label('','Valor') !!} <span style="color: #ff0500;">*</span>
                                                {!! Form::text('item_valor[]', ($acao=='edit'?($Item?\App\Helper\Helper::H_Decimal_DB_ptBR($Item->valor):null):null), ['class' => 'form-control money_v2 item_valor', 'placeholder' => '0,00', "disabled"=>$disabled]) !!}
                                            </div>
                                        </div>
                                        <div size="6" class="inputbox col-md-6">
                                            <div class="form-group">
                                                {!! Form::label('','Descrição') !!} <span style="color: #ff0500;">*</span>
                                                {!! Form::text('item_descricao[]', ($acao=='edit'?($Item?$Item->descricao:null):null), ['class' => 'form-control item_descricao', 'placeholder'=>'', 'maxlength'=>500, "disabled"=>$disabled]) !!}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @if($acao=='add')
                                <i class="glyphicon glyphicon-trash text-danger grid_remove_v2" data="GridFaturasItens_grid"></i>
                                @endif
                            </div>
                            @php
                                }
                            @endphp
                            @php
                                $MRAGridFaturasItens_old = [];
                                if(old('mra_g_iugu_faturas_i_id')){
                                    foreach(old('mra_g_iugu_faturas_i_id') as $K => $old) {
                                        $old_stdClass                   = new stdClass();
                                        $old_stdClass->k                = $K;
                                        $old_stdClass->id               = old('mra_g_iugu_faturas_i_id')[$K];
                                        $old_stdClass->quantidade       = old('item_quantidade')[$K];
                                        $old_stdClass->valor            = (!empty(old('item_valor')[$K])?\App\Helper\Helper::H_Decimal_ptBR_DB(old('item_valor')[$K]):null);
                                        $old_stdClass->descricao        = old('item_descricao')[$K];
                                        $MRAGridFaturasItens_old[]        = $old_stdClass;
                                    }
                                }
                            @endphp
                            @if(count($MRAGridFaturasItens_old))
                                @foreach($MRAGridFaturasItens_old as $K => $Item)
                                    {{ GridFaturasItens($acao,null,$Item,$disabled) }}
                                @endforeach
                            @elseif($MRAGIuguFaturas and $MRAGIuguFaturas->MRAGIuguFaturasItens and count($MRAGIuguFaturas->MRAGIuguFaturasItens))
                                @foreach($MRAGIuguFaturas->MRAGIuguFaturasItens as $K => $Item)
                                    @php
                                        $Item->k = $K;
                                    @endphp
                                    {{ GridFaturasItens($acao,$MRAGIuguFaturas,$Item,$disabled) }}
                                @endforeach
                            @else
                                {{ GridFaturasItens($acao,null,null,$disabled) }}
                            @endif
                        </div>
                        @if($acao=='add')
                        <div class="col-md-12 text-right">
                            <i class="glyphicon glyphicon-plus multiple_add_v2" data="GridFaturasItens_grid"></i>
                        </div>
                        @endif
                    </div>
                    <div class="row">
                        <div size="3" class="col-md-3">
                            <div class="form-group">
                                {!! Form::label('','Valor Total') !!}
                                {!! Form::text('valor_total', null, ['class' => 'form-control money_v2', 'placeholder'=>'0,00', 'id'=>'input_valor_total', 'disabled'=>true]) !!}
                            </div>
                        </div>
                    </div>
                </div>
                <div size="12" class="inputbox col-md-12">
                    <h2 class="page-header" style="font-size:20px;">
                        <i class="glyphicon glyphicon-th-large"></i> Cliente
                    </h2>
                </div>
                <div size="12" class="inputbox col-md-12">
                    <div class="row">
                        {{--@if($acao=='edit')
                            <div size="4" class="inputbox col-md-4">
                                <div class="form-group">
                                    {!! Form::label('','ID Iugu Cliente',['class'=>'text-warning']) !!}
                                    {!! Form::text('iugu_customer_id', ($acao=='edit'?$MRAGIuguFaturas->iugu_customer_id:null), ['class' => 'form-control', "id" => "input_iugu_customer_id", "disabled"=>true]) !!}
                                </div>
                            </div>
                        @endif--}}
                        <div size="2" class="inputbox col-md-2">
                            <div class="form-group">
                                {!! Form::label('','Tipo de Pessoa') !!} <span style="color: #ff0500;">*</span>
                                {!! Form::select('tipo', \App\Http\Controllers\MRA\MRAListas::Get_options_tipo_pessoa(), ($acao=='edit'?$MRAGIuguFaturas->tipo:null), ['class' => 'form-control cad_cliente select_single_no_trigger' , "id" => "input_tipo", "disabled"=>$disabled]) !!}
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div id="box_cnpj" size="4" class="inputbox col-md-4" style="display:none;">
                            <div class="form-group">
                                {!! Form::label('','CNPJ') !!} <span style="color: #ff0500;">*</span>
                                <div class="input-group mb_15p">
                                    {!! Form::text('cnpj', ($acao=='edit'?$MRAGIuguFaturas->cnpj:null), ['class' => 'form-control cad_cliente cnpj_v2', "placeholder"=>"__.___.___/____-__", "id" => "input_cnpj", "maxlength"=>50, "disabled"=>$disabled]) !!}
                                    <span class="input-group-btn">
                                        <button class="btn btn-primary" type="button" {{ ($disabled?'disabled="disabled"':'') }} onclick="javascript:$('#input_cnpj').trigger('change');"><i class="glyphicon glyphicon-refresh"></i></button>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div id="box_cpf" size="4" class="inputbox col-md-4"  style="display:none;">
                            <div class="form-group">
                                {!! Form::label('','CPF') !!} <span style="color: #ff0500;">*</span>
                                {!! Form::text('cpf', ($acao=='edit'?$MRAGIuguFaturas->cpf:null), ['class' => 'form-control cad_cliente cpf', "placeholder"=>"___.___.___-__", "id" => "input_cpf", "maxlength"=>50, "disabled"=>$disabled]) !!}
                            </div>
                        </div>
                        <div id="box_inscricao_estadual" size="4" class="inputbox col-md-4"  style="display:none;">
                            <div class="form-group">
                                {!! Form::label('','Inscrição Estadual (IE)') !!}
                                {!! Form::text('inscricao_estadual', ($acao=='edit'?$MRAGIuguFaturas->inscricao_estadual:null), ['class' => 'form-control cad_cliente', "id" => "input_inscricao_estadual", "maxlength"=>50, "disabled"=>$disabled]) !!}
                            </div>
                        </div>
                        <div id="box_inscricao_municipal" size="4" class="inputbox col-md-4"  style="display:none;">
                            <div class="form-group">
                                {!! Form::label('','Inscrição Municipal (IE)') !!}
                                {!! Form::text('inscricao_municipal', ($acao=='edit'?$MRAGIuguFaturas->inscricao_municipal:null), ['class' => 'form-control cad_cliente', "id" => "input_inscricao_municipal", "maxlength"=>50, "disabled"=>$disabled]) !!}
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div size="4" class="inputbox col-md-4">
                            <div class="form-group">
                                {!! Form::label('','Nome') !!} <span style="color: #ff0500;">*</span>
                                {!! Form::text('nome', ($acao=='edit'?$MRAGIuguFaturas->nome:null), ['class' => 'form-control cad_cliente' , "id" => "input_nome", "maxlength"=>100, "disabled"=>$disabled]) !!}
                            </div>
                        </div>
                        <div size="4" class="inputbox col-md-4">
                            <div class="form-group">
                                {!! Form::label('','Telefone') !!}
                                <div class="input-group">
                                    <div class="input-group-addon"><i class="fa fa-phone"></i></div>
                                    {!! Form::text('cont_telefone', ($acao=='edit'?$MRAGIuguFaturas->cont_telefone:null), ['class' => 'form-control cad_cliente telefone',"placeholder"=>"(__) ____-____", "id" => "input_cont_telefone", "maxlength"=>200, "disabled"=>$disabled]) !!}
                                </div>
                            </div>
                        </div>
                        <div size="4" class="inputbox col-md-4">
                            <div class="form-group">
                                {!! Form::label('','E-mail') !!} <span style="color: #ff0500;">*</span>
                                <div class="input-group">
                                    <div class="input-group-addon"><i class="fa fa-envelope"></i></div>
                                    {!! Form::text('cont_email', ($acao=='edit'?$MRAGIuguFaturas->cont_email:null), ['class' => 'form-control cad_cliente' , "id" => "input_cont_email", "maxlength"=>200, "disabled"=>$disabled]) !!}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div size="12" class="inputbox col-md-12">
                    <h2 class="page-header" style="font-size:20px;">
                        <i class="glyphicon glyphicon-th-large"></i> Endereço
                    </h2>
                </div>
                <div size="12" class="inputbox col-md-12">
                    <div class="row">
                        <div size="2" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','CEP') !!}
                                <div class="input-group mb_15p">
                                    {!! Form::text('end_cep', ($acao=='edit'?$MRAGIuguFaturas->end_cep:null), ['class' => 'form-control cad_cliente cep_v2', "placeholder"=>"_____-___", "id" => "input_end_cep", "maxlength"=>50, "disabled"=>$disabled]) !!}
                                    <span class="input-group-btn">
                                        <button class="btn btn-primary" type="button" {{ ($disabled?'disabled="disabled"':'') }} onclick="javascript:$('#input_end_cep').trigger('change');"><i class="glyphicon glyphicon-refresh"></i></button>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','Logradouro / Rua') !!}
                                {!! Form::text('end_rua', ($acao=='edit'?$MRAGIuguFaturas->end_rua:null), ['class' => 'form-control cad_cliente' , "id" => "input_end_rua", "maxlength"=>200, "disabled"=>$disabled]) !!}
                            </div>
                        </div>
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','Número') !!}
                                {!! Form::text('end_numero', ($acao=='edit'?$MRAGIuguFaturas->end_numero:null), ['class' => 'form-control cad_cliente' , "id" => "input_end_numero", "maxlength"=>200, "disabled"=>$disabled]) !!}
                            </div>
                        </div>
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','Bairro') !!}
                                {!! Form::text('end_bairro', ($acao=='edit'?$MRAGIuguFaturas->end_bairro:null), ['class' => 'form-control cad_cliente' , "id" => "input_end_bairro", "maxlength"=>200, "disabled"=>$disabled]) !!}
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div size="5" class="inputbox col-md-4">
                            <div class="form-group">
                                {!! Form::label('','Complemento') !!}
                                {!! Form::text('end_complemento', ($acao=='edit'?$MRAGIuguFaturas->end_complemento:null), ['class' => 'form-control cad_cliente' , "id" => "input_end_complemento", "maxlength"=>200, "disabled"=>$disabled]) !!}
                            </div>
                        </div>
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','Estado') !!}
                                {!! Form::select('end_estado', \App\Http\Controllers\MRA\MRAListas::Get_options_estados(), ($acao=='edit'?$MRAGIuguFaturas->end_estado:null), ['class' => 'form-control cad_cliente select_single_no_trigger', 'data-live-search' => 'true', "id" => "input_end_estado", "disabled"=>$disabled]) !!}
                            </div>
                        </div>
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','Cidade') !!}
                                {!! Form::text('end_cidade', ($acao=='edit'?$MRAGIuguFaturas->end_cidade:null), ['class' => 'form-control cad_cliente' , "id" => "input_end_cidade", "maxlength"=>50, "disabled"=>$disabled]) !!}
                            </div>
                        </div>
                        <div size="2" class="inputbox col-md-2">
                            <div class="form-group">
                                {!! Form::label('','País') !!}
                                {!! Form::select('end_pais', \App\Http\Controllers\MRA\MRAListas::Get_options_paises(), (($acao=='edit' and !is_null($MRAGIuguFaturas->end_pais))?$MRAGIuguFaturas->end_pais:1058), ['class' => 'form-control cad_cliente select_single_no_trigger', 'data-live-search' => 'true', "dropdown-menu-right"=>"", "id" => "input_end_pais", "disabled"=>$disabled]) !!}
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
                        {{--@if($acao == 'edit' and App\Models\Permissions::permissaoUsuario(\Auth::user(), "$controller@update"))
                            <button type="submit" class="btn btn-default right form-group-btn-edit-salvar" style="float:right;"><span class="glyphicon glyphicon-ok"></span> Salvar</button>
                        @endif--}}
                        @if($acao == 'edit' and App\Models\Permissions::permissaoUsuario(\Auth::user(), "$controller@fatura_paga_externamente"))
                            <button type="submit" name="fatura_paga_externamente" value="1" class="btn btn-success right form-group-btn-edit-fatura-paga-externamente" onclick="javascript: return (confirm('A Fatura sererá considerada Paga Externamente! Tem certeza?')?true:false);" style="float:right; margin: 0px;"><span class="glyphicon glyphicon-ok"></span>&nbsp; Fatura Paga</button>
                        @endif
                        @if($acao == 'edit' and App\Models\Permissions::permissaoUsuario(\Auth::user(), "$controller@enviar_email") and !in_array($MRAGIuguFaturas->iugu_status,['partially_paid','externally_paid','paid']))
                            <button type="submit" name="enviar_email" value="1" class="btn btn-info right form-group-btn-edit-enviar-email" onclick="javascript: return (confirm('Um E-mail será enviado ao cliente relacionado a Fatura! Tem certeza?')?true:false);" style="float:right; margin: 0px; margin-right:15px;"><span class="glyphicon glyphicon-send"></span>&nbsp; Enviar E-mail</button>
                        @endif
                        {{--@if($acao == 'edit' and App\Models\Permissions::permissaoUsuario(\Auth::user(), "$controller@suspender") and !$MRAGIuguFaturas->iugu_suspended)
                            <button type="submit" name="suspender_assinatura" value="1" class="btn btn-warning right form-group-btn-edit-suspender" style="float:right; margin: 0px; margin-right:15px;"><span class="glyphicon glyphicon-alert"></span>&nbsp; Suspender</button>
                        @endif
                        @if($acao == 'edit' and App\Models\Permissions::permissaoUsuario(\Auth::user(), "$controller@ativar") and $MRAGIuguFaturas->iugu_suspended)
                            <button type="submit" name="ativar_assinatura" value="1" class="btn btn-success right form-group-btn-edit-ativar" style="float:right; margin: 0px; margin-right:15px;"><span class="glyphicon glyphicon-ok"></span>&nbsp; Ativar</button>
                        @endif--}}
                        @if($acao == 'add' and App\Models\Permissions::permissaoUsuario(\Auth::user(), "$controller@store"))
                            <button type="submit" class="btn btn-default right form-group-btn-add-cadastrar"><span class="glyphicon glyphicon-plus"></span> Cadastrar</button>
                        @endif
                        @if($acao == 'edit' and App\Models\Permissions::permissaoUsuario(\Auth::user(), "$controller@destroy"))
                            <button type="submit" name="destroy_forcado" value="1" class="btn btn-danger right form-group-btn-edit-destroy-forcado" onclick="javascript: return (confirm('A Assinatura será excluída dentro do sistema! Tem certeza?')?true:false);" style="float:right; margin: 0px; margin-right:15px;"><i class="glyphicon glyphicon-alert"></i>&nbsp; Forçar Exclusão</button>
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
            // :: Tipo de Pessoa
            $("#input_tipo").on("change",function(){
                switch($("#input_tipo").val()) {
                    case 'F':
                        $("#box_cnpj").hide();
                        $("#box_cpf").show();
                        $("#box_inscricao_estadual").hide();
                        $("#box_inscricao_municipal").hide();
                        break;
                    case 'J':
                        $("#box_cnpj").show();
                        $("#box_cpf").hide();
                        $("#box_inscricao_estadual").show();
                        $("#box_inscricao_municipal").show();
                        break;
                    case 'E':
                        $("#box_cnpj").hide();
                        $("#box_cpf").hide();
                        $("#box_inscricao_estadual").hide();
                        $("#box_inscricao_municipal").show();
                        break;
                    default:
                        $("#box_cnpj").hide();
                        $("#box_cpf").hide();
                        $("#box_inscricao_estadual").hide();
                        $("#box_inscricao_municipal").hide();
                        break;
                }
            }).trigger('change');

            // :: CNPJ
            $("#input_cnpj").on('change', async function(){
                let _this = $(this);
                if(_this.attr('af') != undefined && _this.attr('af')=='true'){ return false; }
                _this.attr('af','true');
                setTimeout(function(){ _this.attr('af','false'); },1000);  // Fix*
                let cnpj = await RA.consulta.cnpj(_this.val());
                if(cnpj!=undefined){
                    $("#input_nome").val((cnpj.nome!=undefined)?cnpj.nome.toUpperCase():'');
                    $("#input_cont_email").val((cnpj.email!=undefined)?cnpj.email:'');
                    $("#input_cont_telefone").val((cnpj.telefone!=undefined)?cnpj.telefone:'');
                    $("#input_end_cep").val((cnpj.cep!=undefined?cnpj.cep:'')).trigger('change');
                    $("#input_end_numero").val((cnpj.numero!=undefined?cnpj.numero:''));
                }
            });

            // :: CEP
            $("#input_end_cep").on('change', async function(){
                let _this = $(this);
                if(_this.attr('af') != undefined && _this.attr('af')=='true'){ return false; }
                _this.attr('af','true');
                setTimeout(function(){ _this.attr('af','false'); },1000); // Fix*
                let cep = await RA.consulta.cep(_this.val());
                if(cep!=undefined){
                    $("#input_end_rua").val((cep.logradouro!=undefined?cep.logradouro.toUpperCase():''));
                    $("#input_end_bairro").val((cep.bairro!=undefined?cep.bairro.toUpperCase():''));
                    $("#input_end_complemento").val((cep.complemento!=undefined?cep.complemento.toUpperCase():''));
                    $("#input_end_estado").val((cep.uf!=undefined?cep.uf.toUpperCase():'')).trigger('change');
                    $("#input_end_cidade").val((cep.localidade!=undefined?cep.localidade.toUpperCase():''));
                    $("#input_end_pais").val(1058).trigger('change');
                }
            });

            // :: Cliente
            $("#input_mra_g_iugu_clientes_id").on('change', async function(){
                let _this = $(this);
                if(_this.val() == ''){
                    $(".cad_cliente").prop('disabled',false);
                    $("#input_tipo,#input_end_pais").trigger('change');
                }else {
                    $(".cad_cliente").prop('disabled',true);
                    $("#input_tipo,#input_end_pais").trigger('change');
                }
                if((_this.attr('af') != undefined && _this.attr('af')=='true') || _this.val() == ''){ return false; }
                _this.attr('af','true');
                setTimeout(function(){ _this.attr('af','false'); },1000); // Fix*
                try {
                    $.get('{{URL('mra_g_iugu/mra_g_iugu_clientes')}}/'+_this.val()+'/ajax', function(d,s){
                        if(d!=undefined && Object.keys(d).length){
                            $("#input_iugu_customer_id").val(d.iugu_customer_id);
                            $("#input_nome").val(d.nome);
                            $("#input_tipo").val(d.tipo).trigger('change');
                            $("#input_cnpj").val(d.cnpj);
                            $("#input_cpf").val(d.cpf);
                            $("#input_inscricao_estadual").val(d.inscricao_estadual);
                            $("#input_inscricao_municipal").val(d.inscricao_municipal);
                            $("#input_cont_telefone").val(d.cont_telefone);
                            $("#input_cont_email").val(d.cont_email);
                            $("#input_end_cep").val(d.end_cep);
                            $("#input_end_rua").val(d.end_rua);
                            $("#input_end_numero").val(d.end_numero);
                            $("#input_end_bairro").val(d.end_bairro);
                            $("#input_end_complemento").val(d.end_complemento);
                            $("#input_end_estado").val(d.end_estado).trigger('change');
                            $("#input_end_cidade").val(d.end_cidade);
                            $("#input_end_pais").val(d.end_pais);
                        }
                    });
                }catch(e){
                    //console.log('Erro: '+e);
                }
            });

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

            // :: Itens
            function qt_itens(){
                let e_itens_pos      = $("#box_faturas_itens_grid input.item_posicao");
                let e_itens_pos_qt   = e_itens_pos.length;
                e_itens_pos.each(function(i,e){
                    $(e).val((i+1));
                });
            }

            function calculo_qt_valor_total(){
                let qt_itens                = $("#box_faturas_itens_grid input.item_posicao").length;
                if(qt_itens=="" || qt_itens <= 0){ return false; }
                let e_itens_item            = $("#box_faturas_itens_grid .item");
                let e_itens_item_qt         = e_itens_item.length;

                let valor_total             = 0;
                e_parcelas_item             = $("#box_faturas_itens_grid .item");
                e_parcelas_item.each(function(i,e){
                    let item_quantidade     = $(e).find('.item_quantidade').val();
                    let item_valor          = $(e).find('.item_valor').val();
                    item_valor              = RA.format.Decimal_ptBR_DB(item_valor);
                    valor_total            += (item_quantidade * item_valor);
                });
                // - #
                $("#input_valor_total").val(RA.format.Decimal_DB_ptBR(String(valor_total)));
            }

            function keyup_change_item_qt_valor(E){
                $(E).on("keyup change", function(){
                    calculo_qt_valor_total();
                });
            }
            keyup_change_item_qt_valor($("#box_faturas_itens_grid .item_quantidade,#box_faturas_itens_grid .item_valor"));

            function click_item_grid_remove_v2(E){
                E.on('click', function(){
                    setTimeout(function(){
                        qt_itens();
                        calculo_qt_valor_total();
                    },500);
                });
            }
            click_item_grid_remove_v2($("#box_faturas_itens_grid .grid_remove_v2"));

            $("#box_faturas_itens_grid .multiple_add_v2").on('click', function(){
                let e_item_pos_i = $("#box_faturas_itens_grid .item").length - 1;
                let last_item = $("#box_faturas_itens_grid .item").eq(e_item_pos_i);
                qt_itens();
                click_item_grid_remove_v2(last_item.find('.grid_remove_v2'));
                keyup_change_item_qt_valor(last_item.find('.item_quantidade,.item_valor'));
            });

            qt_itens();
            calculo_qt_valor_total();

            // :: Consultar Fatura
            $(".consultar-fatura").on("click", function() {
                let modulo     = ($(this).attr('modulo')!=undefined && $(this).attr('modulo') != ""?$(this).attr('modulo'):undefined);
                let modulo_id  = ($(this).attr('modulo_id')!=undefined && $(this).attr('modulo_id') != ""?$(this).attr('modulo_id'):undefined);

                if(modulo == undefined || modulo_id == undefined){ return false; }

                let form    = document.createElement('form');
                form.method = 'GET';
                form.action = base+'/'+modulo+'/consultar_fatura/'+modulo_id
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
