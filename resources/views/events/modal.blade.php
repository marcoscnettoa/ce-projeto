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
                    {!! Form::label('','Evento') !!}
                    {!! Form::text('title', $events->title, ['class' => 'form-control' , "id" => "input_title",'disabled' => 'disabled',]) !!}
                </div>
            </div>

            <div size="12" class="inputbox col-md-12">
                <div class="form-group">
                    {!! Form::label('','Data Início') !!}
                    {!! Form::text('start_date', $events->start_date, ['class' => 'form-control componenteDataHora' , "id" => "input_start_date",'disabled' => 'disabled',]) !!}
                </div>
            </div>

            <div size="12" class="inputbox col-md-12">
                <div class="form-group">
                    {!! Form::label('','Data Fim') !!}
                    {!! Form::text('end_date', $events->end_date, ['class' => 'form-control componenteDataHora' , "id" => "input_end_date",'disabled' => 'disabled',]) !!}
                </div>
            </div>

            <div size="12" class="inputbox col-md-12">
                <div class="form-group">
                    {!! Form::label('','Dia inteiro?') !!}
                    {!! Form::checkbox('is_all_day', null, $events->is_all_day, ['class' => '' , "id" => "input_is_all_day",'disabled' => 'disabled',]) !!}
                </div>
            </div>

    </div>

</div>