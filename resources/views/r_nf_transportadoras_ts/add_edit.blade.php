@php
    $acao       = ((isset($MRANfTransportadoras) and !is_null($MRANfTransportadoras))?'edit':'add');
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
        <h1>Nota Fiscal - Transportadoras</h1>
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
    {!! Form::open(['url' => "nota_fiscal/transportadoras/ts".($acao=='edit'?'/'.$MRANfTransportadoras->id:''), 'method' => ($acao=='edit'?'put':'post'), 'novalidate'=> true, 'enctype' => 'multipart/form-data', 'accept-charset' => 'utf-8', 'id' => ($acao=='edit'?'form_edit':'form_add').'_transportadoras_ts']) !!}
    <section class="content">
        <div class="box">
            @if($acao=='edit')
                {!! Form::hidden('id', $MRANfTransportadoras->id) !!}
                @if(\App\Models\Permissions::permissaoUsuario(\Auth::user(), "$controller@destroy"))
                    <div class="row" style="position: absolute; right: 0; padding: 5px; z-index: 100;">
                        <div class="col-md-12">
                            <a href="javascript:void(0);" type="button" class="btn btn-danger excluir-auto-confirma" modulo="nota_fiscal/transportadoras/ts" modulo_id="{{$MRANfTransportadoras->id}}"><i class="glyphicon glyphicon-trash"></i></a>
                        </div>
                    </div>
                @endif
            @endif
            <div class="box-body" id="div_transportadoras_ts">

                <div size="12" class="inputbox col-md-12">
                    <h2 class="page-header" style="font-size:20px;">
                        <i class="glyphicon glyphicon-th-large"></i> Informações da Transportadora
                    </h2>
                </div>

                <div size="12" class="inputbox col-md-12">
                    <div class="row">
                        <div size="2" class="inputbox col-md-2">
                            <div class="form-group">
                                {!! Form::label('','Status') !!}
                                {!! Form::select('status', \App\Http\Controllers\MRA\MRAListas::Get_options_status_ai([""]), ($acao=='edit'?$MRANfTransportadoras->status:null), ['class' => 'form-control select_single_no_trigger' , "id" => "input_status"]) !!}
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div size="4" class="inputbox col-md-4">
                            {!! Form::label('','CNPJ') !!} <span style="color: #ff0500;">*</span>
                            <div class="input-group mb_15p">
                                {!! Form::text('cnpj', ($acao=='edit'?$MRANfTransportadoras->cnpj:null), ['class' => 'form-control cnpj_v2', "placeholder"=>"__.___.___/____-__", "id" => "input_cnpj", "maxlength"=>50]) !!}
                                <span class="input-group-btn">
                                    <button class="btn btn-primary" type="button" onclick="javascript:$('#input_cnpj').trigger('change');"><i class="glyphicon glyphicon-refresh"></i></button>
                                </span>
                            </div>
                        </div>
                        <div size="4" class="inputbox col-md-4">
                            <div class="form-group">
                                {!! Form::label('','CPF') !!}
                                {!! Form::text('cpf', ($acao=='edit'?$MRANfTransportadoras->cpf:null), ['class' => 'form-control cpf', "placeholder"=>"___.___.___-__", "id" => "input_cpf", "maxlength"=>50]) !!}
                            </div>
                        </div>
                        <div size="4" class="inputbox col-md-4">
                            <div class="form-group">
                                {!! Form::label('','Inscrição Estadual (IE)') !!}
                                {!! Form::text('ie', ($acao=='edit'?$MRANfTransportadoras->ie:null), ['class' => 'form-control', "id" => "input_ie", "maxlength"=>50]) !!}
                            </div>
                        </div>

                    </div>
                    <div class="row">
                        <div size="4" class="inputbox col-md-4">
                            <div class="form-group">
                                {!! Form::label('','Nome / Razão Social') !!} <span style="color: #ff0500;">*</span>
                                {!! Form::text('nome', ($acao=='edit'?$MRANfTransportadoras->nome:null), ['class' => 'form-control' , "id" => "input_nome", "maxlength"=>100]) !!}
                            </div>
                        </div>
                        <div size="4" class="inputbox col-md-4">
                            <div class="form-group">
                                {!! Form::label('','Apelido / Fantasia') !!}
                                {!! Form::text('apelido', ($acao=='edit'?$MRANfTransportadoras->apelido:null), ['class' => 'form-control' , "id" => "input_apelido", "maxlength"=>100]) !!}
                            </div>
                        </div>
                        <div size="4" class="inputbox col-md-4">
                            <div class="form-group">
                                {!! Form::label('','Telefone') !!}
                                <div class="input-group">
                                    <div class="input-group-addon"><i class="fa fa-phone"></i></div>
                                    {!! Form::text('cont_telefone', ($acao=='edit'?$MRANfTransportadoras->cont_telefone:null), ['class' => 'form-control telefone',"placeholder"=>"(__) ____-____", "id" => "input_cont_telefone", "maxlength"=>200]) !!}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','E-mail') !!}
                                <div class="input-group">
                                    <div class="input-group-addon"><i class="fa fa-envelope"></i></div>
                                    {!! Form::text('cont_email', ($acao=='edit'?$MRANfTransportadoras->cont_email:null), ['class' => 'form-control' , "id" => "input_cont_email", "maxlength"=>200]) !!}
                                </div>
                            </div>
                        </div>
                        <div size="9" class="inputbox col-md-9">
                            <div class="form-group">
                                {!! Form::label('','E-mail(s) envio Nota Fiscal') !!}
                                <div class="input-group">
                                    <div class="input-group-addon"><i class="fa fa-envelope"></i></div>
                                    {!! Form::text('cont_emails_nf', ($acao=='edit'?$MRANfTransportadoras->cont_emails_nf:null), ['class' => 'form-control select_tags' , "id" => "input_cont_emails_nf", "maxlength"=>600, "maxTags"=>3]) !!}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div size="12" class="inputbox col-md-12">
                    <h2 class="page-header" style="font-size:20px;">
                        <i class="glyphicon glyphicon-th-large"></i> Endereço
                    </h2>
                </div>

                <div size="12" class="inputbox col-md-12">
                    <div class="row">
                        <div size="2" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','CEP') !!} <span style="color: #ff0500;">*</span>
                                <div class="input-group mb_15p">
                                    {!! Form::text('end_cep', ($acao=='edit'?$MRANfTransportadoras->end_cep:null), ['class' => 'form-control cep_v2', "placeholder"=>"_____-___", "id" => "input_end_cep", "maxlength"=>50]) !!}
                                    <span class="input-group-btn">
                                        <button class="btn btn-primary" type="button" onclick="javascript:$('#input_end_cep').trigger('change');"><i class="glyphicon glyphicon-refresh"></i></button>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','Tipo de Logradouro') !!} <span style="color: #ff0500;">*</span>
                                {!! Form::select('end_tipo_logradouro', \App\Http\Controllers\MRA\RNfListasTsController::Get_options_tipo_de_logradouro(), ($acao=='edit'?$MRANfTransportadoras->end_tipo_logradouro:null), ['class' => 'form-control', "id" => "input_end_tipo_logradouro"]) !!}
                            </div>
                        </div>
                        <div size="6" class="inputbox col-md-6">
                            <div class="form-group">
                                {!! Form::label('','Logradouro / Rua') !!} <span style="color: #ff0500;">*</span>
                                {!! Form::text('end_rua', ($acao=='edit'?$MRANfTransportadoras->end_rua:null), ['class' => 'form-control' , "id" => "input_end_rua", "maxlength"=>200]) !!}
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div size="2" class="inputbox col-md-2">
                            <div class="form-group">
                                {!! Form::label('','Número') !!} <span style="color: #ff0500;">*</span>
                                {!! Form::text('end_numero', ($acao=='edit'?$MRANfTransportadoras->end_numero:null), ['class' => 'form-control' , "id" => "input_end_numero", "maxlength"=>200]) !!}
                            </div>
                        </div>
                        <div size="4" class="inputbox col-md-4">
                            <div class="form-group">
                                {!! Form::label('','Bairro') !!} <span style="color: #ff0500;">*</span>
                                {!! Form::text('end_bairro', ($acao=='edit'?$MRANfTransportadoras->end_bairro:null), ['class' => 'form-control' , "id" => "input_end_bairro", "maxlength"=>200]) !!}
                            </div>
                        </div>
                    
                        {{--<div size="5" class="inputbox col-md-5">
                            <div class="form-group">
                                {!! Form::label('','Complemento') !!} <span style="color: #ff0500;">*</span>
                                {!! Form::text('end_complemento', ($acao=='edit'?$MRANfTransportadoras->end_complemento:null), ['class' => 'form-control' , "id" => "input_end_complemento", "maxlength"=>200]) !!}
                            </div>
                        </div>--}}
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','Estado') !!} <span style="color: #ff0500;">*</span>
                                {!! Form::select('end_estado', \App\Http\Controllers\MRA\MRAListas::Get_options_estados(), ($acao=='edit'?$MRANfTransportadoras->end_estado:null), ['class' => 'form-control select_single_no_trigger', 'data-live-search' => 'true', "id" => "input_end_estado"]) !!}
                            </div>
                        </div>
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','Cidade') !!} <span style="color: #ff0500;">*</span>
                                {!! Form::text('end_cidade', ($acao=='edit'?$MRANfTransportadoras->end_cidade:null), ['class' => 'form-control' , "id" => "input_end_cidade", "maxlength"=>50]) !!}
                            </div>
                        </div>
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
                <div class="col-md-12" style="margin-top: 20px; margin-bottom: 20px;">
                    <div class="form-group form-group-btn-{{($acao=='edit'?'edit':'add')}}">
                        <a href="{{ URL::previous() }}" class="btn btn-default form-group-btn-add-voltar"><i class="glyphicon glyphicon-backward"></i> Voltar</a>
                        @if($acao == 'add' and App\Models\Permissions::permissaoUsuario(\Auth::user(), "$controller@store"))
                            <button type="submit" class="btn btn-default right form-group-btn-add-cadastrar"><span class="glyphicon glyphicon-plus"></span> Cadastrar</button>
                        @endif
                        @if($acao == 'edit' and App\Models\Permissions::permissaoUsuario(\Auth::user(), "$controller@update"))
                            <button type="submit" class="btn btn-default right form-group-btn-edit-salvar"><span class="glyphicon glyphicon-ok"></span> Salvar</button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
    {!! Form::close() !!}

    @section('script')
        <script type="text/javascript">
            // :: CNPJ
            $("#input_cnpj").on('change', async function(){
                let _this = $(this);
                if(_this.attr('af') != undefined && _this.attr('af')=='true'){ return false; }
                _this.attr('af','true');
                setTimeout(function(){ _this.attr('af','false'); },1000); // Fix*
                let cnpj = await RA.consulta.cnpj(_this.val());
                if(cnpj!=undefined){
                    //$("#input_cpf").val((cnpj.nome!=undefined)?cnpj.nome.toUpperCase():'');
                    $("#input_nome").val((cnpj.nome!=undefined)?cnpj.nome.toUpperCase():'');
                    $("#input_apelido").val((cnpj.fantasia!=undefined)?cnpj.fantasia.toUpperCase():'');
                    $("#input_cont_telefone").val((cnpj.telefone!=undefined)?cnpj.telefone:'');
                    $("#input_cont_email").val((cnpj.email!=undefined)?cnpj.email:'');
                    $("#input_end_cep").val((cnpj.cep!=undefined?cnpj.cep:'')).trigger('change');
                }
            });

            // :: CEP
            $("#input_end_cep").on('change', async function(){
                let _this = $(this);
                if(_this.attr('af') != undefined && _this.attr('af')=='true'){ return false; }
                _this.attr('af','true');
                setTimeout(function(){ _this.attr('af','false'); },1000); // Fix*
                let cep = await RA.consulta.cep(_this.val());
                if(cep!=undefined){
                    $("#input_end_rua").val((cep.logradouro!=undefined?cep.logradouro.toUpperCase():''));
                    $("#input_end_bairro").val((cep.bairro!=undefined?cep.bairro.toUpperCase():''));
                    $("#input_end_complemento").val((cep.complemento!=undefined?cep.complemento.toUpperCase():''));
                    $("#input_end_estado").val((cep.uf!=undefined?cep.uf.toUpperCase():'')).trigger('change');
                    $("#input_end_cidade").val((cep.localidade!=undefined?cep.localidade.toUpperCase():''));
                }
            });
        </script>
    @endsection

@endsection
