<?php

namespace iMi\LaravelRequestLogger;

use Illuminate\Support\ServiceProvider;

class RequestLoggerServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $filename = 'migrations/2015_09_25_111650_create_request_log_entries_table.php';
            $this->publishes(
                [__DIR__ . '/../database/' . $filename => database_path($filename)],
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
