<?php

namespace Mkopcic\BotProtection\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Mkopcic\BotProtection\Events\BotBlocked;
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

        $matched = $this->resolveBlockReason($request);

        if ($matched !== null) {
            $this->dispatchAndLog($request, $matched);

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
     * Vrati razlog blokiranja (matched bot string) ili null ako se request propušta.
     */
    protected function resolveBlockReason(Request $request): ?string
    {
        // IP whitelist bypass
        $clientIp = $request->ip();
        $allowedIps = (array) config('bot-protection.allowed_ips', []);
        if ($clientIp !== null && in_array($clientIp, $allowedIps, true)) {
            return null;
        }

        $userAgent = $request->userAgent() ?? '';

        if ($userAgent === '') {
            return config('bot-protection.block_empty_user_agent', false) ? '(empty)' : null;
        }

        return $this->matchedBlockedAgent($userAgent);
    }

    /**
     * Vrati prvi blocked_agents string koji se podudara s UA, ili null.
     */
    protected function matchedBlockedAgent(string $userAgent): ?string
    {
        foreach ((array) config('bot-protection.blocked_agents', []) as $needle) {
            if ($needle === '' || $needle === null) {
                continue;
            }

            if (stripos($userAgent, (string) $needle) !== false) {
                return (string) $needle;
            }
        }

        return null;
    }

    /**
     * Emit BotBlocked event i, ako je log_blocked uključen, zapiši u log.
     */
    protected function dispatchAndLog(Request $request, string $matched): void
    {
        $event = new BotBlocked(
            userAgent: $request->userAgent() ?? '',
            ip: $request->ip() ?? 'unknown',
            url: $request->fullUrl(),
            matchedAgent: $matched,
        );

        event($event);

        if (!config('bot-protection.log_blocked', false)) {
            return;
        }

        $channel = config('bot-protection.log_channel');
        $logger = is_string($channel) && $channel !== ''
            ? Log::channel($channel)
            : Log::channel(config('logging.default', 'stack'));

        $logger->warning('[bot-protection] Bot blocked', [
            'user_agent'    => $event->userAgent,
            'ip'            => $event->ip,
            'url'           => $event->url,
            'matched_agent' => $event->matchedAgent,
        ]);
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
