@php

    $isPublic = 0;

    $controller = get_class(\Request::route()->getController());

@endphp

@extends($isPublic ? 'layouts.app-public' : 'layouts.app')

@section('content')

@section('style')

    <style type="text/css">

    </style>

@endsection

<section class="content-header r_clientes_documentos_add">
    <h1>r_clientes_documentos</h1>
    <!--@if(!$isPublic)
    <ol class="breadcrumb">
        <li><a href="{{ URL('/') }}">Home</a></li>
        <li><a href="{{ URL('/') }}/r_clientes_documentos">r_clientes_documentos</a></li>
        <li class="active">r_clientes_documentos</li>
    </ol>
    @endif-->
</section>

<section class="content r_clientes_documentos_add">

<div class="box">

    {!! Form::open(['url' => "r_clientes_documentos", 'method' => 'post', 'novalidate'=> true, 'enctype' => 'multipart/form-data', 'accept-charset' => 'utf-8', 'id' => 'form_add_r_clientes_documentos']) !!}

        <div class="box-body" id="div_r_clientes_documentos">

<div class="col-md-12">
                    {!! Form::label('','Documentos') !!}
</div>

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

                @if(App\Models\Permissions::permissaoModerador(\Auth::user()))
                    <div class="col-md-12">
                        <div class="form-group">

                            <label for="">Para quem essa informação ficará disponível? Selecione um usuário. </label>

                            @php

                                $parserList = array();

                                $userlist = App\Models\User::get()->toArray();

                                array_unshift($userlist, array('id' => '',  'name' => ''));
                                array_unshift($userlist, array('id' => 0,  'name' => 'Disponível para todos'));

                                foreach($userlist as $u)
                                {
                                    $parserList[$u['id']] = $u['name'];
                                }

                            @endphp

                            {!! Form::select('r_auth', $parserList, null, ['class' => 'form-control']) !!}

                        </div>
                    </div>
                @endif

            @endif

            <div class="col-md-12" style="margin-top: 20px;">

                <div class="form-group form-group-btn-add">

                    @if(!$isPublic)
                        <a href="{{ URL::previous() }}" class="btn btn-default form-group-btn-add-voltar"><i class="glyphicon glyphicon-backward"></i> Voltar</a>
                    @endif

                    @if(App\Models\Permissions::permissaoUsuario(\Auth::user(), "$controller@store") OR $isPublic)

                        <button type="submit" class="btn btn-default right form-group-btn-add-cadastrar">
                            <span class="glyphicon glyphicon-plus"></span> Cadastrar
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
