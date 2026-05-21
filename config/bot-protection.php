<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Enabled
    |--------------------------------------------------------------------------
    |
    | Globalni toggle. Kad je false, middleware propušta sve requestove
    | i NE dodaje X-Robots-Tag header.
    |
    */
    'enabled' => env('BOT_PROTECTION_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Auto Register Middleware
    |--------------------------------------------------------------------------
    |
    | Ako je true, package automatski gura middleware u 'web' grupu preko
    | service providera. Postavi na false ako želiš ručno registrirati
    | middleware u bootstrap/app.php (Laravel 11+) ili Kernel.php (Laravel 10).
    |
    */
    'auto_register' => env('BOT_PROTECTION_AUTO_REGISTER', true),

    /*
    |--------------------------------------------------------------------------
    | Middleware Group
    |--------------------------------------------------------------------------
    |
    | Koja middleware grupa će dobiti BotProtectionMiddleware kad je
    | auto_register=true. Default 'web' pokriva sve web rute.
    |
    */
    'middleware_group' => env('BOT_PROTECTION_MIDDLEWARE_GROUP', 'web'),

    /*
    |--------------------------------------------------------------------------
    | Block Status Code
    |--------------------------------------------------------------------------
    |
    | HTTP status koji se vraća blokiranim botovima.
    | 403 = standard "Forbidden"
    | 404 = otežava otkrivanje da site postoji
    | 429 = "Too Many Requests" — nekad efikasnije jer botovi backoff-aju
    |
    */
    'block_status' => env('BOT_PROTECTION_BLOCK_STATUS', 403),

    /*
    |--------------------------------------------------------------------------
    | Block Response Message
    |--------------------------------------------------------------------------
    */
    'block_message' => env('BOT_PROTECTION_BLOCK_MESSAGE', 'Forbidden'),

    /*
    |--------------------------------------------------------------------------
    | X-Robots-Tag Header
    |--------------------------------------------------------------------------
    |
    | Vrijednost X-Robots-Tag headera koji se dodaje na sve odgovore.
    | Postavi na null ili '' da se header ne dodaje uopće.
    |
    */
    'x_robots_tag' => env('BOT_PROTECTION_X_ROBOTS_TAG', 'noindex, nofollow, noarchive, nosnippet'),

    /*
    |--------------------------------------------------------------------------
    | Block Empty User Agent
    |--------------------------------------------------------------------------
    |
    | Većina legitimnog prometa šalje User-Agent. Prazan UA je sumnjiv —
    | obično su to skripte / scraperi. Postavi false ako koristiš API klijente
    | koji namjerno ne šalju UA.
    |
    */
    'block_empty_user_agent' => env('BOT_PROTECTION_BLOCK_EMPTY_UA', false),

    /*
    |--------------------------------------------------------------------------
    | Log Blocked Requests
    |--------------------------------------------------------------------------
    |
    | Ako je true, svaki blokiran bot se zapisuje u Laravel log
    | s razinom 'warning'. Korisno za monitoring i analitiku.
    |
    */
    'log_blocked' => env('BOT_PROTECTION_LOG_BLOCKED', false),

    /*
    |--------------------------------------------------------------------------
    | Log Channel
    |--------------------------------------------------------------------------
    |
    | Naziv log channela u koji se piše. Null ili prazan string =
    | koristi default Laravel log channel (config logging.default).
    |
    */
    'log_channel' => env('BOT_PROTECTION_LOG_CHANNEL'),

    /*
    |--------------------------------------------------------------------------
    | Allowed IPs
    |--------------------------------------------------------------------------
    |
    | IP adrese koje su uvijek dopuštene, čak i ako im UA odgovara
    | blocked_agents listi. Korisno za testiranje s lažnim UA.
    |
    */
    'allowed_ips' => array_filter(
        explode(',', (string) env('BOT_PROTECTION_ALLOWED_IPS', ''))
    ),

    /*
    |--------------------------------------------------------------------------
    | Blocked User Agents
    |--------------------------------------------------------------------------
    |
    | Lista User-Agent stringa koji se blokiraju (case-insensitive,
    | djelomično podudaranje preko stripos()).
    |
    | Ažurirano: ova lista pokriva poznate AI crawlere i SEO botove
    | po stanju 2026. Dodaj nove crawlere prema potrebi.
    |
    */
    'blocked_agents' => [

        // === OpenAI ===
        'GPTBot',
        'ChatGPT-User',
        'OAI-SearchBot',

        // === Anthropic ===
        'ClaudeBot',
        'anthropic-ai',
        'Claude-Web',

        // === Google AI & Search ===
        'Google-Extended',
        'Googlebot',
        'AdsBot-Google',

        // === Meta / Facebook ===
        'Meta-ExternalAgent',
        'FacebookBot',
        'facebookexternalhit',

        // === Apple ===
        'Applebot',
        'Applebot-Extended',

        // === Amazon ===
        'Amazonbot',

        // === Perplexity ===
        'PerplexityBot',

        // === ByteDance / TikTok ===
        'Bytespider',

        // === Common Crawl (AI trening dataset) ===
        'CCBot',

        // === Cohere ===
        'cohere-ai',

        // === Diffbot ===
        'Diffbot',

        // === Mistral ===
        'MistralAI-User',

        // === SEO crawleri (često neželjeni) ===
        'SemrushBot',
        'AhrefsBot',
        'MJ12bot',
        'DotBot',
        'BLEXBot',

        // === Yandex, Baidu, Sogou ===
        'YandexBot',
        'Baiduspider',
        'Sogou',

        // === Generički scraper alati ===
        'Scrapy',
        'python-requests',
        'curl/',
        'wget/',
    ],

];
