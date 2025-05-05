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

    $('.componenteData').datepicker({
        format: 'dd/mm/yyyy',
        language: 'pt-BR'
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

    $(".multiple_remove").click(function() {
        $(this).parent().parent().remove();
    });

    $(".input_remove").click(function() {
        $(this).parent().remove();
    });

    $(".multiple_add").click(function() {

        var divclass = $(this).attr('data');

        var html = $("." + divclass).find('.divdefault').html();

        $("." + divclass).append( html );

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

        $('.chosen-select').chosen();

        $(".multiple_remove").click(function() {
            $(this).parent().parent().remove();
        });
    });

    $(function() {
        $('.componenteDataHora').datetimepicker({
            format: 'dd/mm/yyyy hh:ii',
            autoclose: true
        });
    });

    $("form").submit(function(){

        $(".componenteData").each(function(e) {

            var date = $(this).val();

            if (date.length > 0) {

                var dateSplit = date.split("/");

                var dateConvert = dateSplit[2] + '-' + dateSplit[1] + '-' + dateSplit[0];

                $(this).val(dateConvert);
            }
        });

        $(".componenteDataHora").each(function(e) {

            var date = $(this).val();

            if (date.length > 0) {

                var dataHora = date.split(" ");

                var dateSplit = dataHora[0].split("/");

                var dateConvert = dateSplit[2] + '-' + dateSplit[1] + '-' + dateSplit[0];

                $(this).val(dateConvert + " " + dataHora[1] + ":00");
            }

        });
    });

    $(".money").maskMoney({
        decimal: ".",
        thousands: ""
    });

    $('.cep').mask('99999-999');

    $('.data').mask('99/99/9999');

    $('.hora').mask('99:99');

    $('.dataehora').mask('99/99/9999 99:99');

    $('.cpf').mask('999.999.999-99');

    $('.cnpj').mask('99.999.999/9999-99');

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

    $('.chosen-select').chosen();

    $(".select_relationship").change(function(){

        var controller_ajax = $(this).attr('controller');

        var input_id = $(this).attr('id');

        if ($(this).val() != '')
        {
            $.get(base + "/" + controller_ajax + "/" + $(this).val() + "/ajax", function(data, status){

                $.each(data, function (key, item) {

                    var chave = "input_" + key;

                    if ($("#" + chave).prop("type") == 'file' || $("#" + chave).hasClass("isFile")) {

                        $("#" + chave).hide().attr('type', 'text').addClass('isFile').val(item);

                        console.log(chave, item);

                        if (item) {
                            $("#file_input_foto").attr('src', 'https://s3.xxxxrxxx.com.br/files/'+parseInt(window.filekey)+'/images/' + item).show();
                        }
                    }
                    else
                    {
                        if (chave != input_id) {
                            $("#" + chave).val(item);
                        }

                        $('#' + chave).selectpicker('refresh');
                    }
                });

                $('.subrelationship').each(function(key, item) {


                    var id = $(item).attr('id');
                    var name = $(item).attr('name');
                    var input_principal = $(item).attr('data-sub');
                    var sub = $(item).attr('controller');

                    console.log(key, item);

                    console.log(id);
                    console.log(name);
                    console.log(input_principal);
                    console.log(sub);

                    if(id != undefined){

                        if (input_principal) {

                            var input_principal_value = $("#input_"+input_principal).val();

                            $.get(base + "/" + sub + "/" + input_principal_value + "/ajax?subForm=" + input_principal, function(data, status){

                                var options = '';

                                for (var i = 0, len = data.length; i < len; ++i) {
                                    options += '<option value="' + data[i]['id'] + '">' + data[i][name] + '</option>';
                                }

                                $('#' + id).html(options);

                                $('#' + id).selectpicker('refresh');

                            });
                        }
                    }

                });
            });
        }
    });

    $('.cep').focusout(function(){

        var cep = $('.cep').val().replace(/\D/g, '');

        $.get('https://dashboard.xxxxrxxxapps.com/v1/cep/'+cep, function(data){

            console.log(data);

            $("#input_cidade").val(data.localidade);
            $("#input_estado").val(data.uf);
            $("#input_bairro").val(data.bairro);
            $("#input_endereco").val(data.logradouro);
            $("#input_municipio").val(data.localidade);
            $("#input_logradouro").val(data.logradouro);
            $("#input_localidade").val(data.localidade);
            $("#input_uf").val(data.uf);
        });
    });

    $('.cnpj').focusout(function(){

        var cnpj = $('.cnpj').val().replace(/\D/g, '');

        $.get('https://dashboard.xxxxrxxxapps.com/v1/cnpj/'+cnpj, function(data){

            console.log(data);

            $("#input_atividade_principal").val(data.atividade_principal);
            $("#input_data_da_situacao").val(data.data_situacao);
            $("#input_complemento").val(data.complemento);
            $("#input_tipo").val(data.tipo);
            $("#input_nome").val(data.nome);
            $("#input_telefone").val(data.telefone);
            $("#input_e_mail").val(data.email);
            $("#input_atividades_secundarias").val(data.atividades_secundarias);
            $("#input_situacao").val(data.situacao);
            $("#input_bairro").val(data.bairro);
            $("#input_logradouro").val(data.logradouro);
            $("#input_numero").val(data.numero);
            $("#input_cep").val(data.cep);
            $("#input_municipio").val(data.municipio);
            $("#input_fantasia").val(data.fantasia);
            $("#input_porte").val(data.porte);
            $("#input_abertura").val(data.abertura);
            $("#input_natureza_juridica").val(data.natureza_juridica);
            $("#input_uf").val(data.uf);
            $("#input_ultima_atualizacao").val(data.ultima_atualizacao);
            $("#input_capital_social").val(data.capital_social);

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

    $('body').on('hidden.bs.modal', '.modal', function () {
        $(this).removeData('bs.modal');
    });

    $(".select_single").selectpicker({
        noneSelectedText : '',
        noneResultsText: 'Nenhum resultado encontrado'
    });

    var datatabeApi;

    var table_btns = $('#datatable').DataTable( {
        "scrollX": true,
        'dom': 'Blfrtip',
        'buttons': [
            {
                extend: 'copy',
                className: 'btn btn-default btn-flat'
            },
            {
                extend: 'csv',
                className: 'btn btn-default btn-flat'
            },
            {
                extend: 'excel',
                className: 'btn btn-default btn-flat',
                footer: true,
                title: $('.box-title').html(),
                exportOptions: {
                    columns: ':not(:last-child)',
                }
            },
            {
                orientation: 'portrait',
                pageSize: 'A4',
                extend: 'pdfHtml5',
                className: 'btn btn-default btn-flat',
                footer: true,
                title: $('.box-title').html(),
                exportOptions: {
                    columns: ':not(:last-child)',
                },
                customize: function (doc) {
                    doc.content[1].layout = "Borders";
                    //doc.styles['table'] = { width:100% };
                }
            },
            {
                extend: 'print',
                footer: true,
                className: 'btn btn-default btn-flat',
                exportOptions: {
                    columns: ':not(:last-child)',
                },
                customize: function (win) {
                    $body = $(win.document.body);
                    $body.find('h1').css('text-align', 'center');
                }
            }
        ],
        "oLanguage": {
            "sEmptyTable": "Nenhum registro encontrado",
            "sInfo": "Mostrando de _START_ até _END_ de _TOTAL_ registros",
            "sInfoEmpty": "Mostrando 0 até 0 de 0 registros",
            "sInfoFiltered": "(Filtrados de _MAX_ registros)",
            "sInfoPostFix": "",
            "sInfoThousands": ".",
            "sLengthMenu": "_MENU_ resultados por página",
            "sLoadingRecords": "Carregando...",
            "sProcessing": "Processando...",
            "sZeroRecords": "Nenhum registro encontrado",
            "sSearch": "Pesquisar",
            "oPaginate": {
                "sNext": "Próximo",
                "sPrevious": "Anterior",
                "sFirst": "Primeiro",
                "sLast": "Último"
            },
            "oAria": {
                "sSortAscending": ": Ordenar colunas de forma ascendente",
                "sSortDescending": ": Ordenar colunas de forma descendente"
            }
        },
        "footerCallback": function ( row, data, start, end, display ) {
            window.datatabeApi = this.api(), data;
        }
    } );

    if (defaultOrder && defaultOrderValue) {

        var column = table_btns.column(':contains('+window.defaultOrder+')');

        table_btns.order( [ column.index(), "'"+window.defaultOrderValue+"'" ] ).draw();
    }

    table_btns.columns('.sum').each(function (el) {

        if (el.length > 0) {
            $(".tfoot").show();
        }

        var intVal = function ( i ) {
            return typeof i === 'string' ?
                i.replace(/[\$,]/g, '')*1 :
                typeof i === 'number' ?
                    i : 0;
        };

        for (var i = 0; i < el.length; i++) {

            var sum = table_btns
                .column(el[i])
                .data()
                .reduce(function (a, b) {

                    if(a.includes(','))
                    {
                        a = a.toString().replace('.', '');
                        a = a.toString().replace(',', '.');
                    }

                    if(b.includes(','))
                    {
                        b = b.toString().replace('.', '');
                        b = b.toString().replace(',', '.');
                    }

                    var n = intVal(a) + intVal(b);

                    var vl = (n).toFixed(2).toString().replace(',', '');

                    return vl;
                });

            $( window.datatabeApi.column( el[i] ).footer() ).html(sum);
        }

        $(".sorting").trigger('click');
    });

    $(".sorting").trigger('click');

    var table = $('#datatable-no-buttons').DataTable( {
        "scrollX": true,
        "oLanguage": {
            "sEmptyTable": "Nenhum registro encontrado",
            "sInfo": "Mostrando de _START_ até _END_ de _TOTAL_ registros",
            "sInfoEmpty": "Mostrando 0 até 0 de 0 registros",
            "sInfoFiltered": "(Filtrados de _MAX_ registros)",
            "sInfoPostFix": "",
            "sInfoThousands": ".",
            "sLengthMenu": "_MENU_ resultados por página",
            "sLoadingRecords": "Carregando...",
            "sProcessing": "Processando...",
            "sZeroRecords": "Nenhum registro encontrado",
            "sSearch": "Pesquisar",
            "oPaginate": {
                "sNext": "Próximo",
                "sPrevious": "Anterior",
                "sFirst": "Primeiro",
                "sLast": "Último"
            },
            "oAria": {
                "sSortAscending": ": Ordenar colunas de forma ascendente",
                "sSortDescending": ": Ordenar colunas de forma descendente"
            }
        }
    } );

    $('input, select').change(function() {

        var field = $(this).attr('name');

        var value = $(this).find('option:selected').text();

        $('body').find('[conditional_field="'+field+'"]').each(function(k, v){

            var conditional_field_value = $(v).attr('conditional_field_value');

            if (conditional_field_value == $(this).val() || conditional_field_value == value) {
                //$('body').find('[hide-by="input_'+field+'"]').show();
                $(v).parent().parent().show();
            } else {
                //$('body').find('[hide-by="input_'+field+'"]').hide();
                $(v).parent().parent().hide();
            }

        });

    });

    $('.select_single').trigger('change');
} );
