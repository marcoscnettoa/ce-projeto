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
                    {!! Form::select('referente_a', $cadastro_de_empresas_nome_fantas, $contas_a_pagar->referente_a, ['class' => 'form-control  select_relationship ', 'data-sub' => '', 'data-live-search' => 'true',  'data-none-selected-text' => true, 'controller' => 'cadastro_de_empresas' , "id" => "input_referente_a",'disabled' => 'disabled',"placeholder" => html_entity_decode("Nome da Empresa"),]) !!}
                </div>
            </div>

            <div size="4" class="inputbox col-md-4">
                <div class="form-group">
                    {!! Form::label('','Fornecedor') !!}
                    {!! Form::select('fornecedor', $fornecedores_fornecedor, $contas_a_pagar->fornecedor, ['class' => 'form-control  select_relationship ', 'data-sub' => '', 'data-live-search' => 'true',  'data-none-selected-text' => true, 'controller' => 'fornecedores' , "id" => "input_fornecedor",'disabled' => 'disabled',"placeholder" => html_entity_decode("Nome do Fornecedor"),]) !!}
                </div>
            </div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','Tipo de documento') !!}
                    {!! Form::text('tipo_de_documento', $contas_a_pagar->tipo_de_documento, ['class' => 'form-control' , "id" => "input_tipo_de_documento",'disabled' => 'disabled',]) !!}
                </div>
            </div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','N° do documento') !!}
                    {!! Form::text('n_do_documento', $contas_a_pagar->n_do_documento, ['class' => 'form-control' , "id" => "input_n_do_documento",'disabled' => 'disabled',]) !!}
                </div>
            </div>

            <div size="4" class="inputbox col-md-4">
                <div class="form-group">
                    {!! Form::label('','Portador') !!}
                    {!! Form::text('portador', $contas_a_pagar->portador, ['class' => 'form-control' , "id" => "input_portador",'disabled' => 'disabled',]) !!}
                </div>
            </div>

            <div size="4" class="inputbox col-md-4">
                <div class="form-group">
                    {!! Form::label('','Descrição do Pagamento') !!}
                    {!! Form::text('descricao_do_pagamento', $contas_a_pagar->descricao_do_pagamento, ['class' => 'form-control' , "id" => "input_descricao_do_pagamento",'disabled' => 'disabled',"placeholder" => html_entity_decode("Motivo do Pagamento"),]) !!}
                </div>
            </div>

            <div size="4" class="inputbox col-md-4">
                <div class="form-group">
                    {!! Form::label('','N° da ordem de compra') !!}
                    {!! Form::text('n_da_ordem_de_compra', $contas_a_pagar->n_da_ordem_de_compra, ['class' => 'form-control' , "id" => "input_n_da_ordem_de_compra",'disabled' => 'disabled',]) !!}
                </div>
            </div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','Data do Vencimento') !!}
                    <div class='input-group'>
                        <div class='input-group-addon'>
                            <i class='glyphicon glyphicon-calendar'></i>
                        </div>
                    {!! Form::text('data_do_vencimento', $contas_a_pagar->data_do_vencimento, ['class' => 'form-control data' , "id" => "input_data_do_vencimento",'disabled' => 'disabled',"placeholder" => html_entity_decode("Vencimento"),]) !!}
                    </div>
                </div>
            </div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','Valor à Pagar') !!}
                    <div class='input-group'>
                        <div class='input-group-addon'>
                            <i class='glyphicon glyphicon-usd'></i>
                        </div>
                    {!! Form::text('valor_a_pagar', $contas_a_pagar->valor_a_pagar, ['class' => 'form-control money' , "id" => "input_valor_a_pagar",'disabled' => 'disabled',]) !!}
                    </div>
                </div>
            </div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','Tipo do valor') !!}
                    {!! Form::select('tipo_do_valor', \App\Models\ContasAPagar::Get_options_tipo_do_valor(), $contas_a_pagar->tipo_do_valor, ['class' => 'form-control select_single' , "id" => "input_tipo_do_valor",'disabled' => 'disabled',]) !!}
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
                    <div class='input-group'>
                        <div class='input-group-addon'>
                            <i class='glyphicon glyphicon-sort-by-order'></i>
                        </div>
                    {!! Form::number('n_de_parcelas', $contas_a_pagar->n_de_parcelas, ['class' => 'form-control' , "id" => "input_n_de_parcelas",'disabled' => 'disabled','conditional' => true, 'conditional_field' => 'tipo_do_valor', 'conditional_field_value' => 'Parcelas',]) !!}
                    </div>
                </div>
            </div>

            <div size="2" class="inputbox col-md-2" style="display: none;" hide-by="input_tipo_do_valor">
                <div class="form-group">
                    {!! Form::label('','Primeira Data') !!}
                    <div class='input-group'>
                        <div class='input-group-addon'>
                            <i class='glyphicon glyphicon-calendar'></i>
                        </div>
                    {!! Form::text('primeira_data', $contas_a_pagar->primeira_data, ['class' => 'form-control data' , "id" => "input_primeira_data",'disabled' => 'disabled','conditional' => true, 'conditional_field' => 'tipo_do_valor', 'conditional_field_value' => 'Parcelas',]) !!}
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
                    {!! Form::label('','Data do Pagamento') !!}
                    <div class='input-group'>
                        <div class='input-group-addon'>
                            <i class='fa fa-calendar'></i>
                        </div>
                    {!! Form::text('data_do_pagamento', $contas_a_pagar->data_do_pagamento, ['class' => 'form-control data' , "id" => "input_data_do_pagamento",'disabled' => 'disabled',"placeholder" => html_entity_decode("Envio da Cobrança"),]) !!}
                    </div>
                </div>
            </div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','Valor Pago') !!}
                    <div class='input-group'>
                        <div class='input-group-addon'>
                            <i class='glyphicon glyphicon-usd'></i>
                        </div>
                    {!! Form::text('valor_pago', $contas_a_pagar->valor_pago, ['class' => 'form-control money' , "id" => "input_valor_pago",'disabled' => 'disabled',]) !!}
                    </div>
                </div>
            </div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','Forma de pagamento') !!}
                    <div class='input-group'>
                        <div class='input-group-addon'>
                            <i class='glyphicon glyphicon-briefcase'></i>
                        </div>
                    {!! Form::select('forma_de_pagamento', $formas_de_pagamentos_forma_de_pa, $contas_a_pagar->forma_de_pagamento, ['class' => 'form-control  select_relationship ', 'data-sub' => '', 'data-live-search' => 'true',  'data-none-selected-text' => true, 'controller' => 'formas_de_pagamentos' , "id" => "input_forma_de_pagamento",'disabled' => 'disabled',]) !!}
                    </div>
                </div>
            </div>

            <div size="3" class="inputbox col-md-3">
                <div class="form-group">
                    {!! Form::label('','Status') !!}
                    <div class='input-group'>
                        <div class='input-group-addon'>
                            <i class='glyphicon glyphicon-hourglass'></i>
                        </div>
                    {!! Form::select('status', \App\Models\ContasAPagar::Get_options_status(), $contas_a_pagar->status, ['class' => 'form-control select_single' , "id" => "input_status",'disabled' => 'disabled',]) !!}
                    </div>
                </div>
            </div>

            <div size="3" class="inputbox col-md-3">
                <div class="form-group">
                    {!! Form::label('','Comprovante de Pagamento') !!}
                    <div class='input-group'>
                        <div class='input-group-addon'>
                            <i class='glyphicon glyphicon-hdd'></i>
                        </div>
@if($contas_a_pagar->comprovante_de_pagamento && pathinfo($contas_a_pagar->comprovante_de_pagamento, PATHINFO_EXTENSION))
        <ol style="margin:0px;padding:0px;">
            <a class="fancybox" rel="gallery1" target="_blank" href="{{$fileurlbase . "images"}}/{{$contas_a_pagar->comprovante_de_pagamento}}">
                <img src="{{in_array(explode(".", $contas_a_pagar->comprovante_de_pagamento)[1], array("mp4", "pdf", "doc", "docx", "rar", "zip", "txt", "7zip", "csv", "xls", "xlsx")) ? URL("/") . "/" . explode(".", $contas_a_pagar->comprovante_de_pagamento)[1] . "-icon.png" : $fileurlbase . "images/" . $contas_a_pagar->comprovante_de_pagamento}}" height="100">
            </a>
        </ol>
{!! Form::hidden("comprovante_de_pagamento", $contas_a_pagar->comprovante_de_pagamento) !!}
@endif
                    </div>
                </div>
            </div>

    </div>

</div>