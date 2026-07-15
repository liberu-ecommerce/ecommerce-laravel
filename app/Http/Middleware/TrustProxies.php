<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;

class TrustProxies extends Middleware
{
    /**
     * The trusted proxies for this application.
     *
     * '*' trusts the immediate caller (REMOTE_ADDR), which is safe here because the
     * app is only ever reached through a proxy — nginx in dev, the k8s/Traefik ingress
     * in prod — and never exposed directly. Trusting nothing was worse than it looked:
     * X-Forwarded-For was ignored, so $request->ip() returned the proxy's IP for every
     * client, collapsing per-IP throttles into one shared bucket (one attacker throttles
     * all guests) and leaving $request->isSecure() false behind TLS termination.
     *
     * If the app is ever exposed directly, replace '*' with the ingress CIDRs.
     *
     * @var array<int, string>|string|null
     */
    protected $proxies = '*';

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
}
