<?php

it('blokira request s poznatim bot User-Agentom', function () {
    $response = $this->withHeaders([
        'User-Agent' => 'Mozilla/5.0 (compatible; GPTBot/1.0; +https://openai.com/gptbot)',
    ])->get('/test-route');

    $response->assertStatus(403);
    $response->assertSeeText('Forbidden');
});

it('propušta legitimni User-Agent', function () {
    $response = $this->withHeaders([
        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0',
    ])->get('/test-route');

    $response->assertStatus(200);
    $response->assertSeeText('OK');
});

it('dodaje X-Robots-Tag header na propuštene odgovore', function () {
    $response = $this->withHeaders([
        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0',
    ])->get('/test-route');

    $response->assertStatus(200);
    expect($response->headers->get('X-Robots-Tag'))
        ->toBe('noindex, nofollow, noarchive, nosnippet');
});

it('blokira sve agente iz blocked_agents liste', function () {
    foreach (config('bot-protection.blocked_agents') as $agent) {
        $response = $this->withHeaders([
            'User-Agent' => $agent . ' /1.0',
        ])->get('/test-route');

        expect($response->status())
            ->toBe(403, "Agent {$agent} bi trebao biti blokiran");
    }
});

it('case-insensitive podudaranje na User-Agent', function () {
    $response = $this->withHeaders([
        'User-Agent' => 'gptbot/1.0',
    ])->get('/test-route');

    $response->assertStatus(403);
});

it('propušta sve kad je enabled=false', function () {
    config()->set('bot-protection.enabled', false);

    $response = $this->withHeaders([
        'User-Agent' => 'GPTBot/1.0',
    ])->get('/test-route');

    $response->assertStatus(200);
});

it('ne dodaje X-Robots-Tag kad je enabled=false', function () {
    config()->set('bot-protection.enabled', false);

    $response = $this->get('/test-route');

    expect($response->headers->get('X-Robots-Tag'))->toBeNull();
});

it('koristi konfiguriran block_status', function () {
    config()->set('bot-protection.block_status', 404);

    $response = $this->withHeaders([
        'User-Agent' => 'GPTBot/1.0',
    ])->get('/test-route');

    $response->assertStatus(404);
});

it('koristi konfiguriran block_message', function () {
    config()->set('bot-protection.block_message', 'Get lost, bot');

    $response = $this->withHeaders([
        'User-Agent' => 'GPTBot/1.0',
    ])->get('/test-route');

    $response->assertSeeText('Get lost, bot');
});

it('ne dodaje header kad je x_robots_tag prazan string', function () {
    config()->set('bot-protection.x_robots_tag', '');

    $response = $this->get('/test-route');

    $response->assertStatus(200);
    expect($response->headers->get('X-Robots-Tag'))->toBeNull();
});

it('blokira prazan User-Agent kad je block_empty_user_agent=true', function () {
    config()->set('bot-protection.block_empty_user_agent', true);

    $response = $this->withHeaders([
        'User-Agent' => '',
    ])->get('/test-route');

    $response->assertStatus(403);
});

it('propušta prazan User-Agent kad je block_empty_user_agent=false', function () {
    config()->set('bot-protection.block_empty_user_agent', false);

    $response = $this->withHeaders([
        'User-Agent' => '',
    ])->get('/test-route');

    $response->assertStatus(200);
});

it('propušta IP iz allowed_ips čak i kad UA odgovara bot listi', function () {
    config()->set('bot-protection.allowed_ips', ['127.0.0.1']);

    $response = $this->withHeaders([
        'User-Agent' => 'GPTBot/1.0',
    ])->get('/test-route');

    $response->assertStatus(200);
});
