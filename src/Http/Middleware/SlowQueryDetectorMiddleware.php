<?php

declare(strict_types=1);

namespace Ilzrv\LaravelSlowQueryDetector\Http\Middleware;

use Closure;
use Illuminate\Database\Connection;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Psr\Log\LoggerInterface;

final class SlowQueryDetectorMiddleware
{
    protected int $queriesCount = 0;
    protected int $heavyQueryCount = 0;
    protected float $heaviestQueryTime = 0;
    protected string $heaviestQuery = '';
    protected float $executionTime = 0;

    public function __construct(
        private Connection $dbConnection,
        private LoggerInterface $logger,
    ) {
    }

    public function handle(
        Request $request,
        Closure $next,
    ): mixed {
        $this->executionTime = -round(microtime(true) * 1000);

        $this->dbConnection->listen(function (QueryExecuted $query) {
            $this->queriesCount++;
            if ($query->time > config('sqd.query.max_time')) {
                $this->heavyQueryCount++;
                if ($query->time > $this->heaviestQueryTime) {
                    $this->heaviestQueryTime = $query->time;
                    $this->heaviestQuery = $this->getQuery($query);
                }
            }
        });

        $next = $next($request);

        $this->executionTime += round(microtime(true) * 1000);

        if ($this->needNotify()) {
            $this->notify($request);
        }

        return $next;
    }

    protected function needNotify(): bool
    {
        return $this->queriesCount > config('sqd.code.max_queries')
            || $this->executionTime > config('sqd.code.max_time')
            || $this->heaviestQueryTime > config('sqd.query.max_time');
    }

    protected function notify(Request $request): void
    {
        $data = [
            'SQD' => [
                'Execution Time' => $this->executionTime . ' ms.',
                'Queries Count' => $this->queriesCount,
                'Heavy Queries Count' => $this->heavyQueryCount,
                'Full URL' => $request->fullUrl(),
                'Action' => $request->route()->getActionName(),
                'Heaviest Query' => [
                    'Query' => $this->heaviestQuery,
                    'Time' => $this->heaviestQueryTime . ' ms.',
                ]
            ]
        ];

        $this->logger->critical(
            print_r($data, true)
        );
    }

    protected function getQuery(QueryExecuted $query): string
    {
        return config('sqd.query.with_bindings')
            ? Str::replaceArray('?', $query->bindings, $query->sql)
            : $query->sql;
    }
}
