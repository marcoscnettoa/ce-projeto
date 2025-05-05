<script type="text/javascript">

    $(".loader").show();

    $(document).ready(function() {
        // - #
        $.fn.dataTable.ext.order['ptBRDate-asc'] = function (settings, col) {
            return this.api().column(col, { order: 'index' }).nodes().map(function (td, i) {
                var date = $(td).text().trim();
                var parts = date.split('/');
                return new Date(parts[2], parts[1] - 1, parts[0]);
            });
        };
        $.fn.dataTable.ext.order['ptBRDate-desc'] = function (settings, col) {
            return this.api().column(col, { order: 'index' }).nodes().map(function (td, i) {
                var date = $(td).text().trim();
                var parts = date.split('/');
                return new Date(parts[2], parts[1] - 1, parts[0]);
            });
        };
        $.fn.dataTable.ext.type.order['ptBRDecimal'] = function (data) {
            if (data === null || data === "") { return 0; }
            data = data.replace(".", "").replace(",", ".");
            return parseFloat(data);
        };
        // - #

        var datatabeApi;

        var table_btns = $('#datatable').DataTable( {
            "scrollX": true,
            "scrollY": '400px',
            "scrollCollapse": true,
            "order": @if(isset($key) && isset($order))[[ "{{$key}}", "{{$order}}" ]] @else [] @endif,
            'dom': '<"export-button-container"B>lfrtip',
              'buttons': [
                  /*{
                      extend: 'pdfHtml5',
                      text: '<i class="fas fa-file-pdf"></i> PDF',
                      className: 'btn btn-default',
                      footer: true,
                      title: $('.box-title').html(),
                      orientation: 'landscape',
                      pageSize: 'LEGAL',
                      exportOptions: {
                          columns: ':not(.no-export)'
                      }
                  },
                  {
                      extend: 'excelHtml5',
                      text: '<i class="fas fa-file-excel"></i> EXCEL',
                      className: 'btn btn-default',
                      footer: true,
                      title: $('.box-title').html(),
                      exportOptions: {
                          columns: ':not(.no-export), :not(:last-child)'
                      },
                  },
                  {
                      extend: 'csvHtml5',
                      text: '<i class="fas fa-file-csv"></i> CSV',
                      className: 'btn btn-default',
                      footer: true,
                      title: $('.box-title').html(),
                      exportOptions: {
                          columns: ':not(.no-export)'
                      }
                  }*/
              ],
            "oLanguage": {
                "sEmptyTable": "Nenhum registro encontrado",
                "sInfo": "Mostrando de _START_ até _END_ de _TOTAL_ registros",
                "sInfoEmpty": "Mostrando 0 até 0 de 0 registros",
                "sInfoFiltered": "(Filtrados de _MAX_ registros)",
                "sInfoPostFix": "",
                "sInfoThousands": ".",
                "sLengthMenu": "_MENU_ Resultados por página",
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
            },
            columnDefs: @if(isset($columnDefs)) {!! $columnDefs !!} @else [] @endif,
        });

        var table = $('#datatable-no-buttons').DataTable( {
            "scrollX": true,
            "scrollY": '400px',
            "scrollCollapse": true,
            "order": @if(isset($key) && isset($order))[[ "{{$key}}", "{{$order}}" ]] @else [] @endif,
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
            },
            columnDefs: @if(isset($columnDefs)) {!! $columnDefs !!} @else [] @endif,
        });

        if($("#datatable-no-buttons").length){
            var table_opt = $("#datatable-no-buttons").DataTable();
        }
        if($("#datatable").length){
            var table_opt = $("#datatable").DataTable();
        }
        if(defaultOrder && defaultOrderValue) {
            var column = table_opt.column(':contains('+window.defaultOrder+')');
            table_opt.order( [ column.index(), "'"+window.defaultOrderValue+"'" ] ).draw();
        }
        if(table_opt.data().count()) {
            table_opt.columns('.sum').each(function (el) {
                if (el.length > 0) {
                    $(".tfoot").show();
                    var intVal = function ( i ) {
                        return typeof i === 'string' ?
                            i.replace(/[\$,]/g, '')*1 :
                            typeof i === 'number' ?
                                i : 0;
                    };
                    for(var i = 0; i < el.length; i++) {
                        var sum = table_opt
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
                }
            });
        }

        $(".loader").hide();

    } );

</script>
