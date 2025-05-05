@php
    $_v = '25042024-001';
@endphp
<!DOCTYPE html>
<html lang="pt-br">
    <head>
        <title>{{ config('app.name', 'Rxxx') }}</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <meta name="robots" content="noindex, nofollow, noarchive">
        <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
        <meta http-equiv="Pragma" content="no-cache" />
        <meta http-equiv="Expires" content="0" />
        <link rel="icon" type="image/x-icon" href="{{ (ENV('FAVICON')?ENV('FAVICON'):"https://lxxxtxx.xxxxrxxxapps.com/images/logo-lxxxtxx.jpg") }}?_v={{$_v}}"/>
        <!-- Styles -->
        <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css?_v={{$_v}}" rel="stylesheet" type="text/css" />
        <link href="//cdnjs.cloudflare.com/ajax/libs/font-awesome/4.1.0/css/font-awesome.min.css?_v={{$_v}}" rel="stylesheet" type="text/css" />
        <link href="{{ URL('/') }}/css/modern.min.css?_v={{$_v}}" rel="stylesheet" type="text/css"/>
        <script>
            window.Laravel = <?php echo json_encode([
                'csrfToken' => csrf_token(),
            ]); ?>
        </script>
        <style>
            body{
                background: #F1F4F9;
            }
            .btn-info{
                background-color: #3568D4;
            }
            .btn-success{
                background-color: #06C270;
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
        <link href="{{ URL('/') }}/css/login.css" rel="stylesheet" type="text/css"/>
    </head>
    <body>
        @yield('content')
        <script src="{{ URL('/') }}/assets/bower_components/jquery/dist/jquery.min.js?_v={{$_v}}"></script>
        <script src="{{ URL('/') }}/assets/bower_components/bootstrap/dist/js/bootstrap.min.js?_v={{$_v}}"></script>
        <script type="text/javascript">
            $(document).ready(function() {
                $("#show_hide_password a").on('click', function(event) {
                    event.preventDefault();
                    if($('#show_hide_password input').attr("type") == "text"){
                        $('#show_hide_password input').attr('type', 'password');
                        $('#show_hide_password i').addClass( "fa-eye-slash" );
                        $('#show_hide_password i').removeClass( "fa-eye" );
                    }else if($('#show_hide_password input').attr("type") == "password"){
                        $('#show_hide_password input').attr('type', 'text');
                        $('#show_hide_password i').removeClass( "fa-eye-slash" );
                        $('#show_hide_password i').addClass( "fa-eye" );
                    }
                });
            });
        </script>
    </body>
</html>
