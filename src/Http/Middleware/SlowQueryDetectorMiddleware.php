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

    protected function needNotify()
    {
        return $this->queriesCount > config('sqd.code.max_queries')
            || $this->executionTime > config('sqd.code.max_time')
            || $this->heaviestQueryTime > config('sqd.query.max_time');
    }

    protected function notify($request)
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

        app('log')->critical(print_r($data, true));
    }

    protected function getQuery($query)
    {
        return config('sqd.query.with_bindings')
            ? Str::replaceArray('?', $query->bindings, $query->sql)
            : $query->sql;
    }
}
