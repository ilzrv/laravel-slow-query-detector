<?php

declare(strict_types=1);

namespace Ilzrv\LaravelSlowQueryDetector\Tests;

use Illuminate\Database\SQLiteConnection;
use Illuminate\Support\Str;
use Ilzrv\LaravelSlowQueryDetector\Http\Middleware\SlowQueryDetectorMiddleware;
use Ilzrv\LaravelSlowQueryDetector\ServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Illuminate\Support\Facades\Log;
use TiMacDonald\Log\LogFake;

abstract class TestCase extends OrchestraTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Log::swap(new LogFake);
    }

    protected function getPackageProviders($app): array
    {
        return [ServiceProvider::class];
    }

    public function request(callable $callback): void
    {
        $uri = Str::random();

        $this->app['router']->get($uri, $callback)->middleware(SlowQueryDetectorMiddleware::class);

        $this->get($uri);
    }

    public function getConnectionWithSleepFunction(string $name = 'sqn'): SQLiteConnection
    {
        app()['config']->set('database.connections.'.$name, [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        $connection = \DB::connection($name);
        $pdo = $connection->getPdo();
        $pdo->sqliteCreateFunction(
            'sleep',
            function ($miliseconds) {
                return usleep($miliseconds * 1000);
            }
        );

        return $connection;
    }
}
