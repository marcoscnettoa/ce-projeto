@extends('layouts.app')

@section('content')

<h3 class="box-title" style="margin-left: 15px;">Links de pagamento</h3>

<section class="content">

    <div class="box">

        <div class="box-body table-responsive">

            <table id="datatable-master" class="display table table-striped table-bordered" cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Link pagamento</th>
                        <th>Cliente</th>
                        <th>Valor</th>
                        <th>Status</th>
                        <th>Emissão</th>
                        <th>Vencimento</th>
                        <th></th>
                    </tr>
                </thead>

                <tbody>

                    @foreach($link_pagamento as $value)

                    <div class="modal fade text-left" id="text_{{$value->id}}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel35" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h3 class="modal-title" id="myModalLabel35"> Compartilhe Via Whatsapp </h3>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <form method="GET" action="https://web.whatsapp.com/send">
                                    <div class="modal-body">

                                        <fieldset class="form-group floating-label-form-group">
                                            <p>Personalize a mensagem que será enviada.</p>
                                            <textarea rows="10" name="text"  class="form-control" id="title1" rows="3" placeholder="Mensagem">Olá, segue o cobrança no valor de R$ {{ $value->price }}. Cobrança referente à {{$value->product}}, emitida por {{ env('APP_NAME') }}, com vencimento para {{ $value->due_date }}. Acesse através do link: {{ $value->payment_link }}</textarea>
                                        </fieldset>
                                    </div>
                                    <div class="modal-footer">
                                        <input type="reset" class="btn-sm btn-outline-secondary btn-lg" data-dismiss="modal" value="Cancelar">
                                        <input type="submit" class="btn-sm btn-outline-primary btn-lg" value="Selecionar contatos">
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <tr>

                        <td> {{$value->gerencianet_charge_id}} </td>

                        <td> <a href="{{ $value->payment_link }}" target="_blank">{{$value->product}}</a> </td>
                        <td> {{ $value->email }} </td>
                        <td> {{ $value->price }} </td>
                        <td> {{ (!$value->status || $value->status == 1) ? 'Aguardando pagamento' : $value->status }} </td>
                        <td> {{ date('d/m/Y H:i', strtotime($value->created_at)) }} </td>
                        <td> {{ $value->due_date }} </td>
                        <td>
                            <a data-toggle="modal" data-target="#text_{{$value->id}}" href="javascript:void(0);"> <i class="fa fa-whatsapp fa-lg" style="font-size: 20px;"></i></a>
                        </td>

                    </tr>

                    @endforeach

                </tbody>

            </table>

            <br>
            <br>

            <div class="form-group form-group-btn-index" style="float: right;">
                @if(\App\Models\Permissions::permissaoUsuario(\Auth::user(), 'App\Http\Controllers\LinkPagamentoController@store'))
                    <a href="{{ URL('/') }}/link_pagamento/create" class="btn btn-xs btn-primary right form-group-btn-index-cadastrar">Cadastrar</a>
                @endif
            </div>

        </div>
    </div>

</section>

@section('script')

    <script type="text/javascript">
        $('#datatable-master').DataTable( {
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
    </script>

@endsection

@endsection
