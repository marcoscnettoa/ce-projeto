<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Closure;
use Auth;
use Session;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        if (! $request->expectsJson()) {
            return route('login');
        }
    }

    // #
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string[]  ...$guards
     * @return mixed
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function handle($request, Closure $next, ...$guards)
    {
        try {

            $this->authenticate($request, $guards);

        }catch(\Exception $e){
            // ! Caso seja uma solicitação da 'Api'
            // ! Não tem autorização de utilizar o access_token | refresh_token
            if(in_array('api',$guards)){
                return response()->json([
                    'error'                 => 'access_token_not_authorized',
                    'error_description'     => 'O token de acesso não foi encontrado ou expirou!'
                ], 401);
            }
        }

        // ! Remove Sessão Dupla Derruba o Login Anterior
        // ! Não pode ser Guards - API | auth:api
        if(
            !in_array('api',$guards) &&
            (
                !Auth::check() ||
                (Auth::check() && Auth::user()->last_session_id != Session::getId())
            )
        ){
            Auth::logout();
            return redirect()->to('/login');
        }

        return $next($request);
    }
    // -
}
