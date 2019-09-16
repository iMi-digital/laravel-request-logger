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
            RequestLogEntry::create($this->getData());
        }

        return $next($request);
    }

    /**
     * @return array
     */
    protected function getData() : array
    {
        return [
            'ip' => $request->getClientIp(),
            'path' => urldecode($request->path()),
            'method' => $request->getMethod(),
            'agent' => $request->server('HTTP_USER_AGENT'),
            'get' => $this->get($request),
            'post' => $this->post($request),
            'cookies' => $this->cookies($request),
            'session' => $this->session($request)
        ];
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
     * @param array $data
     * @return array|null
     */
    protected function export(array $data = []) : ?array
    {
        if (count($data) === 0) {
            return null;
        }

        return $data;
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return array|null
     */
    protected function get($request) : ?array
    {
        return $this->export(
            Arr::except($request->query->all(), $this->getExceptGet())
        );
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return array|null
     */
    protected function post($request) : ?array
    {
        return $this->export(
            Arr::except($request->request->all(), $this->getExceptPost())
        );
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return array|null
     */
    protected function cookies($request) : ?array
    {
        return $this->export(
            Arr::except($request->cookies->all(), $this->getExceptCookies())
        );
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
