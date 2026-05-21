<?php

use Illuminate\Support\Facades\Log;

it('ne loga kad je log_blocked=false (default)', function () {
    config()->set('bot-protection.log_blocked', false);
    Log::spy();

    $this->withHeaders([
        'User-Agent' => 'GPTBot/1.0',
    ])->get('/test-route');

    Log::shouldNotHaveReceived('warning');
});

it('loga warning kad je log_blocked=true i bot blokiran', function () {
    config()->set('bot-protection.log_blocked', true);
    Log::spy();

    $this->withHeaders([
        'User-Agent' => 'ClaudeBot/2.0',
    ])->get('/test-route');

    Log::shouldHaveReceived('channel')->atLeast()->once();
});

it('ne loga kad je request propušten', function () {
    config()->set('bot-protection.log_blocked', true);
    Log::spy();

    $this->withHeaders([
        'User-Agent' => 'Mozilla/5.0 Chrome/120.0',
    ])->get('/test-route');

    Log::shouldNotHaveReceived('warning');
});
