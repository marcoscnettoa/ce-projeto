@php
    $acao       = ($MRAGraficos?'edit':'add');
    $isPublic   = 0;
    $controller = get_class(\Request::route()->getController());
@endphp

@extends($isPublic ? 'layouts.app-public' : 'layouts.app')

@section('content')
    @section('style')
        <style type="text/css">
        </style>
    @endsection
    <section class="content-header">
        <h1>Gráficos</h1>
        {{--
        @if(!$isPublic)
            <ol class="breadcrumb">
                <li><a href="{{ URL('/') }}">Home</a></li>
                <li><a href="{{ URL('/') }}/emissao_nota_fiscal">Emissão Nota Fiscal</a></li>
                <li class="active">Emissão Nota Fiscal</li>
            </ol>
        @endif
        --}}
    </section>
    {!! Form::open(['url' => "mra_graficos".($acao=='edit'?'/1':''), 'method' => ($acao=='edit'?'put':'post'), 'novalidate'=> true, 'enctype' => 'multipart/form-data', 'accept-charset' => 'utf-8', 'id' => ($acao=='edit'?'form_edit':'form_add').'_mra_graficos']) !!}
    <section class="content">
        <div class="box" style="margin-bottom: 0px;">
            @if($acao=='edit')
                {!! Form::hidden('id', $MRAGraficos->id) !!}
            @endif
            <div class="box-body" id="div_mra_graficos">
                <div size="12" class="inputbox col-md-12">
                    <h2 class="page-header" style="font-size:20px;">
                        <i class="glyphicon glyphicon-th-large"></i> Gráficos Modular
                    </h2>
                </div>

                <div size="4" class="inputbox col-md-4">
                    {!! Form::label('','Status') !!}
                    <div class="form-group">
                    {!! Form::select('status', \App\Http\Controllers\MRA\MRAListas::Get_options_status_ai([""]), ($acao=='edit'?$MRAGraficos->status:null), ['class' => 'form-control select_single_no_trigger' , "id" => "input_status"]) !!}
                    </div>
                </div>

                <div size="4" class="inputbox col-md-4">
                    {!! Form::label('','Posição Dashboard') !!}
                    <div class="form-group">
                    {!! Form::select('posicao', \App\Models\MRAGraficos::Get_options_posicao([""]), ($acao=='edit'?$MRAGraficos->posicao:null), ['class' => 'form-control select_single_no_trigger', "id" => "input_posicao"]) !!}
                    </div>
                </div>

                <div size="12" class="inputbox col-md-12">
                    {!! Form::label('','CSS / Estilização') !!}
                    <div class="form-group">
                    {!! Form::textarea('css', ($acao=='edit'?$MRAGraficos->css:null), ['class' => 'form-control', 'rows'=>15, "id" => "input_css"]) !!}
                    </div>
                </div>

                <div size="12" class="inputbox col-md-12">
                    {!! Form::label('','CÓDIGO') !!}
                    <div class="form-group">
                    {!! Form::textarea('codigo', ($acao=='edit'?$MRAGraficos->codigo:null), ['class' => 'form-control', 'rows'=>15, "id" => "input_codigo"]) !!}
                    </div>
                </div>

                <div size="12" class="inputbox col-md-12">
                    {!! Form::label('','HTML / SCRIPT') !!}
                    <div class="form-group">
                    {!! Form::textarea('html', ($acao=='edit'?$MRAGraficos->html:null), ['class' => 'form-control', 'rows'=>15, "id" => "input_html"]) !!}
                    </div>
                </div>

                <div size="12" class="inputbox col-md-12">
                    {!! Form::label('','SCRIPT') !!}
                    <div class="form-group">
                    {!! Form::textarea('script', ($acao=='edit'?$MRAGraficos->script:null), ['class' => 'form-control', 'rows'=>15, "id" => "input_script"]) !!}
                    </div>
                </div>
                {{--@if(0)
                    @if(App\Models\Permissions::permissaoModerador(\Auth::user()))
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="">Para quem essa informação ficará disponível? Selecione um
                                    usuário. </label>
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
                @endif--}}
            </div>
        </div>
    </section>
    <section class="content">
        <div class="box">
            <div class="box-body" style="margin-top:15px;">
                <div class="col-md-12">
                    <div class="form-group form-group-btn-{{($acao=='edit'?'edit':'add')}}">
                        <a href="{{ URL('/') }}" class="btn btn-default form-group-btn-add-voltar"><i class="glyphicon glyphicon-backward"></i> Voltar</a>
                        @if(
                                App\Models\Permissions::permissaoUsuario(\Auth::user(), "$controller@store") ||
                                App\Models\Permissions::permissaoUsuario(\Auth::user(), "$controller@update")
                            )
                            <button type="submit" class="btn btn-default right form-group-btn-edit-salvar"><span
                                    class="glyphicon glyphicon-ok"></span> Salvar
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
    {!! Form::close() !!}
    @section('script')
        <script type="text/javascript">

        </script>
    @endsection

@endsection
