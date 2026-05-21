<?php

namespace Mkopcic\BotProtection\Http\Controllers;

use Illuminate\Http\Response;

class RobotsController
{
    /**
     * Vrati dinamički generiran robots.txt na temelju blocked_agents config-a.
     */
    public function __invoke(): Response
    {
        $lines = ['User-agent: *', 'Disallow: /', ''];

        foreach ((array) config('bot-protection.blocked_agents', []) as $agent) {
            $agent = trim((string) $agent);
            if ($agent === '') {
                continue;
            }

            $lines[] = "User-agent: {$agent}";
            $lines[] = 'Disallow: /';
            $lines[] = '';
        }

        return response(implode("\n", $lines), 200, [
            'Content-Type'  => 'text/plain; charset=UTF-8',
            'X-Robots-Tag'  => (string) config('bot-protection.x_robots_tag', 'noindex'),
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}
