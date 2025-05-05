@php

    $isPublic = 0;

@endphp

@extends($isPublic ? 'layouts.app-public' : 'layouts.app')

@section('content')

<style type="text/css" media="print">

    @page
    {
        size: auto;
        margin: 0mm;
    }

    body
    {
        margin: 0px;
       }

       .noprint,
    {
        display: none !important;
    }

    @media print {
        a[href]::after {
            content: none !important;
        }
        .listar { clear:both; }
        .content { padding-top: 0px; padding-bottom: 0px; }
    }

</style>

@section('style')

    <style type="text/css">

    </style>

@endsection

<section class="content-header CCF Cartão de Crédito_show">
    <h1>CCF Cartão de Crédito </h1>
    <!--@if(!$isPublic)
    <ol class="breadcrumb">
        <li><a href="{{ URL('/') }}">Home</a></li>
        <li><a href="{{ URL('/') }}/ccf_cartao_de_credito">CCF Cartão de Crédito</a></li>
        <li class="active">#{{$ccf_cartao_de_credito->id}}</li>
    </ol>
    @endif-->
</section>

<section class="content CCF Cartão de Crédito_show">

    <div class="box">

        <div class="box-logo">
            <img src="https://lxxxtxx.xxxxrxxxapps.com/images/logo-lxxxtxx.jpg" style="height: 100px; margin-left: 10px;">
        </div>

        @php

            if(env('FILESYSTEM_DRIVER') == 's3')
            {
                $fileurlbase = env('URLS3') . '/' . env('FILEKEY') . '/';
            }
            else
            {
                $fileurlbase = env('APP_URL') . '/';
            }

        @endphp

        <div class="box-body">

            <div size="12" class="inputbox col-md-12">
    <h2 class="page-header" style="font-size:20px;">
        <i class="glyphicon glyphicon-th-large"></i> BANDEIRA DO CARTÃO:
    </h2>
</div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','Visa:') !!}
                    {!! Form::checkbox('visa_', null, $ccf_cartao_de_credito->visa_, ['class' => '' , "id" => "input_visa_",'disabled' => 'disabled',]) !!}
                </div>
            </div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','Mastercard:') !!}
                    {!! Form::checkbox('mastercard_', null, $ccf_cartao_de_credito->mastercard_, ['class' => '' , "id" => "input_mastercard_",'disabled' => 'disabled',]) !!}
                </div>
            </div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','Diners:') !!}
                    {!! Form::checkbox('diners_', null, $ccf_cartao_de_credito->diners_, ['class' => '' , "id" => "input_diners_",'disabled' => 'disabled',]) !!}
                </div>
            </div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','Outros') !!}
                    {!! Form::checkbox('outros', null, $ccf_cartao_de_credito->outros, ['class' => '' , "id" => "input_outros",'disabled' => 'disabled',]) !!}
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
                    {!! Form::text('numero_do_cartao_', $ccf_cartao_de_credito->numero_do_cartao_, ['class' => 'form-control' , "id" => "input_numero_do_cartao_",'disabled' => 'disabled',]) !!}
                </div>
            </div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','Código de Verificação:') !!}
                    {!! Form::number('codigo_de_verificacao_', $ccf_cartao_de_credito->codigo_de_verificacao_, ['class' => 'form-control' , "id" => "input_codigo_de_verificacao_",'disabled' => 'disabled',]) !!}
                </div>
            </div>

            <div size="3" class="inputbox col-md-3">
                <div class="form-group">
                    {!! Form::label('','Data de Validade Cartão:') !!}
                    <div class='input-group'>
                        <div class='input-group-addon'>
                            <i class='fa fa-calendar'></i>
                        </div>
                    {!! Form::text('data_de_validade_cartao_', $ccf_cartao_de_credito->data_de_validade_cartao_, ['class' => 'form-control data' , "id" => "input_data_de_validade_cartao_",'disabled' => 'disabled',]) !!}
                    </div>
                </div>
            </div>

            <div size="12" class="inputbox col-md-12">
                <div class="form-group">
                    {!! Form::label('','Nome do Titular:') !!}
                    {!! Form::text('nome_do_titular_', $ccf_cartao_de_credito->nome_do_titular_, ['class' => 'form-control' , "id" => "input_nome_do_titular_", "style" => "text-transform: uppercase;", "oninput" => "this.value = this.value.toUpperCase();",'disabled' => 'disabled',]) !!}
                </div>
            </div>

            <div size="3" class="inputbox col-md-3">
                <div class="form-group">
                    {!! Form::label('','CPF:') !!}
                    {!! Form::text('cpf_', $ccf_cartao_de_credito->cpf_, ['class' => 'form-control' , "id" => "input_cpf_",'disabled' => 'disabled',]) !!}
                </div>
            </div>

            <div size="3" class="inputbox col-md-3">
                <div class="form-group">
                    {!! Form::label('','Nro do Telefone do Responsável:') !!}
                    <div class='input-group'>
                        <div class='input-group-addon'>
                            <i class='fa fa-phone'></i>
                        </div>
                    {!! Form::text('nro_do_telefone_do_responsavel_', $ccf_cartao_de_credito->nro_do_telefone_do_responsavel_, ['class' => 'form-control telefone' , "id" => "input_nro_do_telefone_do_responsavel_",'disabled' => 'disabled',]) !!}
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
                    {!! Form::text('valor_total_', $ccf_cartao_de_credito->valor_total_, ['class' => 'form-control money' , "id" => "input_valor_total_",'disabled' => 'disabled',]) !!}
                </div>
            </div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','Nro de Parcelas:') !!}
                    {!! Form::number('nro_de_parcelas_', $ccf_cartao_de_credito->nro_de_parcelas_, ['class' => 'form-control' , "id" => "input_nro_de_parcelas_",'disabled' => 'disabled',]) !!}
                </div>
            </div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','Valor da Parcela:') !!}
                    {!! Form::text('valor_da_parcela_', $ccf_cartao_de_credito->valor_da_parcela_, ['class' => 'form-control' , "id" => "input_valor_da_parcela_",'disabled' => 'disabled',]) !!}
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
                    {!! Form::text('esta_autorizacao_destina_se_ao_pagamento_em_nome_de_', $ccf_cartao_de_credito->esta_autorizacao_destina_se_ao_pagamento_em_nome_de_, ['class' => 'form-control' , "id" => "input_esta_autorizacao_destina_se_ao_pagamento_em_nome_de_",'disabled' => 'disabled',]) !!}
                </div>
            </div>

            <div size="3" class="inputbox col-md-3">
                <div class="form-group">
                    {!! Form::label('','Nro. Telefone Passageiro:') !!}
                    <div class='input-group'>
                        <div class='input-group-addon'>
                            <i class='fa fa-phone'></i>
                        </div>
                    {!! Form::text('nro_telefone_passageiro_', $ccf_cartao_de_credito->nro_telefone_passageiro_, ['class' => 'form-control telefone' , "id" => "input_nro_telefone_passageiro_",'disabled' => 'disabled',]) !!}
                    </div>
                </div>
            </div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','Cia Aérea:') !!}
                    {!! Form::text('cia_aerea_', $ccf_cartao_de_credito->cia_aerea_, ['class' => 'form-control' , "id" => "input_cia_aerea_",'disabled' => 'disabled',]) !!}
                </div>
            </div>

            <div size="3" class="inputbox col-md-3">
                <div class="form-group">
                    {!! Form::label('','Data de Embarque:') !!}
                    <div class='input-group'>
                        <div class='input-group-addon'>
                            <i class='fa fa-calendar'></i>
                        </div>
                    {!! Form::text('data_de_embarque_', $ccf_cartao_de_credito->data_de_embarque_, ['class' => 'form-control data' , "id" => "input_data_de_embarque_",'disabled' => 'disabled',]) !!}
                    </div>
                </div>
            </div>

            <div size="6" class="inputbox col-md-6">
                <div class="form-group">
                    {!! Form::label('','Destino:') !!}
                    {!! Form::text('destino_', $ccf_cartao_de_credito->destino_, ['class' => 'form-control' , "id" => "input_destino_",'disabled' => 'disabled',]) !!}
                </div>
            </div>

            <div class="col-md-12" style="margin-top: 20px; margin-bottom: 20px;">
                <div class="form-group no-print form-group-btn-show">
                    @if(!$isPublic)
                        <a href="{{ URL::previous() }}" class="btn btn-default form-group-btn-add-voltar" style="float: left;"><i class="glyphicon glyphicon-backward"></i> Voltar</a>
                    @endif
                    <a href="javascript:void(0);" onclick="printScreen();" class="btn btn-default form-group-btn-show-imprimir" style="float: right;"><i class="glyphicon glyphicon-print"></i> Imprimir</a>
                </div>
            </div>

        </div>

    </div>

</section>

<script type="text/javascript">

    function printScreen(){

        $('.inputbox').each(function(){
            var size = $(this).attr('size');
            var percent = (( size * 100 ) / 12);
            $(this).css({"width": percent + "%", "float": "left"});
        });

        $('.grid_remove').remove();

        window.print();

    }

</script>

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
