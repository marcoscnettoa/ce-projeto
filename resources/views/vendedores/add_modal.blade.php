@php
    $isPublic   = 0;
    $controller = get_class(\Request::route()->getController());
@endphp
<section class="content-header Vendedores_add">
    <div id="myModal-success-errors" style="display:none;"></div>
    <h1>Vendedores</h1>
</section>
<section class="content Vendedores_add">
    <div class="box">
        {!! Form::open(['url' => "vendedores", 'method' => 'post', 'novalidate'=> true, 'enctype' => 'multipart/form-data', 'accept-charset' => 'utf-8', 'id' => 'form_add_vendedores','class'=>'form-modal-create form_add']) !!}
        {!! Form::hidden('modal-create-edit', 1) !!}
        <div class="box-body" id="div_vendedores" style="margin-top:0; padding-top:0;">
            <div size="12" class="inputbox col-md-12">
                <h2 class="page-header" style="font-size:20px;">
                    <i class="glyphicon glyphicon-th-large"></i> Dados do Vendedor
                </h2>
            </div>
            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','Código do Vendedor') !!}
                    {!! Form::number('codigo_do_vendedor', null, ['class' => 'form-control' , "id" => "input_codigo_do_vendedor"]) !!}
                </div>
            </div>
            <div size="7" class="inputbox col-md-7">
                <div class="form-group">
                    {!! Form::label('','Nome do Vendedor') !!}
                    {!! Form::text('nome_do_vendedor', null, ['class' => 'form-control' , "id" => "input_nome_do_vendedor"]) !!}
                </div>
            </div>
            <div size="3" class="inputbox col-md-3">
                <div class="form-group">
                    {!! Form::label('','CPF') !!}
                    {!! Form::text('cpf_do_vendedor', null, ['class' => 'form-control cpf' , "id" => "input_cpf_do_vendedor"]) !!}
                </div>
            </div>
            <div size="4" class="inputbox col-md-4">
                <div class="form-group">
                    {!! Form::label('','E-mail') !!}
                    {!! Form::text('e_mail_do_vendedor', null, ['class' => 'form-control' , "id" => "input_e_mail_do_vendedor"]) !!}
                </div>
            </div>
            <div size="3" class="inputbox col-md-3">
                <div class="form-group">
                    {!! Form::label('','Telefone') !!}
                    <div class='input-group'>
                        <div class='input-group-addon'>
                            <i class='fa fa-phone'></i>
                        </div>
                        {!! Form::text('telefone_1', null, ['class' => 'form-control telefone' , "id" => "input_telefone_1"]) !!}
                    </div>
                </div>
            </div>
            <div size="12" class="inputbox col-md-12">
                <div class="form-group">
                    {!! Form::label('','Observação') !!}
                    {!! Form::text('observacao', null, ['class' => 'form-control' , "id" => "input_observacao"]) !!}
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
                    {!! Form::text('cep_do_vendedor', null, ['class' => 'form-control cep' , "id" => "input_cep_do_vendedor"]) !!}
                </div>
            </div>
            <div size="7" class="inputbox col-md-7">
                <div class="form-group">
                    {!! Form::label('','Endereço') !!}
                    {!! Form::text('endereco_d_vendedor', null, ['class' => 'form-control' , "id" => "input_endereco_d_vendedor"]) !!}
                </div>
            </div>
            <div size="2" class="inputbox col-md-2">
                <div class="form-group">
                    {!! Form::label('','Número') !!}
                    {!! Form::text('numero_do_endereco_do_vendedor', null, ['class' => 'form-control' , "id" => "input_numero_do_endereco_do_vendedor"]) !!}
                </div>
            </div>
            <div size="8" class="inputbox col-md-8">
                <div class="form-group">
                    {!! Form::label('','Complemento') !!}
                    {!! Form::text('complemento_do_vendedor', null, ['class' => 'form-control' , "id" => "input_complemento_do_vendedor"]) !!}
                </div>
            </div>
            <div size="4" class="inputbox col-md-4">
                <div class="form-group">
                    {!! Form::label('','Bairro') !!}
                    {!! Form::text('bairro_do_vendedor', null, ['class' => 'form-control' , "id" => "input_bairro_do_vendedor"]) !!}
                </div>
            </div>
            <div size="5" class="inputbox col-md-5">
                <div class="form-group">
                    {!! Form::label('','Cidade') !!}
                    {!! Form::text('cidade_do_vendedor', null, ['class' => 'form-control' , "id" => "input_cidade_do_vendedor"]) !!}
                </div>
            </div>
            <div size="4" class="inputbox col-md-4">
                <div class="form-group">
                    {!! Form::label('','Estado') !!}
                    {!! Form::text('estado_do_vendedor', null, ['class' => 'form-control' , "id" => "input_estado_do_vendedor"]) !!}
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
                        <a href="javascript:$('#myModal_CE').modal('hide');" class="btn btn-default form-group-btn-add-cancelar"><i class="glyphicon glyphicon-remove-circle"></i> Cancelar</a>
                    @endif
                    @if(App\Models\Permissions::permissaoUsuario(\Auth::user(), "$controller@store") OR $isPublic)
                        <button type="submit" class="btn btn-default right form-group-btn-add-cadastrar">
                            <span class="glyphicon glyphicon-plus"></span> Cadastrar
                        </button>
                    @endif
                </div>
            </div>
            {!! Form::close() !!}
        </div>
    </div>
</section>
<script type="text/javascript">
</script>
