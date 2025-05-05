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
                    {!! Form::label('','Fornecedor') !!}
                    {!! Form::text('fornecedor', $fornecedores->fornecedor, ['class' => 'form-control' , "id" => "input_fornecedor", "style" => "text-transform: uppercase;", "oninput" => "this.value = this.value.toUpperCase();",'disabled' => 'disabled',]) !!}
                </div>
            </div>

    </div>

</div>