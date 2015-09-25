<?php

namespace iMi\LaravelRequestLogger;

use Illuminate\Support\ServiceProvider;

class RequestLoggerServiceProvider extends ServiceProvider
{

    public function boot()
    {
        $filename = __DIR__ . '/../database/migrations/2015_09_25_111650_create_request_log_entries_table.php';
        $this->publishes([$filename => database_path('migrations/2015_09_25_111650_create_request_log_entries_table.php')], 'migrations');
    }

    public function register()
    {

    }

}
