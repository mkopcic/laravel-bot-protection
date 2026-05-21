<?php

it('bot-protection:test config dumpa konfiguraciju', function () {
    $this->artisan('bot-protection:test', ['action' => 'config'])
        ->expectsOutputToContain('Bot Protection — trenutna konfiguracija')
        ->expectsOutputToContain('Enabled:')
        ->expectsOutputToContain('GPTBot')
        ->assertExitCode(0);
});

it('bot-protection:test s nepoznatom akcijom vraća error', function () {
    $this->artisan('bot-protection:test', ['action' => 'foobar'])
        ->expectsOutputToContain("Nepoznata akcija: 'foobar'")
        ->assertExitCode(2);
});

it('bot-protection:test url bez URL-a vraća error', function () {
    $this->artisan('bot-protection:test', ['action' => 'url'])
        ->expectsOutputToContain('URL je obavezan')
        ->assertExitCode(2);
});
