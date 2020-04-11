<?php

return [

    'enabled' => (bool) env('SQD_ENABLED', true),

    'code' => [
        'max_queries' => (int) env('SQD_CODE_MAX_QUERIES', 50),
        'max_time' => (int) env('SQD_CODE_MAX_TIME', 1000),
    ],

    'query' => [
        'with_bindings' => (bool) env('SQD_QUERY_BINDINGS', true),
        'max_time' => (int) env('SQD_QUERY_MAX_TIME', 50),
    ],

];
