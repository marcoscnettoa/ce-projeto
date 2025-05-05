@php
    $acao       = ($MRAGIuguConfiguracoes?'edit':'add');
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
        <h1>Gateway Iugu - Configurações</h1>
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
    {!! Form::open(['url' => "mra_g_iugu/mra_g_iugu_configuracoes".($acao=='edit'?'/1':''), 'method' => ($acao=='edit'?'put':'post'), 'novalidate'=> true, 'enctype' => 'multipart/form-data', 'accept-charset' => 'utf-8', 'id' => ($acao=='edit'?'form_edit':'form_add').'_mra_g_iugu_configuracoes']) !!}
    <section class="content">
        <div class="box" style="margin-bottom: 0px;">
            @if($acao=='edit')
                {!! Form::hidden('id', $MRAGIuguConfiguracoes->id) !!}
            @endif
            <div class="box-body" id="div_mra_g_iugu_configuracoes">
                <div size="12" class="inputbox col-md-12">
                    <h2 class="page-header" style="font-size:20px;">
                        <i class="glyphicon glyphicon-th-large"></i> API Iugu
                    </h2>
                </div>

                <div size="12" class="inputbox col-md-12">
                    <div class="row">
                        <div size="6" class="inputbox col-md-6">
                            <div class="form-group">
                                {!! Form::label('','Token API') !!} <span style="color: #ff0500;">*</span>
                                <div class="input-group">
                                    <div class="input-group-addon"><i class="fa fa-bullseye"></i></div>
                                    {!! Form::text('token_api', ($acao=='edit'?$MRAGIuguConfiguracoes->token_api:null), ['class' => 'form-control', "id" => "input_token_api", "maxlength"=>500]) !!}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div size="6" class="inputbox col-md-6">
                            <div class="form-group">
                                {!! Form::label('','Prefixo Faturas ( Avulsas )') !!}
                                {!! Form::text('prefix_order_id', ($acao=='edit'?$MRAGIuguConfiguracoes->prefix_order_id:null), ['class' => 'form-control', "id" => "input_prefix_order_id", "maxlength"=>500, "placeholder"=>"ex: FAT-00001"]) !!}
                            </div>
                        </div>
                    </div>
                    {{--<div class="row">
                        <div size="6" class="inputbox col-md-6">
                            <div class="form-group" style="margin-bottom:5px;">
                                {!! Form::label('','Token Webhook') !!} <span style="color: #ff0500;">*</span>
                                <div class="input-group">
                                    <div class="input-group-addon"><i class="fa fa-retweet"></i></div>
                                    {!! Form::text('token_webhook', ($acao=='edit'?$MRAGIuguConfiguracoes->token_webhook:null), ['class' => 'form-control', "id" => "input_token_webhook", "maxlength"=>500]) !!}
                                </div>
                            </div>
                        </div>
                        <div size="12" class="inputbox col-md-12" style="margin-bottom:15px;">
                            <span style="overflow-wrap: break-word;"><span class="text-warning">Link Webhook:</span> <i>{{URL('mra_g_iugu/webhook')}}</i></span>
                        </div>
                    </div>--}}
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
