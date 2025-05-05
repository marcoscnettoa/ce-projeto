@extends('layouts.auth')

@section('content')

<div class="page-login">
    <main>
        <div>
            <div id="main-wrapper">
                <div class="login-box">
                    @if(session('status'))
                        <p class="alert alert-success">{{ session('status') }}</p>
                    @endif
                    @if ($errors->has('email'))
                        <div class="alert alert-danger text-center">
                            {{ $errors->first('email') }}
                        </div>
                    @endif
                    {{--<a href="{{ URL('/') }}" class="logo-name text-lg text-center">{{ config('app.name', 'Rxxx') }}</a>--}}
                    <a href="{{ URL('/') }}" class="logo-name text-lg text-center"><p style="text-align: center; margin-bottom:30px;"><img src="{{URL('images')}}/logo-lxxxtxx.jpg" alt="" width="250" /></p></a>
                    <p class="text-center m-t-md">Informe seu e-mail.</p>
                    <form class="m-t-md" role="form" method="POST" action="{{ url('/password/email') }}">

                        {{ csrf_field() }}
                        <div class="form-group">
                            <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" placeholder="E-mail"  required>

                        </div>
                        <button type="submit" class="btn btn-success btn-block">Recuperar senha</button>
                    </form>
                    <p class="display-block text-center m-t-md text-sm">Já tem uma conta? <a href="{{ url('/') }}">Clique para acessar</a></p>
                    <p class="text-center m-t-xs text-sm">{{ date('Y') }} &copy; {{ config('app.name', 'Rxxx') }}</p>
                </div>
            </div><!-- Main Wrapper -->
        </div><!-- Page Inner -->
    </main><!-- Page Content -->
</div>
@endsection
