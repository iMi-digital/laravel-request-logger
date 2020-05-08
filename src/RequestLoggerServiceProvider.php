<?php

namespace iMi\LaravelRequestLogger;

use Illuminate\Support\ServiceProvider;

class RequestLoggerServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations/');
        $this->app['router']->aliasMiddleware('request-logger', 'iMi\LaravelRequestLogger\LogRequest::class');
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/request-logger.php', 'request-logger');
    }

}
