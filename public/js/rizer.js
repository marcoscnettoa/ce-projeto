var myModalCE           = null;
var myModalCEContent    = null;
function setup()
{
    $('.telefone').focusout(function(){
        var phone, element;
        element = $(this);
        element.unmask();
        phone = element.val().replace(/\D/g, '');
        if(phone.length > 10) {
            element.mask("(99) 99999-999?9");
        } else {
            element.mask("(99) 9999-9999?9");
        }
    }).trigger('focusout');

    $(".fancybox").fancybox({
        openEffect  : 'none',
        closeEffect : 'none'
    });

    $('.cep').mask('99999-999');

    $('.data').mask('99/99/9999');

    $('.hora').mask('99:99');

    $('.dataehora').mask('99/99/9999 99:99');

    $('.cpf').mask('999.999.999-99');

    $('.cnpj').mask('99.999.999/9999-99');

    $(".select_multiple").selectpicker({
        noneSelectedText : '',
        noneResultsText: 'Nenhum resultado encontrado'
    });

    $(".select_single").selectpicker({
        noneSelectedText : '',
        noneResultsText: 'Nenhum resultado encontrado'
    });

    $(".money").maskMoney({
        decimal: ".",
        thousands: ""
    });

    $('.chosen-select').chosen();

    $(".multiple_remove").click(function() {
        $(this).parent().parent().remove();
    });

    $(".grid_remove").click(function() {
        $(this).parent().parent().remove();
    });

    $(".input_remove").click(function() {
        $(this).parent().remove();
    });

    $('.componenteDataHora').datetimepicker({
        format: 'dd/mm/yyyy hh:ii',
        autoclose: true
    });

    $('.componenteData').datepicker({
        format: 'dd/mm/yyyy',
        language: 'pt-BR'
    });

    // # -
    $('.cep').focusout(function(){
        var form_parent = $(this).closest('form');
        var cep = $(this).val().replace(/\D/g, '');

        $.get('https://dashboard.xxxxrxxxapps.com/v1/cep/'+cep, function(data){
            form_parent.find("#input_cidade").val(data.localidade);
            form_parent.find("#input_estado").val(data.uf);
            form_parent.find("#input_bairro").val(data.bairro);
            form_parent.find("#input_endereco").val(data.logradouro);
            form_parent.find("#input_municipio").val(data.localidade);
            form_parent.find("#input_logradouro").val(data.logradouro);
            form_parent.find("#input_localidade").val(data.localidade);
            form_parent.find("#input_uf").val(data.uf);
        });
    });

    // # -
    $('.cnpj').focusout(function(){
        var form_parent = $(this).closest('form');
        var cnpj        = $(this).val().replace(/\D/g, '');

        $.get('https://dashboard.xxxxrxxxapps.com/v1/cnpj/'+cnpj, function(data){

            form_parent.find("#input_atividade_principal").val(data.atividade_principal);
            form_parent.find("#input_data_da_situacao").val(data.data_situacao);
            form_parent.find("#input_complemento").val(data.complemento);
            form_parent.find("#input_tipo").val(data.tipo);

            if (form_parent.find("#input_nome").val() == '') {
                form_parent.find("#input_nome").val(data.nome);
            }

            if (form_parent.find("#input_telefone").val() == '') {
                form_parent.find("#input_telefone").val(data.telefone);
            }

            if (form_parent.find("#input_e_mail").val() == '') {
                form_parent.find("#input_e_mail").val(data.email);
            }

            form_parent.find("#input_atividades_secundarias").val(data.atividades_secundarias);
            form_parent.find("#input_situacao").val(data.situacao);
            form_parent.find("#input_bairro").val(data.bairro);
            form_parent.find("#input_logradouro").val(data.logradouro);
            form_parent.find("#input_numero").val(data.numero);
            form_parent.find("#input_cep").val(data.cep);
            form_parent.find("#input_municipio").val(data.municipio);
            form_parent.find("#input_fantasia").val(data.fantasia);
            form_parent.find("#input_porte").val(data.porte);
            form_parent.find("#input_abertura").val(data.abertura);
            form_parent.find("#input_natureza_juridica").val(data.natureza_juridica);
            form_parent.find("#input_uf").val(data.uf);
            form_parent.find("#input_ultima_atualizacao").val(data.ultima_atualizacao);
            form_parent.find("#input_capital_social").val(data.capital_social);

        });
    });

    tinymce.init({
        selector: "textarea.tinymce",
        plugins: [
            "advlist autolink lists link image charmap print preview anchor",
            "searchreplace visualblocks code fullscreen",
            "insertdatetime media table contextmenu paste "
        ],
        plugin_preview_width : "900",
        toolbar: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image"
    });

    $("select").change(function(){
        if($(this).attr('no-trigger-change')!=undefined){ return false; }
        var form_parent = $(this).closest('form');

        var selfSelect  = this;

        var grid= false;

        if ($(this).attr('name').includes('grid')) {
            grid = true;
        }

        var controller_ajax = $(this).attr('controller');

        var input_id = $(this).attr('id');

        if ($(this).val() != '' && controller_ajax != undefined)
        {
            $.get(base + "/" + controller_ajax + "/" + $(this).val() + "/ajax", function(data, status){

                $.each(data, function (key, item) {

                    var chave = "input_" + key;

                    if (form_parent.find("#" + chave).prop("type") == 'file' || form_parent.find("#" + chave).hasClass("isFile")) {

                        form_parent.find("#" + chave).hide().attr('type', 'text').addClass('isFile').val(item);

                        if (item) {
                            form_parent.find("#file_input_" + key).attr('src', 'https://s3.xxxxrxxx.com.br/files/'+parseInt(window.filekey)+'/images/' + item).show();
                        }
                    }
                    else
                    {
                        if (form_parent.find("#" + chave).prop("type") == 'text' && key == 'id') {
                            // Não alterar o ID
                        }
                        else
                        {
                            if (chave != input_id) {

                                if (!grid) {
                                    form_parent.find("#" + chave).val(item);
                                }
                                else
                                {
                                    $(selfSelect).parent().parent().parent().parent().find("#" + chave).val(item);
                                }

                            }

                            form_parent.find('#' + chave).selectpicker('refresh');
                        }
                    }
                });

                $('select.subrelationship').each(function(key, item) {
                    var form_parent = $(this).closest('form');

                    var id = $(item).attr('id');
                    var name = $(item).attr('name');
                    var input_principal = $(item).attr('data-sub');
                    var sub = $(item).attr('controller');
                    var value = $(item).val();

                    var input_principal_value = form_parent.find("#input_"+input_principal).val();

                    if(
                        typeof id !== "undefined"
                        && typeof input_principal !== "undefined"
                        && typeof input_principal_value !== "undefined"
                        && input_principal_value
                    ){

                        if (input_principal) {

                            var url = base + "/" + sub + "/" + input_principal_value + "/ajax?subForm=" + input_principal;

                            $.get(url, function(data, status){

                                var options = '';

                                options += '<option value=""></option>';

                                for (var i = 0; i < data.length; i++) {
                                    options += '<option value="' + data[i]['id'] + '">' + data[i][name] + '</option>';
                                }

                                form_parent.find('#' + id).html(options);

                                if (value) {
                                    form_parent.find('#' + id).val(value);
                                }

                                form_parent.find('#' + id).selectpicker('refresh');

                            });
                        }
                    }

                });
            });
        }

    });

    $('input, select').change(function() {
        readConditional(this);
    });
}

function readConditional(input)
{
    var form_parent = $(this).closest('form');
    var field       = $(input).attr('name');
    var value       = $(input).find('option:selected').text();

    /*$('body').find('[conditional_field="'+field+'"]').each(function(k, v){*/
    form_parent.find('[conditional_field="'+field+'"]').each(function(k, v){
        var conditional_field_value = $(v).attr('conditional_field_value');
        if (conditional_field_value == $(input).val() || conditional_field_value == value) {
            $(v).closest('.inputbox').show();
        }else {
            $(v).closest('.inputbox').hide();
        }
    });
}

$(document).ready(function() {

    var menu = false;
    $("#menu li").each(function(e) {
        if ($(this).attr('id') == controller) {
            menu = true;
            $(this).addClass('active');
        } else {
            if ($(this).hasClass('active')) {
                $(this).removeClass('active');
            }
        }
    });

    if (!menu) {
        $("#principal").addClass('active');
    }

    $(".componenteData").each(function(e) {
        var date = $(this).val();
        if (date.length > 0) {
            var dateSplit = date.split("-");
            var dateConvert = dateSplit[2] + '/' + dateSplit[1] + '/' + dateSplit[0];
            $(this).val(dateConvert);
        }
    });

    $(".componenteDataHora").each(function(e) {
        var date = $(this).val();
        if (date.length > 0) {
            var dataHora = date.split(" ");
            var dateSplit = dataHora[0].split("-");
            var dateConvert = dateSplit[2] + '/' + dateSplit[1] + '/' + dateSplit[0];
            $(this).val(dateConvert + " " + dataHora[1].substr(0, 5));
        }
    });

    $("#btn-upload").click(function() {
        $("#inputCsvImportar").trigger('click');
    });

    $('.operador').change(function() {
        if ($(this).val() == 'entre') {
            $(this).parent().parent().find('.between').show();
        }
        else
        {
            $(this).parent().parent().find('.between').hide();
        }
    });

    $(".operador").each(function(e) {
        if ($(this).val() == 'entre') {
            $(this).parent().parent().find('.between').show();
        }
    });

    $('#inputCsvImportar').change(function() {
        $("#formCsvImportar").submit();
    });

    if($("form").attr('form-submit') == undefined){
        $("form").attr('form-submit',''); // Fix *
        $("form").submit(function(){
            event_form_before_submit($(this));
        });
    }

    $('body').on('hidden.bs.modal', '.modal', function () {
        $(this).removeData('bs.modal');
    });

    multiple_add($(".multiple_add"));

    $('.select_relationship').selectpicker({
        noneSelectedText : '',
        noneResultsText: 'Nenhum resultado encontrado'
    });

    setup();

    $('.select_single').trigger('change');

    // ## myModalCE
    myModalCE               = $("#myModal_CE");
    myModalCEContent        = $("#myModal_CE .modal-content");
    modal_create_event_click($("[modal-create]"));
    myModalCE.on('shown.bs.modal', function () { /*...*/ });
    myModalCE.on('hidden.bs.modal', function () {
        myModalCEContent.html('');
    });
    // ################################################
});

function event_form_before_submit(E){
    E.find(".componenteData").each(function(e) {
        var date = $(this).val();
        if (date.length > 0) {
            var dateSplit = date.split("/");
            var dateConvert = dateSplit[2] + '-' + dateSplit[1] + '-' + dateSplit[0];
            $(this).val(dateConvert);
        }
    });
    E.find(".componenteDataHora").each(function(e) {
        var date = $(this).val();
        if (date.length > 0) {
            var dataHora = date.split(" ");
            var dateSplit = dataHora[0].split("/");
            var dateConvert = dateSplit[2] + '-' + dateSplit[1] + '-' + dateSplit[0];
            $(this).val(dateConvert + " " + dataHora[1] + ":00");
        }
    });
}

var count = 0;
function multiple_add(E){
    E.click(function() {
        ++count;
        var divclass = $(this).attr('data');
        var html = $("." + divclass).find('.divdefault').html();
        var div = '<div class="dinamicField'+count+'">';
        div = div + html + '</div>';
        var dinamic = $("." + divclass).append( div );
        $(dinamic).find('.dinamicField' + count).find('.bootstrap-select').remove();
        $(dinamic).find('.dinamicField' + count).find('select').show().selectpicker({
            noneSelectedText : '',
            noneResultsText: 'Nenhum resultado encontrado'
        });
        setup();
        modal_create_event_click($(dinamic).find('.dinamicField' + count).find("[modal-create]"));
    });
}

function modal_create_event_click(E){
    E.on('click',function(){
        let _this           = $(this);
        let select          = $(this).closest(".form-group").find('select');
        let btModalCreate   = $(this);
        let inputModulo     = $(this).attr('modal-modulo');
        let inputRefresh    = $(this).attr('modal-input-refresh');
        myModalCEContent.html('');
        myModalCE.modal('show');
        myModalCE.addClass('preloader');
        myModalCEContent.load(btModalCreate.attr('modal-url'), function(){
            setup();
            multiple_add(myModalCEContent.find('.multiple_add'));
            myModalCEContent.removeClass('preloader');
            myModalCEContent.find('form.form-modal-create').on('submit',function(){
                myModalCEContent.find('.form-group-btn-add-cadastrar').prop('disabled',true);
                event_form_before_submit(myModalCEContent);
                let formData = new FormData($(this)[0]);
                myModalCEContent.find("#myModal-success-errors").html('').hide();
                $.ajax({
                    url: base+"/"+inputModulo,
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(d){
                        if(d.ok && d.success.length){
                            let success_html = `<div class="pad margin no-print alert alert-success" style="margin-bottom: 0!important; margin-left: 15px; margin-right: 15px;"><button type="button" class="close" data-dismiss="alert">×</button><ul style="padding: 0 20px;">`;
                            $.each(d.success,function(k,e){ success_html += `<li>`+e[0]+`</li>`; });
                            success_html += `</ul></div>`;
                            myModalCEContent.find("#myModal-success-errors").html(success_html).show();
                            select.attr('sel_value', d.id);
                            console.log(_this);
                            if(_this.attr('modal-grid')!=undefined){
                                console.log("entrou 1");
                                selectpicker_refresh_data_ajax($('select'+inputRefresh));
                            }else {
                                console.log("entrou 2");
                                selectpicker_refresh_data_ajax(select);
                            }
                        }
                        myModalCEContent.find('.form-group-btn-add-cadastrar').prop('disabled',false);
                        myModalCE.animate({ scrollTop: 0 }, "slow");
                        myModalCEContent.find('form.form-modal-create')[0].reset();
                        setTimeout(function(){ myModalCE.modal('hide'); },2000);
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        let rJson = jqXHR.responseJSON;
                        if(rJson.errors){
                            let erros_html = `<div class="pad margin no-print alert alert-danger" style="margin-bottom: 0!important; margin-left: 15px; margin-right: 15px;"><button type="button" class="close" data-dismiss="alert">×</button><ul style="padding: 0 20px;">`;
                            $.each(rJson.errors,function(k,e){ erros_html += `<li>`+e[0]+`</li>`; });
                            erros_html += `</ul></div>`;
                            myModalCEContent.find("#myModal-success-errors").html(erros_html).show();
                        }
                        console.error('Error: ', jqXHR);
                        myModalCEContent.find('.form-group-btn-add-cadastrar').prop('disabled',false);
                        myModalCE.animate({ scrollTop: 0 }, "slow");
                    }
                });
                return false;
            })
        });
    });
}

function selectpicker_refresh_data_ajax(ev){
    let E          = null;
    let controller = null;
    if(typeof(ev)==='string'){
        E           = $(ev);
    }else {
        E           = ev;
    }
    controller = E.attr('controller');
    if(controller != null && E != null && E[0].tagName.toLowerCase() == 'select'){
        $.get(base+'/'+controller+'/list', function(data){
            $.each(E,function(i2,e2){
                if($(e2).attr('sel_value') == undefined){
                    $(e2).attr('sel_value',$(e2).val());
                }
            });
            E.empty();
            let option_html = '';
            $.each(data,function(i3,e3){
                option_html += '<option value="'+i3+'">'+e3+'</option>';
            });
            E.html(option_html);
            $.each(E,function(i2,e2){
                $(e2).val($(e2).attr('sel_value'));
            });
            E.selectpicker('refresh');
        });
    }
}
