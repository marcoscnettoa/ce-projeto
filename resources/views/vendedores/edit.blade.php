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

<section class="content-header Vendedores_edit">
    <h1>Vendedores </h1>
    <!--@if(!$isPublic)
    <ol class="breadcrumb">
        <li><a href="{{ URL('/') }}">Home</a></li>
        <li><a href="{{ URL('/') }}/vendedores">Vendedores</a></li>
        <li class="active">#{{$vendedores->id}}</li>
    </ol>
    @endif-->
</section>

<section class="content Vendedores_edit">

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
                <form id="form-destroy" method="POST" action="{{ route('vendedores.destroy', $vendedores->id) }}" accept-charset="UTF-8">
                    {!! csrf_field() !!}
                    {!! method_field('DELETE') !!}
                    <button type="submit" style="margin-left: 10px; margin-top: -1px;" onclick="return confirm('Tem certeza que quer deletar?')" class="btn btn-danger glyphicon glyphicon-trash"></button>
                </form>
            </div>
        </div>
    @endif

    {!! Form::open(['url' => "vendedores/$vendedores->id", 'method' => 'put', 'novalidate'=> true, 'enctype' => 'multipart/form-data', 'accept-charset' => 'utf-8', 'id' => 'form_edit_vendedores']) !!}

        @if(\Request::get('modal'))
            {!! Form::hidden('modal-close', 1) !!}
        @endif
        {!! Form::hidden('id', $vendedores->id) !!}

        <div class="box-body" id="div_vendedores">

<div size="12" class="inputbox col-md-12">
    <h2 class="page-header" style="font-size:20px;">
        <i class="glyphicon glyphicon-th-large"></i> Dados do Vendedor
    </h2>
</div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','Código do Vendedor') !!}
                    {!! Form::number('codigo_do_vendedor', $vendedores->codigo_do_vendedor, ['class' => 'form-control' , "id" => "input_codigo_do_vendedor"]) !!}
                </div>
            </div>

            <div size="7" class="inputbox col-md-7">
                <div class="form-group">
                    {!! Form::label('','Nome do Vendedor') !!}
                    {!! Form::text('nome_do_vendedor', $vendedores->nome_do_vendedor, ['class' => 'form-control' , "id" => "input_nome_do_vendedor"]) !!}
                </div>
            </div>

            <div size="3" class="inputbox col-md-3">
                <div class="form-group">
                    {!! Form::label('','CPF') !!}
                    {!! Form::text('cpf_do_vendedor', $vendedores->cpf_do_vendedor, ['class' => 'form-control cpf' , "id" => "input_cpf_do_vendedor"]) !!}
                </div>
            </div>

            <div size="4" class="inputbox col-md-4">
                <div class="form-group">
                    {!! Form::label('','E-mail') !!}
                    {!! Form::text('e_mail_do_vendedor', $vendedores->e_mail_do_vendedor, ['class' => 'form-control' , "id" => "input_e_mail_do_vendedor"]) !!}
                </div>
            </div>

            <div size="3" class="inputbox col-md-3">
                <div class="form-group">
                    {!! Form::label('','Telefone') !!}
                    <div class='input-group'>
                        <div class='input-group-addon'>
                            <i class='fa fa-phone'></i>
                        </div>
                    {!! Form::text('telefone_1', $vendedores->telefone_1, ['class' => 'form-control telefone' , "id" => "input_telefone_1"]) !!}
                    </div>
                </div>
            </div>

            <div size="12" class="inputbox col-md-12">
                <div class="form-group">
                    {!! Form::label('','Observação') !!}
                    {!! Form::text('observacao', $vendedores->observacao, ['class' => 'form-control' , "id" => "input_observacao"]) !!}
                </div>
            </div>

<div size="12" class="inputbox col-md-12">
    <h2 class="page-header" style="font-size:20px;">
        <i class="glyphicon glyphicon-th-large"></i> Endereço do Vendedor
    </h2>
</div>

            <div size="3" class="inputbox col-md-3">
                <div class="form-group">
                    {!! Form::label('','CEP') !!}
                    {!! Form::text('cep_do_vendedor', $vendedores->cep_do_vendedor, ['class' => 'form-control cep' , "id" => "input_cep_do_vendedor"]) !!}
                </div>
            </div>

            <div size="7" class="inputbox col-md-7">
                <div class="form-group">
                    {!! Form::label('','Endereço') !!}
                    {!! Form::text('endereco_d_vendedor', $vendedores->endereco_d_vendedor, ['class' => 'form-control' , "id" => "input_endereco_d_vendedor"]) !!}
                </div>
            </div>

            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','Número') !!}
                    {!! Form::text('numero_do_endereco_do_vendedor', $vendedores->numero_do_endereco_do_vendedor, ['class' => 'form-control' , "id" => "input_numero_do_endereco_do_vendedor"]) !!}
                </div>
            </div>

            <div size="8" class="inputbox col-md-8">
                <div class="form-group">
                    {!! Form::label('','Complemento') !!}
                    {!! Form::text('complemento_do_vendedor', $vendedores->complemento_do_vendedor, ['class' => 'form-control' , "id" => "input_complemento_do_vendedor"]) !!}
                </div>
            </div>

            <div size="4" class="inputbox col-md-4">
                <div class="form-group">
                    {!! Form::label('','Bairro') !!}
                    {!! Form::text('bairro_do_vendedor', $vendedores->bairro_do_vendedor, ['class' => 'form-control' , "id" => "input_bairro_do_vendedor"]) !!}
                </div>
            </div>

            <div size="5" class="inputbox col-md-5">
                <div class="form-group">
                    {!! Form::label('','Cidade') !!}
                    {!! Form::text('cidade_do_vendedor', $vendedores->cidade_do_vendedor, ['class' => 'form-control' , "id" => "input_cidade_do_vendedor"]) !!}
                </div>
            </div>

            <div size="4" class="inputbox col-md-4">
                <div class="form-group">
                    {!! Form::label('','Estado') !!}
                    {!! Form::text('estado_do_vendedor', $vendedores->estado_do_vendedor, ['class' => 'form-control' , "id" => "input_estado_do_vendedor"]) !!}
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

                            {!! Form::select('r_auth', $parserList, $vendedores->r_auth, ['class' => 'form-control']) !!}
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
