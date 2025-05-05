@php
    $acao       = ((isset($MRAFExtratoBancario) and !is_null($MRAFExtratoBancario))?'edit':'add');
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
        <h1>Financeiro - Extrato Bancários</h1>
        {{--
        @if(!$isPublic)
            <ol class="breadcrumb">
                <li><a href="{{ URL('/') }}">Home</a></li>
                <li><a href="{{ URL('/') }}/xxxxxxxxxxxxxxx">xxxxxxxxxxxxxxx</a></li>
                <li class="active">xxxxxxxxxxxxxxx</li>
            </ol>
        @endif
        --}}
    </section>
    {!! Form::open(['url' => "mra_fluxo_financeiro/mra_f_extrato_bancario".($acao=='edit'?'/'.$MRAFExtratoBancario->id:''), 'method' => ($acao=='edit'?'put':'post'), 'novalidate'=> true, 'enctype' => 'multipart/form-data', 'accept-charset' => 'utf-8', 'id' => ($acao=='edit'?'form_edit':'form_add').'_mra_f_extrato_bancario']) !!}
    <section class="content">
        <div class="box">
            @if($acao=='edit')
                {!! Form::hidden('id', $MRAFExtratoBancario->id) !!}
                @if(\App\Models\Permissions::permissaoUsuario(\Auth::user(), "$controller@destroy"))
                    <div class="row" style="position: absolute; right: 0; padding: 5px; z-index: 100;">
                        <div class="col-md-12">
                            <a href="javascript:void(0);" type="button" class="btn btn-danger excluir-auto-confirma" modulo="mra_fluxo_financeiro/mra_f_extrato_bancario" modulo_id="{{$MRAFExtratoBancario->id}}"><i class="glyphicon glyphicon-trash"></i></a>
                        </div>
                    </div>
                @endif
            @endif
            <div class="box-body" id="div_mra_f_extrato_bancario">
                <div size="12" class="inputbox col-md-12">
                    <h2 class="page-header" style="font-size:20px;">
                        <i class="glyphicon glyphicon-th-large"></i> Transferências
                    </h2>
                </div>
                <div size="12" class="inputbox col-md-12">
                    @if($acao=='edit')
                    <div class="row">
                        <div size="3" class="inputbox col-md-3">
                            <div class="form-group">
                                {!! Form::label('','Data da Movimentação') !!}
                                {!! Form::text('create_at', date('d/m/Y H:i',strtotime($MRAFExtratoBancario->create_at)), ['class' => 'form-control', "id" => "input_nome", "disabled"=>true]) !!}
                            </div>
                        </div>
                    </div>
                    @endif
                    <div class="row">
                        <div size="12" class="inputbox col-md-12">
                            <div class="form-group">
                                {!! Form::label('','Descrição') !!} <span style="color: #ff0500;">*</span>
                                {!! Form::text('descricao', ($acao=='edit'?$MRAFExtratoBancario->descricao:null), ['class' => 'form-control', "id" => "input_descricao", "maxlength"=>200]) !!}
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div size="4" class="inputbox col-md-4">
                            <div class="form-group">
                                {!! Form::label('','Conta Origem') !!} <span style="color: #ff0500;">*</span>
                                {!! Form::select('mra_f_conta_ori_id', \App\Models\MRAFExtratoBancario::Get_ContasBancarias_options(), ($acao=='edit'?$MRAFExtratoBancario->mra_f_conta_ori_id:null), ['class' => 'form-control select_single_no_trigger', 'data-live-search' => 'true', "id" => "input_mra_f_conta_ori_id"]) !!}
                            </div>
                        </div>
                        <div size="4" class="inputbox col-md-4">
                            <div class="form-group">
                                {!! Form::label('','Conta Destino') !!} <span style="color: #ff0500;">*</span>
                                {!! Form::select('mra_f_conta_des_id', \App\Models\MRAFExtratoBancario::Get_ContasBancarias_options(), ($acao=='edit'?$MRAFExtratoBancario->mra_f_conta_des_id:null), ['class' => 'form-control select_single_no_trigger', 'data-live-search' => 'true', "id" => "input_mra_f_conta_des_id"]) !!}
                            </div>
                        </div>
                        <div size="4" class="inputbox col-md-4">
                            <div class="form-group">
                                {!! Form::label('','Valor') !!} <span style="color: #ff0500;">*</span>
                                {!! Form::text('valor', ($acao=='edit'?\App\Helper\Helper::H_Decimal_DB_ptBR($MRAFExtratoBancario->valor):null), ['class' => 'form-control money_v2', "placeholder" => "0,00", "id"=>"input_valor"]) !!}
                            </div>
                        </div>
                    </div>
                    {{--<div class="row">
                        <div size="2" class="inputbox col-md-2">
                            <div class="form-group">
                                {!! Form::label('','Status') !!}
                                {!! Form::select('status', \App\Http\Controllers\MRA\MRAListas::Get_options_status_ai([""]), ($acao=='edit'?$MRAFExtratoBancario->status:null), ['class' => 'form-control select_single_no_trigger' , "id" => "input_status"]) !!}
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div size="4" class="inputbox col-md-4">
                            <div class="form-group">
                                {!! Form::label('','Tipo de Conta') !!} <span style="color: #ff0500;">*</span>
                                {!! Form::select('tipo_conta', \App\Models\MRAFExtratoBancario::Get_options_tipos_de_conta(), ($acao=='edit'?$MRAFExtratoBancario->tipo_conta:null), ['class' => 'form-control select_single_no_trigger' , "id" => "input_tipo_conta"]) !!}
                            </div>
                        </div>
                        <div size="4" class="inputbox col-md-4">
                            <div class="form-group">
                                {!! Form::label('','Banco') !!} <span style="color: #ff0500;">*</span>
                                {!! Form::select('mra_f_bancos_id', \App\Models\MRAFBancos::Get_Bancos_options(), ($acao=='edit'?$MRAFExtratoBancario->mra_f_bancos_id:null), ['class' => 'form-control select_single_no_trigger' , "id" => "input_mra_f_bancos_id"]) !!}
                            </div>
                        </div>
                        <div size="4" class="inputbox col-md-4">
                            <div class="form-group">
                                {!! Form::label('','Nome da Conta Bancária') !!} <span style="color: #ff0500;">*</span>
                                {!! Form::text('nome', ($acao=='edit'?$MRAFExtratoBancario->nome:null), ['class' => 'form-control' , "id" => "input_nome", "maxlength"=>100]) !!}
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div size="4" class="inputbox col-md-4">
                            <div class="form-group">
                                {!! Form::label('','Agência') !!} <span style="color: #ff0500;">*</span>
                                {!! Form::text('agencia', ($acao=='edit'?$MRAFExtratoBancario->agencia:null), ['class' => 'form-control' , "id" => "input_agencia", "maxlength"=>200]) !!}
                            </div>
                        </div>
                        <div size="4" class="inputbox col-md-4">
                            <div class="form-group">
                                {!! Form::label('','Número da Conta') !!} <span style="color: #ff0500;">*</span>
                                {!! Form::text('conta', ($acao=='edit'?$MRAFExtratoBancario->conta:null), ['class' => 'form-control' , "id" => "input_conta", "maxlength"=>200]) !!}
                            </div>
                        </div>
                        <div size="2" class="inputbox col-md-2">
                            <div class="form-group">
                                {!! Form::label('','Dígito') !!} <span style="color: #ff0500;">*</span>
                                {!! Form::text('digito', ($acao=='edit'?$MRAFExtratoBancario->digito:null), ['class' => 'form-control' , "id" => "input_digito", "maxlength"=>200]) !!}
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div size="4" class="inputbox col-md-4">
                            <div class="form-group">
                                {!! Form::label('','Data do Saldo Inicial') !!}
                                <div class="input-group">
                                    <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                                    {!! Form::text('data_inicial', ($acao=='edit'?\App\Helper\Helper::H_Data_DB_ptBR($MRAFExtratoBancario->data_inicial):null), ['autocomplete' =>'off', 'class' => 'form-control componenteData_v2', "placeholder"=>"__/__/____", "id" => "input_data_inicial"]) !!}
                                </div>
                            </div>
                        </div>
                        <div size="4" class="inputbox col-md-4">
                            <div class="form-group">
                                {!! Form::label('','Valor Inicial') !!}
                                {!! Form::text('valor_inicial', ($acao=='edit'?\App\Helper\Helper::H_Decimal_DB_ptBR($MRAFExtratoBancario->valor_inicial):null), ['class' => 'form-control money_v2', "placeholder" => "0,00"]) !!}
                            </div>
                        </div>
                        <div size="4" class="inputbox col-md-4">
                            <div class="form-group">
                                {!! Form::label('','Valor Atual ( Automático )') !!}
                                {!! Form::text('valor_atual', ($acao=='edit'?\App\Helper\Helper::H_Decimal_DB_ptBR($MRAFExtratoBancario->valor_atual):null), ['class' => 'form-control money_v2', "placeholder" => "0,00"]) !!}
                            </div>
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

        </script>
    @endsection

@endsection
