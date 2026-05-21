<?php

namespace Mkopcic\BotProtection\Support;

class MetaTags
{
    /**
     * Renderiraj HTML za <meta name="robots"> i srodne tagove.
     *
     * Koristi se preko @botProtectionMeta Blade direktive.
     * Sadržaj se čita iz config('bot-protection.x_robots_tag').
     */
    public static function render(): string
    {
        $content = (string) config('bot-protection.x_robots_tag', 'noindex, nofollow, noarchive, nosnippet');

        if ($content === '') {
            return '';
        }

        $escaped = htmlspecialchars($content, ENT_QUOTES, 'UTF-8');

        return implode("\n", [
            '<meta name="robots" content="' . $escaped . '">',
            '<meta name="googlebot" content="' . $escaped . '">',
            '<meta name="googlebot-news" content="noindex">',
            '<meta name="bingbot" content="' . $escaped . '">',
        ]);
    }
}
