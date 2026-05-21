<div align="center">

# 🤖🛡️ Laravel Bot Protection

**Block AI crawlers, search engines, and known scrapers from your Laravel app — with one line of `composer require`.**

[![Latest Version on Packagist](https://img.shields.io/packagist/v/mkopcic/laravel-bot-protection.svg?style=flat-square)](https://packagist.org/packages/mkopcic/laravel-bot-protection)
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
- 🔧 **Fully configurable** via `.env` or published config — toggle, status code, custom message, allow-list IPs
- 📄 **Publishable `robots.txt`** with comprehensive AI/SEO crawler disallow list
- 🌐 **Server-level config stubs** — Nginx (shared map + per-vhost), Apache vhost, `.htaccess`
- 🧪 **Artisan test command** — verify protection works against a live URL
- ✅ **Tested with Pest** — 13 tests covering blocking, headers, config flags, IP whitelist
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
```

For custom blocked agent lists, publish the config and edit `config/bot-protection.php`.

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
