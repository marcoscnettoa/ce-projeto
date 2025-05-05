@php

    $isPublic = 0;

    $enable_kanban = 0;

    $controller = get_class(\Request::route()->getController());

@endphp

@extends($isPublic ? 'layouts.app-public' : 'layouts.app')

@section('content')

@section('style')

    <style type="text/css">

    </style>

@endsection

<section class="content-header r_clientes_documentos_edit">
    <h1>r_clientes_documentos </h1>
    <!--@if(!$isPublic)
    <ol class="breadcrumb">
        <li><a href="{{ URL('/') }}">Home</a></li>
        <li><a href="{{ URL('/') }}/r_clientes_documentos">r_clientes_documentos</a></li>
        <li class="active">#{{$r_clientes_documentos->id}}</li>
    </ol>
    @endif-->
</section>

<section class="content r_clientes_documentos_edit">

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

    @if(\App\Models\Permissions::permissaoUsuario(\Auth::user(), "$controller@destroy"))
        <div class="row" style="position: absolute; right: 0; padding: 5px;">
            <div class="col-md-12">
                <form id="form-destroy" method="POST" action="{{ route('r_clientes_documentos.destroy', $r_clientes_documentos->id) }}" accept-charset="UTF-8">
                    {!! csrf_field() !!}
                    {!! method_field('DELETE') !!}
                    <button type="submit" style="margin-left: 10px; margin-top: -1px;" onclick="return confirm('Tem certeza que quer deletar?')" class="btn btn-danger glyphicon glyphicon-trash"></button>
                </form>
            </div>
        </div>
    @endif

    {!! Form::open(['url' => "r_clientes_documentos/$r_clientes_documentos->id", 'method' => 'put', 'novalidate'=> true, 'enctype' => 'multipart/form-data', 'accept-charset' => 'utf-8', 'id' => 'form_edit_r_clientes_documentos']) !!}

        @if(\Request::get('modal'))
            {!! Form::hidden('modal-close', 1) !!}
        @endif
        {!! Form::hidden('id', $r_clientes_documentos->id) !!}

        <div class="box-body" id="div_r_clientes_documentos">

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

                <i class="glyphicon glyphicon-trash multiple_remove" style="margin-top: 5px;"></i>
                </ol>
                <input type="hidden" name="documentos[{{100-$key}}]" value="{{$value->documentos}}">
            </div>
        </div>
    @endforeach

@endif

<div class="documentos_multiplos">
    <div class="divdefault">
        <div size="12" class="inputbox col-md-12">
                <ol>
                                        {!! Form::file('documentos[]', ['class' => 'form-control isFile' , "id" => "input_documentos"]) !!}
        <div class='row'>
            <div class='col-md-12'>
                <img src='' id='file_input_documentos[]' style='height: 100px; display: none;'>
            </div>
        </div>
                <i class="glyphicon glyphicon-trash multiple_remove" style="margin-top: 5px;"></i>
                </ol>
        </div>
    </div>
</div>

<div class="col-md-12" style="margin: 20px 0 20px 0;">
    <div class="form-group">
        <i class="glyphicon glyphicon-plus multiple_add" data="documentos_multiplos"></i>
    </div>
</div>

</div>

            @if(0)

                @if(\App\Models\Permissions::permissaoModerador(\Auth::user()))
                    <div class="col-md-12">
                        <div class="form-group">

                            <label for="">Para quem essa informação ficará disponível? Selecione um usuário. </label>

                            @php

                                $parserList = array();

                                $userlist = \App\Models\User::get()->toArray();

                                array_unshift($userlist, array('id' => '',  'name' => ''));
                                array_unshift($userlist, array('id' => 0,  'name' => 'Disponível para todos'));

                                foreach($userlist as $u)
                                {
                                    $parserList[$u['id']] = $u['name'];
                                }

                            @endphp

                            {!! Form::select('r_auth', $parserList, $r_clientes_documentos->r_auth, ['class' => 'form-control']) !!}
                        </div>
                    </div>
                @endif

            @endif

            <div class="col-md-12" style="margin-top: 20px; margin-bottom: 20px;">

                <div class="form-group form-group-btn-edit">

                    @if(!$isPublic)
                        <a href="{{ URL::previous() }}" class="btn btn-default form-group-btn-add-voltar" style="float: left;"><i class="glyphicon glyphicon-backward"></i> Voltar</a>
                    @endif

                    @if(\App\Models\Permissions::permissaoUsuario(\Auth::user(), "$controller@update") OR $isPublic)

                        <button type="submit" class="btn btn-default right form-group-btn-edit-salvar">
                            <span class="glyphicon glyphicon-ok"></span> Salvar
                        </button>

                    @endif

                </div>

            </div>

        </div>

    {!! Form::close() !!}

</div>

</section>

@section('script')

    <script type="text/javascript">

    </script>

@endsection

@endsection
