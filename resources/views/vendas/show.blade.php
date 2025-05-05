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

<section class="content-header Vendas_show">
    <h1>Vendas </h1>
    <!--@if(!$isPublic)
    <ol class="breadcrumb">
        <li><a href="{{ URL('/') }}">Home</a></li>
        <li><a href="{{ URL('/') }}/vendas">Vendas</a></li>
        <li class="active">#{{$vendas->id}}</li>
    </ol>
    @endif-->
</section>

<section class="content Vendas_show">

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

                        <div size="3" class="inputbox col-md-3">
                <div class="form-group">
                    {!! Form::label('','Tipo de Venda') !!}
                    <div class='input-group'>
                        <div class='input-group-addon'>
                            <i class='glyphicon glyphicon-star'></i>
                        </div>
                    {!! Form::select('tipo_de_venda', \App\Models\Vendas::Get_options_tipo_de_venda(), $vendas->tipo_de_venda, ['class' => 'form-control select_single' , "id" => "input_tipo_de_venda",'disabled' => 'disabled',]) !!}
                    </div>
                </div>
            </div>

            <div size="3" class="inputbox col-md-3">
                <div class="form-group">
                    {!! Form::label('','N° da Fatura') !!}
                    <div class='input-group'>
                        <div class='input-group-addon'>
                            <i class='glyphicon glyphicon-pencil'></i>
                        </div>
                    {!! Form::text('faturamento', $vendas->faturamento, ['class' => 'form-control' , "id" => "input_faturamento",'disabled' => 'disabled',]) !!}
                    </div>
                </div>
            </div>

            <div size="3" class="inputbox col-md-3">
                <div class="form-group">
                    {!! Form::label('','Foi Faturado?') !!}
                    <div class='input-group'>
                        <div class='input-group-addon'>
                            <i class='glyphicon glyphicon-ok-circle'></i>
                        </div>
                    {!! Form::select('foi_faturado', \App\Models\Vendas::Get_options_foi_faturado(), $vendas->foi_faturado, ['class' => 'form-control select_single' , "id" => "input_foi_faturado",'disabled' => 'disabled',]) !!}
                    </div>
                </div>
            </div>

<div size="12" class="inputbox col-md-12">
    <h2 class="page-header" style="font-size:20px;">
        <i class="glyphicon glyphicon-th-large"></i> INFORMAÇÕES DA VENDA:
    </h2>
</div>

            <div size="3" class="inputbox col-md-3">
                <div class="form-group">
                    {!! Form::label('','Número') !!}
                    {!! Form::number('id', $vendas->id, ['class' => 'form-control' , "id" => "input_id",'disabled' => 'disabled',]) !!}
                </div>
            </div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','Data') !!}
                    <div class='input-group'>
                        <div class='input-group-addon'>
                            <i class='glyphicon glyphicon-calendar'></i>
                        </div>
                    {!! Form::text('data', $vendas->data, ['autocomplete' =>'off', 'class' => 'form-control componenteData' , "id" => "input_data",'disabled' => 'disabled',]) !!}
                    </div>
                </div>
            </div>

            <div size="4" class="inputbox col-md-4">
                <div class="form-group">
                    {!! Form::label('','Cliente') !!}
                    {!! Form::select('cliente', $clientes_nome_do_cliente, $vendas->cliente, ['class' => 'form-control  select_relationship ', 'data-sub' => '', 'data-live-search' => 'true',  'data-none-selected-text' => true, 'controller' => 'clientes' , "id" => "input_cliente",'disabled' => 'disabled',]) !!}
                </div>
            </div>

            <div size="3" class="inputbox col-md-3">
                <div class="form-group">
                    {!! Form::label('','Vendedor') !!}
                    {!! Form::select('vendedor', $vendedores_nome_do_vendedor, $vendas->vendedor, ['class' => 'form-control  select_relationship ', 'data-sub' => '', 'data-live-search' => 'true',  'data-none-selected-text' => true, 'controller' => 'vendedores' , "id" => "input_vendedor",'disabled' => 'disabled',]) !!}
                </div>
            </div>

            <div size="3" class="inputbox col-md-3">
                <div class="form-group">
                    {!! Form::label('','Localizador') !!}
                    <div class='input-group'>
                        <div class='input-group-addon'>
                            <i class='glyphicon glyphicon-search'></i>
                        </div>
                    {!! Form::text('localizador', $vendas->localizador, ['class' => 'form-control' , "id" => "input_localizador", "style" => "text-transform: uppercase;", "oninput" => "this.value = this.value.toUpperCase();",'disabled' => 'disabled',]) !!}
                    </div>
                </div>
            </div>

            <div size="3" class="inputbox col-md-3">
                <div class="form-group">
                    {!! Form::label('','Produto') !!}
                    {!! Form::select('produto', $produtos_produto, $vendas->produto, ['class' => 'form-control  select_relationship ', 'data-sub' => '', 'data-live-search' => 'true',  'data-none-selected-text' => true, 'controller' => 'produtos' , "id" => "input_produto",'disabled' => 'disabled',]) !!}
                </div>
            </div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','Serviço') !!}
                    {!! Form::select('servico', $servicos_servico, $vendas->servico, ['class' => 'form-control  select_relationship ', 'data-sub' => '', 'data-live-search' => 'true',  'data-none-selected-text' => true, 'controller' => 'servicos' , "id" => "input_servico",'disabled' => 'disabled',]) !!}
                </div>
            </div>

            <div size="3" class="inputbox col-md-3">
                <div class="form-group">
                    {!! Form::label('','Fornecedor') !!}
                    {!! Form::select('fornecedor', $fornecedores_fornecedor, $vendas->fornecedor, ['class' => 'form-control  select_relationship ', 'data-sub' => '', 'data-live-search' => 'true',  'data-none-selected-text' => true, 'controller' => 'fornecedores' , "id" => "input_fornecedor",'disabled' => 'disabled',]) !!}
                </div>
            </div>

            <div size="3" class="inputbox col-md-3">
                <div class="form-group">
                    {!! Form::label('','Companhia') !!}
                    {!! Form::select('companhia', $companhias_companhia, $vendas->companhia, ['class' => 'form-control  select_relationship ', 'data-sub' => '', 'data-live-search' => 'true',  'data-none-selected-text' => true, 'controller' => 'companhias' , "id" => "input_companhia",'disabled' => 'disabled',]) !!}
                </div>
            </div>

            <div size="4" class="inputbox col-md-4">
                <div class="form-group">
                    {!! Form::label('','Trecho') !!}
                    {!! Form::select('trecho', $trechos_trechos, $vendas->trecho, ['class' => 'form-control  select_relationship ', 'data-sub' => '', 'data-live-search' => 'true',  'data-none-selected-text' => true, 'controller' => 'trechos' , "id" => "input_trecho",'disabled' => 'disabled',]) !!}
                </div>
            </div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','Dt. Embarque') !!}
                    {!! Form::text('data_embarque', $vendas->data_embarque, ['autocomplete' =>'off', 'class' => 'form-control componenteData' , "id" => "input_data_embarque",'disabled' => 'disabled',]) !!}
                </div>
            </div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','Dt. Retorno') !!}
                    {!! Form::text('data_retorno', $vendas->data_retorno, ['autocomplete' =>'off', 'class' => 'form-control componenteData' , "id" => "input_data_retorno",'disabled' => 'disabled',]) !!}
                </div>
            </div>

<div class="row">
@if(!empty($vendas->VendasGridPassageiros))
    @foreach($vendas->VendasGridPassageiros as $key => $value)
        <div class="listar">
            <div class="col-md-11" style="margin-bottom: 10px;">
            <div size="3" class="inputbox col-md-3">
                <div class="form-group">
                    {!! Form::label('','Passageiros') !!}
                    {!! Form::select('grid[VendasGridPassageiros][passageiros][]', $passageiro_nome, $value->passageiros, ['class' => 'form-control  select_relationship ', 'data-sub' => '', 'data-live-search' => 'true',  'data-none-selected-text' => true, 'controller' => 'passageiro' , "id" => "input_passageiros", "style" => "text-transform: uppercase;", "oninput" => "this.value = this.value.toUpperCase();",'disabled' => 'disabled',]) !!}
                </div>
            </div>

            </div>
            <div class="col-md-1">
                <i class="glyphicon glyphicon-trash grid_remove" data="GridPassageiros_grid" style="margin-top: 30px;"></i>
            </div>
        </div>
    @endforeach
@endif
</div>
            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','Tarifa') !!}
                    <div class='input-group'>
                        <div class='input-group-addon'>
                            <i class='glyphicon glyphicon-usd'></i>
                        </div>
                    {!! Form::text('valor_tarifa', $vendas->valor_tarifa, ['class' => 'form-control money' , "id" => "input_valor_tarifa",'disabled' => 'disabled',]) !!}
                    </div>
                </div>
            </div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','Tx. Embarque') !!}
                    <div class='input-group'>
                        <div class='input-group-addon'>
                            <i class='glyphicon glyphicon-usd'></i>
                        </div>
                    {!! Form::text('tx_embarque', $vendas->tx_embarque, ['class' => 'form-control money' , "id" => "input_tx_embarque",'disabled' => 'disabled',]) !!}
                    </div>
                </div>
            </div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','Outras Taxas') !!}
                    <div class='input-group'>
                        <div class='input-group-addon'>
                            <i class='glyphicon glyphicon-usd'></i>
                        </div>
                    {!! Form::text('outras_taxas', $vendas->outras_taxas, ['class' => 'form-control money' , "id" => "input_outras_taxas",'disabled' => 'disabled',]) !!}
                    </div>
                </div>
            </div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','Desconto') !!}
                    <div class='input-group'>
                        <div class='input-group-addon'>
                            <i class='glyphicon glyphicon-usd'></i>
                        </div>
                    {!! Form::text('desconto', $vendas->desconto, ['class' => 'form-control money' , "id" => "input_desconto",'disabled' => 'disabled',]) !!}
                    </div>
                </div>
            </div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','Comissão (DU)') !!}
                    <div class='input-group'>
                        <div class='input-group-addon'>
                            <i class='glyphicon glyphicon-usd'></i>
                        </div>
                    {!! Form::text('comissao', $vendas->comissao, ['class' => 'form-control money' , "id" => "input_comissao",'disabled' => 'disabled',]) !!}
                    </div>
                </div>
            </div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','Valor Total') !!}
                    <div class='input-group'>
                        <div class='input-group-addon'>
                            <i class='glyphicon glyphicon-usd'></i>
                        </div>
                    {!! Form::text('valor_total', $vendas->valor_total, ['class' => 'form-control' , "id" => "input_valor_total",'disabled' => 'disabled',]) !!}
                    </div>
                </div>
            </div>

            <div size="12" class="inputbox col-md-12">
                <div class="form-group">
                    {!! Form::label('','OBSERVACÕES DA VENDA:') !!}
                    {!! Form::text('observacoes_da_venda_', $vendas->observacoes_da_venda_, ['class' => 'form-control' , "id" => "input_observacoes_da_venda_",'disabled' => 'disabled',]) !!}
                </div>
            </div>

<div size="12" class="inputbox col-md-12">
    <h2 class="page-header" style="font-size:20px;">
        <i class="glyphicon glyphicon-th-large"></i> FORMAS DE PAGAMENTO:
    </h2>
</div>

<div class="row">
@if(!empty($vendas->VendasGridPagamentos))
    @foreach($vendas->VendasGridPagamentos as $key => $value)
        <div class="listar">
            <div class="col-md-11" style="margin-bottom: 10px;">
            <div size="3" class="inputbox col-md-3">
                <div class="form-group">
                    {!! Form::label('','Forma de Pagamento') !!}
                    {!! Form::select('grid[VendasGridPagamentos][forma_de_pagamento][]', $formas_de_pagamentos_forma_de_pa, $value->forma_de_pagamento, ['class' => 'form-control  select_relationship ', 'data-sub' => '', 'data-live-search' => 'true',  'data-none-selected-text' => true, 'controller' => 'formas_de_pagamentos' , "id" => "input_forma_de_pagamento",'disabled' => 'disabled',]) !!}
                </div>
            </div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','Valor Pago') !!}
                    {!! Form::text('grid[VendasGridPagamentos][valor_pago][]', $value->valor_pago, ['class' => 'form-control money' , "id" => "input_valor_pago",'disabled' => 'disabled',]) !!}
                </div>
            </div>

            </div>
            <div class="col-md-1">
                <i class="glyphicon glyphicon-trash grid_remove" data="GridPagamentos_grid" style="margin-top: 30px;"></i>
            </div>
        </div>
    @endforeach
@endif
</div>
            {{--<div size="12" class="inputbox col-md-12">
                <h2 class="page-header" style="font-size:20px;">
                    <i class="glyphicon glyphicon-th-large"></i> PGTO AO FORNECEDOR
                </h2>
            </div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','Dt. Pgto:') !!}
                    {!! Form::text('dt_pgto_', $vendas->dt_pgto_, ['autocomplete' =>'off', 'class' => 'form-control componenteData' , "id" => "input_dt_pgto_",'disabled' => 'disabled',]) !!}
                </div>
            </div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','Nr. Documento:') !!}
                    {!! Form::text('nr_documento_', $vendas->nr_documento_, ['class' => 'form-control' , "id" => "input_nr_documento_",'disabled' => 'disabled',]) !!}
                </div>
            </div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','Valor:') !!}
                    {!! Form::text('valor_', $vendas->valor_, ['class' => 'form-control money' , "id" => "input_valor_",'disabled' => 'disabled',]) !!}
                </div>
            </div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','Acréscimo:') !!}
                    {!! Form::text('acrescimo_', $vendas->acrescimo_, ['class' => 'form-control money' , "id" => "input_acrescimo_",'disabled' => 'disabled',]) !!}
                </div>
            </div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','Desconto:') !!}
                    {!! Form::text('desconto_', $vendas->desconto_, ['class' => 'form-control money' , "id" => "input_desconto_",'disabled' => 'disabled',]) !!}
                </div>
            </div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','Valor Pago:') !!}
                    {!! Form::text('vlr_pago_', $vendas->vlr_pago_, ['class' => 'form-control' , "id" => "input_vlr_pago_",'disabled' => 'disabled',]) !!}
                </div>
            </div>

            <div size="12" class="inputbox col-md-12">
                <div class="form-group">
                    {!! Form::label('','OBSERVAÇÕES:') !!}
                    {!! Form::text('observacoes_', $vendas->observacoes_, ['class' => 'form-control' , "id" => "input_observacoes_",'disabled' => 'disabled',]) !!}
                </div>
            </div>--}}

            {{--<div size="12" class="inputbox col-md-12">
                <div class="form-group">
                    {!! Form::label('','Template') !!}
                    {!! Form::select('template', $templates_nome_do_template, $vendas->template, ['class' => 'form-control  select_relationship ', 'data-sub' => '', 'data-live-search' => 'true',  'data-none-selected-text' => true, 'controller' => 'templates' , "id" => "input_template",'disabled' => 'disabled',]) !!}
                </div>
            </div>--}}

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
        $("select#input_tipo_de_venda").closest(".form-group").addClass('inpsel-destaque-st1');
$(
	`#input_valor_tarifa,
	#input_tx_embarque,
	#input_outras_taxas,
	#input_desconto,
	#input_comissao`
)
.on('change keyup',function(){
	venda_calculo_total();
});
function venda_calculo_total(){
	console.log('foi');
	let valor_tarifa  = parseFloat(($("#input_valor_tarifa").val()!="")?$("#input_valor_tarifa").val():0);
	let tx_embarque   = parseFloat(($("#input_tx_embarque").val()!="")?$("#input_tx_embarque").val():0);
	let outras_taxas  = parseFloat(($("#input_outras_taxas").val()!="")?$("#input_outras_taxas").val():0);
	let desconto  	  = parseFloat(($("#input_desconto").val()!="")?$("#input_desconto").val():0);
	let comissao  	  = parseFloat(($("#input_comissao").val()!="")?$("#input_comissao").val():0);
	let valor_total   = ((valor_tarifa + tx_embarque + outras_taxas + comissao) - desconto);
	$("#input_valor_total").val(valor_total.toFixed(2));
}
    </script>

@endsection

@endsection
