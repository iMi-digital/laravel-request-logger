<?php

namespace iMi\LaravelRequestLogger;

use Closure;

class LogRequest
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $entry = new RequestLogEntry();
        $entry->ip = $request->getClientIp();
        $entry->path = urldecode($request->path());
        $entry->method = $request->getMethod();
        $get = $request->query->all();
        if (count($get) > 0) {
            $entry->get = var_export($get, true);
        }
        $post = $request->request->all();
        if (count($post) > 0) {
            $entry->post = var_export($post, true);
        }
        $cookies = $request->cookies->all();
        if (count($cookies) > 0) {
            $entry->cookies = var_export($cookies, true);
        }
        $entry->agent = $request->server('HTTP_USER_AGENT');
        $entry->session = $request->session()->getId();
        $entry->save();
        return $next($request);
    }
}
