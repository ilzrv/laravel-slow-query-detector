<?php

namespace Ilzrv\LaravelSlowQueryDetector\Tests;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SlowQueryTest extends TestCase
{
    public function testBeNotifiedWhenQueryExceededMaxTime()
    {
        $this->request(function () {
            $connection = $this->getConnectionWithSleepFunction();
            $connection->select((\DB::raw('select sleep(100)')));

            return response('slow');
        });

        Log::assertLogged('critical', function ($message, $context) {
            return Str::contains($message, 'SQD');
        });
    }

    public function testBeSilentWhenQueryNotExceededMaxTime()
    {
        $this->request(function () {
            $connection = $this->getConnectionWithSleepFunction();
            $connection->select((\DB::raw('select sleep(1)')));

            return response('fast');
        });

        Log::assertNotLogged('critical');
    }
}
