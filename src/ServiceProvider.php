<?php

namespace Ilzrv\LaravelSlowQueryDetector;

use Ilzrv\LaravelSlowQueryDetector\Http\Middleware\SlowQueryDetectorMiddleware;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
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
        $this->publishes([__DIR__ . '/../config/sqd.php' => config_path('sqd.php')]);

        if (config('sqd.enabled')) {
            $this->app['router']->pushMiddlewareToGroup('web', SlowQueryDetectorMiddleware::class);
        }
    }
}
