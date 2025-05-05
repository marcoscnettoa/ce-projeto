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

        <div size="12" class="inputbox col-md-12">
    <h2 class="page-header" style="font-size:20px;">
        <i class="glyphicon glyphicon-th-large"></i> Dados do Vendedor
    </h2>
</div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','Código do Vendedor') !!}
                    {!! Form::number('codigo_do_vendedor', $vendedores->codigo_do_vendedor, ['class' => 'form-control' , "id" => "input_codigo_do_vendedor",'disabled' => 'disabled',]) !!}
                </div>
            </div>

            <div size="7" class="inputbox col-md-7">
                <div class="form-group">
                    {!! Form::label('','Nome do Vendedor') !!}
                    {!! Form::text('nome_do_vendedor', $vendedores->nome_do_vendedor, ['class' => 'form-control' , "id" => "input_nome_do_vendedor",'disabled' => 'disabled',]) !!}
                </div>
            </div>

            <div size="3" class="inputbox col-md-3">
                <div class="form-group">
                    {!! Form::label('','CPF') !!}
                    {!! Form::text('cpf_do_vendedor', $vendedores->cpf_do_vendedor, ['class' => 'form-control cpf' , "id" => "input_cpf_do_vendedor",'disabled' => 'disabled',]) !!}
                </div>
            </div>

            <div size="4" class="inputbox col-md-4">
                <div class="form-group">
                    {!! Form::label('','E-mail') !!}
                    {!! Form::text('e_mail_do_vendedor', $vendedores->e_mail_do_vendedor, ['class' => 'form-control' , "id" => "input_e_mail_do_vendedor",'disabled' => 'disabled',]) !!}
                </div>
            </div>

            <div size="3" class="inputbox col-md-3">
                <div class="form-group">
                    {!! Form::label('','Telefone') !!}
                    <div class='input-group'>
                        <div class='input-group-addon'>
                            <i class='fa fa-phone'></i>
                        </div>
                    {!! Form::text('telefone_1', $vendedores->telefone_1, ['class' => 'form-control telefone' , "id" => "input_telefone_1",'disabled' => 'disabled',]) !!}
                    </div>
                </div>
            </div>

            <div size="12" class="inputbox col-md-12">
                <div class="form-group">
                    {!! Form::label('','Observação') !!}
                    {!! Form::text('observacao', $vendedores->observacao, ['class' => 'form-control' , "id" => "input_observacao",'disabled' => 'disabled',]) !!}
                </div>
            </div>

<div size="12" class="inputbox col-md-12">
    <h2 class="page-header" style="font-size:20px;">
        <i class="glyphicon glyphicon-th-large"></i> Endereço do Vendedor
    </h2>
</div>

            <div size="3" class="inputbox col-md-3">
                <div class="form-group">
                    {!! Form::label('','CEP') !!}
                    {!! Form::text('cep_do_vendedor', $vendedores->cep_do_vendedor, ['class' => 'form-control cep' , "id" => "input_cep_do_vendedor",'disabled' => 'disabled',]) !!}
                </div>
            </div>

            <div size="7" class="inputbox col-md-7">
                <div class="form-group">
                    {!! Form::label('','Endereço') !!}
                    {!! Form::text('endereco_d_vendedor', $vendedores->endereco_d_vendedor, ['class' => 'form-control' , "id" => "input_endereco_d_vendedor",'disabled' => 'disabled',]) !!}
                </div>
            </div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','Número') !!}
                    {!! Form::text('numero_do_endereco_do_vendedor', $vendedores->numero_do_endereco_do_vendedor, ['class' => 'form-control' , "id" => "input_numero_do_endereco_do_vendedor",'disabled' => 'disabled',]) !!}
                </div>
            </div>

            <div size="8" class="inputbox col-md-8">
                <div class="form-group">
                    {!! Form::label('','Complemento') !!}
                    {!! Form::text('complemento_do_vendedor', $vendedores->complemento_do_vendedor, ['class' => 'form-control' , "id" => "input_complemento_do_vendedor",'disabled' => 'disabled',]) !!}
                </div>
            </div>

            <div size="4" class="inputbox col-md-4">
                <div class="form-group">
                    {!! Form::label('','Bairro') !!}
                    {!! Form::text('bairro_do_vendedor', $vendedores->bairro_do_vendedor, ['class' => 'form-control' , "id" => "input_bairro_do_vendedor",'disabled' => 'disabled',]) !!}
                </div>
            </div>

            <div size="5" class="inputbox col-md-5">
                <div class="form-group">
                    {!! Form::label('','Cidade') !!}
                    {!! Form::text('cidade_do_vendedor', $vendedores->cidade_do_vendedor, ['class' => 'form-control' , "id" => "input_cidade_do_vendedor",'disabled' => 'disabled',]) !!}
                </div>
            </div>

            <div size="4" class="inputbox col-md-4">
                <div class="form-group">
                    {!! Form::label('','Estado') !!}
                    {!! Form::text('estado_do_vendedor', $vendedores->estado_do_vendedor, ['class' => 'form-control' , "id" => "input_estado_do_vendedor",'disabled' => 'disabled',]) !!}
                </div>
            </div>

    </div>

</div>
