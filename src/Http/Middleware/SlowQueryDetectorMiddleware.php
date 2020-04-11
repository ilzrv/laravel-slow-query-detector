<?php

namespace Ilzrv\LaravelSlowQueryDetector\Http\Middleware;

use Closure;
use Illuminate\Support\Str;

class SlowQueryDetectorMiddleware
{
    protected $queriesCount = 0;
    protected $heavyQueryCount = 0;
    protected $heaviestQueryTime = 0;
    protected $heaviestQuery = '';
    protected $executionTime = 0;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $this->executionTime = -round(microtime(true) * 1000);

        \DB::listen(function ($query) {
            $this->queriesCount++;
            if ($query->time > config('slow-query-detector.query.max_time')) {
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

    protected function needNotify()
    {
        return $this->queriesCount > config('slow-query-detector.code.max_queries')
            || $this->executionTime > config('slow-query-detector.code.max_time')
            || $this->heaviestQueryTime > config('slow-query-detector.query.max_time');
    }

    protected function notify($request)
    {
        app('log')->critical(print_r([
            'Execution Time' => $this->executionTime,
            'Queries Count' => $this->queriesCount,
            'Heavy Queries Count' => $this->heavyQueryCount,
            'Full URL' => $request->fullUrl(),
            'Action' => $request->route()->getActionName(),
            'Heaviest Query' => [
                'Query' => $this->heaviestQuery,
                'Time' => $this->heaviestQueryTime,
            ]
        ], true));
    }

    protected function getQuery($query)
    {
        return config('slow-query-detector.query.with_bindings')
            ? Str::replaceArray('?', $query->bindings, $query->sql)
            : $query->sql;
    }
}
