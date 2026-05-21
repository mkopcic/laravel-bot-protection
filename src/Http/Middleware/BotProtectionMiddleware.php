<?php

namespace Mkopcic\BotProtection\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BotProtectionMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!config('bot-protection.enabled', true)) {
            return $next($request);
        }

        if ($this->shouldBlock($request)) {
            return response(
                config('bot-protection.block_message', 'Forbidden'),
                (int) config('bot-protection.block_status', 403)
            );
        }

        /** @var Response $response */
        $response = $next($request);

        $this->applyHeaders($response);

        return $response;
    }

    /**
     * Determine if the request should be blocked.
     */
    protected function shouldBlock(Request $request): bool
    {
        // IP whitelist bypass
        $clientIp = $request->ip();
        $allowedIps = (array) config('bot-protection.allowed_ips', []);
        if ($clientIp !== null && in_array($clientIp, $allowedIps, true)) {
            return false;
        }

        $userAgent = $request->userAgent() ?? '';

        // Prazan UA
        if ($userAgent === '') {
            return (bool) config('bot-protection.block_empty_user_agent', false);
        }

        return $this->matchesBlockedAgent($userAgent);
    }

    /**
     * Provjeri sadrži li User-Agent neki od blokiranih stringa.
     */
    protected function matchesBlockedAgent(string $userAgent): bool
    {
        foreach ((array) config('bot-protection.blocked_agents', []) as $needle) {
            if ($needle === '' || $needle === null) {
                continue;
            }

            if (stripos($userAgent, (string) $needle) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Dodaj X-Robots-Tag header.
     */
    protected function applyHeaders(Response $response): void
    {
        $tag = config('bot-protection.x_robots_tag');

        if (is_string($tag) && $tag !== '') {
            $response->headers->set('X-Robots-Tag', $tag);
        }
    }
}
