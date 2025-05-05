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
                <div class="form-group">
                    {!! Form::label('','Forma de pagamento') !!}
                    {!! Form::text('forma_de_pagamento', $formas_de_pagamentos->forma_de_pagamento, ['class' => 'form-control' , "id" => "input_forma_de_pagamento",'disabled' => 'disabled',]) !!}
                </div>
            </div>

    </div>

</div>