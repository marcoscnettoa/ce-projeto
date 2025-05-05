@php
    $acao       = ((isset($RNfClientes) and !is_null($RNfClientes))?'edit':'add');
    $isPublic   = 0;
    $controller = get_class(\Request::route()->getController());

    $estados = App\Models\REstados::selectRaw('*,CONCAT(sigla," - ",nome) as sigla_nome')
            ->pluck('sigla_nome', 'id')
            ->prepend("---", "");
@endphp
@extends($isPublic ? 'layouts.app-public' : 'layouts.app')
@section('content')
    @section('style')
        <style type="text/css">
        </style>
    @endsection
    <section class="content-header">
        <h1>Nota Fiscal - Clientes</h1>
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
    {!! Form::open(['url' => "nota_fiscal/clientes/ts".($acao=='edit'?'/'.$RNfClientes->id:''), 'method' => ($acao=='edit'?'put':'post'), 'novalidate'=> true, 'enctype' => 'multipart/form-data', 'accept-charset' => 'utf-8', 'id' => ($acao=='edit'?'form_edit':'form_add').'_clientes_ts']) !!}
    <section class="content">
        <div class="box">
            @if($acao=='edit')
                {!! Form::hidden('id', $RNfClientes->id) !!}
                @if(\App\Models\Permissions::permissaoUsuario(\Auth::user(), "$controller@destroy"))
                    <div class="row" style="position: absolute; right: 0; padding: 5px; z-index: 100;">
                        <div class="col-md-12">
                            <a href="javascript:void(0);" type="button" class="btn btn-danger excluir-auto-confirma" modulo="nota_fiscal/clientes/ts" modulo_id="{{$RNfClientes->id}}"><i class="glyphicon glyphicon-trash"></i></a>
                        </div>
                    </div>
                @endif
            @endif
            <div class="box-body" id="div_clientes_ts">

                <div size="12" class="inputbox col-md-12">
                    <h2 class="page-header" style="font-size:20px;">
                        <i class="glyphicon glyphicon-th-large"></i> Informações do Cliente
                    </h2>
                </div>

                <div size="12" class="inputbox col-md-12">
                    <div class="row">
                        <div size="2" class="inputbox col-md-2">
                            <div class="form-group">
                                {!! Form::label('','Status') !!}
                                {!! Form::select('status', \App\Http\Controllers\MRA\MRAListas::Get_options_status_ai([""]), ($acao=='edit'?$RNfClientes->status:null), ['class' => 'form-control select_single_no_trigger' , "id" => "input_status"]) !!}
                            </div>
                        </div>
                        <div size="2" class="inputbox col-md-2">
                            <div class="form-group">
                                {!! Form::label('','Tipo de Pessoa') !!} <span style="color: #ff0500;">*</span>
                                {!! Form::select('tipo', \App\Http\Controllers\MRA\MRAListas::Get_options_tipo_pessoa(), ($acao=='edit'?$RNfClientes->tipo:null), ['class' => 'form-control select_single_no_trigger' , "id" => "input_tipo"]) !!}
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div id="box_cnpj" size="4" class="inputbox col-md-4" style="display:none;">
                            <div class="form-group">
                                {!! Form::label('','CNPJ') !!} <span style="color: #ff0500;">*</span>
                                <div class="input-group mb_15p">
                                    {!! Form::text('cnpj', ($acao=='edit'?$RNfClientes->cnpj:null), ['class' => 'form-control cnpj_v2', "placeholder"=>"__.___.___/____-__", "id" => "input_cnpj", "maxlength"=>50]) !!}
                                    <span class="input-group-btn">
                                        <button class="btn btn-primary" type="button" onclick="javascript:$('#input_cnpj').trigger('change');"><i class="glyphicon glyphicon-refresh"></i></button>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div id="box_cpf" size="4" class="inputbox col-md-4"  style="display:none;">
                            <div class="form-group">
                                {!! Form::label('','CPF') !!} <span style="color: #ff0500;">*</span>
                                {!! Form::text('cpf', ($acao=='edit'?$RNfClientes->cpf:null), ['class' => 'form-control cpf', "placeholder"=>"___.___.___-__", "id" => "input_cpf", "maxlength"=>50]) !!}
                            </div>
                        </div>
                        <div id="box_inscricao_estadual" size="4" class="inputbox col-md-4"  style="display:none;">
                            <div class="form-group">
                                {!! Form::label('','Inscrição Estadual (IE)') !!}
                                {!! Form::text('inscricao_estadual', ($acao=='edit'?$RNfClientes->inscricao_estadual:null), ['class' => 'form-control', "id" => "input_inscricao_estadual", "maxlength"=>50]) !!}
                            </div>
                        </div>
                        <div id="box_inscricao_municipal" size="4" class="inputbox col-md-4"  style="display:none;">
                            <div class="form-group">
                                {!! Form::label('','Inscrição Municipal (IE)') !!}
                                {!! Form::text('inscricao_municipal', ($acao=='edit'?$RNfClientes->inscricao_municipal:null), ['class' => 'form-control', "id" => "input_inscricao_municipal", "maxlength"=>50]) !!}
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div size="4" class="inputbox col-md-4">
                            <div class="form-group">
                                {!! Form::label('','Nome') !!} <span style="color: #ff0500;">*</span>
                                {!! Form::text('nome', ($acao=='edit'?$RNfClientes->nome:null), ['class' => 'form-control' , "id" => "input_nome", "maxlength"=>100]) !!}
                            </div>
                        </div>
                        <div size="4" class="inputbox col-md-4">
                            <div class="form-group">
                                {!! Form::label('','Telefone') !!}
                                <div class="input-group">
                                    <div class="input-group-addon"><i class="fa fa-phone"></i></div>
                                    {!! Form::text('cont_telefone', ($acao=='edit'?$RNfClientes->cont_telefone:null), ['class' => 'form-control telefone',"placeholder"=>"(__) ____-____", "id" => "input_cont_telefone", "maxlength"=>200]) !!}
                                </div>
                            </div>
                        </div>
                        <div size="4" class="inputbox col-md-4">
                            <div class="form-group">
                                {!! Form::label('','E-mail') !!}
                                <div class="input-group">
                                    <div class="input-group-addon"><i class="fa fa-envelope"></i></div>
                                    {!! Form::text('cont_email', ($acao=='edit'?$RNfClientes->cont_email:null), ['class' => 'form-control' , "id" => "input_cont_email", "maxlength"=>200]) !!}
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- <div class="row">
                        <div size="4" class="inputbox col-md-4">
                            <div class="form-group">
                                {!! Form::label('','Enviar Nota Fiscal - E-mail') !!}
                                {!! Form::select('enviar_nf_email', [1=>'Sim',0=>'Não'], ($acao=='edit'?$RNfClientes->enviar_nf_email:null), ['class' => 'form-control select_single_no_trigger' , "id" => "input_enviar_nf_email"]) !!}
                            </div>
                        </div>
                    </div> --}}
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
                                    {!! Form::text('end_cep', ($acao=='edit'?$RNfClientes->end_cep:null), ['class' => 'form-control cep_v2', "placeholder"=>"_____-___", "id" => "input_end_cep", "maxlength"=>50]) !!}
                                    <span class="input-group-btn">
                                        <button class="btn btn-primary" type="button" onclick="javascript:$('#input_end_cep').trigger('change');"><i class="glyphicon glyphicon-refresh"></i></button>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','Tipo de Logradouro') !!} <span style="color: #ff0500;">*</span>
                                {!! Form::select('end_tipo_logradouro', \App\Http\Controllers\MRA\RNfListasTsController::Get_options_tipo_de_logradouro(), ($acao=='edit'?$RNfClientes->end_tipo_logradouro:null), ['class' => 'form-control select_single_no_trigger', 'data-live-search' => 'true', "id" => "input_end_tipo_logradouro"]) !!}
                            </div>
                        </div>
                        <div size="6" class="inputbox col-md-6">
                            <div class="form-group">
                                {!! Form::label('','Logradouro / Rua') !!} <span style="color: #ff0500;">*</span>
                                {!! Form::text('end_rua', ($acao=='edit'?$RNfClientes->end_rua:null), ['class' => 'form-control' , "id" => "input_end_rua", "maxlength"=>200]) !!}
                            </div>
                        </div>

                    </div>
                    <div class="row">
                        <div size="2" class="inputbox col-md-2">
                            <div class="form-group">
                                {!! Form::label('','Número') !!} <span style="color: #ff0500;">*</span>
                                {!! Form::text('end_numero', ($acao=='edit'?$RNfClientes->end_numero:null), ['class' => 'form-control' , "id" => "input_end_numero", "maxlength"=>200]) !!}
                            </div>
                        </div>
                        <div size="4" class="inputbox col-md-4">
                            <div class="form-group">
                                {!! Form::label('','Bairro') !!} <span style="color: #ff0500;">*</span>
                                {!! Form::text('end_bairro', ($acao=='edit'?$RNfClientes->end_bairro:null), ['class' => 'form-control' , "id" => "input_end_bairro", "maxlength"=>200]) !!}
                            </div>
                        </div>
                        <div size="6" class="inputbox col-md-6">
                            <div class="form-group">
                                {!! Form::label('','Complemento') !!}
                                {!! Form::text('end_complemento', ($acao=='edit'?$RNfClientes->end_complemento:null), ['class' => 'form-control' , "id" => "input_end_complemento", "maxlength"=>200]) !!}
                            </div>
                        </div>
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','Estado') !!} <span style="color: #ff0500;">*</span>
                                {!! Form::select('end_estado', $estados, ($acao=='edit'?$RNfClientes->end_estado:null), ['class' => 'form-control select_single_no_trigger', 'data-live-search' => 'true', "id" => "input_end_estado"]) !!}
                            </div>
                        </div>
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','Cidade') !!} <span style="color: #ff0500;">*</span>
                                {!! Form::select('end_cidade', [], ($acao=='edit'?$RNfClientes->end_cidade:null), ['class' => 'form-control select_single_no_trigger' , 'data-live-search' => 'true', "id" => "input_end_cidade", '_value'=>($acao=='edit'?$RNfClientes->end_cidade:null), "maxlength"=>50, 'placeholder'=>'Selecione o Estado']) !!}
                            </div>
                        </div>
                        <div size="4" class="inputbox col-md-4">
                            <div class="form-group">
                                {!! Form::label('','País') !!}
                                {!! Form::select('end_pais', \App\Http\Controllers\MRA\MRAListas::Get_options_paises(), (($acao=='edit' and !is_null($RNfClientes->end_pais))?$RNfClientes->end_pais:1058), ['class' => 'form-control select_single_no_trigger', 'data-live-search' => 'true', "dropdown-menu-right"=>"", "id" => "input_end_pais"]) !!}
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

            // Ajax para carregar cidades de acordo com o estado selecionado    
            $('#input_end_estado').on('change',function(){
                $("#input_end_cidade").html('<option value="">---</option>').selectpicker('refresh');
                if($(this).val == ''){ return false; }
                $.get(base + '/municipios/estado/' + $(this).val() + '/ajax?subForm=uf', function(data, status){
                    $("#input_end_cidade").val('');
                    if(typeof(data)!='object'){ return; }
                    if(status == 'success'){
                        let options = '';
                        options += '<option value="">---</option>';
                        for(let i = 0; i < data.length; i++) {
                            options += '<option value="' + data[i]['id'] + '">' + data[i]['nome'] + '</option>';
                        }
                        $("#input_end_cidade").html(options);
                        $("#input_end_cidade").val($("#input_end_cidade").attr('_value')).selectpicker('refresh');
                    }
                });
            }).trigger('change');
        </script>
    @endsection

@endsection
