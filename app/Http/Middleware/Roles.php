<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use \App\Models\Permissions;
use Route;
use Redirect;
use Session;

class Roles
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {   
        $logged = Auth::user();

        if (!Permissions::permissaoUsuario($logged, Route::getCurrentRoute()->getActionName())) {
            Session::flash('flash_error', "Você não tem permissão de acessar essa página.");
            return Redirect::to('/');
        }  

        return $next($request);
    }
}
