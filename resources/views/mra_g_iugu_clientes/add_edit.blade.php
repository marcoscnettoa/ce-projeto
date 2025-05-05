@php
    $acao       = ((isset($MRAGIuguClientes) and !is_null($MRAGIuguClientes))?'edit':'add');
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
        <h1>Gateway Iugu - Clientes</h1>
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
    {!! Form::open(['url' => "mra_g_iugu/mra_g_iugu_clientes".($acao=='edit'?'/'.$MRAGIuguClientes->id:''), 'method' => ($acao=='edit'?'put':'post'), 'novalidate'=> true, 'enctype' => 'multipart/form-data', 'accept-charset' => 'utf-8', 'id' => ($acao=='edit'?'form_edit':'form_add').'_mra_g_iugu_clientes']) !!}
    <section class="content">
        <div class="box">
            @if($acao=='edit')
                {!! Form::hidden('id', $MRAGIuguClientes->id) !!}
                @if(\App\Models\Permissions::permissaoUsuario(\Auth::user(), "$controller@destroy"))
                    <div class="row" style="position: absolute; right: 0; padding: 5px; z-index: 100;">
                        <div class="col-md-12">
                            <a href="javascript:void(0);" type="button" class="btn btn-info consultar-cliente" modulo="mra_g_iugu/mra_g_iugu_clientes" modulo_id="{{$MRAGIuguClientes->id}}" style="margin:2px;"><i class="glyphicon glyphicon-refresh"></i> Recarregar Cliente Iugu</a>
                            <a href="javascript:void(0);" type="button" class="btn btn-danger excluir-auto-confirma" modulo="mra_g_iugu/mra_g_iugu_clientes" modulo_id="{{$MRAGIuguClientes->id}}" style="float:none;"><i class="glyphicon glyphicon-trash"></i></a>
                        </div>
                    </div>
                @endif
            @endif
            <div class="box-body" id="div_mra_g_iugu_clientes">

                <div size="12" class="inputbox col-md-12">
                    <h2 class="page-header" style="font-size:20px;">
                        <i class="glyphicon glyphicon-th-large"></i> Informações do Cliente
                    </h2>
                </div>

                <div size="12" class="inputbox col-md-12">
                    <div class="row">
                        {{--<div size="2" class="inputbox col-md-2">
                            <div class="form-group">
                                {!! Form::label('','Status') !!}
                                {!! Form::select('status', \App\Http\Controllers\MRA\MRAListas::Get_options_status_ai([""]), ($acao=='edit'?$MRAGIuguClientes->status:null), ['class' => 'form-control select_single_no_trigger' , "id" => "input_status"]) !!}
                            </div>
                        </div>--}}
                        @if($acao=='edit')
                            <div size="4" class="inputbox col-md-4">
                                <div class="form-group">
                                    {!! Form::label('','ID Iugu Cliente',['class'=>'text-warning']) !!}
                                    {!! Form::text('iugu_customer_id', ($acao=='edit'?$MRAGIuguClientes->iugu_customer_id:null), ['class' => 'form-control', "id" => "input_iugu_customer_id", "disabled"=>true]) !!}
                                </div>
                            </div>
                        @endif
                        <div size="2" class="inputbox col-md-2">
                            <div class="form-group">
                                {!! Form::label('','Tipo de Pessoa') !!} <span style="color: #ff0500;">*</span>
                                {!! Form::select('tipo', \App\Http\Controllers\MRA\MRAListas::Get_options_tipo_pessoa(), ($acao=='edit'?$MRAGIuguClientes->tipo:null), ['class' => 'form-control select_single_no_trigger' , "id" => "input_tipo"]) !!}
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div id="box_cnpj" size="4" class="inputbox col-md-4" style="display:none;">
                            <div class="form-group">
                                {!! Form::label('','CNPJ') !!} <span style="color: #ff0500;">*</span>
                                <div class="input-group mb_15p">
                                    {!! Form::text('cnpj', ($acao=='edit'?$MRAGIuguClientes->cnpj:null), ['class' => 'form-control cnpj_v2', "placeholder"=>"__.___.___/____-__", "id" => "input_cnpj", "maxlength"=>50]) !!}
                                    <span class="input-group-btn">
                                        <button class="btn btn-primary" type="button" onclick="javascript:$('#input_cnpj').trigger('change');"><i class="glyphicon glyphicon-refresh"></i></button>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div id="box_cpf" size="4" class="inputbox col-md-4"  style="display:none;">
                            <div class="form-group">
                                {!! Form::label('','CPF') !!} <span style="color: #ff0500;">*</span>
                                {!! Form::text('cpf', ($acao=='edit'?$MRAGIuguClientes->cpf:null), ['class' => 'form-control cpf', "placeholder"=>"___.___.___-__", "id" => "input_cpf", "maxlength"=>50]) !!}
                            </div>
                        </div>
                        <div id="box_inscricao_estadual" size="4" class="inputbox col-md-4"  style="display:none;">
                            <div class="form-group">
                                {!! Form::label('','Inscrição Estadual (IE)') !!}
                                {!! Form::text('inscricao_estadual', ($acao=='edit'?$MRAGIuguClientes->inscricao_estadual:null), ['class' => 'form-control', "id" => "input_inscricao_estadual", "maxlength"=>50]) !!}
                            </div>
                        </div>
                        <div id="box_inscricao_municipal" size="4" class="inputbox col-md-4"  style="display:none;">
                            <div class="form-group">
                                {!! Form::label('','Inscrição Municipal (IE)') !!}
                                {!! Form::text('inscricao_municipal', ($acao=='edit'?$MRAGIuguClientes->inscricao_municipal:null), ['class' => 'form-control', "id" => "input_inscricao_municipal", "maxlength"=>50]) !!}
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div size="4" class="inputbox col-md-4">
                            <div class="form-group">
                                {!! Form::label('','Nome') !!} <span style="color: #ff0500;">*</span>
                                {!! Form::text('nome', ($acao=='edit'?$MRAGIuguClientes->nome:null), ['class' => 'form-control' , "id" => "input_nome", "maxlength"=>100]) !!}
                            </div>
                        </div>
                        <div size="4" class="inputbox col-md-4">
                            <div class="form-group">
                                {!! Form::label('','Telefone') !!}
                                <div class="input-group">
                                    <div class="input-group-addon"><i class="fa fa-phone"></i></div>
                                    {!! Form::text('cont_telefone', ($acao=='edit'?$MRAGIuguClientes->cont_telefone:null), ['class' => 'form-control telefone',"placeholder"=>"(__) ____-____", "id" => "input_cont_telefone", "maxlength"=>200]) !!}
                                </div>
                            </div>
                        </div>
                        <div size="4" class="inputbox col-md-4">
                            <div class="form-group">
                                {!! Form::label('','E-mail') !!} <span style="color: #ff0500;">*</span>
                                <div class="input-group">
                                    <div class="input-group-addon"><i class="fa fa-envelope"></i></div>
                                    {!! Form::text('cont_email', ($acao=='edit'?$MRAGIuguClientes->cont_email:null), ['class' => 'form-control' , "id" => "input_cont_email", "maxlength"=>200]) !!}
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
                                {!! Form::label('','CEP') !!}
                                <div class="input-group mb_15p">
                                    {!! Form::text('end_cep', ($acao=='edit'?$MRAGIuguClientes->end_cep:null), ['class' => 'form-control cep_v2', "placeholder"=>"_____-___", "id" => "input_end_cep", "maxlength"=>50]) !!}
                                    <span class="input-group-btn">
                                        <button class="btn btn-primary" type="button" onclick="javascript:$('#input_end_cep').trigger('change');"><i class="glyphicon glyphicon-refresh"></i></button>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','Logradouro / Rua') !!}
                                {!! Form::text('end_rua', ($acao=='edit'?$MRAGIuguClientes->end_rua:null), ['class' => 'form-control' , "id" => "input_end_rua", "maxlength"=>200]) !!}
                            </div>
                        </div>
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','Número') !!}
                                {!! Form::text('end_numero', ($acao=='edit'?$MRAGIuguClientes->end_numero:null), ['class' => 'form-control' , "id" => "input_end_numero", "maxlength"=>200]) !!}
                            </div>
                        </div>
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','Bairro') !!}
                                {!! Form::text('end_bairro', ($acao=='edit'?$MRAGIuguClientes->end_bairro:null), ['class' => 'form-control' , "id" => "input_end_bairro", "maxlength"=>200]) !!}
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div size="5" class="inputbox col-md-4">
                            <div class="form-group">
                                {!! Form::label('','Complemento') !!}
                                {!! Form::text('end_complemento', ($acao=='edit'?$MRAGIuguClientes->end_complemento:null), ['class' => 'form-control' , "id" => "input_end_complemento", "maxlength"=>200]) !!}
                            </div>
                        </div>
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','Estado') !!}
                                {!! Form::select('end_estado', \App\Http\Controllers\MRA\MRAListas::Get_options_estados(), ($acao=='edit'?$MRAGIuguClientes->end_estado:null), ['class' => 'form-control select_single_no_trigger', 'data-live-search' => 'true', "id" => "input_end_estado"]) !!}
                            </div>
                        </div>
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','Cidade') !!}
                                {!! Form::text('end_cidade', ($acao=='edit'?$MRAGIuguClientes->end_cidade:null), ['class' => 'form-control' , "id" => "input_end_cidade", "maxlength"=>50]) !!}
                            </div>
                        </div>
                        <div size="2" class="inputbox col-md-2">
                            <div class="form-group">
                                {!! Form::label('','País') !!}
                                {!! Form::select('end_pais', \App\Http\Controllers\MRA\MRAListas::Get_options_paises(), (($acao=='edit' and !is_null($MRAGIuguClientes->end_pais))?$MRAGIuguClientes->end_pais:1058), ['class' => 'form-control select_single_no_trigger', 'data-live-search' => 'true', "dropdown-menu-right"=>"", "id" => "input_end_pais"]) !!}
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div size="8" class="inputbox col-md-8">
                            <div class="form-group">
                                {!! Form::label('','Anotações') !!}
                                {!! Form::textarea('observacoes', ($acao=='edit'?$MRAGIuguClientes->observacoes:null), ['class' => 'form-control' , "id" => "input_observacoes", "rows" => 4]) !!}
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
                            <button type="submit" class="btn btn-default right form-group-btn-edit-salvar" style="float:right;"><span class="glyphicon glyphicon-ok"></span> Salvar</button>
                        @endif
                        @if($acao == 'edit' and App\Models\Permissions::permissaoUsuario(\Auth::user(), "$controller@destroy"))
                            <button type="submit" name="destroy_forcado" value="1" class="btn btn-danger right form-group-btn-edit-destroy-forcado" onclick="javascript: return (confirm('O Cliente será excluído dentro do sistema! Tem certeza?')?true:false);" style="float:right; margin: 0px; margin-right:15px;"><i class="glyphicon glyphicon-alert"></i>&nbsp; Forçar Exclusão</button>
                        @endif
                    </div>
                </div>
                @if($acao == 'edit')
                    <div class="col-md-12">
                        <p class="text-warning text-right">
                            <strong>** Atenção!</strong> <i class="glyphicon glyphicon-alert"></i><br/>
                            - <strong class="text-danger">Forçar Exclusão</strong> apenas será removido <strong>dentro do sistema</strong>!</strong>
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </section>
    {!! Form::close() !!}

    @section('script')
        <script type="text/javascript">
            // :: Tipo de Pessoa
            $("#input_tipo").on("change",function(){
                switch($("#input_tipo").val()) {
                    case 'F':
                        $("#box_cnpj").hide();
                        $("#box_cpf").show();
                        $("#box_inscricao_estadual").hide();
                        $("#box_inscricao_municipal").hide();
                        break;
                    case 'J':
                        $("#box_cnpj").show();
                        $("#box_cpf").hide();
                        $("#box_inscricao_estadual").show();
                        $("#box_inscricao_municipal").show();
                        break;
                    case 'E':
                        $("#box_cnpj").hide();
                        $("#box_cpf").hide();
                        $("#box_inscricao_estadual").hide();
                        $("#box_inscricao_municipal").show();
                        break;
                    default:
                        $("#box_cnpj").hide();
                        $("#box_cpf").hide();
                        $("#box_inscricao_estadual").hide();
                        $("#box_inscricao_municipal").hide();
                        break;
                }
            }).trigger('change');

            // :: CNPJ
            $("#input_cnpj").on('change', async function(){
                let _this = $(this);
                if(_this.attr('af') != undefined && _this.attr('af')=='true'){ return false; }
                _this.attr('af','true');
                setTimeout(function(){ _this.attr('af','false'); },1000);  // Fix*
                let cnpj = await RA.consulta.cnpj(_this.val());
                if(cnpj!=undefined){
                    $("#input_nome").val((cnpj.nome!=undefined)?cnpj.nome.toUpperCase():'');
                    $("#input_cont_email").val((cnpj.email!=undefined)?cnpj.email:'');
                    $("#input_cont_telefone").val((cnpj.telefone!=undefined)?cnpj.telefone:'');
                    $("#input_end_cep").val((cnpj.cep!=undefined?cnpj.cep:'')).trigger('change');
                    $("#input_end_numero").val((cnpj.numero!=undefined?cnpj.numero:''));
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
                    $("#input_end_pais").val(1058).trigger('change');
                }
            });

            // :: Consultar Cliente
            $(".consultar-cliente").on("click", function() {
                let modulo     = ($(this).attr('modulo')!=undefined && $(this).attr('modulo') != ""?$(this).attr('modulo'):undefined);
                let modulo_id  = ($(this).attr('modulo_id')!=undefined && $(this).attr('modulo_id') != ""?$(this).attr('modulo_id'):undefined);

                if(modulo == undefined || modulo_id == undefined){ return false; }

                let form    = document.createElement('form');
                form.method = 'GET';
                form.action = base+'/'+modulo+'/consultar_cliente/'+modulo_id
                let input   = document.createElement('input');
                input.type  = 'hidden';
                input.name  = '_token';
                input.value = $("meta[name=\'csrf-token\']").attr('content');
                form.appendChild(input);
                $('body').append(form);
                form.submit();
            });
        </script>
    @endsection

@endsection
