<?php

namespace iMi\LaravelRequestLogger;

use Closure;
use Illuminate\Support\Arr;

/**
 * @property array $exceptUri
 * @property array $exceptGet
 * @property array $exceptPost
 * @property array $exceptCookies
 */
class LogRequest
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        if (! $this->isExceptRequest($request)) {
            (new RequestLogEntry)->forceFill([
                'ip' => $request->getClientIp(),
                'path' => urldecode($request->path()),
                'method' => $request->getMethod(),
                'agent' => $request->server('HTTP_USER_AGENT'),
                'get' => $this->get($request),
                'post' => $this->post($request),
                'cookies' => $this->cookies($request),
                'session' => $this->session($request)
            ])->save();
        }

        return $next($request);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return string|null
     */
    protected function session($request) : ?string
    {
        if ($request->hasSession()) {
            return $request->session()->getId();
        }

        return null;
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return string|null
     */
    protected function get($request) : ?string
    {
        $get = Arr::except($request->query->all(), $this->getExceptGet());
        if (count($get) > 0) {
            return var_export($get, true);
        }

        return null;
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return string|null
     */
    protected function post($request) : ?string
    {
        $post = Arr::except($request->request->all(), $this->getExceptPost());
        if (count($post) > 0) {
            return var_export($post, true);
        }

        return null;
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return string|null
     */
    protected function cookies($request) : ?string
    {
        $cookies = Arr::except($request->cookies->all(), $this->getExceptCookies());
        if (count($cookies) > 0) {
            return var_export($cookies, true);
        }

        return null;
    }

    /**
     * @return array
     */
    protected function getExceptGet() : array
    {
        return property_exists($this, 'exceptGet') ? $this->exceptGet : config('request-logger.except.get');
    }

    /**
     * @return array
     */
    protected function getExceptPost() : array
    {
        return property_exists($this, 'exceptPost') ? $this->exceptPost : config('request-logger.except.post');
    }

    /**
     * @return array
     */
    protected function getExceptCookies() : array
    {
        return property_exists($this, 'exceptCookies') ? $this->exceptCookies : config('request-logger.except.cookies');
    }

    /**
     * @return array
     */
    public function getExceptUri() : array
    {
        return property_exists($this, 'exceptUri') ? $this->exceptUri : config('request-logger.except.uri');
    }

    /**
     * Determine if the application is running unit tests.
     *
     * @return bool
     */
    protected function runningUnitTests() : bool
    {
        return app()->runningInConsole() && app()->runningUnitTests();
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return bool
     */
    protected function isExceptRequest($request) : bool
    {
        return ($this->runningUnitTests() || $this->inExceptUriArray($request));
    }

    /**
     * Determine if the request has a URI that should pass through CSRF verification.
     *
     * @param \Illuminate\Http\Request $request
     * @return bool
     */
    protected function inExceptUriArray($request) : bool
    {
        foreach ($this->getExceptUri() as $except) {
            if ($except !== '/') {
                $except = trim($except, '/');
            }

            if ($request->fullUrlIs($except) || $request->is($except)) {
                return true;
            }
        }

        return false;
    }
}
