<div class="box">

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

                    <div size="4" class="inputbox col-md-4">
                <div class="form-group">
                    {!! Form::label('','Empresa') !!}
                    {!! Form::select('empresa', $cadastro_de_empresas_nome_fantas, $contas_a_receber->empresa, ['class' => 'form-control  select_relationship ', 'data-sub' => '', 'data-live-search' => 'true',  'data-none-selected-text' => true, 'controller' => 'cadastro_de_empresas' , "id" => "input_empresa",'disabled' => 'disabled',"placeholder" => html_entity_decode("Nome da empresa"),]) !!}
                </div>
            </div>

            <div size="4" class="inputbox col-md-4">
                <div class="form-group">
                    {!! Form::label('','Cliente') !!}
                    {!! Form::select('cliente', $clientes_nome_do_cliente, $contas_a_receber->cliente, ['class' => 'form-control  select_relationship ', 'data-sub' => '', 'data-live-search' => 'true',  'data-none-selected-text' => true, 'controller' => 'clientes' , "id" => "input_cliente",'disabled' => 'disabled',"placeholder" => html_entity_decode("Nome do Cliente"),]) !!}
                </div>
            </div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','Tipo de documento') !!}
                    {!! Form::text('tipo_de_documento', $contas_a_receber->tipo_de_documento, ['class' => 'form-control' , "id" => "input_tipo_de_documento",'disabled' => 'disabled',]) !!}
                </div>
            </div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','N° do documento') !!}
                    {!! Form::text('n_do_documento', $contas_a_receber->n_do_documento, ['class' => 'form-control' , "id" => "input_n_do_documento",'disabled' => 'disabled',]) !!}
                </div>
            </div>

            <div size="4" class="inputbox col-md-4">
                <div class="form-group">
                    {!! Form::label('','Vendedor') !!}
                    {!! Form::select('vendedor', $vendedores_nome_do_vendedor, $contas_a_receber->vendedor, ['class' => 'form-control  select_relationship ', 'data-sub' => '', 'data-live-search' => 'true',  'data-none-selected-text' => true, 'controller' => 'vendedores' , "id" => "input_vendedor",'disabled' => 'disabled',"placeholder" => html_entity_decode("Nome do Vendedor"),]) !!}
                </div>
            </div>

            <div size="8" class="inputbox col-md-8">
                <div class="form-group">
                    {!! Form::label('','Descrição do Recebimento') !!}
                    {!! Form::text('descricao_do_recebimento', $contas_a_receber->descricao_do_recebimento, ['class' => 'form-control' , "id" => "input_descricao_do_recebimento",'disabled' => 'disabled',"placeholder" => html_entity_decode("Motivo do Recebimento"),]) !!}
                </div>
            </div>

            <div size="3" class="inputbox col-md-3">
                <div class="form-group">
                    {!! Form::label('','Data do Vencimento') !!}
                    <div class='input-group'>
                        <div class='input-group-addon'>
                            <i class='fa fa-calendar'></i>
                        </div>
                    {!! Form::text('data_do_vencimento', $contas_a_receber->data_do_vencimento, ['class' => 'form-control data' , "id" => "input_data_do_vencimento",'disabled' => 'disabled',"placeholder" => html_entity_decode("Vencimento"),]) !!}
                    </div>
                </div>
            </div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','Valor à Receber') !!}
                    {!! Form::text('valor_a_receber', $contas_a_receber->valor_a_receber, ['class' => 'form-control money' , "id" => "input_valor_a_receber",'disabled' => 'disabled',]) !!}
                </div>
            </div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','Tipo do valor') !!}
                    {!! Form::select('tipo_do_valor', \App\Models\ContasAReceber::Get_options_tipo_do_valor(), $contas_a_receber->tipo_do_valor, ['class' => 'form-control select_single' , "id" => "input_tipo_do_valor",'disabled' => 'disabled',]) !!}
                </div>
            </div>

<div size="12" class="inputbox col-md-12">
    <h2 class="page-header" style="font-size:20px;">
        <i class="glyphicon glyphicon-th-large"></i> Parcelas
    </h2>
</div>

            <div size="2" class="inputbox col-md-2" style="display: none;" hide-by="input_tipo_do_valor">
                <div class="form-group">
                    {!! Form::label('','N° de Parcelas') !!}
                    {!! Form::number('n_de_parcelas', $contas_a_receber->n_de_parcelas, ['class' => 'form-control' , "id" => "input_n_de_parcelas",'disabled' => 'disabled','conditional' => true, 'conditional_field' => 'tipo_do_valor', 'conditional_field_value' => 'Parcelas',]) !!}
                </div>
            </div>

            <div size="2" class="inputbox col-md-2" style="display: none;" hide-by="input_tipo_do_valor">
                <div class="form-group">
                    {!! Form::label('','Primeira Data') !!}
                    <div class='input-group'>
                        <div class='input-group-addon'>
                            <i class='fa fa-calendar'></i>
                        </div>
                    {!! Form::text('primeira_data', $contas_a_receber->primeira_data, ['class' => 'form-control data' , "id" => "input_primeira_data",'disabled' => 'disabled','conditional' => true, 'conditional_field' => 'tipo_do_valor', 'conditional_field_value' => 'Parcelas',]) !!}
                    </div>
                </div>
            </div>

<div size="12" class="inputbox col-md-12">
    <h2 class="page-header" style="font-size:20px;">
        <i class="glyphicon glyphicon-th-large"></i> Recepção
    </h2>
</div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','Data do Recebimento') !!}
                    <div class='input-group'>
                        <div class='input-group-addon'>
                            <i class='fa fa-calendar'></i>
                        </div>
                    {!! Form::text('data_do_recebimento', $contas_a_receber->data_do_recebimento, ['class' => 'form-control data' , "id" => "input_data_do_recebimento",'disabled' => 'disabled',"placeholder" => html_entity_decode("Envio da Cobrança"),]) !!}
                    </div>
                </div>
            </div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','Valor Recebido') !!}
                    {!! Form::text('valor_recebido', $contas_a_receber->valor_recebido, ['class' => 'form-control money' , "id" => "input_valor_recebido",'disabled' => 'disabled',]) !!}
                </div>
            </div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','Forma de pagamento') !!}
                    {!! Form::select('forma_de_pagamento', $formas_de_pagamentos_forma_de_pa, $contas_a_receber->forma_de_pagamento, ['class' => 'form-control  select_relationship ', 'data-sub' => '', 'data-live-search' => 'true',  'data-none-selected-text' => true, 'controller' => 'formas_de_pagamentos' , "id" => "input_forma_de_pagamento",'disabled' => 'disabled',]) !!}
                </div>
            </div>

            <div size="3" class="inputbox col-md-3">
                <div class="form-group">
                    {!! Form::label('','Status') !!}
                    {!! Form::select('status', \App\Models\ContasAReceber::Get_options_status(), $contas_a_receber->status, ['class' => 'form-control select_single' , "id" => "input_status",'disabled' => 'disabled',]) !!}
                </div>
            </div>

            <div size="3" class="inputbox col-md-3">
                <div class="form-group">
                    {!! Form::label('','Comprovante') !!}
@if($contas_a_receber->comprovante && pathinfo($contas_a_receber->comprovante, PATHINFO_EXTENSION))
        <ol style="margin:0px;padding:0px;">
            <a class="fancybox" rel="gallery1" target="_blank" href="{{$fileurlbase . "images"}}/{{$contas_a_receber->comprovante}}">
                <img src="{{in_array(explode(".", $contas_a_receber->comprovante)[1], array("mp4", "pdf", "doc", "docx", "rar", "zip", "txt", "7zip", "csv", "xls", "xlsx")) ? URL("/") . "/" . explode(".", $contas_a_receber->comprovante)[1] . "-icon.png" : $fileurlbase . "images/" . $contas_a_receber->comprovante}}" height="100">
            </a>
        </ol>
{!! Form::hidden("comprovante", $contas_a_receber->comprovante) !!}
@endif
                </div>
            </div>

    </div>

</div>