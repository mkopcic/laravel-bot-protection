# Changelog

All notable changes to `laravel-bot-protection` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.2.0] - 2026-05-21

### Added

- **GitHub Actions CI** — matrix of 11 PHP/Laravel combinations (PHP 8.1-8.4 × Laravel 10/11/12/13) plus a `--prefer-lowest` job. Verifies the support claim instead of just stating it.
- **`noai, noimageai` AI opt-out meta tag** — `@botProtectionMeta` now renders an additional `<meta name="robots" content="noai, noimageai">` tag. Emerging standard adopted by DeviantArt, ArtStation, Squarespace. Configurable via `BOT_PROTECTION_AI_META_TAGS`.
- **Dynamic `/robots.txt` route** (opt-in via `BOT_PROTECTION_GENERATE_ROBOTS_ROUTE=true`) — generates robots.txt content from the `blocked_agents` config. Single source of truth: change config, robots.txt updates automatically.
- **6 new Pest tests** — AI meta tags, robots route registration, robots route content (total: 34 tests, 63 assertions).

### Changed

- `MetaTags::render()` now outputs an additional AI opt-out meta tag when `ai_meta_tags` is non-empty. Existing 4 tags unchanged.

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

[1.2.0]: https://github.com/mkopcic/laravel-bot-protection/compare/v1.1.0...v1.2.0
[1.1.0]: https://github.com/mkopcic/laravel-bot-protection/compare/v1.0.0...v1.1.0
[1.0.0]: https://github.com/mkopcic/laravel-bot-protection/releases/tag/v1.0.0
