@php
    $acao       = ($MRANfConfiguracoes?'edit':'add');
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
        <h1>Nota Fiscal - Configurações</h1>
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
    {!! Form::open(['url' => "nota_fiscal/configuracoes/ts".($acao=='edit'?'/1':''), 'method' => ($acao=='edit'?'put':'post'), 'novalidate'=> true, 'enctype' => 'multipart/form-data', 'accept-charset' => 'utf-8', 'id' => ($acao=='edit'?'form_edit':'form_add').'_configuracoes']) !!}
    <section class="content">
        <div class="box" style="margin-bottom: 0px;">
            @if($acao=='edit')
                {!! Form::hidden('id', $MRANfConfiguracoes->id) !!}
            @endif
            <div class="box-body" id="div_configuracoes">
                <div size="12" class="inputbox col-md-12">
                    <h2 class="page-header" style="font-size:20px;">
                        <i class="glyphicon glyphicon-th-large"></i> Informações da Empresa
                    </h2>
                </div>

                <div size="12" class="inputbox col-md-12">
                    <div class="row">
                        <div size="4" class="inputbox col-md-4">
                            {!! Form::label('','CNPJ') !!} <span style="color: #ff0500;">*</span>
                            <div class="input-group mb_15p">
                                {!! Form::text('cnpj', ($acao=='edit'?$MRANfConfiguracoes->cnpj:null), [
                                    'class' => 'form-control cnpj_v2',
                                    "placeholder"=>"__.___.___/____-__",
                                    "id" => "input_cnpj",
                                    "maxlength" => 50,
                                    $MRANfConfiguracoes->cnpj ? 'readonly' : '',
                                ]) !!}
                                <span class="input-group-btn">
                                    <button class="btn btn-primary" type="button" onclick="javascript:$('#input_cnpj').trigger('change');"><i class="glyphicon glyphicon-refresh"></i></button>
                                </span>
                            </div>
                        </div>
                        <div size="4" class="inputbox col-md-4">
                            <div class="form-group">
                                {!! Form::label('','Razão Social') !!} <span style="color: #ff0500;">*</span>
                                {!! Form::text('razao_social', ($acao=='edit'?$MRANfConfiguracoes->razao_social:null), [
                                    'class' => 'form-control',
                                    "id" => "input_razao_social",
                                    "maxlength" => 100,
                                    'readonly',
                                ]) !!}
                            </div>
                        </div>
                        <div size="4" class="inputbox col-md-4">
                            <div class="form-group">
                                {!! Form::label('','Nome Fantasia') !!} <span style="color: #ff0500;">*</span>
                                {!! Form::text('nome_fantasia', ($acao=='edit'?$MRANfConfiguracoes->nome_fantasia:null), ['class' => 'form-control' , "id" => "input_nome_fantasia", "maxlength"=>100]) !!}
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div size="4" class="inputbox col-md-4">
                            <div class="form-group">
                                {!! Form::label('','Apelido da Empresa') !!}
                                {!! Form::text('apelido_empresa', ($acao=='edit'?$MRANfConfiguracoes->apelido_empresa:null), ['class' => 'form-control' , "id" => "input_apelido_empresa", "maxlength"=>100]) !!}
                            </div>
                        </div>
                        <div size="4" class="inputbox col-md-4">
                            <div class="form-group">
                                {!! Form::label('','Inscrição Estadual') !!} <span style="color: #ff0500;">{{env('MODULO_NF_PRODUTO') ? '*' : ''}}</span>
                                {!! Form::text('inscricao_estadual', ($acao=='edit'?$MRANfConfiguracoes->inscricao_estadual:null), ['class' => 'form-control' , "id" => "input_inscricao_estadual", "maxlength"=>50]) !!}
                            </div>
                        </div>
                        <div size="4" class="inputbox col-md-4">
                            <div class="form-group">
                                {!! Form::label('','Inscrição Municipal') !!}  <span style="color: #ff0500;">{{env('MODULO_NF_SERVICO') ? '*' : ''}}</span>
                                {!! Form::text('inscricao_municipal', ($acao=='edit'?$MRANfConfiguracoes->inscricao_municipal:null), ['class' => 'form-control' , "id" => "input_inscricao_municipal", "maxlength"=>50]) !!}
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div size="7" class="inputbox col-md-7">
                            <div class="form-group">
                                {!! Form::label('','E-mail') !!} <span style="color: #ff0500;">*</span>
                                <div class="input-group">
                                    <div class="input-group-addon"><i class="fa fa-envelope"></i></div>
                                    {!! Form::text('cont_email', ($acao=='edit'?$MRANfConfiguracoes->cont_email:null), ['class' => 'form-control' , "id" => "input_cont_email", "maxlength"=>200]) !!}
                                </div>
                            </div>
                        </div>
                        <div size="5" class="inputbox col-md-5">
                            <div class="form-group">
                                {!! Form::label('','Telefone') !!} <span style="color: #ff0500;">*</span>
                                <div class="input-group">
                                    <div class="input-group-addon"><i class="fa fa-phone"></i></div>
                                    {!! Form::text('cont_telefone', ($acao=='edit'?$MRANfConfiguracoes->cont_telefone:null), ['class' => 'form-control telefone',"placeholder"=>"(__) ____-____", "id" => "input_cont_telefone", "maxlength"=>200]) !!}
                                </div>
                            </div>
                        </div>
                       
                        {{-- <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','Status') !!} <span style="color: #ff0500;">*</span>
                                {!! Form::select('status', \App\Http\Controllers\MRA\MRAListas::Get_options_status_ai(), ($acao=='edit'?$MRANfConfiguracoes->status:null), ['class' => 'form-control select_single_no_trigger', "id" => "input_status"]) !!}
                            </div>
                        </div> --}}

                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','Optante Simples Nacional?') !!} <span style="color: #ff0500;">*</span>
                                {!! Form::select('optante_simples_nacional', ['0'=>'Não', '1'=>'Sim'], ($acao=='edit'?$MRANfConfiguracoes->optante_simples_nacional:null), ['class' => 'form-control select_single_no_trigger', 'data-live-search' => 'true', "dropdown-menu-right"=>"", "id" => "input_optante_simples_nacional"]) !!}
                            </div>
                        </div>
                        <div size="4" class="inputbox col-md-4">
                            <div class="form-group">
                                {!! Form::label('','Regime Tributário') !!} <span style="color: #ff0500;">*</span>
                                {!! Form::select('regime_tributario', \App\Http\Controllers\MRA\RNfListasTsController::Get_options_regime_tributario(), ($acao=='edit'?$MRANfConfiguracoes->regime_tributario:null), ['class' => 'form-control select_single_no_trigger', 'data-live-search' => 'true', "dropdown-menu-right"=>"", "id" => "input_regime_tributario"]) !!}
                            </div>
                        </div>
                        <div size="5" class="inputbox col-md-5">
                            <div class="form-group">
                                {!! Form::label('','Regime Tributário Especial') !!} <span style="color: #ff0500;">*</span>
                                {!! Form::select('regime_tributario_especial', \App\Http\Controllers\MRA\RNfListasTsController::Get_options_regime_tributario_especial(), ($acao=='edit'?$MRANfConfiguracoes->regime_tributario_especial:null), ['class' => 'form-control select_single_no_trigger', 'data-live-search' => 'true', "dropdown-menu-right"=>"", "id" => "input_regime_tributario_especial"]) !!}
                            </div>
                        </div>

                        <div size="12" class="inputbox col-md-12">
                            <div class="form-group">
                                {!! Form::label('','CNAE Fiscal') !!}
                                {!! Form::select('cnae_fiscal', \App\Http\Controllers\MRA\RNfListasTsController::Get_options_nf_cnae_23(), ($acao=='edit'?$MRANfConfiguracoes->cnae_fiscal:null), ['class' => 'form-control select_single_no_trigger bootstrap-select-st2', 'data-live-search' => 'true', "dropdown-menu-right"=>"", "id" => "input_cnae_fiscal"]) !!}
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
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','CEP') !!} <span style="color: #ff0500;">*</span>
                                <div class="input-group mb_15p">
                                    {!! Form::text('end_cep', ($acao=='edit'?$MRANfConfiguracoes->end_cep:null), ['class' => 'form-control cep_v2', "placeholder"=>"_____-___", "id" => "input_end_cep", "maxlength"=>50]) !!}
                                    <span class="input-group-btn">
                                        <button class="btn btn-primary" type="button" onclick="javascript:$('#input_end_cep').trigger('change');"><i class="glyphicon glyphicon-refresh"></i></button>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','Tipo de Logradouro') !!} <span style="color: #ff0500;">*</span>
                                {!! Form::select('end_tipo_logradouro', \App\Http\Controllers\MRA\RNfListasTsController::Get_options_tipo_de_logradouro(), ($acao=='edit'?$MRANfConfiguracoes->end_tipo_logradouro:null), ['class' => 'form-control select_single_no_trigger', 'data-live-search' => 'true', "id" => "input_end_tipo_logradouro"]) !!}
                            </div>
                        </div>
                        <div size="6" class="inputbox col-md-6">
                            <div class="form-group">
                                {!! Form::label('','Logradouro / Rua') !!} <span style="color: #ff0500;">*</span>
                                {!! Form::text('end_rua', ($acao=='edit'?$MRANfConfiguracoes->end_rua:null), ['class' => 'form-control' , "id" => "input_end_rua", "maxlength"=>200]) !!}
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div size="2" class="inputbox col-md-2">
                            <div class="form-group">
                                {!! Form::label('','Número') !!} <span style="color: #ff0500;">*</span>
                                {!! Form::text('end_numero', ($acao=='edit'?$MRANfConfiguracoes->end_numero:null), ['class' => 'form-control' , "id" => "input_end_numero", "maxlength"=>200]) !!}
                            </div>
                        </div>
                        <div size="5" class="inputbox col-md-5">
                            <div class="form-group">
                                {!! Form::label('','Bairro') !!} <span style="color: #ff0500;">*</span>
                                {!! Form::text('end_bairro', ($acao=='edit'?$MRANfConfiguracoes->end_bairro:null), ['class' => 'form-control' , "id" => "input_end_bairro", "maxlength"=>200]) !!}
                            </div>
                        </div>
                        <div size="5" class="inputbox col-md-5">
                            <div class="form-group">
                                {!! Form::label('','Complemento') !!} <span style="color: #ff0500;">*</span>
                                {!! Form::text('end_complemento', ($acao=='edit'?$MRANfConfiguracoes->end_complemento:null), ['class' => 'form-control' , "id" => "input_end_complemento", "maxlength"=>200]) !!}
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','Estado') !!} <span style="color: #ff0500;">*</span>
                                {!! Form::select('end_estado', $estados, ($acao=='edit'?$MRANfConfiguracoes->end_estado:null), ['class' => 'form-control select_single_no_trigger', 'data-live-search' => 'true', "id" => "input_end_estado"]) !!}
                            </div>
                        </div>
                        <div size="4" class="inputbox col-md-4">
                            <div class="form-group">
                                {!! Form::label('','Cidade') !!} <span style="color: #ff0500;">*</span>
                                {!! Form::select('end_cidade', [], ($acao=='edit'?$MRANfConfiguracoes->end_cidade:null), ['class' => 'form-control select_single_no_trigger', 'data-live-search' => 'true', "id" => "input_end_cidade", '_value'=>($acao=='edit'?$MRANfConfiguracoes->end_cidade:null), "maxlength"=>50, 'placeholder'=>'Selecione o Estado']) !!}
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
            </div>
        </div>
    </section>
    <section class="content">
        <div class="box" style="margin-bottom: 0px; margin-top: -25px">
            <div class="box-body" style="margin-top:0px;">
                <div size="12" class="inputbox col-md-12">
                    <h2 class="page-header" style="font-size:20px;">
                        <i class="glyphicon glyphicon-th-large"></i> Configurações
                    </h2>
                </div>

                <div size="12" class="inputbox col-md-12">
                    <div class="row">
                        <div size="4" class="inputbox col-md-4">
                            <div class="form-group">
                                {!! Form::label('','Certificado Digital') !!} <span style="color: #ff0500;">{{(isset($MRANfConfiguracoes->certificado_id) && !empty($MRANfConfiguracoes->certificado_id)) ? '' : '*'}}</span>
                                <div class="input-group">
                                    <div class="input-group-addon"><i class="fa fa-file"></i></div>
                                    {!! Form::file('certificado_digital', ($acao=='edit'?$MRANfConfiguracoes->certificado_digital:null), ['class' => 'form-control', "id" => "input_certificado_digital"]) !!}
                                </div>
                            </div>
                        </div>
                        <div size="4" class="inputbox col-md-4">
                            <div class="form-group">
                                {!! Form::label('','Senha') !!} <span style="color: #ff0500;">{{(isset($MRANfConfiguracoes->certificado_id) && !empty($MRANfConfiguracoes->certificado_id)) ? '' : '*'}}</span>
                                <div class="input-group">
                                    <div class="input-group-addon"><i class="fa fa-key"></i></div>
                                    {!! Form::text('senha_certificado', null, ['class' => 'form-control', "id" => "input_senha_certificado"]) !!}
                                </div>
                            </div>
                        </div>
                        <div size="4" class="inputbox col-md-4">
                            <div class="form-group">
                                {!! Form::label('','Ambiente') !!} <span style="color: #ff0500;">*</span>
                                {!! Form::select('producao', ['0'=>'Homologação', '1'=>'Produção'], ($acao=='edit'?$MRANfConfiguracoes->producao:null), ['class' => 'form-control', "id" => "input_producao", '_value'=>($acao=='edit'?$MRANfConfiguracoes->producao:null)]) !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="content">
        <div class="box" style="margin-top: -25px">
            <div class="box-body">
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
            // :: CNPJ
            $("#input_cnpj").on('change', async function(){
                let _this = $(this);
                if(_this.attr('af') != undefined && _this.attr('af')=='true'){ return false; }
                _this.attr('af','true');
                setTimeout(function(){ _this.attr('af','false'); },1000);  // Fix*
                let cnpj = await RA.consulta.cnpj(_this.val());
                if(cnpj!=undefined){
                    $("#input_razao_social").val((cnpj.nome!=undefined)?cnpj.nome.toUpperCase():'');
                    $("#input_nome_fantasia").val((cnpj.fantasia!=undefined)?cnpj.fantasia.toUpperCase():'');
                    $("#input_cont_email").val((cnpj.email!=undefined)?cnpj.email:'');
                    $("#input_cont_telefone").val((cnpj.telefone!=undefined)?cnpj.telefone:'');
                    $("#input_status").val((cnpj.situacao!=undefined && cnpj.situacao.toUpperCase()=='ATIVA')?1:'').trigger('change');
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
