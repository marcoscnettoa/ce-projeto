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
                    {!! Form::label('','Nome do Template') !!}
                    {!! Form::text('nome_do_template', $templates->nome_do_template, ['class' => 'form-control' , "id" => "input_nome_do_template",'disabled' => 'disabled',]) !!}
                </div>
            </div>

            <div size="12" class="inputbox col-md-12">
                <div class="form-group">
                    {!! Form::label('','Template') !!}
                    {!! Form::textarea('template', $templates->template, ['class' => 'form-control tinymce' , "id" => "input_template",'disabled' => 'disabled',]) !!}
                </div>
            </div>

    </div>

</div>