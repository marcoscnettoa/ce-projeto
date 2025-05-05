@php

    $isPublic = 0;

    $controller = get_class(\Request::route()->getController());

@endphp

@extends($isPublic ? 'layouts.app-public' : 'layouts.app')

@section('content')

@section('style')

    <style type="text/css">

    </style>

@endsection

<section class="content-header CCF Cartão de Crédito_add">
    <h1>CCF Cartão de Crédito</h1>
    <!--@if(!$isPublic)
    <ol class="breadcrumb">
        <li><a href="{{ URL('/') }}">Home</a></li>
        <li><a href="{{ URL('/') }}/ccf_cartao_de_credito">CCF Cartão de Crédito</a></li>
        <li class="active">CCF Cartão de Crédito</li>
    </ol>
    @endif-->
</section>

<section class="content CCF Cartão de Crédito_add">

<div class="box">

    {!! Form::open(['url' => "ccf_cartao_de_credito", 'method' => 'post', 'novalidate'=> true, 'enctype' => 'multipart/form-data', 'accept-charset' => 'utf-8', 'id' => 'form_add_ccf_cartao_de_credito']) !!}

        <div class="box-body" id="div_ccf_cartao_de_credito">

<div size="12" class="inputbox col-md-12">
    <h2 class="page-header" style="font-size:20px;">
        <i class="glyphicon glyphicon-th-large"></i> BANDEIRA DO CARTÃO:
    </h2>
</div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','Visa:') !!}
                    {!! Form::checkbox('visa_', null, null, ['class' => '' , "id" => "input_visa_"]) !!}
                </div>
            </div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','Mastercard:') !!}
                    {!! Form::checkbox('mastercard_', null, null, ['class' => '' , "id" => "input_mastercard_"]) !!}
                </div>
            </div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','Diners:') !!}
                    {!! Form::checkbox('diners_', null, null, ['class' => '' , "id" => "input_diners_"]) !!}
                </div>
            </div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','Outros') !!}
                    {!! Form::checkbox('outros', null, null, ['class' => '' , "id" => "input_outros"]) !!}
                </div>
            </div>

<div size="12" class="inputbox col-md-12">
    <h2 class="page-header" style="font-size:20px;">
        <i class="glyphicon glyphicon-th-large"></i> DADOS DO CARTÃO:
    </h2>
</div>

            <div size="3" class="inputbox col-md-3">
                <div class="form-group">
                    {!! Form::label('','Número do Cartão:') !!}
                    {!! Form::text('numero_do_cartao_', null, ['class' => 'form-control' , "id" => "input_numero_do_cartao_"]) !!}
                </div>
            </div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','Código de Verificação:') !!}
                    {!! Form::number('codigo_de_verificacao_', null, ['class' => 'form-control' , "id" => "input_codigo_de_verificacao_"]) !!}
                </div>
            </div>

            <div size="3" class="inputbox col-md-3">
                <div class="form-group">
                    {!! Form::label('','Data de Validade Cartão:') !!}
                    <div class='input-group'>
                        <div class='input-group-addon'>
                            <i class='fa fa-calendar'></i>
                        </div>
                    {!! Form::text('data_de_validade_cartao_', null, ['class' => 'form-control data' , "id" => "input_data_de_validade_cartao_"]) !!}
                    </div>
                </div>
            </div>

            <div size="12" class="inputbox col-md-12">
                <div class="form-group">
                    {!! Form::label('','Nome do Titular:') !!}
                    {!! Form::text('nome_do_titular_', null, ['class' => 'form-control' , "id" => "input_nome_do_titular_", "style" => "text-transform: uppercase;", "oninput" => "this.value = this.value.toUpperCase();"]) !!}
                </div>
            </div>

            <div size="3" class="inputbox col-md-3">
                <div class="form-group">
                    {!! Form::label('','CPF:') !!}
                    {!! Form::text('cpf_', null, ['class' => 'form-control' , "id" => "input_cpf_"]) !!}
                </div>
            </div>

            <div size="3" class="inputbox col-md-3">
                <div class="form-group">
                    {!! Form::label('','Nro do Telefone do Responsável:') !!}
                    <div class='input-group'>
                        <div class='input-group-addon'>
                            <i class='fa fa-phone'></i>
                        </div>
                    {!! Form::text('nro_do_telefone_do_responsavel_', null, ['class' => 'form-control telefone' , "id" => "input_nro_do_telefone_do_responsavel_"]) !!}
                    </div>
                </div>
            </div>

<div size="12" class="inputbox col-md-12">
    <h2 class="page-header" style="font-size:20px;">
        <i class="glyphicon glyphicon-th-large"></i> VALORES E CONDIÇÕES:
    </h2>
</div>

            <div size="3" class="inputbox col-md-3">
                <div class="form-group">
                    {!! Form::label('','Valor Total:') !!}
                    {!! Form::text('valor_total_', null, ['class' => 'form-control money' , "id" => "input_valor_total_"]) !!}
                </div>
            </div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','Nro de Parcelas:') !!}
                    {!! Form::number('nro_de_parcelas_', null, ['class' => 'form-control' , "id" => "input_nro_de_parcelas_"]) !!}
                </div>
            </div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','Valor da Parcela:') !!}
                    {!! Form::text('valor_da_parcela_', null, ['class' => 'form-control' , "id" => "input_valor_da_parcela_"]) !!}
                </div>
            </div>

<div size="12" class="inputbox col-md-12">
    <h2 class="page-header" style="font-size:20px;">
        <i class="glyphicon glyphicon-th-large"></i> IMPORTANTE:
    </h2>
</div>

            <div size="4" class="inputbox col-md-4">
                <div class="form-group">
                    {!! Form::label('','Esta autorização destina-se ao pagamento em nome de:') !!}
                    {!! Form::text('esta_autorizacao_destina_se_ao_pagamento_em_nome_de_', null, ['class' => 'form-control' , "id" => "input_esta_autorizacao_destina_se_ao_pagamento_em_nome_de_"]) !!}
                </div>
            </div>

            <div size="3" class="inputbox col-md-3">
                <div class="form-group">
                    {!! Form::label('','Nro. Telefone Passageiro:') !!}
                    <div class='input-group'>
                        <div class='input-group-addon'>
                            <i class='fa fa-phone'></i>
                        </div>
                    {!! Form::text('nro_telefone_passageiro_', null, ['class' => 'form-control telefone' , "id" => "input_nro_telefone_passageiro_"]) !!}
                    </div>
                </div>
            </div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','Cia Aérea:') !!}
                    {!! Form::text('cia_aerea_', null, ['class' => 'form-control' , "id" => "input_cia_aerea_"]) !!}
                </div>
            </div>

            <div size="3" class="inputbox col-md-3">
                <div class="form-group">
                    {!! Form::label('','Data de Embarque:') !!}
                    <div class='input-group'>
                        <div class='input-group-addon'>
                            <i class='fa fa-calendar'></i>
                        </div>
                    {!! Form::text('data_de_embarque_', null, ['class' => 'form-control data' , "id" => "input_data_de_embarque_"]) !!}
                    </div>
                </div>
            </div>

            <div size="6" class="inputbox col-md-6">
                <div class="form-group">
                    {!! Form::label('','Destino:') !!}
                    {!! Form::text('destino_', null, ['class' => 'form-control' , "id" => "input_destino_"]) !!}
                </div>
            </div>

            @if(0)

                @if(App\Models\Permissions::permissaoModerador(\Auth::user()))
                    <div class="col-md-12">
                        <div class="form-group">

                            <label for="">Para quem essa informação ficará disponível? Selecione um usuário. </label>

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

            @endif

            <div class="col-md-12" style="margin-top: 20px;">

                <div class="form-group form-group-btn-add">

                    @if(!$isPublic)
                        <a href="{{ URL::previous() }}" class="btn btn-default form-group-btn-add-voltar"><i class="glyphicon glyphicon-backward"></i> Voltar</a>
                    @endif

                    @if(App\Models\Permissions::permissaoUsuario(\Auth::user(), "$controller@store") OR $isPublic)

                        <button type="submit" class="btn btn-default right form-group-btn-add-cadastrar">
                            <span class="glyphicon glyphicon-plus"></span> Cadastrar
                        </button>

                    @endif

                </div>

            </div>

        </div>

    {!! Form::close() !!}

</div>

</section>

@section('script')

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
    </script>

@endsection

@endsection
