@php
    $_v = '25042024-001';
    if(env('FILESYSTEM_DRIVER') == 's3')
    {
        $fileurlbase = env('URLS3') . '/' . env('FILEKEY') . '/';
    }
    else
    {
        $fileurlbase = env('APP_URL') . '/';
    }
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>{{ config('app.name', 'Rxxx') }}</title>
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <link rel="icon" type="image/x-icon" href="{{ (ENV('FAVICON')?ENV('FAVICON'):"https://lxxxtxx.xxxxrxxxapps.com/images/logo-lxxxtxx.jpg") }}"/>
        <link rel="apple-touch-icon" href="{{ (ENV('FAVICON')?ENV('FAVICON'):"https://lxxxtxx.xxxxrxxxapps.com/images/logo-lxxxtxx.jpg") }}">
        <meta name="robots" content="noindex,nofollow,noarchive">
        <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
        {{-- <link rel="stylesheet" href="{{ URL('xxxxrxxx/v1') }}/assets/bootstrap.min.css?_v={{$_v}}"> --}}
        <link rel="stylesheet" href="{{URL('/')}}/assets/bower_components/bootstrap/dist/css/bootstrap.min.css?_v={{$_v}}">
        <link rel="stylesheet" href="{{ URL('xxxxrxxx/v1') }}/assets/font-awesome.min.css?_v={{$_v}}">
        <link rel="stylesheet" href="{{ URL('xxxxrxxx/v1') }}/assets/ionicons.min.css?_v={{$_v}}">
        <link rel="stylesheet" href="{{ URL('xxxxrxxx/v1') }}/assets/jquery-jvectormap.css?_v={{$_v}}">
        <link rel="stylesheet" href="{{ URL('xxxxrxxx/v1') }}/assets/sweetalert.css?_v={{$_v}}">
        <link rel="stylesheet" href="{{ URL('xxxxrxxx/v1') }}/assets/AdminLTE.min.css?_v={{$_v}}">
        <link rel="stylesheet" href="{{ URL('xxxxrxxx/v1') }}/assets/_all-skins.min.css?_v={{$_v}}">
        <link rel="stylesheet" href="{{URL('/')}}/css/xxxxrxxx.css?_v={{$_v}}">
        <link rel="stylesheet" href="{{ URL('xxxxrxxx/v1') }}/assets/jquery.dataTables.css?_v={{$_v}}" rel="stylesheet">
        <link rel="stylesheet" href="{{ URL('xxxxrxxx/v1') }}/assets/datepicker.css?_v={{$_v}}" rel="stylesheet">
        <link rel="stylesheet" href="{{ URL('xxxxrxxx/v1') }}/assets/bootstrap-datetimepicker.min.css?_v={{$_v}}" rel="stylesheet">
        <link rel="stylesheet" href="{{ URL('xxxxrxxx/v1') }}/assets/jquery.fancybox.css?_v={{$_v}}" rel="stylesheet">
        <link rel="stylesheet" href="{{ URL('xxxxrxxx/v1') }}/assets/chosen.min.css?_v={{$_v}}" rel="stylesheet">
        <link rel="stylesheet" href="{{ URL('xxxxrxxx/v1') }}/assets/dataTables.tableTools.min.css?_v={{$_v}}" rel="stylesheet">
        <link rel="stylesheet" href="{{ URL('xxxxrxxx/v1') }}/assets/bootstrap-select_old.css?_v={{$_v}}" rel="stylesheet">
        <link rel="stylesheet" href="{{URL('/')}}/css/bootstrap-tagsinput.css?_v={{$_v}}" rel="stylesheet"> {{-- # - --}}
        <link rel="stylesheet" href="{{URL('/')}}/css/jkanban/jkanban.min.css?_v={{$_v}}" rel="stylesheet"> {{-- # - --}}
        <link rel="stylesheet" href="{{URL('/')}}/css/jkanban/app-kanban.css?_v={{$_v}}" rel="stylesheet"> {{-- # - --}}
        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">
        <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css?_v={{$_v}}">
        @if(env('GANTT') and \App\Models\Permissions::permissaoUsuario(\Auth::user(), 'App\Http\Controllers\GanttController@get'))
            <link href="https://cdn.dhtmlx.com/gantt/edge/dhtmlxgantt.css" rel="stylesheet">
        @endif
        <meta property="og:title" content="{{ config('app.name', 'Rxxx') }}" />
        <meta property="og:image" content="{{ (ENV('FAVICON')?ENV('FAVICON'):"https://lxxxtxx.xxxxrxxxapps.com/images/logo-lxxxtxx.jpg") }}" />
        <meta property="og:image:type" content="image/png">
        <meta property="og:locale" content="pt_BR">
        <meta property="og:type" content="website" />
        <style type="text/css">

            ul.sidebar-menu li {
                line-height: 1.5em;
            }
            .modal-dialog {
                width: 80% !important;
            }

            .box-body{
                margin-top: 20px;
            }

            .dataTables_wrapper .dt-buttons {
                float:none;
                text-align:right;
                margin-bottom: 20px;
            }

            #datatable .btn-default, .btn-danger{
                margin: 2px;
            }

            #datatable .btn-default, .btn-danger{
                margin: 2px;
                float: left;
            }

            #datatable-no-buttons .btn-default, .btn-danger{
                margin: 2px;
                float: left;
            }

            .grid{
                min-height: auto;
                float: left;
                width: 100%;
            }

            .spinner {
              border: 8px solid #f2f2f5;
              border-left-color: #555770;
              border-radius: 50%;
              width: 50px;
              height: 50px;
              animation: spin 1s linear infinite;
            }

            @keyframes spin {
              to {
                transform: rotate(360deg);
              }
            }

            .alert-success{
                background-color: #06C270 !important;
                border-color: #06C270;
            }

            .jumbotron p {
                margin-bottom: 20px;
                font-size: 14px;
                font-weight: 200;
            }

            .form_filter .row,

form[id^="form_edit_"] .row,

form[id^="form_add_"] .row,

section.content[class$="_show"] .row

{ padding-left: 12.5px; padding-right: 12.5px; }

.form_filter div[class*="col-md-"],

form[id^="form_edit_"] div[class*="col-md-"],

form[id^="form_add_"] div[class*="col-md-"],

section.content[class$="_show"] [class*="col-md-"]

{ padding-left: 2.5px; padding-right: 2.5px; }

.form_filter .form-group,

form[id^="form_edit_"] .form-group,

form[id^="form_add_"] .form-group,

section.content[class$="_show"] .form-group

{ margin-bottom: 5px; }

.inpsel-destaque-st1.form-group { }
.inpsel-destaque-st1.form-group label { color: #f39c12 !important; }
.inpsel-destaque-st1.form-group .input-group-addon { border: 1px solid #f39c12 !important; background-color: #fff1da !important; }
.inpsel-destaque-st1.form-group .input-group-addon i { color: #f39c12 !important; }
.inpsel-destaque-st1.form-group .bootstrap-select button { border: 1px solid #f39c12 !important; background-color: #fff1da !important; }

        </style>

        <link rel="stylesheet" href="{{URL("/")}}/css/RA.css?_v={{$_v}}"> {{-- # - --}}
        @yield('style')

        <script>
            @if(Auth::user())
                var USER_ID         = {{ Auth::user()->id }};
                var USER_PERFIL_ID  = {{ Auth::user()->perfil->id }};
                @if(Auth::user()->perfil)
                var USER_PERFIL_EXT = "{{ Auth::user()->perfil->name }}";
                @endif
            @endif
        </script>

    </head>

    <div class="loader" style="height:100%; width:100%; position:fixed;left:0;top:0;z-index:9999;  background:#000; opacity:0.8; display:none;">
        <div style="height: 100%; width: 100%; display: flex; align-items: center; justify-content: center; z-index:9999;">
            <div class="spinner"></div>
        </div>
    </div>

    <body class="sidebar-mini fixed sidebar-mini-expand-feature skin-blue {{(\Request::get('modal')==true?'remove_edit_iframe':'')}}">
        <div class="wrapper">
            <header class="main-header">
                <a href="{{ URL('/') }}" class="logo">
                    {{--<span class="logo-mini"><b>{{ config('app.name', 'Rxxx')[0] }}</b></span>
                    <span class="logo-lg">{{ config('app.name', 'Rxxx') }}</span>--}}
                    <span class="logo-mini"><img src="{{URL('images')}}/lxxxtxx-icon-50x50.jpg?_v={{$_v}}" title="{{ENV('APP_NAME')}}"/></span>
                    <span class="logo-lg"><img src="{{URL('images')}}/lxxxtxx-h-230x50.jpg?_v={{$_v}}" title="{{ENV('APP_NAME')}}"/></span>
                </a>
                <nav class="navbar navbar-static-top">
                    <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
                    <span class="sr-only">Toggle navigation</span>
                    </a>
                    <div class="navbar-custom-menu">
                        <ul class="nav navbar-nav">
                            @if(!env('APP_PLANO_CONTRATADO'))
                            <li>
                                <a href="https://dashboard.xxxxrxxxapps.com/assinar/a1237fbbc565f90b3d7d9a4c2aec6f3a" target="_blank" style="background: transparent; border: none; font-weight: bold;" title="Clique para publicar seu sistema" alt="Clique para publicar seu sistema" class="btn btn-default">
                                    <span style=" background-color: #16d39a; color: #fff; padding: 5px 10px 5px 10px; border-radius: 5px; position: relative;">
                                        <i class="fas fa-external-link-alt" style=" margin-right: 5px; font-size: 12px; "></i> Publicar
                                    </span>
                                </a>
                            </li>
                            @endif

                            @if(env('APP_EDIT'))

                            <li>
                                <a href="https://dashboard.xxxxrxxxapps.com/app/edit/a1237fbbc565f90b3d7d9a4c2aec6f3a" style="background: transparent; border: none; font-weight: bold;" target="_parent" title="Clique para editar seu sistema" alt="Clique para editar seu sistema" class="btn btn-info">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </li>

                            <li>
                                <a href="{{ URL('/doc/swagger') }}" target="_blank" style="background: transparent; border: none; font-weight: bold;" target="_parent" title="Documentação da API" alt="Documentação da API" class="btn btn-info">
                                    <i class="fas fa-book"></i>
                                </a>
                            </li>

                            @endif
                            <li class="dropdown user user-menu">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                @if(\Auth::user()->image)
                                    <img src="{{ $fileurlbase . "images/" . \Auth::user()->image }}" width="160" height="160" class="user-image" alt="{{ \Auth::user()->name }}">
                                @else
                                    <img src="https://dashboard.xxxxrxxxapps.com/assets/images/profile.png" width="160" height="160" class="user-image" alt="{{ \Auth::user()->name }}">
                                @endif
                                <span class="hidden-xs">{{ \Auth::user()->name }}</span>
                                </a>
                                <ul class="dropdown-menu">
                                    <li class="user-header">

                                        @if(\Auth::user()->image)
                                            <img src="{{ $fileurlbase . "images/" . \Auth::user()->image }}" width="160" height="160" class="img-circle" alt="{{ \Auth::user()->name }}">
                                        @else
                                            <img src="https://dashboard.xxxxrxxxapps.com/assets/images/profile.png" width="160" height="160" class="img-circle" alt="{{ \Auth::user()->name }}">
                                        @endif

                                        <p>
                                            {{ \Auth::user()->name }}
                                            @if(\Auth::user()->profession)
                                            - {{ \Auth::user()->profession }}
                                            @endif
                                            <small>Cadastrado em {{ date('d/m/Y', strtotime(\Auth::user()->created_at)) }} </small>
                                        </p>
                                    </li>
                                    <li class="user-footer">

                                        @if(\App\Models\Permissions::permissaoUsuario(\Auth::user(), 'App\Http\Controllers\ProfilesController@perfil'))
                                        <div class="pull-left">
                                            <a href="{{ URL('/') }}/perfil" class="btn btn-default btn-flat">Minha conta</a>
                                        </div>
                                        @endif

                                        <div class="pull-right">
                                            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">{{ csrf_field() }}</form>
                                            <a href="javascript:void(0);" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="btn btn-default btn-flat">Sair</a>
                                        </div>
                                    </li>
                                </ul>
                            </li>

                            @if(env('APP_EDIT'))
                                <li>
                                    <a href="#" data-toggle="control-sidebar"><i class="fa fa-gears"></i></a>
                                </li>
                            @endif

                        </ul>
                    </div>
                </nav>
            </header>
            <aside class="main-sidebar">
                <section class="sidebar">
                    <ul class="sidebar-menu" data-widget="tree" style="white-space: normal;">
                        {{--<li class="header">MENU</li>--}}
@if(\App\Models\Permissions::permissaoUsuario(\Auth::user(), 'App\Http\Controllers\CadastrosController@index'))
<li class='treeview {{ Request::segment(1) == 'cadastros' ? 'active' : '' }}'>
    <a href="javascript:void(0);">
        <i class="glyphicon glyphicon-th-list"></i>
        <span>Cadastros</span>
        <span class="pull-right-container">
            <i class="fa fa-angle-left pull-right"></i>
        </span>
    </a>
    <ul class="treeview-menu" style="display: none;">
@if(\App\Models\Permissions::permissaoUsuario(\Auth::user(), 'App\Http\Controllers\ClientesController@index'))
<li id='clientes' class='{{ Request::segment(1) == 'clientes' ? 'active' : '' }}'>
<a href="{{ URL('/') }}/clientes">
<i class="glyphicon glyphicon-user"></i>
<span>Clientes</span>
</a>
</li>

@endif

@if(\App\Models\Permissions::permissaoUsuario(\Auth::user(), 'App\Http\Controllers\CompanhiasController@index'))
<li id='companhias' class='{{ Request::segment(1) == 'companhias' ? 'active' : '' }}'>
<a href="{{ URL('/') }}/companhias">
<i class="glyphicon glyphicon-chevron-right"></i>
<span>Companhias</span>
</a>
</li>

@endif

@if(\App\Models\Permissions::permissaoUsuario(\Auth::user(), 'App\Http\Controllers\FornecedoresController@index'))
<li id='fornecedores' class='{{ Request::segment(1) == 'fornecedores' ? 'active' : '' }}'>
<a href="{{ URL('/') }}/fornecedores">
<i class="glyphicon glyphicon-chevron-right"></i>
<span>Fornecedores</span>
</a>
</li>

@endif

@if(\App\Models\Permissions::permissaoUsuario(\Auth::user(), 'App\Http\Controllers\ProdutosController@index'))
<li id='produtos' class='{{ Request::segment(1) == 'produtos' ? 'active' : '' }}'>
<a href="{{ URL('/') }}/produtos">
<i class="glyphicon glyphicon-chevron-right"></i>
<span>Produtos</span>
</a>
</li>

@endif

@if(\App\Models\Permissions::permissaoUsuario(\Auth::user(), 'App\Http\Controllers\ServicosController@index'))
<li id='servicos' class='{{ Request::segment(1) == 'servicos' ? 'active' : '' }}'>
<a href="{{ URL('/') }}/servicos">
<i class="glyphicon glyphicon-chevron-right"></i>
<span>Serviços</span>
</a>
</li>

@endif

@if(\App\Models\Permissions::permissaoUsuario(\Auth::user(), 'App\Http\Controllers\TrechosController@index'))
<li id='trechos' class='{{ Request::segment(1) == 'trechos' ? 'active' : '' }}'>
<a href="{{ URL('/') }}/trechos">
<i class="glyphicon glyphicon-chevron-right"></i>
<span>Trechos</span>
</a>
</li>

@endif

@if(\App\Models\Permissions::permissaoUsuario(\Auth::user(), 'App\Http\Controllers\PassageiroController@index'))
<li id='passageiro' class='{{ Request::segment(1) == 'passageiro' ? 'active' : '' }}'>
<a href="{{ URL('/') }}/passageiro">
<i class="glyphicon glyphicon-user"></i>
<span>Passageiro</span>
</a>
</li>

@endif

@if(\App\Models\Permissions::permissaoUsuario(\Auth::user(), 'App\Http\Controllers\VendedoresController@index'))
<li id='vendedores' class='{{ Request::segment(1) == 'vendedores' ? 'active' : '' }}'>
<a href="{{ URL('/') }}/vendedores">
<i class="glyphicon glyphicon-user"></i>
<span>Vendedores</span>
</a>
</li>

@endif

    </ul>
</li>
@endif

@if(\App\Models\Permissions::permissaoUsuario(\Auth::user(), 'App\Http\Controllers\CcfCartaoDeCreditoController@index'))
<li id='ccf_cartao_de_credito' class='{{ Request::segment(1) == 'ccf_cartao_de_credito' ? 'active' : '' }}'>
<a href="{{ URL('/') }}/ccf_cartao_de_credito">
<i class="glyphicon glyphicon-credit-card"></i>
<span>CCF Cartão de Crédito</span>
</a>
</li>
@endif

@if(\App\Models\Permissions::permissaoUsuario(\Auth::user(), 'App\Http\Controllers\FinanceiroController@index'))
<li class='treeview {{ Request::segment(1) == 'financeiro' ? 'active' : '' }}'>
    <a href="javascript:void(0);">
        <i class="glyphicon glyphicon-usd"></i>
        <span>Financeiro</span>
        <span class="pull-right-container">
            <i class="fa fa-angle-left pull-right"></i>
        </span>
    </a>
    <ul class="treeview-menu" style="display: none;">
@if(\App\Models\Permissions::permissaoUsuario(\Auth::user(), 'App\Http\Controllers\ContasAReceberController@index'))
<li id='contas_a_receber' class='{{ Request::segment(1) == 'contas_a_receber' ? 'active' : '' }}'>
<a href="{{ URL('/') }}/contas_a_receber">
<i class="glyphicon glyphicon-download"></i>
<span>Contas a Receber</span>
</a>
</li>

@endif

@if(\App\Models\Permissions::permissaoUsuario(\Auth::user(), 'App\Http\Controllers\ContasAPagarController@index'))
<li id='contas_a_pagar' class='{{ Request::segment(1) == 'contas_a_pagar' ? 'active' : '' }}'>
<a href="{{ URL('/') }}/contas_a_pagar">
<i class="glyphicon glyphicon-upload"></i>
<span>Contas a Pagar</span>
</a>
</li>

@endif

@if(\App\Models\Permissions::permissaoUsuario(\Auth::user(), 'App\Http\Controllers\FluxoDeCaixaController@index'))
<li id='fluxo_de_caixa' class='{{ Request::segment(1) == 'fluxo_de_caixa' ? 'active' : '' }}'>
<a href="{{ URL('/') }}/fluxo_de_caixa">
<i class="glyphicon glyphicon-signal"></i>
<span>Fluxo de caixa</span>
</a>
</li>

@endif

    </ul>
</li>
@endif

@if(\App\Models\Permissions::permissaoUsuario(\Auth::user(), 'App\Http\Controllers\VendasController@index'))
<li id='vendas' class='{{ Request::segment(1) == 'vendas' ? 'active' : '' }}'>
<a href="{{ URL('/') }}/vendas">
<i class="glyphicon glyphicon-tags"></i>
<span>Vendas</span>
</a>
</li>
@endif

@if(\App\Models\Permissions::permissaoUsuario(\Auth::user(), 'App\Http\Controllers\FaturamentoController@index'))
<li id='faturamento' class='{{ Request::segment(1) == 'faturamento' ? 'active' : '' }}'>
<a href="{{ URL('/') }}/faturamento">
<i class="glyphicon glyphicon-scissors"></i>
<span>Faturamento</span>
</a>
</li>
@endif

@if(\App\Models\Permissions::permissaoUsuario(\Auth::user(), 'App\Http\Controllers\FormasDePagamentosController@index'))
<li id='formas_de_pagamentos' class='{{ Request::segment(1) == 'formas_de_pagamentos' ? 'active' : '' }}'>
<a href="{{ URL('/') }}/formas_de_pagamentos">
<i class="glyphicon glyphicon-th-list"></i>
<span>Formas de Pagamentos</span>
</a>
</li>
@endif

@if(\App\Models\Permissions::permissaoUsuario(\Auth::user(), 'App\Http\Controllers\CadastroDeEmpresasController@index'))
<li id='cadastro_de_empresas' class='{{ Request::segment(1) == 'cadastro_de_empresas' ? 'active' : '' }}'>
<a href="{{ URL('/') }}/cadastro_de_empresas">
<i class="glyphicon glyphicon-file"></i>
<span>Cadastro de Empresas</span>
</a>
</li>
@endif

@if(\App\Models\Permissions::permissaoUsuario(\Auth::user(), 'App\Http\Controllers\OrcamentosController@index'))
<li id='orcamentos' class='{{ Request::segment(1) == 'orcamentos' ? 'active' : '' }}'>
<a href="{{ URL('/') }}/orcamentos">
<i class="glyphicon glyphicon-th-list"></i>
<span>Orçamentos</span>
</a>
</li>
@endif

@if(\App\Models\Permissions::permissaoUsuario(\Auth::user(), 'App\Http\Controllers\ReportsController@index'))
    <li id='reports' class='{{ Request::segment(1) == 'reports' ? 'active' : '' }}'>
        <a href="{{ URL('/') }}/reports">
            <i class="glyphicon glyphicon-list-alt"></i>
            <span>Relatórios</span>
        </a>
    </li>
@endif

@if(\App\Models\Permissions::permissaoUsuario(\Auth::user(), 'App\Http\Controllers\TemplatesController@index'))
    <li id='templates' class='{{ Request::segment(1) == 'templates' ? 'active' : '' }}'>
        <a href="{{ URL('/') }}/templates">
            <i class="glyphicon glyphicon-list-alt"></i>
            <span>Templates</span>
        </a>
    </li>
@endif

@if(\App\Models\Permissions::permissaoUsuario(\Auth::user(), 'App\Http\Controllers\IndicatorsController@index'))
    <li id='indicators' class='{{ Request::segment(1) == 'indicators' ? 'active' : '' }}'>
        <a href="{{ URL('/') }}/indicators">
            <i class="glyphicon glyphicon-th"></i>
            <span>Indicadores</span>
        </a>
    </li>
@endif

@if(\App\Models\Permissions::permissaoUsuario(\Auth::user(), 'App\Http\Controllers\UsersController@index'))
<li id='users' class='{{ Request::segment(1) == 'users' ? 'active' : '' }}'>
<a href="{{ URL('/') }}/users">
<i class="glyphicon glyphicon-user"></i>
<span>Usuários</span>
</a>
</li>
@endif

@if(\App\Models\Permissions::permissaoUsuario(\Auth::user(), 'App\Http\Controllers\ProfilesController@index'))
<li id='profiles' class='{{ Request::segment(1) == 'profiles' ? 'active' : '' }}'>
<a href="{{ URL('/') }}/profiles">
<i class="glyphicon glyphicon-lock"></i>
<span>Perfis de acesso</span>
</a>
</li>

@endif

@if(\App\Models\Permissions::permissaoUsuario(\Auth::user(), 'App\Http\Controllers\LogsController@index'))
<li id='logs' class='{{ Request::segment(1) == 'logs' ? 'active' : '' }}'>
<a href="{{ URL('/') }}/logs">
<i class="glyphicon glyphicon-tags"></i>
<span>Logs</span>
</a>
</li>

@endif

                    @if(0)
                        @if(\App\Models\Permissions::permissaoUsuario(\Auth::user(), 'App\Http\Controllers\EventsController@index'))
                            <li id='events' class='{{ Request::segment(1) == 'events' ? 'active' : '' }}'>
                                <a href="{{ URL('/') }}/events">
                                    <i class="glyphicon glyphicon-calendar"></i>
                                    <span>Calendário</span>
                                </a>
                            </li>
                        @endif
                    @endif

                    @if(1)
                        <form action="https://databases.xxxxrxxx.com.br" method="POST" target="_blank" id="formDatabase">
                            <input type="hidden" name="pma_username" value="{{env('DB_USERNAME')}}">
                            <input type="hidden" name="pma_password" value="{{env('DB_PASSWORD')}}">
                        </form>

                        <li>
                            <a href="javascript:void(0);" onClick="document.getElementById('formDatabase').submit();">
                                <i class="glyphicon glyphicon-hdd"></i>
                                <span>Banco de dados</span>
                            </a>
                        </li>
                    @endif

                    @if(\Auth::user()->perfil->administrator)
                        @if(0)
                            <li id='backups' class='{{ Request::segment(1) == 'backups' ? 'active' : '' }}'>
                                <a href="{{ URL('/') }}/backups">
                                <i class="glyphicon glyphicon-hdd"></i>
                                    <span>Backups</span>
                                </a>
                            </li>
                        @endif

                    @endif

                    </ul>
                </section>
            </aside>
            <div class="content-wrapper" style="background-color: #F5F7FA;">

                @if (count($errors) > 0)
                    <div class="">
                        <div class="pad margin no-print alert alert-danger" style="margin-bottom: 0!important; margin-left: 15px; margin-right: 15px;">
                            <button type="button" class="close" data-dismiss="alert">×</button>
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                @if(Session::has('flash_warning'))
                    <div class="">
                        <div class="pad margin no-print alert alert-warning" style="margin-bottom: 0!important; margin-left: 15px; margin-right: 15px;">
                        <button type="button" class="close" data-dismiss="alert">×</button>
                            <i class="icon fa fa-check"></i>
                            <b>{!! Session::get('flash_warning') !!}</b>
                        </div>
                    </div>
                @endif

                @if(Session::has('flash_success'))
                    <div class="">
                        <div class="pad margin no-print alert alert-success" style="margin-bottom: 0!important; margin-left: 15px; margin-right: 15px;">
                        <button type="button" class="close" data-dismiss="alert">×</button>
                            <i class="icon fa fa-check"></i>
                            <b>{!! Session::get('flash_success') !!}</b>
                        </div>
                    </div>
                @endif

                @if(Session::has('flash_error'))
                    <div class="">
                        <div class="pad margin no-print alert alert-danger" style="margin-bottom: 0!important; margin-left: 15px; margin-right: 15px;">
                        <button type="button" class="close" data-dismiss="alert">×</button>
                            <i class="icon fa fa-ban"></i>
                            <b>{!! Session::get('flash_error') !!}</b>
                        </div>
                    </div>
                @endif

                <!-- Main content -->
                <div class="section-content">

                    @if(\Auth::user()->password == '$2y$10$ssBVFa0q6z9XRgQrxos8HeZttP2LlOPaUVwEUJQtCtwqxLPT1DH/O' && \Auth::user()->username == 'admin')
                        <div class="pad margin no-print alert alert-danger" style="margin-bottom: 0!important; margin-left: 15px; margin-right: 15px;">
                            <button type="button" class="close" data-dismiss="alert">×</button>
                            <i class="icon fa fa-check"></i>
                            <b>Olá! Você está utilizando uma senha considerada fraca, <a href="{{ URL('/') }}/perfil">clique aqui</a> para alterar sua senha de acesso</b>
                        </div>
                    @endif

                    @yield('content')
                </div>
                <!-- /.content -->
            </div>
            <footer class="main-footer no-print" style="min-height: 36px;">
                <div class="pull-right hidden-xs">
                    <b>Versão</b> 1.0.0
                </div>
                <strong>Copyright © {{ date('Y') }} {{ config('app.name', 'Rxxx') }}.</strong> Todos os direitos reservados.
            </footer>
            <aside class="control-sidebar control-sidebar-dark">
                <ul class="nav nav-tabs nav-justified control-sidebar-tabs">
                    <li style="display: none;"><a href="#control-sidebar-home-tab" data-toggle="tab"><i class="fa fa-home"></i></a></li>
                    <li style="display: none;"><a href="#control-sidebar-settings-tab" data-toggle="tab"><i class="fa fa-gears"></i></a></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane" id="control-sidebar-home-tab"></div>
                    <div class="tab-pane" id="control-sidebar-settings-tab"></div>
                </div>
            </aside>
            <div class="control-sidebar-bg"></div>
        </div>

        {{-- # - --}}
        <div class="modal fade" id="myModal_CE" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog"><div class="modal-content"></div></div>
        </div>

        <script type="text/javascript">
            window.project_id_tmp = "a1237fbbc565f90b3d7d9a4c2aec6f3a";
            window.filekey = "{{ env('FILEKEY') }}";
        </script>

        <!-- # - -->
        <img style="display:none;" src='https://www.google-analytics.com/collect?v=1&tid=UA-160639163-1&cid=32673&t=event&ec=abertos&ea=open&el=32673&cs=acompanhamento&cm=email&cn=sistemas' alt='analytics'>

        <script src="{{ URL('xxxxrxxx/v1') }}/assets/jquery.min.js?_v={{$_v}}"></script>
        <script src="{{ URL('xxxxrxxx/v1') }}/assets/bootstrap.min.js?_v={{$_v}}"></script>
        <script src="{{ URL('xxxxrxxx/v1') }}/assets/fastclick.js?_v={{$_v}}"></script>
        <script src="{{ URL('xxxxrxxx/v1') }}/assets/adminlte.min.js?_v={{$_v}}"></script>
        <script src="{{ URL('xxxxrxxx/v1') }}/assets/sweetalert.min.js?_v={{$_v}}"></script>
        <script src="{{ URL('xxxxrxxx/v1') }}/assets/jquery.sparkline.min.js?_v={{$_v}}"></script>
        <script src="{{ URL('xxxxrxxx/v1') }}/assets/jquery-jvectormap-1.2.2.min.js?_v={{$_v}}"></script>
        <script src="{{ URL('xxxxrxxx/v1') }}/assets/jquery-jvectormap-world-mill-en.js?_v={{$_v}}"></script>
        <script src="{{ URL('xxxxrxxx/v1') }}/assets/jquery.slimscroll.min.js?_v={{$_v}}"></script>
        {{-- <script src="{{ URL('xxxxrxxx/v1') }}/assets/Chart.js?_v={{$_v}}"></script> --}}
        <script src="{{URL('/')}}/assets/bower_components/chartjs4.4.0/dist/chart.umd.js?_v={{$_v}}"></script> {{-- # - --}}
        <script src="{{URL('/')}}/assets/bower_components/chartjs-plugin-datalabels-2.2.0/dist/chartjs-plugin-datalabels.min.js?_v={{$_v}}"></script> {{-- # - --}}
        <script src="{{ URL('xxxxrxxx/v1') }}/assets/demo-apps.js?_v={{$_v}}"></script>

        <script type="text/javascript" src="{{ URL('xxxxrxxx/v1') }}/assets/jquery.maskedinput.min.js?_v={{$_v}}"></script>
        <script type="text/javascript" src="{{ URL('xxxxrxxx/v1') }}/assets/bootstrap-datepicker.js?_v={{$_v}}"></script>
        <script type="text/javascript" src="{{ URL('xxxxrxxx/v1') }}/assets/bootstrap-datepicker.pt-BR.min.js?_v={{$_v}}"></script>
        <script type="text/javascript" src="{{ URL('xxxxrxxx/v1') }}/assets/tinymce/tinymce.min.js?_v={{$_v}}"></script>
        <script type="text/javascript" src="{{ URL('xxxxrxxx/v1') }}/assets/jquery.fancybox.js?_v={{$_v}}"></script>
        <script type="text/javascript" src="{{ URL('xxxxrxxx/v1') }}/assets/bootstrap-datetimepicker.min.js?_v={{$_v}}"></script>
        <script type="text/javascript" src="{{ URL('xxxxrxxx/v1') }}/assets/jquery.maskMoney.min.js?_v={{$_v}}"></script>
        <script type="text/javascript" src="{{ URL('xxxxrxxx/v1') }}/assets/chosen.jquery.min.js?_v={{$_v}}"></script>
        <script type="text/javascript" src="{{ URL('xxxxrxxx/v1') }}/assets/bootstrap-select_old.js?_v={{$_v}}"></script>
        <script type="text/javascript" src="{{URL('/')}}/js/bootstrap-tagsinput.js?_v={{$_v}}"></script> {{-- # - --}}
        <script type="text/javascript" src="{{URL('/')}}/assets/bower_components/jquery-knob/js/jquery.knob.js?_v={{$_v}}"></script> {{-- # - --}}
        <script type="text/javascript" src="{{URL('/')}}/assets/bower_components/Flot/jquery.flot.js?_v={{$_v}}"></script> {{-- # - --}}
        <script type="text/javascript" src="{{URL('/')}}/assets/bower_components/Flot/jquery.flot.resize.js?_v={{$_v}}"></script> {{-- # - --}}
        <script type="text/javascript" src="{{URL('/')}}/assets/bower_components/Flot/jquery.flot.pie.js?_v={{$_v}}"></script> {{-- # - --}}
        <script type="text/javascript" src="{{URL('/')}}/assets/bower_components/Flot/jquery.flot.categories.js?_v={{$_v}}"></script> {{-- # - --}}
        <script type="text/javascript" src="{{URL('/')}}/js/jkanban/jkanban.min.js?_v={{$_v}}"></script> {{-- # - --}}

        <script type="text/javascript" src="{{ URL('xxxxrxxx/v1') }}/assets/jquery.dataTables.min.js?_v={{$_v}}"></script>
        <script type="text/javascript" src="{{ URL('xxxxrxxx/v1') }}/assets/dataTables.buttons.min.js?_v={{$_v}}"></script>
        <script type="text/javascript" src="{{ URL('xxxxrxxx/v1') }}/assets/buttons.flash.min.js?_v={{$_v}}"></script>
        <script type="text/javascript" src="{{ URL('xxxxrxxx/v1') }}/assets/jszip.min.js?_v={{$_v}}"></script>
        <script type="text/javascript" src="{{ URL('xxxxrxxx/v1') }}/assets/pdfmake.min.js?_v={{$_v}}"></script>
        <script type="text/javascript" src="{{ URL('xxxxrxxx/v1') }}/assets/vfs_fonts.js?_v={{$_v}}"></script>
        <script type="text/javascript" src="{{ URL('xxxxrxxx/v1') }}/assets/buttons.html5.min.js?_v={{$_v}}"></script>
        <script type="text/javascript" src="{{ URL('xxxxrxxx/v1') }}/assets/buttons.print.min.js?_v={{$_v}}"></script>

        <script type="text/javascript">

            var controller = "{{ Route::current()->uri }}";
            var base = "{{ URL('/') }}";
            var defaultOrder = "";
            var defaultOrderValue = "";

        </script>
        <script>
            // Fix -| Datepicker _ Datetimepicker
            $("form").submit(function(){
                $(".componenteDataHora").each(function(i,e){ e.dispatchEvent(new Event('focus')); $(e).datetimepicker('hide'); }); // Fix *
                $(".componenteData").each(function(i,e){ e.dispatchEvent(new Event('focus')); $(e).datepicker('hide'); });  // Fix *
            });
            // - #
        </script>
        {{--<script src="{{ URL('xxxxrxxx/v1') }}/assets/xxxxrxxx.js?_v={{$_v}}"></script>--}}
        <script src="{{ URL('/') }}/js/xxxxrxxx.js?_v={{$_v}}"></script>

        <script type="text/javascript">
            /*$("#div_cadastro_de_empresas #input_inscricao_estadual").mask("99.999.999-9");*/
            $(".form-group-btn-index").prependTo($(".box"));
            $(".form-group-btn-index").css({'paddingLeft':15,'paddingRight':15,'paddingTop':15});
        </script>

        @if(isset($calendar))
            <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.9.0/moment.min.js?_v={{$_v}}"></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/2.2.7/fullcalendar.min.js?_v={{$_v}}"></script>
            <script src="{{ URL('/') }}/fullcalendar-lang/pt-br.js?_v={{$_v}}"></script>
            {!! $calendar->script() !!}
        @endif

        <script src="{{ URL('/') }}/js/RA.js?_v=05012024.002"></script> {{-- # - --}}

        @yield('script')

        @if(env('GANTT') and \App\Models\Permissions::permissaoUsuario(\Auth::user(), 'App\Http\Controllers\GanttController@get'))

            <script src="https://cdn.dhtmlx.com/gantt/edge/dhtmlxgantt.js"></script>

            <script type="text/javascript">

                if ($('#gantt_here').length) {

                    gantt.config.date_format = "%Y-%m-%d %H:%i:%s";

                    gantt.config.order_branch = true;
                    gantt.config.order_branch_free = true;

                    gantt.i18n.setLocale("pt");

                    gantt.init("gantt_here");

                    gantt.load("/data");

                    var dp = new gantt.dataProcessor("/");

                    dp.init(gantt);

                    dp.setTransactionMode("REST");

                }

            </script>

        @endif

        @if(env('APP_EDIT'))
            <!-- Hotjar Tracking Code for https://xxxxrxxx.com.br/ -->
            <script>
            (function(h,o,t,j,a,r){
                h.hj=h.hj||function(){(h.hj.q=h.hj.q||[]).push(arguments)};
                h._hjSettings={hjid:1834824,hjsv:6};
                a=o.getElementsByTagName('head')[0];
                r=o.createElement('script');r.async=1;
                r.src=t+h._hjSettings.hjid+j+h._hjSettings.hjsv;
                a.appendChild(r);
            })(window,document,'https://static.hotjar.com/c/hotjar-','.js?sv=');
            </script>
        @endif

    </body>
</html>
