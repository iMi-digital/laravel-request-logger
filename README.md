Encode Session IDs in URLs for Laravel 5 Projects (Transparent SID)
===================================================================

This module adds support for keeping session IDs (as normally stored in session cookies) to all URLs.
This is especially useful if your application runs in Iframe as some browser block cookies in those.
PHP already provides the `session.use_trans_sid` configuration value for this, but as Laravel 5 is implementing sessions in its on way, our module is necessary.

Installation
------------

1. Install `imi/laravel-request-logger` via composer.
2. Insert iMi\laravel-request-logger\RequestLoggerServiceProvider::class into providers in your config/app.php
3. Insert iMi\laravel-request-logger\LogRequest::class into middleware in your app/Http/Kernel.php

About Us
========

[iMi digital GmbH](http://www.imi.de/) offers Laravel related open source modules. If you are confronted with any bugs, you may want to open an issue here.

In need of support or an implementation of a modul in an existing system, [free to contact us](mailto:digital@iMi.de). In this case, we will provide full service support for a fee.

Of course we provide development of closed-source modules as well.
