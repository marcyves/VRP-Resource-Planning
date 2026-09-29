<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;

class TrustProxies extends Middleware
{
    /**
     * The trusted proxies for this application.
     *
     * Null = do not trust X-Forwarded-* (local). Set TRUSTED_PROXIES=* on IONOS
     * so HTTPS behind the host reverse proxy is visible to Laravel (webhooks).
     *
     * @var array<int, string>|string|null
     */
    protected $proxies;

    /**
     * The headers that should be used to detect proxies.
     *
     * @var int
     */
    protected $headers =
        Request::HEADER_X_FORWARDED_FOR |
        Request::HEADER_X_FORWARDED_HOST |
        Request::HEADER_X_FORWARDED_PORT |
        Request::HEADER_X_FORWARDED_PROTO |
        Request::HEADER_X_FORWARDED_AWS_ELB;

    public function handle($request, \Closure $next)
    {
        $configured = env('TRUSTED_PROXIES');

        if ($configured === '*') {
            $this->proxies = '*';
        } elseif (is_string($configured) && $configured !== '') {
            $this->proxies = array_values(array_filter(array_map('trim', explode(',', $configured))));
        }

        return parent::handle($request, $next);
    }
}
