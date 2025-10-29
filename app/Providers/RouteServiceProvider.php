<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router;

class RouteServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        $this->app->make(Router::class)
            ->aliasMiddleware('role', \App\Http\Middleware\RoleMiddleware::class);
    }
}
