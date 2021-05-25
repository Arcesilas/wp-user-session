<?php

namespace WpUserSession;

use Illuminate\Support\ServiceProvider;

class WpUserSessionServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/wp-user-session.php' => config_path('wp-user-session.php'),
        ]);
        app('router')->pushMiddlewareToGroup('web', Http\Middleware\WpSessionMiddleware::class);
    }
}
