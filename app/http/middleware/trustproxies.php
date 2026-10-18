<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;

class TrustProxies extends Middleware
{
    /**
     * The trusted proxies for this application.
     *
     * Set to "*" so the app correctly detects the original request scheme
     * (HTTPS) when sitting behind the Cloudflare tunnel / reverse proxy.
     * The proxy forwards X-Forwarded-Proto / X-Forwarded-Host / etc.
     */
    protected $proxies = '*';

    /**
     * The headers that should be trusted from the proxy.
     */
    protected $headers = Request::HEADER_X_FORWARDED_FOR
        | Request::HEADER_X_FORWARDED_HOST
        | Request::HEADER_X_FORWARDED_PORT
        | Request::HEADER_X_FORWARDED_PROTO
        | Request::HEADER_X_FORWARDED_AWS_ELB;
}
