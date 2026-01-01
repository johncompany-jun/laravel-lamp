<?php

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'admin' => \App\Http\Middleware\IsAdmin::class,
        ]);
    })
    ->withSchedule(function (Schedule $schedule) {
        // Close expired events daily at midnight
        $schedule->command('events:close-expired')->daily();
        // Complete finished events every hour
        $schedule->command('events:complete-finished')->hourly();
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
