<section id="section-import">
    <form action="{{URL('/import_export')}}" id="r_import_export" method="POST" enctype="multipart/form-data" target="_blank">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <input type="hidden" name="r_model" value="{{$model}}">
        <input type="hidden" name="r_export_options_order_field" value="{{isset($export_options_order_field)?$export_options_order_field:'id'}}">
        <input type="hidden" name="r_export_options_order_field_value" value="{{isset($export_options_order_field_value)?$export_options_order_field_value:'DESC'}}">
        <input type="hidden" name="r_export_options_size" value="{{isset($export_options_size)?$export_options_size:''}}">
        <input type="hidden" name="r_export_options_size_width" value="{{isset($export_options_size_width)?$export_options_size_width:''}}">
        <input type="hidden" name="r_export_options_size_height" value="{{isset($export_options_size_height)?$export_options_size_height:''}}">
        <input type="file" name="r_import_file" id="r_import_file" style="display: none;">
        @if($export_enable_btns)
            @if($exibe_filtros)
                <button type="button" id="r_import_filtro" name="r_import_filtro" value="" title="Baixar Dados de Pesquisa" class="btn btn-xs btn-default"><input type="checkbox" id="r_import_filtro_check" name="r_import_filtro_check" value="1">&nbsp;&nbsp;<strong>Pesquisa</strong></button>
            @endif
            @php
                $lote_loop     = 1;
                if($lote_count > 0){
                    $lote_loop = ceil($lote_count / (ENV('EXPORT_LIMIT')?ENV('EXPORT_LIMIT'):500));
                }
            @endphp
            <select id="r_export_page" name="r_export_page">
                @for($i = 0; $i < $lote_loop; $i++)
                    <option value="{{$i+1}}">Lote {{$i+1}}</option>
                @endfor
            </select>
            <button type="submit" id="r_export_pdf" name="r_export_pdf" value="1" title="Baixar Dados em PDF" class="btn btn-xs btn-warning"><i class="glyphicon glyphicon-cloud-download"></i>&nbsp;&nbsp;<strong>PDF</strong></button>
            <button type="submit" id="r_export_excel" name="r_export_excel" value="1" title="Baixar Dados em Excel" class="btn btn-xs btn-warning"><i class="glyphicon glyphicon-cloud-download"></i>&nbsp;&nbsp;<strong>Excel</strong></button>
            <button type="submit" id="r_export_csv" name="r_export_csv" value="1" title="Baixar Dados para Importação em CSV" class="btn btn-xs btn-success"><i class="glyphicon glyphicon-cloud-download"></i>&nbsp;&nbsp;<strong>CSV</strong></button>
        @endif
        @if($import_enable_btns)
            <button type="button" id="r_import_csv_excel" name="r_import_csv_excel" title="Importar Dados em CSV/Excel" class="btn btn-xs btn-info"><i class="glyphicon glyphicon-cloud-upload"></i>&nbsp;&nbsp;Importar <strong>CSV/Excel</strong></button>
        @endif
    </form>
</section>
<script>
    window.addEventListener("DOMContentLoaded",function(){

        // :: Importar CSV / Excel
        $("#r_import_csv_excel").on('click',function(){
            $('#r_import_file').trigger('click')
        });

        // :: Filtro / Pesquisar
        $("#r_import_filtro").on('click',function(){
            $('#r_import_filtro_check').prop('checked', !$('input[name=\'r_import_filtro_check\']').prop('checked'));
        });

        // :: File -> Arquivo Importação
        $("#r_import_file").on('change',function(){
            $('#r_import_export').attr('target','_self').submit();
        });

        // :: Solicitando a Exportação
        $("#r_import_export").on('submit',function(){
            $(".componenteData").each(function(e) {
                $(this).attr('_value',$(this).val());
                var date = $(this).val();
                if(date.length > 0) {
                    var dateSplit = date.split("/");
                    var dateConvert = dateSplit[2] + '-' + dateSplit[1] + '-' + dateSplit[0];
                    $(this).val(dateConvert);
                }
            });

            $(".componenteDataHora").each(function(e) {
                $(this).attr('_value',$(this).val());
                var date = $(this).val();
                if(date.length > 0){
                    var dataHora = date.split(" ");
                    var dateSplit = dataHora[0].split("/");
                    var dateConvert = dateSplit[2] + '-' + dateSplit[1] + '-' + dateSplit[0];
                    $(this).val(dateConvert + " " + dataHora[1] + ":00");
                }
            });

            $("#r_import_filtro_check").val('');

            if($("#r_import_filtro_check").prop('checked')){
                let serialize = $(".form_filter").serializeArray();
                serialize     = $.grep(serialize, function (field) { return field.name !== '_token'; });
                serialize     = $.param(serialize);
                $("#r_import_filtro_check").val(serialize);
            }

            setTimeout(function(){
                $(".componenteData").each(function(e) {
                    console.log($(this).attr('_value'));
                    $(this).val($(this).attr('_value'));
                });

                $(".componenteDataHora").each(function(e) {
                    console.log($(this).attr('_value'));
                    $(this).val($(this).attr('_value'));
                });
            },500);
        });

    });
</script>
