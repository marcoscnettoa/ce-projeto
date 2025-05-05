<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GerencianetConfig extends Model
{
    static public function options()
    {
        if (env('APP_GERENCIANET_SANDBOX')) {
            
            $options = [
                'client_id' => env('APP_GERENCIANET_SANDBOX_ID'),
                'client_secret' => env('APP_GERENCIANET_SANDBOX_SECRET'),
                'sandbox' => true,
                'timeout' => 30
            ];
        }
        else
        {
            $options = [
                'client_id' => env('APP_GERENCIANET_PRODUCTION_ID'),
                'client_secret' => env('APP_GERENCIANET_PRODUCTION_SECRET'),
                'sandbox' => false,
                'timeout' => 30
            ];
        }

        return $options;
    }
}
