@php

    if(env('FILESYSTEM_DRIVER') == 's3'){
        $fileurlbase = env('URLS3') . '/' . env('FILEKEY') . '/';
    }else{
        $fileurlbase = env('APP_URL') . '/';
    }

    $td_colunas_fields_key  = [];
    $td_colunas_fields      = json_decode('[{"name":"Documentos","type":"file","disabled":false,"note":null,"value":null,"actions_advanced":true,"multiple":true,"className":"Documentos","cleanName":"documentos"}]',true);
    foreach($td_colunas_fields as $tcf){ $td_colunas_fields_key[$tcf['cleanName']] = $tcf; }
    // :: Buscando Opções de Quadro
    $quadros                = [];
    $quadros_options        = [];

    // ! Tipo -> Seleção
    if($td_colunas_fields_key[$kanban_field]['type'] == 'selectbox'){
        $Get_Options            = 'Get_options_'.$kanban_field;
        if(method_exists($controller_model,$Get_Options)){
            $quadros_options    = $controller_model::$Get_Options();
        }

    // ! Tipo -> Relacionamento
    }elseif($td_colunas_fields_key[$kanban_field]['type'] == 'select'){
        $Get_relationship           = "\\App\\Models\\".$td_colunas_fields_key[$kanban_field]['relationshipReferenceClassName'];
        $Get_relationship_reference = $td_colunas_fields_key[$kanban_field]['relationshipReferenceCleanName'];
        $Get_Options                = $Get_relationship::kanban_list(10000, $Get_relationship_reference);
        $quadros_options            = $Get_Options->toArray();
    }

    foreach($quadros_options as $key => $qo){
        $quadros[$key]['id']     = (string) $key;
        $quadros[$key]['title']  = (in_array($key,[0,''])?'Início':$qo);
        $quadros[$key]['item']   = [];
    }

    foreach($r_clientes_documentos as $key => $list){

        // ! Verifica se o 'Registro' possuí 'Status' caso contrário = (null,0,'')
        // atribuí para o 'Início' posição = 0
        $list->$kanban_field = (in_array($list->$kanban_field,[null,0,''])?0:$list->$kanban_field);

        $quadros[$list->$kanban_field]['item'][$key]['column'] = (string) $kanban_field;
        $quadros[$list->$kanban_field]['item'][$key]['id']     = (string) $list->id;
        $quadros[$list->$kanban_field]['item'][$key]['title']  = '';

        if(is_array($td_colunas_fields)){
            foreach($td_colunas_fields as $tcf){
                if(
                    (isset($tcf['hidden_view']) && $tcf['hidden_view']) ||
                    ($td_colunas_fields_key[$kanban_field]['type'] == 'select' and $tcf['cleanName'] == $kanban_field)
                ){ continue; }

                try {
                    // :: Texto
                    if(isset($tcf['type']) and $tcf['type'] == 'text'){
                        $cleanName = $tcf['cleanName'];
                        $quadros[$list->$kanban_field]['item'][$key]['title'] .= '
                            <div class="kanban-box-columns">
                                <div class="kanban-bc-campo">'.$tcf['name'].':</div>
                                <div class="kanban-bc-campo-value">'.(!empty($list->$cleanName)?$list->$cleanName:'---').'</div>
                            </div>';
                    // :: Relacionamento
                    }elseif(isset($tcf['type']) and $tcf['type'] == 'select' and isset($tcf['className']) and isset($tcf['relationshipReferenceCleanName'])){
                        $cleanName = $tcf['cleanName'];
                        $className = $tcf['className'];
                        $relationshipReferenceCleanName = $tcf['relationshipReferenceCleanName'];
                        $quadros[$list->$kanban_field]['item'][$key]['title'] .= '
                            <div class="kanban-box-columns">
                                <div class="kanban-bc-campo">'.$tcf['name'].':</div>
                                <div class="kanban-bc-campo-value">'.($list->$className?$list->$className->$relationshipReferenceCleanName:'---').'</div>
                            </div>';
                    // :: Caixa de Seleção
                    }elseif(isset($tcf['type']) and $tcf['type'] == 'selectbox' and isset($tcf['cleanName'])){
                        $cleanName      = $tcf['cleanName'];
                        $Get_Options    = 'Get_'.$cleanName;
                        $quadros[$list->$kanban_field]['item'][$key]['title'] .= '
                            <div class="kanban-box-columns">
                                <div class="kanban-bc-campo">'.$tcf['name'].':</div>
                                <div class="kanban-bc-campo-value">'.(!empty($list->$Get_Options())?$list->$Get_Options():'---').'</div>
                            </div>';
                    // :: Data
                    }elseif(isset($tcf['type']) and $tcf['type'] == 'data'){
                        $cleanName      = $tcf['cleanName'];
                        $quadros[$list->$kanban_field]['item'][$key]['title'] .= '
                            <div class="kanban-box-columns">
                                <div class="kanban-bc-campo">'.$tcf['name'].':</div>
                                <div class="kanban-bc-campo-value">'.(!empty($list->$cleanName)?$list->$cleanName:'---').'</div>
                            </div>';

                    // :: Data e Hora
                    }elseif(isset($tcf['type']) and $tcf['type'] == 'dataehora'){
                        $cleanName      = $tcf['cleanName'];
                        $quadros[$list->$kanban_field]['item'][$key]['title'] .= '
                            <div class="kanban-box-columns">
                                <div class="kanban-bc-campo">'.$tcf['name'].':</div>
                                <div class="kanban-bc-campo-value">'.(!empty($list->$cleanName)?$list->$cleanName:'---').'</div>
                            </div>';

                    // :: Hora
                    }elseif(isset($tcf['type']) and $tcf['type'] == 'hora'){
                        $cleanName      = $tcf['cleanName'];
                        $quadros[$list->$kanban_field]['item'][$key]['title'] .= '
                            <div class="kanban-box-columns">
                                <div class="kanban-bc-campo">'.$tcf['name'].':</div>
                                <div class="kanban-bc-campo-value">'.(!empty($list->$cleanName)?$list->$cleanName:'---').'</div>
                            </div>';

                    // :: Data / Date - Pop Up
                    }elseif(isset($tcf['type']) and $tcf['type'] == 'date'){
                        $cleanName      = $tcf['cleanName'];
                        $quadros[$list->$kanban_field]['item'][$key]['title'] .= '
                            <div class="kanban-box-columns">
                                <div class="kanban-bc-campo">'.$tcf['name'].':</div>
                                <div class="kanban-bc-campo-value">'.(!empty($list->$cleanName)?date("d/m/Y", strtotime($list->$cleanName)):'---').'</div>
                            </div>';

                    // :: Data e Hora / Datetime - Pop Up
                    }elseif(isset($tcf['type']) and $tcf['type'] == 'datetime'){
                        $cleanName      = $tcf['cleanName'];
                        $quadros[$list->$kanban_field]['item'][$key]['title'] .= '
                            <div class="kanban-box-columns">
                                <div class="kanban-bc-campo">'.$tcf['name'].':</div>
                                <div class="kanban-bc-campo-value">'.((!empty($list->$cleanName) and (bool)strtotime($list->$cleanName))?date("d/m/Y H:i", strtotime($list->$cleanName)):'---').'</div>
                            </div>';

                    // :: Data e Hora Automático / Dataehoraauto
                    }elseif(isset($tcf['type']) and $tcf['type'] == 'dataehoraauto'){
                        $cleanName      = $tcf['cleanName'];
                        $quadros[$list->$kanban_field]['item'][$key]['title'] .= '
                            <div class="kanban-box-columns">
                                <div class="kanban-bc-campo">'.$tcf['name'].':</div>
                                <div class="kanban-bc-campo-value">'.((!empty($list->$cleanName) and (bool)strtotime($list->$cleanName))?date("d/m/Y H:i", strtotime($list->$cleanName)):'---').'</div>
                            </div>';

                    // :: CNPJ
                    }elseif(isset($tcf['type']) and $tcf['type'] == 'cnpj'){
                        $cleanName      = $tcf['cleanName'];
                        $quadros[$list->$kanban_field]['item'][$key]['title'] .= '
                            <div class="kanban-box-columns">
                                <div class="kanban-bc-campo">'.$tcf['name'].':</div>
                                <div class="kanban-bc-campo-value">'.(!empty($list->$cleanName)?$list->$cleanName:'---').'</div>
                            </div>';

                    // :: CPF
                    }elseif(isset($tcf['type']) and $tcf['type'] == 'cpf'){
                        $cleanName      = $tcf['cleanName'];
                        $quadros[$list->$kanban_field]['item'][$key]['title'] .= '
                            <div class="kanban-box-columns">
                                <div class="kanban-bc-campo">'.$tcf['name'].':</div>
                                <div class="kanban-bc-campo-value">'.(!empty($list->$cleanName)?$list->$cleanName:'---').'</div>
                            </div>';

                    // :: E-mail
                    }elseif(isset($tcf['type']) and $tcf['type'] == 'email'){
                        $cleanName      = $tcf['cleanName'];
                        $quadros[$list->$kanban_field]['item'][$key]['title'] .= '
                            <div class="kanban-box-columns">
                                <div class="kanban-bc-campo">'.$tcf['name'].':</div>
                                <div class="kanban-bc-campo-value">'.(!empty($list->$cleanName)?$list->$cleanName:'---').'</div>
                            </div>';

                    // :: Dinheiro R$ / Money
                    }elseif(isset($tcf['type']) and $tcf['type'] == 'money'){
                        $cleanName      = $tcf['cleanName'];
                        $quadros[$list->$kanban_field]['item'][$key]['title'] .= '
                            <div class="kanban-box-columns">
                                <div class="kanban-bc-campo">'.$tcf['name'].':</div>
                                <div class="kanban-bc-campo-value">'.(!empty($list->$cleanName)?number_format($list->$cleanName,2,'.',''):'---').'</div>
                            </div>';

                    // :: Cálculo / calculo
                    }elseif(isset($tcf['type']) and $tcf['type'] == 'calculo'){
                        $cleanName      = $tcf['cleanName'];
                        $quadros[$list->$kanban_field]['item'][$key]['title'] .= '
                            <div class="kanban-box-columns">
                                <div class="kanban-bc-campo">'.$tcf['name'].':</div>
                                <div class="kanban-bc-campo-value">'.(!empty($list->$cleanName)?number_format($list->$cleanName,2,'.',''):'---').'</div>
                            </div>';

                    // :: Numérico / number
                    }elseif(isset($tcf['type']) and $tcf['type'] == 'number'){
                        $cleanName      = $tcf['cleanName'];
                        $quadros[$list->$kanban_field]['item'][$key]['title'] .= '
                            <div class="kanban-box-columns">
                                <div class="kanban-bc-campo">'.$tcf['name'].':</div>
                                <div class="kanban-bc-campo-value">'.(!empty($list->$cleanName)?$list->$cleanName:'---').'</div>
                            </div>';

                    // :: Cep
                    }elseif(isset($tcf['type']) and $tcf['type'] == 'cep'){
                        $cleanName      = $tcf['cleanName'];
                        $quadros[$list->$kanban_field]['item'][$key]['title'] .= '
                            <div class="kanban-box-columns">
                                <div class="kanban-bc-campo">'.$tcf['name'].':</div>
                                <div class="kanban-bc-campo-value">'.(!empty($list->$cleanName)?$list->$cleanName:'---').'</div>
                            </div>';

                    // :: Upload / file
                    }elseif(isset($tcf['type']) and $tcf['type'] == 'file'){
                        $cleanName         = $tcf['cleanName'];
                        $campo_value       = '---';
                        if(!empty($list->$cleanName) and count(explode(".", $list->$cleanName)) >= 2){
                            $campo_href    = (in_array(explode(".", $list->$cleanName)[1], array("jpg", "jpeg", "gif", "png", "bmp", "mp4", "pdf", "doc", "docx", "rar", "zip", "txt", "7zip", "csv", "xls", "xlsx")) ? $fileurlbase . "images/" . $list->$cleanName : "javascript:void(0);");
                            $campo_img_src = (in_array(explode(".", $list->$cleanName)[1], array("mp4", "pdf", "doc", "docx", "rar", "zip", "txt", "7zip", "csv", "xls", "xlsx")) ? explode(".", $list->$cleanName)[1] . "-icon.png" : $fileurlbase . "images/" . $list->$cleanName);
                            $campo_value   = '<a class="fancybox" rel="gallery1" target="_blank" href="'.$campo_href.'"><img src="'.$campo_img_src.'" style="max-width: 200px; max-height: 70px; height: auto; width: auto; margin-top: 2px;" /></a>';
                        }
                        $quadros[$list->$kanban_field]['item'][$key]['title'] .= '
                            <div class="kanban-box-columns">
                                <div class="kanban-bc-campo">'.$tcf['name'].':</div>
                                <div class="kanban-bc-campo-value">'.$campo_value.'</div>
                            </div>';
                    }
                }catch(\Exception $e){ }
            }
        }

        $quadros[$list->$kanban_field]['item'][$key]['title']     .= '
            <div class="kanban-buttons text-right">
                <button modulo="r_clientes_documentos" modulo_id="'.$list->id.'" modulo_method="GET" modulo_url="r_clientes_documentos/'.$list->id.'/edit" modulo_redirect="true" class="btn btn-xs btn-default kanban-btn-edit"><i class="glyphicon glyphicon-edit" title="Editar"></i></button>
                <button modulo="r_clientes_documentos" modulo_id="'.$list->id.'" modulo_method="GET" modulo_url="r_clientes_documentos/'.$list->id.'/copy" modulo_redirect="true" class="btn btn-xs btn-default kanban-btn-copy"><i class="glyphicon glyphicon-copy" title="Duplicar linha"></i></button>
                <button modulo="r_clientes_documentos" modulo_id="'.$list->id.'" modulo_method="GET" modulo_url="r_clientes_documentos/'.$list->id.'" modulo_redirect="true" class="btn btn-xs btn-default kanban-btn-view"><i class="glyphicon glyphicon-eye-open" title="Visualizar"></i></button>
                <button modulo="r_clientes_documentos" modulo_id="'.$list->id.'" class="btn btn-xs btn-danger excluir-auto-confirma" style="float:none;margin: 0px;" title="Excluir"><i class="glyphicon glyphicon-trash"></i></button>
            </div>
        ';
    }
    $quadros = array_values($quadros);
    foreach($quadros as $key => $q){ $quadros[$key]['item'] = array_values($q['item']); }
    // - ::

    $boards_list = json_encode(array_values($quadros));
@endphp
<div class="form-group form-group-btn-index text-right">
    @if(\App\Models\Permissions::permissaoUsuario(\Auth::user(), "$controller@store") OR $isPublic)
        <a href="{{ URL('/') }}/r_clientes_documentos/create" class="btn btn-default form-group-btn-index-cadastrar"><i class="glyphicon glyphicon-plus"></i> Cadastrar</a>
    @endif
</div>
<div style="clear:both;"></div>
{{--
<div id="DEBUG" style="display:none;">
    {{ print_r($td_colunas_fields) }}
</div>
--}}
<section>
    <div id="app_kanban">
    </div>
</section>

@section('script')

    <script type="text/javascript">
        var kanban1 = new jKanban({
            element:'#app_kanban',
            addItemButton: false,
            gutter           : '15px',
            widthBoard       : '250px',
            responsivePercentage: false,
            itemAddOptions: {},
            itemHandleOptions: {},
            click            : function (el) {  },
            context          : function (el, event) {  },
            dropEl           : function (el, target, source, sibling) {
                //console.log('el: '+ el); console.log('target: '+ target); console.log('source: '+ source); console.log('sibling: '+ sibling);
                let item_id         = $(el).attr("data-eid");
                let item_column     = $(el).attr("data-column");
                let item_target_id  = $(target).closest('.kanban-board').attr("data-id");
                let item_source_id  = $(source).closest('.kanban-board').attr("data-id");
                // Verifico se está no mesmo Quadro
                if($(target).find('[data-eid=\"'+item_id+'\"]').length && $(source).find('[data-eid=\"'+item_id+'\"]').length){ return false; }
                //console.log('item_id: '+ item_id); console.log('item_column: '+ item_column); console.log('item_target_id: '+ item_target_id); console.log('item_source_id: '+ item_source_id);
                let data = {
                    '_method': 'PUT',
                    '_token': $("meta[name=\'csrf-token\']").attr('content'),
                    'id': item_id,
                    'update_kanban': 1,
                    'item_column': item_column,
                    'item_target_id': item_target_id,
                    'item_source_id': item_source_id
                }
                //console.log('---- data ----'); console.log(data);
                let ajax_return = false;
                $.ajax({
                    dataType: 'json',
                    type: 'POST',
                    async: false,
                    url: base+'/r_clientes_documentos/'+item_id,
                    data: data,
                    success: function(d){
                        if(d.ok != undefined){
                            ajax_return = true;
                        }
                    },
                    error: function(xhr, statusText, errorThrown){
                        d = xhr.responseJSON;
                        ajax_return = false;
                        console.log('KB Quadro | Error: ' + statusText,xhr);
                        if(d.error != undefined){ alert(d.error); }
                    }
                });
                return ajax_return;
            },
            dragendEl        : function (el) { },
            dragBoard        : function (el, source) { },
            dragendBoard     : function (el) { },
            buttonClick      : function(el, boardId) { },
            propagationHandlers: [],
            dragBoards       : false,
            dragItems        : true,
            boards           : {!! $boards_list !!}
        });

        RA.load.form_acoes($('.kanban-btn-edit'));
        RA.load.form_acoes($('.kanban-btn-copy'));
        RA.load.form_acoes($('.kanban-btn-view'));
        RA.onload();
    </script>

@endsection
