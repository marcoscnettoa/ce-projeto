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

                    <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','Saldo do dia') !!}
                    <div class='input-group'>
                        <div class='input-group-addon'>
                            <i class='glyphicon glyphicon-usd'></i>
                        </div>
                    {!! Form::text('saldo_inicial', $fluxo_de_caixa->saldo_inicial, ['class' => 'form-control money' , "id" => "input_saldo_inicial",'disabled' => 'disabled',]) !!}
                    </div>
                </div>
            </div>

            <div size="10" class="inputbox col-md-10">
                <div class="form-group">
                    {!! Form::label('','Movimentação') !!}
                    {!! Form::text('movimentacao', $fluxo_de_caixa->movimentacao, ['class' => 'form-control' , "id" => "input_movimentacao",'disabled' => 'disabled',]) !!}
                </div>
            </div>

<div size="12" class="inputbox col-md-12">
    <h2 class="page-header" style="font-size:20px;">
        <i class="glyphicon glyphicon-th-large"></i> Recebimentos
    </h2>
</div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','Data do Recebimento') !!}
                    <div class='input-group'>
                        <div class='input-group-addon'>
                            <i class='glyphicon glyphicon-calendar'></i>
                        </div>
                    {!! Form::text('data_do_recebimento', $fluxo_de_caixa->data_do_recebimento, ['class' => 'form-control data' , "id" => "input_data_do_recebimento",'disabled' => 'disabled',]) !!}
                    </div>
                </div>
            </div>

            <div size="3" class="inputbox col-md-3">
                <div class="form-group">
                    {!! Form::label('','Valor Recebido') !!}
                    <div class='input-group'>
                        <div class='input-group-addon'>
                            <i class='glyphicon glyphicon-usd'></i>
                        </div>
                    {!! Form::text('valor_recebido', $fluxo_de_caixa->valor_recebido, ['class' => 'form-control money' , "id" => "input_valor_recebido",'disabled' => 'disabled',]) !!}
                    </div>
                </div>
            </div>

<div size="12" class="inputbox col-md-12">
    <h2 class="page-header" style="font-size:20px;">
        <i class="glyphicon glyphicon-th-large"></i> Pagamentos
    </h2>
</div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','Data do Pagamento') !!}
                    <div class='input-group'>
                        <div class='input-group-addon'>
                            <i class='glyphicon glyphicon-calendar'></i>
                        </div>
                    {!! Form::text('data_do_pagamento', $fluxo_de_caixa->data_do_pagamento, ['class' => 'form-control data' , "id" => "input_data_do_pagamento",'disabled' => 'disabled',]) !!}
                    </div>
                </div>
            </div>

            <div size="3" class="inputbox col-md-3">
                <div class="form-group">
                    {!! Form::label('','Total a Pagar') !!}
                    <div class='input-group'>
                        <div class='input-group-addon'>
                            <i class='glyphicon glyphicon-usd'></i>
                        </div>
                    {!! Form::text('total_a_pagar', $fluxo_de_caixa->total_a_pagar, ['class' => 'form-control money' , "id" => "input_total_a_pagar",'disabled' => 'disabled',]) !!}
                    </div>
                </div>
            </div>

<div size="12" class="inputbox col-md-12">
    <h2 class="page-header" style="font-size:20px;">
        <i class="glyphicon glyphicon-th-large"></i> Total
    </h2>
</div>

            <div size="4" class="inputbox col-md-4">
                <div class="form-group">
                    {!! Form::label('','Data Atual') !!}
                    <div class='input-group'>
                        <div class='input-group-addon'>
                            <i class='glyphicon glyphicon-calendar'></i>
                        </div>
                    {!! Form::text('data_atual', $fluxo_de_caixa->data_atual, ['class' => 'form-control data' , "id" => "input_data_atual",'disabled' => 'disabled',]) !!}
                    </div>
                </div>
            </div>

            <div size="4" class="inputbox col-md-4">
                <div class="form-group">
                    {!! Form::label('','Calculando') !!}
                    {!! Form::text('ghost_camp', $fluxo_de_caixa->ghost_camp, ['class' => 'form-control' , "id" => "input_ghost_camp",'disabled' => 'disabled',]) !!}
                </div>
            </div>

            <div size="4" class="inputbox col-md-4">
                <div class="form-group">
                    {!! Form::label('','Saldo da Transação') !!}
                    {!! Form::text('saldo_da_transacao', $fluxo_de_caixa->saldo_da_transacao, ['class' => 'form-control' , "id" => "input_saldo_da_transacao",'disabled' => 'disabled',]) !!}
                </div>
            </div>

    </div>

</div>