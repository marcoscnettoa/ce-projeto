<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Laravel\Passport\Passport;
use Database\Seeders\RCidades; // # -
use Database\Seeders\REstados; // # -
use App\Http\Middleware\AuthApi;
use Carbon\Carbon;
use URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
        Passport::ignoreRoutes();
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if (env('APP_ENV') === 'production') {
            URL::forceScheme('https');
        }

        // - #
        if(env('MODULO_NF_TIPO') == 'TECNOSPEED'){
            (new REstados)->run();
            (new RCidades)->run();
        }

        Passport::personalAccessTokensExpireIn(Carbon::now()->addDays(1)); // # -

        $registrar = new \App\Custom\Routing\ResourceRegistrar($this->app['router']);

        $this->app->bind('Illuminate\Routing\ResourceRegistrar', function () use ($registrar) {
            return $registrar;
        });
    }
}
