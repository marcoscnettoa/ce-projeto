@php
    $isPublic = 0;
    $enable_kanban = 0;
    $controller = get_class(\Request::route()->getController());
@endphp

@extends($isPublic ? 'layouts.app-public' : 'layouts.app')

@section('content')

@section('style')
    <style type="text/css">
    </style>
@endsection

<section class="content-header Faturamento_edit">
    <h1>Faturamento </h1>
    <!--@if(!$isPublic)
    <ol class="breadcrumb">
        <li><a href="{{ URL('/') }}">Home</a></li>
        <li><a href="{{ URL('/') }}/faturamento">Faturamento</a></li>
        <li class="active">#{{$faturamento->id}}</li>
    </ol>
    @endif-->
</section>

{!! Form::open(['url' => "faturamento/$faturamento->id", 'method' => 'put', 'novalidate'=> true, 'enctype' => 'multipart/form-data', 'accept-charset' => 'utf-8', 'id' => 'form_edit_faturamento']) !!}
    @if(\Request::get('modal'))
        {!! Form::hidden('modal-close', 1) !!}
    @endif
    {!! Form::hidden('id', $faturamento->id) !!}
    <section class="content Faturamento_edit">
    <div class="box" style="margin-bottom:0;">
        @php
            if(env('FILESYSTEM_DRIVER') == 's3')
            {
                $fileurlbase = env('URLS3') . '/' . env('FILEKEY') . '/';
            }else
            {
                $fileurlbase = env('APP_URL') . '/';
            }
        @endphp
        {{--@if(\App\Models\Permissions::permissaoUsuario(\Auth::user(), "$controller@destroy"))
            <div class="row" style="position: absolute; right: 0; padding: 5px;">
                <div class="col-md-12">
                    <form id="form-destroy" method="POST" action="{{ route('faturamento.destroy', $faturamento->id) }}" accept-charset="UTF-8">
                        {!! csrf_field() !!}
                        {!! method_field('DELETE') !!}
                        <button type="submit" style="margin-left: 10px; margin-top: -1px;" onclick="return confirm('Tem certeza que quer deletar?')" class="btn btn-danger glyphicon glyphicon-trash"></button>
                    </form>
                </div>
            </div>
        @endif--}}
        <div class="box-body" id="div_faturamento">
            <div size="3" class="inputbox col-md-3">
                <div class="form-group">
                    {!! Form::label('','N° da Fatura') !!}
                    {!! Form::text('n_da_fatura', $faturamento->n_da_fatura, ['class' => 'form-control' , "id" => "input_n_da_fatura",'disabled' => 'disabled',]) !!}
                </div>
            </div>
            <div size="4" class="inputbox col-md-4">
                <div class="form-group">
                    {!! Form::label('','Data da Fatura') !!}
                    {!! Form::text('data_da_fatura', $faturamento->data_da_fatura, ['autocomplete' =>'off', 'class' => 'form-control componenteData' , "id" => "input_data_da_fatura"]) !!}
                </div>
            </div>
            <div size="4" class="inputbox col-md-4">
                <div class="form-group">
                    {!! Form::label('','Data de Vencimento') !!}
                    {!! Form::text('data_de_vencimento', $faturamento->data_de_vencimento, ['autocomplete' =>'off', 'class' => 'form-control componenteData' , "id" => "input_data_de_vencimento"]) !!}
                </div>
            </div>
            <div size="12" class="inputbox col-md-12">
                <h2 class="page-header" style="font-size:20px;">
                    <i class="glyphicon glyphicon-th-large"></i> Lançamento
                </h2>
            </div>
            <div size="4" class="inputbox col-md-4">
                <div class="form-group">
                    {!! Form::label('','Cliente') !!}
                    {!! Form::select('cliente', $clientes_nome_do_cliente, $faturamento->cliente, ['class' => 'form-control  select_relationship ', 'data-sub' => '', 'data-live-search' => 'true',  'data-none-selected-text' => true, 'controller' => 'clientes' , "id" => "input_cliente"]) !!}
                </div>
            </div>
            {{--<div size="4" class="inputbox col-md-4">
                <div class="form-group">
                    {!! Form::label('','Data Inicial') !!}
                    {!! Form::text('data_inicial', $faturamento->data_inicial, ['autocomplete' =>'off', 'class' => 'form-control componenteData' , "id" => "input_data_inicial"]) !!}
                </div>
            </div>
            <div size="4" class="inputbox col-md-4">
                <div class="form-group">
                    {!! Form::label('','Data Final') !!}
                    {!! Form::text('data_final', $faturamento->data_final, ['autocomplete' =>'off', 'class' => 'form-control componenteData' , "id" => "input_data_final"]) !!}
                </div>
            </div>--}}
            {{--<div size="12" class="inputbox col-md-12">
                <div class="form-group">
                    {!! Form::label('','Template') !!}
                    {!! Form::select('template', $templates_nome_do_template, $faturamento->template, ['class' => 'form-control  select_relationship ', 'data-sub' => '', 'data-live-search' => 'true',  'data-none-selected-text' => true, 'controller' => 'templates' , "id" => "input_template"]) !!}
                </div>
            </div>--}}
            {{-- template = 3 ( Faturamento - Venda ) --}}
            {!! Form::hidden('template', 3) !!}
            @if(0)
                @if(\App\Models\Permissions::permissaoModerador(\Auth::user()))
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="">Para quem essa informação ficará disponível? Selecione um usuário. </label>
                            @php
                                $parserList = array();
                                $userlist = \App\Models\User::get()->toArray();
                                array_unshift($userlist, array('id' => '',  'name' => ''));
                                array_unshift($userlist, array('id' => 0,  'name' => 'Disponível para todos'));
                                foreach($userlist as $u)
                                {
                                    $parserList[$u['id']] = $u['name'];
                                }
                            @endphp
                            {!! Form::select('r_auth', $parserList, $faturamento->r_auth, ['class' => 'form-control']) !!}
                        </div>
                    </div>
                @endif
            @endif
        </div>
    </div>
    </section>
    <section class="content" style="min-height: auto;">
        <div class="box" style="margin-bottom:0;">
            <div class="box-body" style="margin-top: 0;">
                <div class="row">
                    <div class="col-md-12">
                        <div class="table-responsive">
                            <table id="table_vendas" class="table-st1 preloader_show table-striped table-bordered stripe " style="width:100%;">
                                <thead>
                                    <tr>
                                        <th class="text-center" style="white-space:nowrap;">#</th>
                                        <th class="text-left" style="white-space:nowrap;">Data</th>
                                        <th class="text-left" style="white-space:nowrap;">N&deg; Venda</th>
                                        <th class="text-left" style="white-space:nowrap;">Localizador</th>
                                        <th class="text-left" style="white-space:nowrap;">Cliente</th>
                                        <th class="text-left" style="white-space:nowrap;">Passageiro(s)</th>
                                        <th class="text-left" style="white-space:nowrap;">Trecho</th>
                                        <th class="text-left" style="white-space:nowrap;">Valor Total</th>
                                        <th class="text-left" style="white-space:nowrap;">Dt. Embarque</th>
                                        <th class="text-left" style="white-space:nowrap;">Dt. Retorno</th>
                                        <th class="text-left" style="white-space:nowrap;">Form. Pagamento</th>
                                        <th class="text-left" style="white-space:nowrap;">Comissão</th>
                                        <th class="text-left" style="white-space:nowrap;">N&deg; Fatura</th>
                                        <th class="text-left" style="white-space:nowrap;">Faturado</th>
                                        <th class="text-left" style="white-space:nowrap;">Fornecedor</th>
                                    </tr>
                                    <tr class="preloader"><td colspan="15" class="va_top text-center"><img src="{{URL('assets/images/loader-barra.gif')}}" title="Carregando" /></td></tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="content" style="min-height: auto;">
        <div class="box" style="margin-bottom:0;">
            <div class="box-body" style="margin-top: 0;">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group form-group-btn-edit">
                            @if(!$isPublic)
                                <a href="{{ URL::previous() }}" class="btn btn-default form-group-btn-add-voltar" style="float: left;"><i class="glyphicon glyphicon-backward"></i> Voltar</a>
                            @endif
                            @if(\App\Models\Permissions::permissaoUsuario(\Auth::user(), "$controller@update") OR $isPublic)
                                <button type="submit" class="btn btn-default right form-group-btn-edit-salvar">
                                    <span class="glyphicon glyphicon-ok"></span> Salvar
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    {!! Form::close() !!}
    @section('script')
        <script type="text/javascript">
            $("#input_cliente").on("change", function(){
                load_vendas_ClienteDTInicialDTFinal();
            });

            /*$("#input_data_inicial").on("changeDate", function(){
                load_vendas_ClienteDTInicialDTFinal();
            });

            $("#input_data_final").on("changeDate", function(){
                load_vendas_ClienteDTInicialDTFinal();
            });*/

            var faturamento_id = {{(request()->route('faturamento')?request()->route('faturamento'):'null')}};
            function load_vendas_ClienteDTInicialDTFinal(){

                if(
                    $("#input_cliente").val() == '' /*|| $("#input_data_inicial").val() == '' || $("#input_data_final").val() == '' ||
                    $("#input_data_inicial").val().indexOf('/') == -1  || $("#input_data_final").val().indexOf('/') == -1*/
                ){
                    return false;
                }

                let data = {
                    '_token'        : $("meta[name=\'csrf-token\']").attr('content'),
                    'cliente'       : $("#input_cliente").val(),
                    //'data_inicial'  : $("#input_data_inicial").val(),
                    //'data_final'    : $("#input_data_final").val()
                }

                $("#table_vendas tbody").html('');
                $("#table_vendas").addClass('preloader_show');

                setTimeout(function(){
                    $.ajax({
                        type: 'POST',
                        dataType: 'json',
                        url: "{{ URL('vendas/faturamento/inicial/final') }}",
                        data: data,
                        async: false,
                        success: function (d) {
                            let table_html = "";
                            if(d.data.length){
                                $(d.data).each(function(i,e){
                                    let cliente                 = (e.cliente != null && e.cliente.nome_do_cliente != undefined ? e.cliente.nome_do_cliente : '---');
                                    let fornecedor              = (e.fornecedor != null && e.fornecedor.fornecedor != undefined ? e.fornecedor.fornecedor : '---');
                                    let trechos                 = (e.trechos != null && e.trechos.trechos != undefined ? e.trechos.trechos : '---');
                                    //let produto               = (e.produto.produto!=undefined?e.produto.produto:'---');
                                    //let servico               = (e.servico.servico!=undefined?e.servico.servico:'---');
                                    let html_passageiros        = '';
                                    let html_pagamentos         = '';
                                    let checked_venda_id        = '';
                                    let disabled_venda_id       = '';
                                    let tr_bg_destaque          = 'ati-hover';
                                    if(faturamento_id != null){
                                        if(e.faturamento_id !== null && e.faturamento_id == faturamento_id){
                                            checked_venda_id        = 'checked';
                                            tr_bg_destaque         += ' bg-success';
                                        }
                                        if(e.faturamento_id !== null && e.faturamento_id != faturamento_id){
                                            disabled_venda_id       = 'disabled';
                                            tr_bg_destaque          = 'disabled';
                                        }
                                    }else {
                                        if(e.faturamento_id != null || e.foi_faturado){
                                            disabled_venda_id       = 'disabled';
                                            tr_bg_destaque          = 'disabled';
                                        }
                                    }

                                    // :: Passageiros
                                    if(e.vendas_grid_passageiros != null){
                                        $(e.vendas_grid_passageiros).each(function(i2,e2){
                                            if(e2.passageiros != null && e2.passageiros.id != 0){
                                                let p_nome          = (e2.passageiros != null && e2.passageiros.nome != undefined ? e2.passageiros.nome : '---');
                                                html_passageiros   += `<span class="d_block badge badge-neutro"><strong>*</strong> `+(i2+1)+`: `+p_nome+`</span>`;
                                            }
                                        });
                                    }

                                    // :: Pagamentos
                                    if(e.vendas_grid_pagamentos != null){
                                        $(e.vendas_grid_pagamentos).each(function(i2,e2){
                                            if(e2.forma_de_pagamento != null && e2.forma_de_pagamento.id != 0){
                                                let p_f_pagamento  = (e2.forma_de_pagamento != null && e2.forma_de_pagamento.forma_de_pagamento != undefined ? e2.forma_de_pagamento.forma_de_pagamento : '---');
                                                html_pagamentos   += `<span class="d_block badge badge-neutro text-center">`+p_f_pagamento+`</span>`;
                                            }
                                        });
                                    }

                                    table_html             += `<tr class="`+tr_bg_destaque+`">
                                                                 <td class="va_top text-center" style="white-space:nowrap;"><input `+checked_venda_id+` `+disabled_venda_id+` class="cursor_pointer" type="checkbox" name="venda_id[]" value="`+e.id+`"></td>
                                                                 <td class="va_top text-left input_data" style="white-space:nowrap;">`+e.data_f+`</td>
                                                                 <td class="va_top text-left input_numero_venda" style="white-space:nowrap;">`+e.id_f+`</td>
                                                                 <td class="va_top text-left input_localizador" style="white-space:nowrap;">`+e.localizador+`</td>
                                                                 <td class="va_top text-left input_cliente" style="white-space:nowrap;">`+cliente+`</td>
                                                                 <td class="va_top text-left input_passageiros" style="white-space:nowrap;">`+(html_passageiros!=''?html_passageiros:'---')+`</td>
                                                                 <td class="va_top text-left input_trechos" style="white-space:nowrap;">`+trechos+`</td>
                                                                 <td class="va_top text-left input_valor_total" style="white-space:nowrap;">`+e.valor_total+`</td>
                                                                 <td class="va_top text-left input_dt_embarque" style="white-space:nowrap;">`+e.data_embarque_f+`</td>
                                                                 <td class="va_top text-left input_dt_retorno" style="white-space:nowrap;">`+e.data_retorno_f+`</td>
                                                                 <td class="va_top text-left input_forma_pagamento" style="white-space:nowrap;">`+html_pagamentos+`</td>
                                                                 <td class="va_top text-left input_commissao" style="white-space:nowrap;">`+e.comissao+`</td>
                                                                 <td class="va_top text-left input_numero_fatura" style="white-space:nowrap;">`+((e.faturamento!=null && e.faturamento!='')?e.faturamento:'---')+`</td>
                                                                 <td class="va_top text-left input_faturado" style="white-space:nowrap;">`+(e.foi_faturado?'FATURADO':'---')+`</td>
                                                                 <td class="va_top text-left input_fornecedor" style="white-space:nowrap;">`+fornecedor+`</td>
                                                               </tr>`;
                                });
                            }else {
                                table_html                  = '<tr><td colspan="15" class="text-center">Nenhum resultado encontrado!</td></tr>';
                            }
                            console.log(d);
                            //console.log(table_html);
                            $("#table_vendas").removeClass('preloader_show');
                            $("#table_vendas tbody").html(table_html);
                        },
                        error: function (jqXHR, textStatus, errorThrown) {
                            if(jqXHR.responseJSON){
                                console.log(jqXHR.responseJSON);
                            }else {
                                console.log(errorThrown);
                            }
                        }
                    });
                },1000);
            }
            $("#input_cliente").trigger("change");
        </script>
    @endsection
@endsection
