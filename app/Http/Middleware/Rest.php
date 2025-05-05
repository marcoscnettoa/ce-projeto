<?php

namespace App\Http\Middleware;

use Closure;
use Auth;
use \App\Models\User;
use Exception;
use Log;
use Route;

class Rest
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        try {

            if (!in_array(
                Route::getCurrentRoute()->getActionName(),
                [
                    'App\Http\Controllers\Api\SetupController@setup',
                    'App\Http\Controllers\Api\AuthController@login',
                    'App\Http\Controllers\Api\AuthController@refresh_token',
                    'App\Http\Controllers\Api\UsersController@store_register',
                    'App\Http\Controllers\Api\UsersController@password_recovery',
                    'App\Http\Controllers\Api\UsersController@password_reset',
                ]
            )) {

                if (!env('APP_PLANO_CONTRATADO')) {
                    throw new Exception('Para utilizar este serviço, é necessário contratar o plano Enterprise!', 403);
                }

                if (env('APP_PLANO_CONTRATADO_DS') != 'ENTERPRISE') {
                    throw new Exception('O serviço que você está tentando acessar não está disponível no seu plano atual. Para utilizar este serviço, por favor, atualize seu plano.', 403);
                }

                $token = $request->bearerToken();

                if (!$token) {
                    throw new Exception('O token não foi informado na requisição', 400);
                }

                $user = User::where('remember_token', $token)->first();

                if (!$user) {
                    throw new Exception("Usuário não encontrado ou sem permissão para executar essa ação!", 401);
                }

                Auth::login($user);

            }

            return $next($request);

        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 401);
        }
    }
}
