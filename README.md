# Laravel Slow Query Detector
[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE)
[![Build Status][ico-travis]][link-travis]
[![Total Downloads][ico-downloads]][link-downloads]

This package allows you to detect controller methods in the code that make a lot of queries to the database as well as find very heavy queries.

## Requirements

 * PHP 8.0+
 * Laravel 9+

## Installation

You can install the package via composer:
```bash
composer require ilzrv/laravel-slow-query-detector
```

## Configuration

By default, the package is already configured and enabled, but you can change the settings if necessary.
To publish the configuration file, run:

```bash
php artisan vendor:publish --provider="Ilzrv\LaravelSlowQueryDetector\ServiceProvider"
```

### `SQD_ENABLED`

Determines whether query listening is enabled.

### `SQD_CODE_MAX_QUERIES`

Maximum number of queries when processing the controller method.
If your method executes more queries than this value the notification will be received.

### `SQD_CODE_MAX_TIME`

Maximum execution time of the controller method (in ms).
If your method takes longer than this value to complete the notification will be received.

### `SQD_QUERY_BINDINGS`

Queries with bindings.
If true then bindings will be applied to queries in notification.
Example (if true): `select * from users where name = John` instead of `select * from users where name = ?`

### `SQD_QUERY_MAX_TIME`

Maximum execution time for each query in DB (in ms).
If at least one query exceeds this value
you will receive a notification.

## Example logs output:

```bash
[2020-04-12 06:59:21] production.CRITICAL: Array
(
    [SQD] => Array
        (
            [Execution Time] => 60 ms.
            [Queries Count] => 2
            [Heavy Queries Count] => 2
            [Full URL] => https://example.org/?name=John
            [Action] => App\Http\Controllers\HomeController@index
            [Heaviest Query] => Array
                (
                    [Query] => select * from `users` where `name` = John
                    [Time] => 50.67 ms.
                )

        )
)
```

### License

The Laravel Slow Query Detector is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)

[ico-version]: https://img.shields.io/packagist/v/ilzrv/laravel-slow-query-detector
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg
[ico-travis]: https://img.shields.io/travis/ilzrv/laravel-slow-query-detector
[ico-downloads]: https://img.shields.io/packagist/dt/ilzrv/laravel-slow-query-detector

[link-packagist]: https://packagist.org/packages/ilzrv/laravel-slow-query-detector
[link-travis]: https://travis-ci.org/ilzrv/laravel-slow-query-detector
[link-downloads]: https://packagist.org/packages/ilzrv/laravel-slow-query-detector
