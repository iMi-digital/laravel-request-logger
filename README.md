Logs Requests for Laravel Projects
===================================================================

This module logs requests for following data to a database table.

* method
* path
* ip
* session
* get
* post
* cookies
* agent

It is possible to exclude certain private fields through configuration (contributed by [@AgelxNash](https://github.com/AgelxNash)).
**By default, all session cookies and xsrf tokens are excluded from being logged.**

Installation
------------

1. Install `imi/laravel-request-logger` via composer with this command:
       `composer require imi/laravel-request-logger -W`
2. Optional - if your Laravel version does not yet support autodiscovery: Insert `iMi\LaravelRequestLogger\RequestLoggerServiceProvider::class` into providers in your config/app.php
3. Use the middleware 'request-logger' => 'iMi\LaravelRequestLogger\LogRequest::class' for routes/route groups. If you want to log all requests insert `iMi\LaravelRequestLogger\LogRequest::class` into middleware in your app/Http/Kernel.php, but not in routeMiddleware. 
4. Call `php artisan migrate`

Similar Modules
---------------

* https://github.com/spatie/laravel-http-logger

About Us
========

[iMi digital GmbH](http://www.imi.de/) offers Laravel related open source modules. If you are confronted with any bugs, you may want to open an issue here.

In need of support or an implementation of a modul in an existing system, [free to contact us](mailto:digital@iMi.de). In this case, we will provide full service support for a fee.

Of course we provide development of closed-source modules as well.
