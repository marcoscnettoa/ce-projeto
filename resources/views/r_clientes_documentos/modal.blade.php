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

        <div class="col-md-12" style="margin-bottom: 20px;">
                    {!! Form::label('','Documentos') !!}
</div>
@if(!empty($r_clientes_documentos->Documentos))
    @foreach($r_clientes_documentos->Documentos as $key => $value)
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