<?php

it('ne registrira /robots.txt rutu kad je generate_robots_route=false', function () {
    config()->set('bot-protection.generate_robots_route', false);

    $routes = collect(app('router')->getRoutes()->getRoutes())
        ->map(fn ($r) => $r->uri())
        ->all();

    expect($routes)->not->toContain('robots.txt');
});

/**
 * Napomena: dinamičku rutu testiramo preko zasebnog test case-a
 * koji forsira generate_robots_route=true PRIJE booting service providera.
 */
it('dinamička /robots.txt ruta vraća User-agent direktive iz blocked_agents', function () {
    // Re-bind config i re-register rute (radimo to ručno jer je default off)
    config()->set('bot-protection.generate_robots_route', true);
    config()->set('bot-protection.blocked_agents', ['GPTBot', 'ClaudeBot', 'PerplexityBot']);

    \Illuminate\Support\Facades\Route::get('/robots.txt', \Mkopcic\BotProtection\Http\Controllers\RobotsController::class);

    $response = $this->get('/robots.txt');

    $response->assertStatus(200);
    $response->assertHeader('Content-Type', 'text/plain; charset=UTF-8');

    $body = $response->getContent();

    expect($body)
        ->toContain('User-agent: *')
        ->toContain('Disallow: /')
        ->toContain('User-agent: GPTBot')
        ->toContain('User-agent: ClaudeBot')
        ->toContain('User-agent: PerplexityBot');
});

it('dinamička /robots.txt ruta postavlja X-Robots-Tag header', function () {
    config()->set('bot-protection.generate_robots_route', true);

    \Illuminate\Support\Facades\Route::get('/robots.txt', \Mkopcic\BotProtection\Http\Controllers\RobotsController::class);

    $response = $this->get('/robots.txt');

    $response->assertStatus(200);
    expect($response->headers->get('X-Robots-Tag'))
        ->toContain('noindex');
});
