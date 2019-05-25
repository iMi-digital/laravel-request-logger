<?php

namespace iMi\LaravelRequestLogger;

use Illuminate\Support\ServiceProvider;

class RequestLoggerServiceProvider extends ServiceProvider
{

    public function boot()
    {
        $this->publishes([__DIR__ . '/../database/migrations/' => database_path('migrations/')], 'migrations');
    }

    public function register()
    {

    }

}
