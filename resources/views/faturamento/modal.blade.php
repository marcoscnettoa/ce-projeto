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

                    <div size="3" class="inputbox col-md-3">
                <div class="form-group">
                    {!! Form::label('','N° da Fatura') !!}
                    {!! Form::text('n_da_fatura', $faturamento->n_da_fatura, ['class' => 'form-control' , "id" => "input_n_da_fatura",'disabled' => 'disabled',]) !!}
                </div>
            </div>

            <div size="4" class="inputbox col-md-4">
                <div class="form-group">
                    {!! Form::label('','Data da Fatura') !!}
                    {!! Form::text('data_da_fatura', $faturamento->data_da_fatura, ['autocomplete' =>'off', 'class' => 'form-control componenteData' , "id" => "input_data_da_fatura",'disabled' => 'disabled',]) !!}
                </div>
            </div>

            <div size="4" class="inputbox col-md-4">
                <div class="form-group">
                    {!! Form::label('','Data de Vencimento') !!}
                    {!! Form::text('data_de_vencimento', $faturamento->data_de_vencimento, ['autocomplete' =>'off', 'class' => 'form-control componenteData' , "id" => "input_data_de_vencimento",'disabled' => 'disabled',]) !!}
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
                    {!! Form::select('cliente', $clientes_nome_do_cliente, $faturamento->cliente, ['class' => 'form-control  select_relationship ', 'data-sub' => '', 'data-live-search' => 'true',  'data-none-selected-text' => true, 'controller' => 'clientes' , "id" => "input_cliente",'disabled' => 'disabled',]) !!}
                </div>
            </div>

            <div size="4" class="inputbox col-md-4">
                <div class="form-group">
                    {!! Form::label('','Data Inicial') !!}
                    {!! Form::text('data_inicial', $faturamento->data_inicial, ['autocomplete' =>'off', 'class' => 'form-control componenteData' , "id" => "input_data_inicial",'disabled' => 'disabled',]) !!}
                </div>
            </div>

            <div size="4" class="inputbox col-md-4">
                <div class="form-group">
                    {!! Form::label('','Data Final') !!}
                    {!! Form::text('data_final', $faturamento->data_final, ['autocomplete' =>'off', 'class' => 'form-control componenteData' , "id" => "input_data_final",'disabled' => 'disabled',]) !!}
                </div>
            </div>

            <div size="12" class="inputbox col-md-12">
                <div class="form-group">
                    {!! Form::label('','Template') !!}
                    {!! Form::select('template', $templates_nome_do_template, $faturamento->template, ['class' => 'form-control  select_relationship ', 'data-sub' => '', 'data-live-search' => 'true',  'data-none-selected-text' => true, 'controller' => 'templates' , "id" => "input_template",'disabled' => 'disabled',]) !!}
                </div>
            </div>

    </div>

</div>