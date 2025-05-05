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
        <i class="glyphicon glyphicon-th-large"></i> Dados do Passageiro
    </h2>
</div>

            <div size="4" class="inputbox col-md-4">
                <div class="form-group">
                    {!! Form::label('','Nome') !!}
                    {!! Form::text('nome', $passageiro->nome, ['class' => 'form-control' , "id" => "input_nome", "style" => "text-transform: uppercase;", "oninput" => "this.value = this.value.toUpperCase();",'disabled' => 'disabled',]) !!}
                </div>
            </div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','CPF') !!}
                    {!! Form::text('cpf', $passageiro->cpf, ['class' => 'form-control cpf' , "id" => "input_cpf",'disabled' => 'disabled',]) !!}
                </div>
            </div>

            <div size="3" class="inputbox col-md-3">
                <div class="form-group">
                    {!! Form::label('','E-mail') !!}
                    <div class='input-group'>
                        <div class='input-group-addon'>
                            <i class='fa fa-envelope'></i>
                        </div>
                    {!! Form::email('e_mail', $passageiro->e_mail, ['class' => 'form-control' , "id" => "input_e_mail",'disabled' => 'disabled',]) !!}
                    </div>
                </div>
            </div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','Telefone') !!}
                    <div class='input-group'>
                        <div class='input-group-addon'>
                            <i class='fa fa-phone'></i>
                        </div>
                    {!! Form::text('telefone', $passageiro->telefone, ['class' => 'form-control telefone' , "id" => "input_telefone",'disabled' => 'disabled',]) !!}
                    </div>
                </div>
            </div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','Telefone 2') !!}
                    <div class='input-group'>
                        <div class='input-group-addon'>
                            <i class='fa fa-phone'></i>
                        </div>
                    {!! Form::text('telefone_2', $passageiro->telefone_2, ['class' => 'form-control telefone' , "id" => "input_telefone_2",'disabled' => 'disabled',]) !!}
                    </div>
                </div>
            </div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','Passaporte') !!}
                    {!! Form::text('passaporte', $passageiro->passaporte, ['class' => 'form-control' , "id" => "input_passaporte",'disabled' => 'disabled',]) !!}
                </div>
            </div>

    </div>

</div>