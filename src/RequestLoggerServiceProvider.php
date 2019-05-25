<?php

namespace iMi\LaravelRequestLogger;

use Illuminate\Support\ServiceProvider;

class RequestLoggerServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes(
                [__DIR__ . '/../database/migrations/' => database_path('migrations/')],
                'migrations'
            );

            $filename = 'request-logger.php';
            $this->publishes(
                [__DIR__ . '/../config/' . $filename => config_path($filename)],
                'config'
            );
        }
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/request-logger.php', 'request-logger');
    }

}
