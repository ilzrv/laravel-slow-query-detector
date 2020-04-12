<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Package state
    |--------------------------------------------------------------------------
    |
    | Determines whether query listening is enabled
    |
    */

    'enabled' => (bool) env('SQD_ENABLED', true),

    'code' => [

        /*
        |--------------------------------------------------------------------------
        | Maximum number of queries when processing the controller method
        |--------------------------------------------------------------------------
        |
        | If your method executes more queries than this
        | value the notification will be received
        |
        */

        'max_queries' => (int) env('SQD_CODE_MAX_QUERIES', 50),

        /*
        |--------------------------------------------------------------------------
        | Maximum execution time of the controller method (in ms)
        |--------------------------------------------------------------------------
        |
        | If your method takes longer than this value to complete
        | the notification will be received
        |
        */

        'max_time' => (int) env('SQD_CODE_MAX_TIME', 1000),
    ],

    'query' => [

        /*
        |--------------------------------------------------------------------------
        | Queries with bindings
        |--------------------------------------------------------------------------
        |
        | If true then bindings will be applied to queries in notification.
        | Example (if true): "select * from users where name = John" instead of
        | "select * from users where name = ?"
        |
        */

        'with_bindings' => (bool) env('SQD_QUERY_BINDINGS', true),

        /*
        |--------------------------------------------------------------------------
        | Maximum execution time for each query in DB (in ms)
        |--------------------------------------------------------------------------
        |
        | If at least one query exceeds this value
        | you will receive a notification
        |
        */

        'max_time' => (int) env('SQD_QUERY_MAX_TIME', 0),
    ],

];
