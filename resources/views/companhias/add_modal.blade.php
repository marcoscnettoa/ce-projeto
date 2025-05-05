@php
    $isPublic = 0;
    $controller = get_class(\Request::route()->getController());
@endphp
<section class="content-header Companhias_add">
    <div id="myModal-success-errors" style="display:none;"></div>
    <h1>Companhias</h1>
</section>
<section class="content Companhias_add">
<div class="box">
    {!! Form::open(['url' => "companhias", 'method' => 'post', 'novalidate'=> true, 'enctype' => 'multipart/form-data', 'accept-charset' => 'utf-8', 'id' => 'form_add_companhias','class'=>'form-modal-create form_add']) !!}
    {!! Form::hidden('modal-create-edit', 1) !!}
    <div class="box-body" id="div_companhias">
        <div size="12" class="inputbox col-md-12">
            <div class="form-group">
                {!! Form::label('','Companhia') !!}
                {!! Form::text('companhia', null, ['class' => 'form-control' , "id" => "input_companhia", "style" => "text-transform: uppercase;", "oninput" => "this.value = this.value.toUpperCase();"]) !!}
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
    </div>
    {!! Form::close() !!}
</div>
</section>
<script type="text/javascript">
</script>
