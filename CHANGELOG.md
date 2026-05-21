# Changelog

All notable changes to `laravel-bot-protection` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.1.0] - 2026-05-21

### Added

- **`@botProtectionMeta` Blade directive** — drop-in replacement for manually adding `<meta name="robots">` tags to layouts. Renders the four standard meta tags (`robots`, `googlebot`, `googlebot-news`, `bingbot`) using the configured `x_robots_tag` value.
- **`BotBlocked` event** — fired when the middleware blocks a request. Includes `userAgent`, `ip`, `url`, and `matchedAgent` properties. Consumers can listen for this to log, alert, or feed analytics.
- **Optional logging** — set `BOT_PROTECTION_LOG_BLOCKED=true` to write every blocked request as a `warning` to the Laravel log. Configurable log channel via `BOT_PROTECTION_LOG_CHANNEL`.
- **Additional Pest tests** — coverage now includes the Artisan command, the `BotBlocked` event, the logging behavior, and the Blade directive.
- **CHANGELOG.md**.

### Changed

- `BotProtectionMiddleware::matchesBlockedAgent()` renamed internally to `matchedBlockedAgent()` and now returns the matched needle string (or `null`), enabling the event payload. No public API change.

## [1.0.0] - 2026-05-21

### Added

- Initial release.
- `BotProtectionMiddleware` blocking 30+ known AI crawlers and SEO bots by User-Agent.
- Auto-registration into the `web` middleware group via the service provider.
- `X-Robots-Tag` header added to every response.
- Configurable: enable toggle, status code, block message, allow-list IPs, empty-UA handling, custom blocked agent list.
- Publishable assets: `config/bot-protection.php`, `public/robots.txt`, Nginx/Apache/`.htaccess` server config stubs.
- `bot-protection:test` Artisan command with `url` and `config` subactions.
- 13 Pest tests.
- Support for Laravel 10 / 11 / 12 / 13 on PHP 8.1+.

[1.1.0]: https://github.com/mkopcic/laravel-bot-protection/compare/v1.0.0...v1.1.0
[1.0.0]: https://github.com/mkopcic/laravel-bot-protection/releases/tag/v1.0.0
