<?php

namespace iMi\LaravelRequestLogger;

use Closure;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Throwable;

/**
 * @property array $exceptUri
 * @property array $exceptGet
 * @property array $exceptPost
 * @property array $exceptCookies
 */
class LogRequest
{
    protected const BASE64_PREFIX = 'base64:';

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        try {
            if (! $this->isExceptRequest($request)) {
                RequestLogEntry::create($this->getData($request));
            }
        } catch (Throwable $exception) {
            report($exception);
        }

        return $next($request);
    }

    /**
     * @return array
     */
    protected function getData($request) : array
    {
        return [
            'ip' => $request->getClientIp(),
            'path' => urldecode($request->path()),
            'method' => $request->getMethod(),
            'agent' => substr($request->server('HTTP_USER_AGENT'), 0, 191),
            'get' => $this->encodeBinary($this->get($request)),
            'post' => $this->encodeBinary($this->post($request)),
            'cookies' => $this->encodeBinary($this->cookies($request)),
            'session' => $this->session($request)
        ];
    }

    /**
     * Everything logged here is raw client input, and a client is free to send
     * bytes that are not valid UTF-8 - one mangled cookie value is enough to
     * make the json cast of the log entry throw.
     *
     * Replacing those bytes would falsify exactly the data someone is reading
     * the log for, so they are preserved in place, base64 encoded and marked
     * with a "base64:" prefix:
     *
     *     ['ok' => 'value', 'sid' => 'base64:gKp0']
     *
     * A value that already starts with the literal prefix is encoded as well,
     * so the marker stays unambiguous: "base64:foo" is stored as
     * "base64:YmFzZTY0OmZvbw==". Decoding is always a single prefix strip
     * plus base64_decode and returns the exact original bytes. Keys get the
     * same treatment as values.
     *
     * @param array|null $data
     * @return array|null
     */
    protected function encodeBinary($data) : ?array
    {
        if ($data === null) {
            return null;
        }

        $encoded = [];

        foreach ($data as $key => $value) {
            $encodedKey = is_string($key) ? $this->encodeValue($key) : $key;

            if (is_array($value)) {
                $encoded[$encodedKey] = $this->encodeBinary($value);

                continue;
            }

            $encoded[$encodedKey] = is_string($value) ? $this->encodeValue($value) : $value;
        }

        return $encoded;
    }

    /**
     * @param string $value
     * @return string
     */
    protected function encodeValue(string $value) : string
    {
        if ($this->needsEncoding($value)) {
            return self::BASE64_PREFIX . base64_encode($value);
        }

        return $value;
    }

    /**
     * @param string $value
     * @return bool
     */
    protected function needsEncoding(string $value) : bool
    {
        return ! mb_check_encoding($value, 'UTF-8')
            || strncmp($value, self::BASE64_PREFIX, strlen(self::BASE64_PREFIX)) === 0;
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
            $this->except($request->query->all(), $this->getExceptGet())
        );
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return array|null
     */
    protected function post($request) : ?array
    {
        return $this->export(
            $this->except($request->request->all(), $this->getExceptPost())
        );
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return array|null
     */
    protected function cookies($request) : ?array
    {
        return $this->export(
            $this->except($request->cookies->all(), $this->getExceptCookies())
        );
    }

    /**
     * Drops the configured keys, additionally honouring shell style wildcards
     * so that a family of keys can be excluded without naming each one -- a
     * host may set session cookies the application does not know about and
     * has no business logging (authelia_session on our review apps).
     *
     * Patterns without a wildcard keep going through Arr::except, so dot
     * notation for nested keys behaves exactly as before.
     *
     * @param array $values
     * @param array $patterns
     * @return array
     */
    protected function except(array $values, array $patterns) : array
    {
        $values = Arr::except($values, $patterns);

        $wildcards = array_filter($patterns, function ($pattern) {
            return is_string($pattern) && strpos($pattern, '*') !== false;
        });

        if (count($wildcards) === 0) {
            return $values;
        }

        return array_filter($values, function ($key) use ($wildcards) {
            return ! Str::is($wildcards, $key);
        }, ARRAY_FILTER_USE_KEY);
    }

    /**
     * @return array
     */
    protected function getExceptGet() : array
    {
        return property_exists($this, 'exceptGet') ? $this->exceptGet : config('request-logger.except.get', []);
    }

    /**
     * @return array
     */
    protected function getExceptPost() : array
    {
        return property_exists($this, 'exceptPost') ? $this->exceptPost : config('request-logger.except.post', []);
    }

    /**
     * @return array
     */
    protected function getExceptCookies() : array
    {
        return property_exists($this, 'exceptCookies') ? $this->exceptCookies : config('request-logger.except.cookies', []);
    }

    /**
     * @return array
     */
    public function getExceptUri() : array
    {
        return property_exists($this, 'exceptUri') ? $this->exceptUri : config('request-logger.except.uri', []);
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
