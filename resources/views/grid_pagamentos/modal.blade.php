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
                    {!! Form::label('','Forma de Pagamento') !!}
                    {!! Form::select('forma_de_pagamento', $formas_de_pagamentos_forma_de_pa, $grid_pagamentos->forma_de_pagamento, ['class' => 'form-control  select_relationship ', 'data-sub' => '', 'data-live-search' => 'true',  'data-none-selected-text' => true, 'controller' => 'formas_de_pagamentos' , "id" => "input_forma_de_pagamento",'disabled' => 'disabled',]) !!}
                </div>
            </div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','Valor Pago') !!}
                    {!! Form::text('valor_pago', $grid_pagamentos->valor_pago, ['class' => 'form-control money' , "id" => "input_valor_pago",'disabled' => 'disabled',]) !!}
                </div>
            </div>

    </div>

</div>