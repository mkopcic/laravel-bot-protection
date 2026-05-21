<?php

namespace Mkopcic\BotProtection\Tests;

use Mkopcic\BotProtection\BotProtectionServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            BotProtectionServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        // Test app key za session/encryption koju web grupa očekuje
        $app['config']->set('app.key', 'base64:' . base64_encode(random_bytes(32)));

        $app['config']->set('bot-protection.enabled', true);
        $app['config']->set('bot-protection.auto_register', true);
        $app['config']->set('bot-protection.middleware_group', 'web');
        $app['config']->set('bot-protection.block_status', 403);
        $app['config']->set('bot-protection.block_message', 'Forbidden');
        $app['config']->set('bot-protection.x_robots_tag', 'noindex, nofollow, noarchive, nosnippet');
        $app['config']->set('bot-protection.ai_meta_tags', 'noai, noimageai');
        $app['config']->set('bot-protection.generate_robots_route', false);
        $app['config']->set('bot-protection.block_empty_user_agent', false);
        $app['config']->set('bot-protection.log_blocked', false);
        $app['config']->set('bot-protection.log_channel', null);
        $app['config']->set('bot-protection.allowed_ips', []);
        $app['config']->set('bot-protection.blocked_agents', [
            'GPTBot',
            'ClaudeBot',
            'PerplexityBot',
        ]);
    }

    protected function defineRoutes($router): void
    {
        $router->middleware(\Mkopcic\BotProtection\Http\Middleware\BotProtectionMiddleware::class)
            ->group(function ($router) {
                $router->get('/test-route', function () {
                    return response('OK', 200);
                });
            });
    }
}
