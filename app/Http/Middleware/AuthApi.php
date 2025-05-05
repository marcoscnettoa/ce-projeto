<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Auth;
use Exception;
use Log;
use Route;

class AuthApi
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

            $RequestHeaderAuthorization = $request->header('Authorization');
            if($RequestHeaderAuthorization){
                if(strpos($RequestHeaderAuthorization, 'Bearer') === 0){
                    $user               = Auth::user();
                    if($user and Carbon::parse($user->token()->expires_at)->isPast()){
                        return response()->json([
                            'error'                 => 'access_token_expired',
                            'error_description'     => 'O token de acesso expirou!'
                        ], 401);
                    }
                }
            }

            return $next($request);

        }catch(\Exception $e){
            Log::error($e->getMessage());
            return response()->json([
                'error'                 => 'auth_api_authorization_expired',
                'error_description'     => 'Não foi possível validar autenticação!'
            ], 400);
        }
    }
}
