<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
//        if ($this->app->environment('production')) {
//            $this->app['request']->server->set('HTTPS','on');
//            URL::forceScheme('https');
//        }
         URL::forceScheme('https');
    }
}
