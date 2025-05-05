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
        <i class="glyphicon glyphicon-th-large"></i> Dados do Cliente
    </h2>
</div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','Código do Cliente') !!}
                    {!! Form::number('codigo_do_cliente', $clientes->codigo_do_cliente, ['class' => 'form-control' , "id" => "input_codigo_do_cliente",'disabled' => 'disabled',]) !!}
                </div>
            </div>

            <div size="3" class="inputbox col-md-3">
                <div class="form-group">
                    {!! Form::label('','CNPJ do Cliente') !!}
                    {!! Form::text('cnpj_do_cliente', $clientes->cnpj_do_cliente, ['class' => 'form-control cnpj' , "id" => "input_cnpj_do_cliente",'disabled' => 'disabled',]) !!}
                </div>
            </div>

            <div size="3" class="inputbox col-md-3">
                <div class="form-group">
                    {!! Form::label('','CPF do Cliente') !!}
                    {!! Form::text('cpf_do_cliente', $clientes->cpf_do_cliente, ['class' => 'form-control cpf' , "id" => "input_cpf_do_cliente",'disabled' => 'disabled',]) !!}
                </div>
            </div>

            <div size="3" class="inputbox col-md-3">
                <div class="form-group">
                    {!! Form::label('','Inscrição Estadual/RG') !!}
                    {!! Form::text('inscricao_estadual_rg', $clientes->inscricao_estadual_rg, ['class' => 'form-control' , "id" => "input_inscricao_estadual_rg",'disabled' => 'disabled',]) !!}
                </div>
            </div>

            <div size="7" class="inputbox col-md-7">
                <div class="form-group">
                    {!! Form::label('','Nome do Cliente') !!}
                    {!! Form::text('nome_do_cliente', $clientes->nome_do_cliente, ['class' => 'form-control' , "id" => "input_nome_do_cliente", "style" => "text-transform: uppercase;", "oninput" => "this.value = this.value.toUpperCase();",'disabled' => 'disabled',]) !!}
                </div>
            </div>

            <div size="5" class="inputbox col-md-5">
                <div class="form-group">
                    {!! Form::label('','Fantasia') !!}
                    {!! Form::text('fantasia', $clientes->fantasia, ['class' => 'form-control' , "id" => "input_fantasia",'disabled' => 'disabled',]) !!}
                </div>
            </div>

            <div size="6" class="inputbox col-md-6">
                <div class="form-group">
                    {!! Form::label('','E-mail') !!}
                    {!! Form::text('e_mail', $clientes->e_mail, ['class' => 'form-control' , "id" => "input_e_mail",'disabled' => 'disabled',]) !!}
                </div>
            </div>

            <div size="12" class="inputbox col-md-12">
                <div class="form-group">
                    {!! Form::label('','Observação') !!}
                    {!! Form::text('observacao', $clientes->observacao, ['class' => 'form-control' , "id" => "input_observacao",'disabled' => 'disabled',]) !!}
                </div>
            </div>

            <div size="3" class="inputbox col-md-3">
                <div class="form-group">
                    {!! Form::label('','Telefone 1') !!}
                    <div class='input-group'>
                        <div class='input-group-addon'>
                            <i class='fa fa-phone'></i>
                        </div>
                    {!! Form::text('telefone_1', $clientes->telefone_1, ['class' => 'form-control telefone' , "id" => "input_telefone_1",'disabled' => 'disabled',]) !!}
                    </div>
                </div>
            </div>

            <div size="3" class="inputbox col-md-3">
                <div class="form-group">
                    {!! Form::label('','Telefone 2') !!}
                    <div class='input-group'>
                        <div class='input-group-addon'>
                            <i class='fa fa-phone'></i>
                        </div>
                    {!! Form::text('telefone_2', $clientes->telefone_2, ['class' => 'form-control telefone' , "id" => "input_telefone_2",'disabled' => 'disabled',]) !!}
                    </div>
                </div>
            </div>

<div size="12" class="inputbox col-md-12">
    <h2 class="page-header" style="font-size:20px;">
        <i class="glyphicon glyphicon-th-large"></i> Endereço do Cliente
    </h2>
</div>

            <div size="3" class="inputbox col-md-3">
                <div class="form-group">
                    {!! Form::label('','CEP do Cliente') !!}
                    {!! Form::text('cep_do_cliente', $clientes->cep_do_cliente, ['class' => 'form-control cep' , "id" => "input_cep_do_cliente",'disabled' => 'disabled',]) !!}
                </div>
            </div>

            <div size="7" class="inputbox col-md-7">
                <div class="form-group">
                    {!! Form::label('','Endereço') !!}
                    {!! Form::text('endereco', $clientes->endereco, ['class' => 'form-control' , "id" => "input_endereco",'disabled' => 'disabled',]) !!}
                </div>
            </div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','Número:') !!}
                    {!! Form::text('numero_', $clientes->numero_, ['class' => 'form-control' , "id" => "input_numero_",'disabled' => 'disabled',]) !!}
                </div>
            </div>

            <div size="8" class="inputbox col-md-8">
                <div class="form-group">
                    {!! Form::label('','Complemento') !!}
                    {!! Form::text('complemento', $clientes->complemento, ['class' => 'form-control' , "id" => "input_complemento",'disabled' => 'disabled',]) !!}
                </div>
            </div>

            <div size="4" class="inputbox col-md-4">
                <div class="form-group">
                    {!! Form::label('','Bairro') !!}
                    {!! Form::text('bairro', $clientes->bairro, ['class' => 'form-control' , "id" => "input_bairro",'disabled' => 'disabled',]) !!}
                </div>
            </div>

            <div size="4" class="inputbox col-md-4">
                <div class="form-group">
                    {!! Form::label('','Cidade') !!}
                    {!! Form::text('cidade', $clientes->cidade, ['class' => 'form-control' , "id" => "input_cidade",'disabled' => 'disabled',]) !!}
                </div>
            </div>

            <div size="4" class="inputbox col-md-4">
                <div class="form-group">
                    {!! Form::label('','Estado') !!}
                    {!! Form::text('estado', $clientes->estado, ['class' => 'form-control' , "id" => "input_estado",'disabled' => 'disabled',]) !!}
                </div>
            </div>

            <div size="4" class="inputbox col-md-4">
                <div class="form-group">
                    {!! Form::label('','Ponto de Referência') !!}
                    {!! Form::text('ponto_de_referencia', $clientes->ponto_de_referencia, ['class' => 'form-control' , "id" => "input_ponto_de_referencia",'disabled' => 'disabled',]) !!}
                </div>
            </div>

<div size="12" class="inputbox col-md-12">
    <h2 class="page-header" style="font-size:20px;">
        <i class="glyphicon glyphicon-th-large"></i> Anexos
    </h2>
</div>

<div class="col-md-12" style="margin-bottom: 20px;">
                    {!! Form::label('','Documentos') !!}
</div>
@if(!empty($clientes->Documentos))
    @foreach($clientes->Documentos as $key => $value)
        <div size="12" class="inputbox col-md-12 multiple">
            <div class="form-group">
                <ol>
                    @if($value->documentos)
    <a class="fancybox" rel="gallery1" target="_blank" href="{{$fileurlbase . "images"}}/{{$value->documentos}}">
        <img src="{{in_array(explode(".", $value->documentos)[1], array("mp4", "pdf", "doc", "docx", "rar", "zip", "txt", "7zip", "csv", "xls", "xlsx")) ? URL("/") . "/" . explode(".", $value->documentos)[1] . "-icon.png" : $fileurlbase . "images/" . $value->documentos}}" height="100">
    </a>
@endif

                </ol>
                <input type="hidden" name="documentos[{{100-$key}}]" value="{{$value->documentos}}">
            </div>
        </div>
    @endforeach

@endif

</div>

    </div>

</div>