<?php

declare(strict_types=1);

namespace Ilzrv\LaravelSlowQueryDetector;

use Ilzrv\LaravelSlowQueryDetector\Http\Middleware\SlowQueryDetectorMiddleware;

final class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/sqd.php', 'sqd');
    }

    public function boot(): void
    {
        $this->publishes([__DIR__ . '/../config/sqd.php' => config_path('sqd.php')]);

        if (config('sqd.enabled') === true) {
            $this->app['router']->pushMiddlewareToGroup('web', SlowQueryDetectorMiddleware::class);
        }
    }
}
