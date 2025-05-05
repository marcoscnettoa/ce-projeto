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

<section class="content-header Templates_add">
    <h1>Templates</h1>
    <!--@if(!$isPublic)
    <ol class="breadcrumb">
        <li><a href="{{ URL('/') }}">Home</a></li>
        <li><a href="{{ URL('/') }}/templates">Templates</a></li>
        <li class="active">Templates</li>
    </ol>
    @endif-->
</section>

<section class="content Templates_add">

<div class="box">

    {!! Form::open(['url' => "templates", 'method' => 'post', 'novalidate'=> true, 'enctype' => 'multipart/form-data', 'accept-charset' => 'utf-8', 'id' => 'form_add_templates']) !!}

        <div class="box-body" id="div_templates">

            <div size="12" class="inputbox col-md-12">
                <div class="form-group">
                    {!! Form::label('','Nome do Template') !!}
                    {!! Form::text('nome_do_template', null, ['class' => 'form-control' , "id" => "input_nome_do_template"]) !!}
                </div>
            </div>

            <div size="12" class="inputbox col-md-12">
                <div class="form-group">
                    {!! Form::label('','Template') !!}
                    {!! Form::textarea('template', null, ['class' => 'form-control tinymce' , "id" => "input_template"]) !!}
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
