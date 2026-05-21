<?php

use Illuminate\Support\Facades\Event;
use Mkopcic\BotProtection\Events\BotBlocked;

it('emitira BotBlocked event kad je bot blokiran', function () {
    Event::fake([BotBlocked::class]);

    $this->withHeaders([
        'User-Agent' => 'GPTBot/1.0',
    ])->get('/test-route');

    Event::assertDispatched(BotBlocked::class, function (BotBlocked $event) {
        return $event->matchedAgent === 'GPTBot'
            && str_contains($event->userAgent, 'GPTBot')
            && str_contains($event->url, '/test-route');
    });
});

it('ne emitira event kad je request propušten', function () {
    Event::fake([BotBlocked::class]);

    $this->withHeaders([
        'User-Agent' => 'Mozilla/5.0 Chrome/120.0',
    ])->get('/test-route');

    Event::assertNotDispatched(BotBlocked::class);
});

it('event nosi sve podatke o blokadi', function () {
    Event::fake([BotBlocked::class]);

    $this->withHeaders([
        'User-Agent' => 'PerplexityBot/1.5',
    ])->get('/test-route?foo=bar');

    Event::assertDispatched(BotBlocked::class, function (BotBlocked $event) {
        return $event->matchedAgent === 'PerplexityBot'
            && $event->userAgent === 'PerplexityBot/1.5'
            && $event->ip !== ''
            && str_contains($event->url, 'foo=bar');
    });
});

it('event matchedAgent je "(empty)" kad je prazan UA blokiran', function () {
    config()->set('bot-protection.block_empty_user_agent', true);

    Event::fake([BotBlocked::class]);

    $this->withHeaders(['User-Agent' => ''])->get('/test-route');

    Event::assertDispatched(BotBlocked::class, function (BotBlocked $event) {
        return $event->matchedAgent === '(empty)';
    });
});
