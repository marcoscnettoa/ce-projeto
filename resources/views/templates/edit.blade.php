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

<section class="content-header Templates_edit">
    <h1>Templates </h1>
    <!--@if(!$isPublic)
    <ol class="breadcrumb">
        <li><a href="{{ URL('/') }}">Home</a></li>
        <li><a href="{{ URL('/') }}/templates">Templates</a></li>
        <li class="active">#{{$templates->id}}</li>
    </ol>
    @endif-->
</section>

<section class="content Templates_edit">

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
                <form id="form-destroy" method="POST" action="{{ route('templates.destroy', $templates->id) }}" accept-charset="UTF-8">
                    {!! csrf_field() !!}
                    {!! method_field('DELETE') !!}
                    <button type="submit" style="margin-left: 10px; margin-top: -1px;" onclick="return confirm('Tem certeza que quer deletar?')" class="btn btn-danger glyphicon glyphicon-trash"></button>
                </form>
            </div>
        </div>
    @endif

    {!! Form::open(['url' => "templates/$templates->id", 'method' => 'put', 'novalidate'=> true, 'enctype' => 'multipart/form-data', 'accept-charset' => 'utf-8', 'id' => 'form_edit_templates']) !!}

        @if(\Request::get('modal'))
            {!! Form::hidden('modal-close', 1) !!}
        @endif
        {!! Form::hidden('id', $templates->id) !!}

        <div class="box-body" id="div_templates">

            <div size="12" class="inputbox col-md-12">
                <div class="form-group">
                    {!! Form::label('','Nome do Template') !!}
                    {!! Form::text('nome_do_template', $templates->nome_do_template, ['class' => 'form-control' , "id" => "input_nome_do_template"]) !!}
                </div>
            </div>

            <div size="12" class="inputbox col-md-12">
                <div class="form-group">
                    {!! Form::label('','Template') !!}
                    {!! Form::textarea('template', $templates->template, ['class' => 'form-control tinymce' , "id" => "input_template"]) !!}
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

                            {!! Form::select('r_auth', $parserList, $templates->r_auth, ['class' => 'form-control']) !!}
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
