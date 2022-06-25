<?php

namespace Ilzrv\LaravelSlowQueryDetector\Tests;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use TiMacDonald\Log\LogEntry;

class SlowCodeTest extends TestCase
{
    public function testBeNotifiedWhenCodeExceededMaxTime(): void
    {
        $this->request(fn() => usleep(1000000)); // 1 s

        Log::assertLogged(
            fn(LogEntry $log) => $log->level === 'critical' && Str::contains($log->message, 'SQD')
        );
    }

    public function testBeSilentWhenCodeRunsFine(): void
    {
        $this->request(fn() => usleep(900000)); // 0.9 s

        Log::assertNotLogged(fn(LogEntry $log) => $log->level === 'critical');
    }

    public function testBeNotifiedWhenCodeHasTooManyQueries(): void
    {
        $this->request(function () {
            $connection = $this->getConnectionWithSleepFunction();
            for ($i = 0; $i < 51; $i++) {
                $connection->select((\DB::raw('select sleep(1)')));
            }
        });

        Log::assertLogged(
            fn(LogEntry $log) => $log->level === 'critical' && Str::contains($log->message, 'SQD')
        );
    }

    public function testBeSilentWhenCodeHasFineCountQueries(): void
    {
        $this->request(function () {
            $connection = $this->getConnectionWithSleepFunction();
            for ($i = 0; $i < 50; $i++) {
                $connection->select((\DB::raw('select sleep(1)')));
            }
        });

        Log::assertNotLogged(fn(LogEntry $log) => $log->level === 'critical');
    }
}
