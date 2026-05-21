<div align="center">

# 🤖🛡️ Laravel Bot Protection

**Block AI crawlers, search engines, and known scrapers from your Laravel app — with one line of `composer require`.**

[![Latest Version on Packagist](https://img.shields.io/packagist/v/mkopcic/laravel-bot-protection.svg?style=flat-square)](https://packagist.org/packages/mkopcic/laravel-bot-protection)
[![Tests](https://img.shields.io/github/actions/workflow/status/mkopcic/laravel-bot-protection/tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/mkopcic/laravel-bot-protection/actions/workflows/tests.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/mkopcic/laravel-bot-protection.svg?style=flat-square)](https://packagist.org/packages/mkopcic/laravel-bot-protection)
[![License](https://img.shields.io/packagist/l/mkopcic/laravel-bot-protection.svg?style=flat-square)](LICENSE)
[![PHP Version](https://img.shields.io/packagist/php-v/mkopcic/laravel-bot-protection.svg?style=flat-square)](composer.json)
[![Laravel](https://img.shields.io/badge/Laravel-10%20%7C%2011%20%7C%2012%20%7C%2013-FF2D20?style=flat-square&logo=laravel)](composer.json)

</div>

---

## 📖 About

`laravel-bot-protection` is a drop-in middleware package that protects Laravel applications from unwanted automated traffic — **AI training crawlers, LLM agents, SEO bots, and generic scrapers**. It blocks known bot User-Agents with `HTTP 403` and adds the `X-Robots-Tag: noindex, nofollow` header to every response so well-behaved crawlers (Google, Bing, etc.) also skip indexing.

Built for production apps where you need **zero-config setup** but **fine-grained control** when you want it.

---

## ✨ Features

- 🚫 **Blocks 30+ known bots** out of the box — GPTBot, ClaudeBot, PerplexityBot, Bytespider, Google-Extended, CCBot, AhrefsBot, SemrushBot, and more
- ⚡ **Auto-registers globally** — install and you're protected, no manual middleware setup
- 🏷️ **Adds `X-Robots-Tag` header** to every response — covers crawlers that respect HTTP-level directives
- 🎨 **`@botProtectionMeta` Blade directive** — one-liner for `<meta name="robots">` and `noai/noimageai` tags
- 🤖 **`noai, noimageai` AI opt-out meta tag** — emerging standard adopted by DeviantArt, ArtStation
- 🔄 **Dynamic `/robots.txt` route** (opt-in) — generated from config, single source of truth
- 📡 **`BotBlocked` event** — listen and react: log, alert, feed analytics
- 📝 **Optional logging** — write blocked requests to any Laravel log channel
- 🔧 **Fully configurable** via `.env` or published config — toggle, status code, custom message, allow-list IPs
- 📄 **Publishable `robots.txt`** with comprehensive AI/SEO crawler disallow list
- 🌐 **Server-level config stubs** — Nginx (shared map + per-vhost), Apache vhost, `.htaccess`
- 🧪 **Artisan test command** — verify protection works against a live URL
- ✅ **CI-tested across Laravel 10/11/12/13 × PHP 8.1–8.4** (34 Pest tests)
- 🐘 **Wide compatibility** — Laravel 10 / 11 / 12 / 13, PHP 8.1+

---

## 📋 Requirements

| Requirement | Version |
| ----------- | ------- |
| PHP         | `^8.1`  |
| Laravel     | `10.x`, `11.x`, `12.x`, `13.x` |

---

## 📦 Installation

```bash
composer require mkopcic/laravel-bot-protection
```

That's it. Laravel package auto-discovery registers the service provider and pushes the middleware into the `web` group. Your app is now protected.

### 🎨 Publishing assets (optional)

| Tag | What it publishes | Destination |
| --- | --- | --- |
| `bot-protection-config` | Configuration file | `config/bot-protection.php` |
| `bot-protection-robots` | Comprehensive `robots.txt` | `public/robots.txt` ⚠️ overwrites! |
| `bot-protection-server` | Nginx + Apache + `.htaccess` snippets | `bot-protection/` |
| `bot-protection` | Config + server stubs (everything except robots.txt) | mixed |

```bash
# Publish config to customize blocked agents, status codes, etc.
php artisan vendor:publish --tag=bot-protection-config

# Publish robots.txt — heads up, this overwrites your existing one!
php artisan vendor:publish --tag=bot-protection-robots

# Publish Nginx / Apache config examples
php artisan vendor:publish --tag=bot-protection-server
```

---

## 🚀 Quick Start

After installation, verify the protection works:

```bash
# Show current configuration
php artisan bot-protection:test config

# Test live URL against default bot User-Agents
php artisan bot-protection:test url https://mojaapp.hr

# Test all configured bot agents
php artisan bot-protection:test url https://mojaapp.hr --all
```

You should see `✓ BLOCKED [403]` for each agent.

---

## ⚙️ Configuration

All settings can be controlled via environment variables (no need to publish config):

```dotenv
# Master toggle
BOT_PROTECTION_ENABLED=true

# Auto-register middleware into web group
BOT_PROTECTION_AUTO_REGISTER=true

# Which middleware group to attach to
BOT_PROTECTION_MIDDLEWARE_GROUP=web

# What status code to return for blocked bots
BOT_PROTECTION_BLOCK_STATUS=403

# Message body for blocked responses
BOT_PROTECTION_BLOCK_MESSAGE="Forbidden"

# X-Robots-Tag header value (empty string to disable)
BOT_PROTECTION_X_ROBOTS_TAG="noindex, nofollow, noarchive, nosnippet"

# Block requests with empty User-Agent (suspicious)
BOT_PROTECTION_BLOCK_EMPTY_UA=false

# IPs that bypass blocking (comma-separated)
BOT_PROTECTION_ALLOWED_IPS="1.2.3.4,5.6.7.8"

# Log every blocked request as a warning
BOT_PROTECTION_LOG_BLOCKED=false

# Specific log channel (defaults to logging.default)
BOT_PROTECTION_LOG_CHANNEL=daily

# AI opt-out meta tags (rendered by @botProtectionMeta)
BOT_PROTECTION_AI_META_TAGS="noai, noimageai"

# Serve /robots.txt dynamically from blocked_agents config
BOT_PROTECTION_GENERATE_ROBOTS_ROUTE=false
```

For custom blocked agent lists, publish the config and edit `config/bot-protection.php`.

---

## 🎨 Blade Directive — `@botProtectionMeta`

Drop one line into your `<head>` and the package renders the standard robots meta tags using your configured `x_robots_tag` value:

```blade
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>My App</title>

    @botProtectionMeta
</head>
```

Renders:

```html
<meta name="robots" content="noindex, nofollow, noarchive, nosnippet">
<meta name="googlebot" content="noindex, nofollow, noarchive, nosnippet">
<meta name="googlebot-news" content="noindex">
<meta name="bingbot" content="noindex, nofollow, noarchive, nosnippet">
<meta name="robots" content="noai, noimageai">
```

The last `<meta>` is the **AI opt-out directive** — an emerging standard adopted by DeviantArt, ArtStation, Squarespace. Some AI scrapers already respect it. Disable via `BOT_PROTECTION_AI_META_TAGS=""`.

If both `x_robots_tag` and `ai_meta_tags` are empty, the directive renders nothing.

---

## 🔄 Dynamic `/robots.txt` Route

Instead of publishing a static `public/robots.txt` and keeping it in sync with your config, opt in to a dynamic route:

```dotenv
BOT_PROTECTION_GENERATE_ROBOTS_ROUTE=true
```

The package registers `GET /robots.txt` that emits content generated from your `blocked_agents` config. Change the config → robots.txt updates instantly. **Single source of truth.**

> ⚠️ If `public/robots.txt` exists, your web server (Nginx/Apache) serves the static file first and the dynamic route never fires. Delete `public/robots.txt` for full dynamic behavior.

---

## 📡 `BotBlocked` Event

Every block fires a `Mkopcic\BotProtection\Events\BotBlocked` event with full request context. Listen to it for logging, alerting, or analytics:

```php
// app/Providers/AppServiceProvider.php
use Illuminate\Support\Facades\Event;
use Mkopcic\BotProtection\Events\BotBlocked;

public function boot(): void
{
    Event::listen(function (BotBlocked $event) {
        // $event->userAgent     — full UA string
        // $event->ip            — client IP
        // $event->url           — full URL the bot tried
        // $event->matchedAgent  — which needle from blocked_agents matched

        \Log::channel('bots')->info('Blocked', (array) $event);
    });
}
```

Or use a dedicated listener class:

```bash
php artisan make:listener LogBlockedBot --event="Mkopcic\BotProtection\Events\BotBlocked"
```

---

## 📝 Built-in Logging

If you don't need custom event handling, just turn on logging:

```dotenv
BOT_PROTECTION_LOG_BLOCKED=true
BOT_PROTECTION_LOG_CHANNEL=daily
```

Every blocked request writes a `warning` to the chosen channel with `user_agent`, `ip`, `url`, and `matched_agent` in the context.

---

## 🛠️ Manual Middleware Registration

If you want full control (e.g. apply only to specific route groups), disable auto-register:

```dotenv
BOT_PROTECTION_AUTO_REGISTER=false
```

Then register manually.

**Laravel 11 / 12 / 13** — in `bootstrap/app.php`:

```php
use Mkopcic\BotProtection\Http\Middleware\BotProtectionMiddleware;

->withMiddleware(function (Middleware $middleware) {
    $middleware->web(append: [
        BotProtectionMiddleware::class,
    ]);
})
```

**Laravel 10** — in `app/Http/Kernel.php`:

```php
protected $middlewareGroups = [
    'web' => [
        // ...
        \Mkopcic\BotProtection\Http\Middleware\BotProtectionMiddleware::class,
    ],
];
```

Or apply per-route:

```php
Route::middleware(BotProtectionMiddleware::class)->group(function () {
    // protected routes
});
```

---

## 🧪 Artisan Command — `bot-protection:test`

The package ships with a built-in tester with two subactions: `url` and `config`.

### `config` — dump current configuration

```bash
php artisan bot-protection:test config
```

Outputs all settings, allowed IPs, and the full list of blocked agents.

### `url` — fire HTTP requests with bot User-Agents

```bash
# Default 3 representative agents (GPTBot, ClaudeBot, PerplexityBot)
php artisan bot-protection:test url https://example.com

# Specific agent
php artisan bot-protection:test url https://example.com --agent=GPTBot

# Test every agent from config
php artisan bot-protection:test url https://example.com --all

# Custom timeout
php artisan bot-protection:test url https://example.com --timeout=30
```

Sample output:

```
Testiranje: https://example.com
Broj agenata: 3

  ✓ BLOCKED [403] GPTBot
  ✓ BLOCKED [403] ClaudeBot
  ✓ BLOCKED [403] PerplexityBot

───────────────────────────────────────
Blocked: 3   Allowed: 0   Errors: 0
```

Returns exit code `0` if all agents are blocked, `1` if any get through.

---

## 🌐 Server-Level Protection (Recommended)

The middleware protects at the Laravel layer. For **defense in depth**, block bots at the web server too — they never reach PHP, saving CPU.

Publish the server config examples:

```bash
php artisan vendor:publish --tag=bot-protection-server
```

You'll get a `bot-protection/` directory with:

| File | Use |
| --- | --- |
| `nginx-shared-map.conf` | Drop in `/etc/nginx/conf.d/` once — defines `$blocked_bot` map for all vhosts |
| `nginx-vhost-snippet.conf` | Paste into each Nginx `server {}` block |
| `apache-vhost-snippet.conf` | Full Apache vhost example with `SetEnvIf` |
| `htaccess-snippet.txt` | `.htaccess` rules (when you can't edit vhosts) |

---

## 🧬 How It Works

```
   ┌─────────────────────┐
   │  Incoming Request   │
   └──────────┬──────────┘
              ▼
   ┌────────────────────────┐
   │   Web Server           │  ← optional: blocks at nginx/apache layer
   │   (nginx/apache)       │
   └──────────┬─────────────┘
              ▼
   ┌────────────────────────┐
   │  BotProtection         │
   │  Middleware            │
   │                        │
   │  1. Check enabled?     │
   │  2. IP in allow-list?  │
   │  3. UA matches bot?    │──── YES ──▶  HTTP 403
   │  4. Empty UA + flag?   │
   └──────────┬─────────────┘
              │ NO
              ▼
   ┌────────────────────────┐
   │   Laravel App          │
   └──────────┬─────────────┘
              ▼
   ┌────────────────────────┐
   │  Response              │
   │  + X-Robots-Tag header │
   └────────────────────────┘
```

---

## 🧪 Running Tests

```bash
composer install
./vendor/bin/pest
```

13 Pest tests cover:
- ✅ Blocking known bot User-Agents
- ✅ Allowing legitimate browser User-Agents
- ✅ Adding `X-Robots-Tag` header to passed responses
- ✅ Case-insensitive User-Agent matching
- ✅ `enabled=false` bypass
- ✅ Custom block status codes
- ✅ Custom block messages
- ✅ Empty `x_robots_tag` disables header
- ✅ Empty User-Agent handling (both modes)
- ✅ Allowed-IP bypass

---

## 🤖 What's Blocked Out of the Box

<details>
<summary><b>Click to expand the full list (33 agents)</b></summary>

| Category | Agents |
| --- | --- |
| **OpenAI** | GPTBot, ChatGPT-User, OAI-SearchBot |
| **Anthropic** | ClaudeBot, anthropic-ai, Claude-Web |
| **Google** | Google-Extended, Googlebot, AdsBot-Google |
| **Meta** | Meta-ExternalAgent, FacebookBot, facebookexternalhit |
| **Apple** | Applebot, Applebot-Extended |
| **Amazon** | Amazonbot |
| **Perplexity** | PerplexityBot |
| **ByteDance** | Bytespider |
| **Common Crawl** | CCBot |
| **Cohere** | cohere-ai |
| **Mistral** | MistralAI-User |
| **Diffbot** | Diffbot |
| **SEO crawlers** | SemrushBot, AhrefsBot, MJ12bot, DotBot, BLEXBot |
| **Eastern engines** | YandexBot, Baiduspider, Sogou |
| **Generic scrapers** | Scrapy, python-requests, curl/, wget/ |

</details>

You can add, remove, or fully override the list by publishing config and editing `blocked_agents`.

---

## ⚠️ What This Package Is NOT

- ❌ **Not authentication.** If content must be private, use Laravel auth, Basic Auth, or Cloudflare Zero Trust.
- ❌ **Not foolproof against UA spoofing.** A determined scraper can fake any User-Agent. This package targets mass crawlers that identify themselves correctly.
- ❌ **Not a WAF.** For rate limiting, geo-blocking, DDoS protection, layer in Cloudflare or a dedicated WAF.

For maximum protection: **this package + server-level rules + authentication for sensitive content.**

---

## 🔗 Related

- 📖 [Google: Robots meta tag and X-Robots-Tag specifications](https://developers.google.com/search/docs/crawling-indexing/robots-meta-tag)
- 📖 [OpenAI: GPTBot opt-out documentation](https://platform.openai.com/docs/gptbot)
- 📖 [Cloudflare: Block AI bots and scrapers](https://blog.cloudflare.com/declaring-your-aindependence-block-ai-bots-scrapers-and-crawlers-with-a-single-click/)

---

## 🤝 Contributing

Contributions are welcome! Please open an issue or PR.

For new bot User-Agents to add to the default list, please include a source link (the bot's official documentation page).

---

## 📜 License

The MIT License (MIT). See [LICENSE](LICENSE) for details.

---

<div align="center">

**Built with ❤️ for the Laravel community.**

If this package saved your bandwidth or your sanity, ⭐ the repo!

</div>
