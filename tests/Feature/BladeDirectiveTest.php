<?php

use Illuminate\Support\Facades\Blade;
use Mkopcic\BotProtection\Support\MetaTags;

it('renderira @botProtectionMeta direktivu', function () {
    $compiled = Blade::compileString('@botProtectionMeta');

    expect($compiled)->toContain(MetaTags::class);
    expect($compiled)->toContain('::render()');
});

it('MetaTags::render() vraća sve standardne robots meta tagove', function () {
    $html = MetaTags::render();

    expect($html)
        ->toContain('<meta name="robots"')
        ->toContain('<meta name="googlebot"')
        ->toContain('<meta name="googlebot-news"')
        ->toContain('<meta name="bingbot"')
        ->toContain('noindex, nofollow, noarchive, nosnippet');
});

it('MetaTags::render() uključuje noai/noimageai AI opt-out tag', function () {
    $html = MetaTags::render();

    expect($html)
        ->toContain('noai, noimageai');
});

it('MetaTags::render() ne renderira AI tag kad je ai_meta_tags prazan', function () {
    config()->set('bot-protection.ai_meta_tags', '');

    $html = MetaTags::render();

    expect($html)
        ->not->toContain('noai')
        ->toContain('<meta name="robots"'); // standardni i dalje tu
});

it('MetaTags::render() vraća prazan string kad su oba prazna', function () {
    config()->set('bot-protection.x_robots_tag', '');
    config()->set('bot-protection.ai_meta_tags', '');

    expect(MetaTags::render())->toBe('');
});

it('MetaTags::render() renderira samo AI tag kad je x_robots_tag prazan', function () {
    config()->set('bot-protection.x_robots_tag', '');

    $html = MetaTags::render();

    expect($html)
        ->toContain('noai, noimageai')
        ->not->toContain('noindex');
});

it('MetaTags::render() escape-a HTML znakove u content atributu', function () {
    config()->set('bot-protection.x_robots_tag', 'noindex"><script>alert(1)</script>');

    $html = MetaTags::render();

    expect($html)
        ->not->toContain('<script>')
        ->toContain('&lt;script&gt;')
        ->toContain('&quot;');
});

it('Blade direktiva renderira HTML kad se evaluira u view-u', function () {
    $view = view()->file(
        __DIR__ . '/../stubs/meta-directive-test.blade.php'
    );

    $rendered = $view->render();

    expect($rendered)
        ->toContain('<meta name="robots"')
        ->toContain('noindex');
});
