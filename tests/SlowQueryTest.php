<?php

declare(strict_types=1);

namespace Ilzrv\LaravelSlowQueryDetector\Tests;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use TiMacDonald\Log\LogEntry;

final class SlowQueryTest extends TestCase
{
    public function testBeNotifiedWhenQueryExceededMaxTime(): void
    {
        $this->request(function () {
            $connection = $this->getConnectionWithSleepFunction();
            $connection->select((\DB::raw('select sleep(100)')));

            return response('slow');
        });

        Log::assertLogged(
            fn(LogEntry $log) => $log->level === 'critical' && Str::contains($log->message, 'SQD')
        );
    }

    public function testBeSilentWhenQueryNotExceededMaxTime(): void
    {
        $this->request(function () {
            $connection = $this->getConnectionWithSleepFunction();
            $connection->select((\DB::raw('select sleep(1)')));

            return response('fast');
        });

        Log::assertNotLogged(fn(LogEntry $log) => $log->level === 'critical');
    }
}
