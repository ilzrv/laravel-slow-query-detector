<?php

declare(strict_types=1);

namespace Ilzrv\LaravelSlowQueryDetector\Tests;

use Illuminate\Database\Connection;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Ilzrv\LaravelSlowQueryDetector\Http\Middleware\SlowQueryDetectorMiddleware;
use Ilzrv\LaravelSlowQueryDetector\ServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use TiMacDonald\Log\LogFake;

abstract class TestCase extends OrchestraTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Log::swap(new LogFake());
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

    public function getConnectionWithSleepFunction(string $name = 'sqn'): Connection
    {
        app()['config']->set('database.connections.' . $name, [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        /** @var DatabaseManager $databaseManager */
        $databaseManager = app(DatabaseManager::class);

        $connection = $databaseManager->connection($name);

        $connection
            ->getPdo()
            ->sqliteCreateFunction(
                'sleep',
                function (int $milliseconds) {
                    return usleep($milliseconds * 1000);
                }
            );

        return $connection;
    }
}
