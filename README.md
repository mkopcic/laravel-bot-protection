# Laravel Bot Protection

Laravel middleware za blokiranje AI crawlera, tražilica i poznatih scraper botova. Automatska registracija u `web` middleware grupu, fully konfigurabilan, podržava Laravel 10 / 11 / 12 / 13.

## Što paket radi

1. **Blokira poznate bot User-Agente** (vraća HTTP 403 — konfigurabilno)
2. **Dodaje `X-Robots-Tag: noindex, nofollow, noarchive, nosnippet` header** na sve odgovore
3. **Publishable `robots.txt`** s kompletnom listom AI crawlera
4. **Publishable Nginx i Apache config primjeri** za web server razinu zaštite
5. **Artisan komanda za testiranje** — pošalji request s lažnim bot UA i provjeri vraća li server 403

Pokriva: GPTBot, ChatGPT-User, ClaudeBot, anthropic-ai, Google-Extended, Googlebot, PerplexityBot, Bytespider, CCBot, Cohere, Mistral, Meta, Apple, Amazon, SemrushBot, AhrefsBot, MJ12bot, Yandex, Baidu, Scrapy i druge.

## Zahtjevi

- PHP 8.1+
- Laravel 10, 11, 12 ili 13

## Instalacija

```bash
composer require mkopcic/laravel-bot-protection
```

Package će se auto-discover preko Laravel package discovery. Middleware se automatski registrira u `web` grupu — nema dodatnih koraka.

### Publish konfiguracije (opcionalno)

```bash
php artisan vendor:publish --tag=bot-protection-config
```

### Publish robots.txt (opcionalno — pazi, overwrite-a postojeći!)

```bash
php artisan vendor:publish --tag=bot-protection-robots
```

### Publish Nginx / Apache config primjera

```bash
php artisan vendor:publish --tag=bot-protection-server
```

Kreira `bot-protection/` direktorij u root-u projekta s primjerima koje zalijepiš u svoje web server configove.

## Konfiguracija

Sve preko `.env` varijabli ili nakon publish-a u `config/bot-protection.php`:

```env
BOT_PROTECTION_ENABLED=true
BOT_PROTECTION_AUTO_REGISTER=true
BOT_PROTECTION_MIDDLEWARE_GROUP=web
BOT_PROTECTION_BLOCK_STATUS=403
BOT_PROTECTION_BLOCK_MESSAGE=Forbidden
BOT_PROTECTION_X_ROBOTS_TAG="noindex, nofollow, noarchive, nosnippet"
BOT_PROTECTION_BLOCK_EMPTY_UA=false
BOT_PROTECTION_ALLOWED_IPS=1.2.3.4,5.6.7.8
```

## Ručna registracija middleware-a

Postavi `BOT_PROTECTION_AUTO_REGISTER=false` i registriraj sam.

**Laravel 11 / 12 / 13** — u `bootstrap/app.php`:

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->web(append: [
        \Mkopcic\BotProtection\Http\Middleware\BotProtectionMiddleware::class,
    ]);
})
```

**Laravel 10** — u `app/Http/Kernel.php` u `$middlewareGroups['web']`:

```php
protected $middlewareGroups = [
    'web' => [
        // ...
        \Mkopcic\BotProtection\Http\Middleware\BotProtectionMiddleware::class,
    ],
];
```

## Artisan komanda — testiranje

Dva subkomanda: `url` i `config`.

### `config` — dump-aj trenutnu konfiguraciju

```bash
php artisan bot-protection:test config
```

### `url` — pošalji HTTP request s bot UA

```bash
# Default test (GPTBot, ClaudeBot, PerplexityBot)
php artisan bot-protection:test url https://example.com

# Konkretan agent
php artisan bot-protection:test url https://example.com --agent=GPTBot

# Svi agenti iz config-a
php artisan bot-protection:test url https://example.com --all

# Custom timeout
php artisan bot-protection:test url https://example.com --timeout=30
```

Komanda ispisuje za svaki agent: ✓ BLOCKED ili ✗ ALLOWED + status code i X-Robots-Tag header.

## Slojeviti pristup — web server config

Middleware je **prvi sloj** unutar Laravel-a. Za maksimalnu zaštitu kombiniraj s web server razinom (botovi se nikad ne dovedu do PHP-a):

```bash
php artisan vendor:publish --tag=bot-protection-server
```

Dobiješ 4 primjera u `bot-protection/`:

- `nginx-shared-map.conf` — staviti jednom u `/etc/nginx/conf.d/`
- `nginx-vhost-snippet.conf` — dodati u svaki Nginx vhost
- `apache-vhost-snippet.conf` — Apache vhost s `SetEnvIf`
- `htaccess-snippet.txt` — `.htaccess` verzija (kad nemaš pristup vhostu)

## Testovi

```bash
composer install
./vendor/bin/pest
```

13 Pest testova pokriva: blokiranje, propuštanje, X-Robots-Tag, disable flag, custom status, prazan UA, IP whitelist, case-insensitive matching.

## Što ovaj paket NIJE

- **Nije autentifikacija.** Ako trebaš stvarnu privatnost (npr. dev portal), koristi Laravel auth, Basic Auth ili Cloudflare Zero Trust.
- **Ne sprječava lažiranje User-Agenta.** Determinirani scraper će promijeniti UA. Ovo je obrana protiv masovnih AI crawlera koji se identificiraju.
- **Nije WAF.** Za napredne stvari (rate limiting, geo-blocking, DDoS) razmotri Cloudflare ili sličan WAF.

## License

MIT
