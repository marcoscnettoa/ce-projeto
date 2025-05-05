@extends('layouts.auth')

@section('content')

<div class="page-login">
    <main>
        <div>
            <div id="main-wrapper">
                <div class="login-box">
                    @if ($alert = Session::get('flash_error'))
                        <div class="alert alert-danger text-center">
                            {{ $alert }}
                        </div>
                    @endif
                    @if ($errors->has('username'))
                        <div class="alert alert-danger text-center">
                            {{ $errors->first('username') }}
                        </div>
                    @endif
                    @if ($alert = Session::get('flash_success'))
                        <div class="alert alert-success text-center">
                            {{ $alert }}
                        </div>
                    @endif
                    {{--https://s3.xxxxrxxx.com.br/files/170567702032673/images/Yi0zZDk0Y2JlZmUxMGRhNzQyZjg1YjljNDhmZmE3MWY1NC5qcGc=-65ba4a7b71fde.jpg--}}
                    <a href="{{ URL('/') }}" class="logo-name text-lg text-center"><p style="text-align: center; margin-bottom:30px;"><img src="{{URL('images')}}/logo-lxxxtxx.jpg" alt="" width="250" /></p></a>
                    <p style="text-align: center;"><strong>FA&Ccedil;A SEU LOGIN</strong></p>
                    <p style="text-align: center;">Informe seu usu&aacute;rio e senha</p>
                    <!--<p class="text-center m-t-md">Faça login na sua conta.</p>-->
                    <form class="m-t-md" role="form" method="POST" action="{{ url('/login') }}">
                        {{ csrf_field() }}
                        <div class="form-group">
                            @if(env('APP_EDIT'))
                                <input type="text" name="username" class="form-control" value="{{ env('DB_USERNAME') }}" placeholder="Usuário" required>
                            @else
                                <input type="text" name="username" class="form-control" placeholder="Usuário" required>
                            @endif
                        </div>

                        <div class="form-group">
                            <div class="input-group" id="show_hide_password">
                                @if(env('APP_EDIT'))
                                    <input type="password" name="password" class="form-control" value="{{ env('DB_PASSWORD') }}" placeholder="Senha" required>
                                @else
                                    <input type="password" name="password" class="form-control" placeholder="Senha" required>
                                @endif

                                @if ($errors->has('password'))
                                    <span class="help-block h-password">
                                                <strong>{{ $errors->first('password') }}</strong>
                                            </span>
                                @endif

                                <div class="input-group-addon">
                                    <a href="javascript:void(0);"><i class="fa fa-eye-slash" aria-hidden="true"></i></a>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-success btn-block">Login</button>

                        <a href="{{ url('/password/reset') }}" class="display-block text-center m-t-md text-sm">Esqueceu a senha?</a>

                        @if(env('ENV_ENABLE_CADASTRO'))
                            <p class="text-center m-t-xs text-sm">Não tem uma conta?</p>
                            <a href="{{ url('/register') }}" class="btn btn-info btn-block m-t-md">Criar uma conta</a>
                        @endif

                    </form>
                    <p class="text-center m-t-xs text-sm">{{ date('Y') }} &copy; {{ config('app.name', 'Rxxx') }}</p>
                </div>
            </div><!-- Main Wrapper -->
        </div><!-- Page Inner -->
    </main><!-- Page Content -->
</div>

@endsection
