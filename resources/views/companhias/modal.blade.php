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
                    {!! Form::label('','Companhia') !!}
                    {!! Form::text('companhia', $companhias->companhia, ['class' => 'form-control' , "id" => "input_companhia", "style" => "text-transform: uppercase;", "oninput" => "this.value = this.value.toUpperCase();",'disabled' => 'disabled',]) !!}
                </div>
            </div>

    </div>

</div>