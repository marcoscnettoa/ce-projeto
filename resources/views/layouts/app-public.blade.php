@php
    $_v = '25042024-001';
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
        <link rel="stylesheet" href="{{ URL('/') }}/assets/bower_components/bootstrap/dist/css/bootstrap.min.css?_v={{$_v}}">
        <link rel="stylesheet" href="{{ URL('/') }}/assets/bower_components/font-awesome/css/font-awesome.min.css?_v={{$_v}}">
        <link rel="stylesheet" href="{{ URL('/') }}/assets/bower_components/Ionicons/css/ionicons.min.css?_v={{$_v}}">
        <link rel="stylesheet" href="{{ URL('/') }}/assets/bower_components/jvectormap/jquery-jvectormap.css?_v={{$_v}}">
        <link rel="stylesheet" href="{{ URL('/') }}/assets/dist/css/sweetalert.css?_v={{$_v}}">
        <link rel="stylesheet" href="{{ URL('/') }}/assets/dist/css/AdminLTE.min.css?_v={{$_v}}">
        <link rel="stylesheet" href="{{ URL('/') }}/assets/dist/css/skins/_all-skins.min.css?_v={{$_v}}">
        <link rel="stylesheet" href="{{ asset('css/jquery.dataTables.css') }}?_v={{$_v}}" rel="stylesheet">
        <link rel="stylesheet" href="{{ URL('/') }}/css/xxxxrxxx.css?_v={{$_v}}">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">

        <link rel="stylesheet" href="{{ asset('css/datepicker.css') }}?_v={{$_v}}" rel="stylesheet">
        <link rel="stylesheet" href="{{ asset('css/bootstrap-datetimepicker.min.css') }}?_v={{$_v}}" rel="stylesheet">
        <link rel="stylesheet" href="{{ asset('css/jquery.fancybox.css') }}?_v={{$_v}}" rel="stylesheet">
        <link rel="stylesheet" href="{{ asset('css/chosen.min.css') }}?_v={{$_v}}" rel="stylesheet">
        <link rel="stylesheet" href="{{ asset('css/dataTables.tableTools.min.css') }}?_v={{$_v}}" rel="stylesheet">
        <link rel="stylesheet" href="{{ asset('css/bootstrap-select.css') }}?_v={{$_v}}" rel="stylesheet">

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

            .alert-success{
                background-color: #06C270 !important;
                border-color: #06C270;
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
        <div style="width:600px; height:600px;  position:fixed;left:45%;top:50%;z-index:9999;">
            <img src="{{ URL('/') }}/assets/images/loader-barra.gif"/>
        </div>
    </div>

    <body class="sidebar-mini fixed sidebar-mini-expand-feature skin-blue">
        <div class="wrapper">
            <div class="content-wrapper" style="margin-left: 0px; margin-top: 0px; background-color: #F5F7FA;">

                @if (count($errors) > 0)
                    <div style="margin-left: 20%;margin-right: 20%;">
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

                @if(Session::has('flash_success'))
                    <div style="margin-left: 20%;margin-right: 20%;">
                        <div class="pad margin no-print alert alert-success" style="margin-bottom: 0!important; margin-left: 15px; margin-right: 15px;">
                        <button type="button" class="close" data-dismiss="alert">×</button>
                            <i class="icon fa fa-check"></i>
                            <b>{!! Session::get('flash_success') !!}</b>
                        </div>
                    </div>
                @endif

                @if(Session::has('flash_error'))
                    <div style="margin-left: 20%;margin-right: 20%;">
                        <div class="pad margin no-print alert alert-danger" style="margin-bottom: 0!important; margin-left: 15px; margin-right: 15px;">
                        <button type="button" class="close" data-dismiss="alert">×</button>
                            <i class="icon fa fa-ban"></i>
                            <b>{!! Session::get('flash_error') !!}</b>
                        </div>
                    </div>
                @endif

                {{-- <div class="section-content" style="margin-left: 20%; margin-right: 20%;"> --}}
                <div class="section-content">
                    @yield('content')
                </div>

            </div>
            <div class="control-sidebar-bg"></div>
        </div>

        <script type="text/javascript">
            window.project_id_tmp = "32673/1705677020";
            window.filekey = "{{ env('FILEKEY') }}";
        </script>

        <img src='https://www.google-analytics.com/collect?v=1&tid=G-E4DF4T96FH&cid={{\URL::current()}}&t=event&ec=sistemas&ea=open&el=32673&cs=track&cm=email&cn=sistemas' alt='analytics'>

        <img src='https://www.google-analytics.com/collect?v=1&tid=UA-160639163-1&cid=32673&t=event&ec=abertos&ea=open&el=32673&cs=acompanhamento&cm=email&cn=sistemas' alt='analytics'>

        <script src="https://xxxxrxxx.com.br/v1/assets/jquery.min.js?_v={{$_v}}"></script>
        <script src="https://xxxxrxxx.com.br/v1/assets/bootstrap.min.js?_v={{$_v}}"></script>
        <script src="https://xxxxrxxx.com.br/v1/assets/fastclick.js?_v={{$_v}}"></script>
        <script src="https://xxxxrxxx.com.br/v1/assets/adminlte.min.js?_v={{$_v}}"></script>
        <script src="https://xxxxrxxx.com.br/v1/assets/sweetalert.min.js?_v={{$_v}}"></script>
        <script src="https://xxxxrxxx.com.br/v1/assets/jquery.sparkline.min.js?_v={{$_v}}"></script>
        <script src="https://xxxxrxxx.com.br/v1/assets/jquery-jvectormap-1.2.2.min.js?_v={{$_v}}"></script>
        <script src="https://xxxxrxxx.com.br/v1/assets/jquery-jvectormap-world-mill-en.js?_v={{$_v}}"></script>
        <script src="https://xxxxrxxx.com.br/v1/assets/jquery.slimscroll.min.js?_v={{$_v}}"></script>
        {{-- <script src="https://xxxxrxxx.com.br/v1/assets/Chart.js?_v={{$_v}}"></script> --}}
        <script src="{{URL('/')}}/assets/bower_components/chartjs4.4.0/dist/chart.umd.js?_v={{$_v}}"></script> {{-- # - --}}
        <script src="{{URL('/')}}/assets/bower_components/chartjs-plugin-datalabels-2.2.0/dist/chartjs-plugin-datalabels.min.js?_v={{$_v}}"></script> {{-- # - --}}
        <script src="https://xxxxrxxx.com.br/v1/assets/demo-apps.js?_v={{$_v}}"></script>

        <script type="text/javascript" src="https://xxxxrxxx.com.br/v1/assets/jquery.maskedinput.min.js?_v={{$_v}}"></script>
        <script type="text/javascript" src="https://xxxxrxxx.com.br/v1/assets/bootstrap-datepicker.js?_v={{$_v}}"></script>
        <script type="text/javascript" src="https://xxxxrxxx.com.br/v1/assets/bootstrap-datepicker.pt-BR.min.js?_v={{$_v}}"></script>
        <script type="text/javascript" src="https://xxxxrxxx.com.br/v1/assets/tinymce/tinymce.min.js?_v={{$_v}}"></script>
        <script type="text/javascript" src="https://xxxxrxxx.com.br/v1/assets/jquery.fancybox.js?_v={{$_v}}"></script>
        <script type="text/javascript" src="https://xxxxrxxx.com.br/v1/assets/bootstrap-datetimepicker.min.js?_v={{$_v}}"></script>
        <script type="text/javascript" src="https://xxxxrxxx.com.br/v1/assets/jquery.maskMoney.min.js?_v={{$_v}}"></script>
        <script type="text/javascript" src="https://xxxxrxxx.com.br/v1/assets/chosen.jquery.min.js?_v={{$_v}}"></script>
        <script type="text/javascript" src="https://xxxxrxxx.com.br/v1/assets/bootstrap-select.js?_v={{$_v}}"></script>

        <script type="text/javascript" src="https://xxxxrxxx.com.br/v1/assets/jquery.dataTables.min.js?_v={{$_v}}"></script>
        <script type="text/javascript" src="https://xxxxrxxx.com.br/v1/assets/dataTables.buttons.min.js?_v={{$_v}}"></script>
        <script type="text/javascript" src="https://xxxxrxxx.com.br/v1/assets/buttons.flash.min.js?_v={{$_v}}"></script>
        <script type="text/javascript" src="https://xxxxrxxx.com.br/v1/assets/jszip.min.js?_v={{$_v}}"></script>
        <script type="text/javascript" src="https://xxxxrxxx.com.br/v1/assets/pdfmake.min.js?_v={{$_v}}"></script>
        <script type="text/javascript" src="https://xxxxrxxx.com.br/v1/assets/vfs_fonts.js?_v={{$_v}}"></script>
        <script type="text/javascript" src="https://xxxxrxxx.com.br/v1/assets/buttons.html5.min.js?_v={{$_v}}"></script>
        <script type="text/javascript" src="https://xxxxrxxx.com.br/v1/assets/buttons.print.min.js?_v={{$_v}}"></script>

        <script type="text/javascript">

            var controller = "{{ Route::current()->uri }}";
            var base = "{{ URL('/') }}";
            var defaultOrder = "";
            var defaultOrderValue = "";

        </script>

        <script src="{{ URL('/') }}/js/xxxxrxxx.js?_v={{$_v}}"></script>

        <script type="text/javascript">
        $("#div_cadastro_de_empresas #input_inscricao_estadual").mask("99.999.999-9");
$(".form-group-btn-index").prependTo($(".box"));

$(".form-group-btn-index").css({'paddingLeft':15,'paddingRight':15,'paddingTop':15});
        </script>

        @if(isset($calendar))
            <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.9.0/moment.min.js?_v={{$_v}}"></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/2.2.7/fullcalendar.min.js?_v={{$_v}}"></script>
            <script src="{{ URL('/') }}/fullcalendar-lang/pt-br.js?_v={{$_v}}"></script>
            {!! $calendar->script() !!}
        @endif

        @yield('script')

    </body>
</html>
