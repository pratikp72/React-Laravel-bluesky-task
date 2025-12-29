<?php

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use PDOException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        apiPrefix: 'api',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'throttle' => ThrottleRequests::class,
        ]);

        $middleware->api(prepend: [
            'throttle:240,1',
        ]);
    })
    ->withSchedule(function (Schedule $schedule) {
        $schedule->command('bluesky:dispatch-due')
            ->everyMinute()
            ->withoutOverlapping()
            ->onOneServer();
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->reportable(function (PDOException $exception) {
            Log::error('Database connection failed', [
                'driver' => config('database.default'),
                'message' => $exception->getMessage(),
            ]);
        });

        $exceptions->reportable(function (QueryException $exception) {
            Log::error('Database query failed', [
                'sql' => app()->isLocal() ? $exception->getSql() : 'hidden',
                'message' => $exception->getMessage(),
            ]);
        });
    })->create();
