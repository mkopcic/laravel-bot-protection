<?php

namespace Mkopcic\BotProtection\Support;

class MetaTags
{
    /**
     * Renderiraj HTML za <meta name="robots"> i AI opt-out tagove.
     *
     * Koristi se preko @botProtectionMeta Blade direktive.
     *   - 'robots', 'googlebot', 'bingbot', 'googlebot-news' iz x_robots_tag
     *   - 'noai, noimageai' iz ai_meta_tags (emerging AI opt-out standard)
     */
    public static function render(): string
    {
        $lines = [];

        $robots = (string) config('bot-protection.x_robots_tag', '');
        if ($robots !== '') {
            $escaped = self::escape($robots);
            $lines[] = '<meta name="robots" content="' . $escaped . '">';
            $lines[] = '<meta name="googlebot" content="' . $escaped . '">';
            $lines[] = '<meta name="googlebot-news" content="noindex">';
            $lines[] = '<meta name="bingbot" content="' . $escaped . '">';
        }

        // AI opt-out (noai, noimageai) — emerging standard
        $aiTags = (string) config('bot-protection.ai_meta_tags', '');
        if ($aiTags !== '') {
            $lines[] = '<meta name="robots" content="' . self::escape($aiTags) . '">';
        }

        return implode("\n", $lines);
    }

    protected static function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
