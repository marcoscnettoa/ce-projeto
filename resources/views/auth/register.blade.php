@extends('layouts.auth')

@section('content')

<div class="page-login">
    <main>
        <div>
            <div id="main-wrapper">
                <div class="row">
                    <div class="col-md-3 center">
                        <div class="login-box">

                            @if ($alert = Session::get('flash_error'))
                              <div class="alert alert-danger text-center">
                                  {{ $alert }}
                              </div>
                            @endif

                            <a href="{{ URL('/') }}" class="logo-name text-lg text-center"><p style="text-align: center;"><img src="../images/b-3d94cbefe10da742f85b9c48ffa71f54.jpg" alt="" width="150" height="150" /></p>

<p style="text-align: center;"><strong>FA&Ccedil;A SEU LOGIN</strong></p>

<p style="text-align: center;">Informe seu usu&aacute;rio e senha</p></a>

                            <!--<p class="text-center m-t-md">Cadastre-se</p>-->

                            <form class="m-t-md" role="form" method="POST" action="{{ url('/register') }}">

                                {{ csrf_field() }}

                                <div class="form-group">
                                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="Nome" required autofocus>
                                    @if ($errors->has('name'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('name') }}</strong>
                                        </span>
                                    @endif
                                </div>

                                <div class="form-group">
                                    <input type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="E-mail" required>
                                    @if ($errors->has('email'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('email') }}</strong>
                                        </span>
                                    @endif
                                </div>

                                <div class="form-group">
                                    <input type="text" name="username" class="form-control" value="{{ old('username') }}" placeholder="Usuário" required>
                                    @if ($errors->has('username'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('username') }}</strong>
                                        </span>
                                    @endif
                                </div>

                                <div class="form-group">
                                    <div class="input-group" id="show_hide_password">
                                        <input type="password" name="password" class="form-control" placeholder="Senha" required>

                                        @if ($errors->has('password'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('password') }}</strong>
                                            </span>
                                        @endif

                                        <div class="input-group-addon">
                                            <a href="javascript:void(0);"><i class="fa fa-eye-slash" aria-hidden="true"></i></a>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <input type="password" name="password_confirmation" class="form-control" placeholder="Confirmação de senha" required>
                                    @if ($errors->has('password_confirmation'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('password_confirmation') }}</strong>
                                        </span>
                                    @endif
                                </div>

                                <button type="submit" class="btn btn-info btn-block">Criar conta</button>

                                <p class="display-block text-center m-t-md text-sm">Já tem uma conta? <a href="{{ url('/') }}">Clique para acessar</a></p>

                            </form>

                            <p class="text-center m-t-xs text-sm">{{ date('Y') }} &copy; {{ config('app.name', 'Rxxx') }}</p>

                        </div>
                    </div>
                </div><!-- Row -->
            </div><!-- Main Wrapper -->
        </div><!-- Page Inner -->
    </main><!-- Page Content -->
</div>
@endsection
