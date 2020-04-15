<?php

namespace Ilzrv\LaravelSlowQueryDetector\Tests;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SlowCodeTest extends TestCase
{
    public function testBeNotifiedWhenCodeExceededMaxTime()
    {
        $this->request(function () {
            usleep(1000001); // 1 s
        });

        Log::assertLogged('critical', function ($message, $context) {
            return Str::contains($message, 'SQD');
        });
    }

    public function testBeSilentWhenCodeRunsFine()
    {
        $this->request(function () {
            usleep(900000); // 0.9 s
        });

        Log::assertNotLogged('critical');
    }

    public function testBeNotifiedWhenCodeHasTooManyQueries()
    {
        $this->request(function () {
            $connection = $this->getConnectionWithSleepFunction();
            for ($i = 0; $i < 51; $i++) {
                $connection->select((\DB::raw('select sleep(1)')));
            }
        });

        Log::assertLogged('critical', function ($message, $context) {
            return Str::contains($message, 'SQD');
        });
    }

    public function testBeSilentWhenCodeHasFineCountQueries()
    {
        $this->request(function () {
            $connection = $this->getConnectionWithSleepFunction();
            for ($i = 0; $i < 50; $i++) {
                $connection->select((\DB::raw('select sleep(1)')));
            }
        });

        Log::assertNotLogged('critical');
    }
}
