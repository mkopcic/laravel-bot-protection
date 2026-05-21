<?php

namespace Mkopcic\BotProtection;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Mkopcic\BotProtection\Console\Commands\TestBotProtectionCommand;
use Mkopcic\BotProtection\Http\Middleware\BotProtectionMiddleware;

class BotProtectionServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/bot-protection.php',
            'bot-protection'
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->registerPublishables();
        $this->registerMiddleware();
        $this->registerCommands();
    }

    /**
     * Registriraj sve publish targete s odvojenim tagovima.
     */
    protected function registerPublishables(): void
    {
        if (!$this->app->runningInConsole()) {
            return;
        }

        // Config
        $this->publishes([
            __DIR__ . '/../config/bot-protection.php' => config_path('bot-protection.php'),
        ], 'bot-protection-config');

        // robots.txt — eksplicitni tag, NIJE u defaultnom publish-u
        $this->publishes([
            __DIR__ . '/../resources/stubs/robots.txt' => public_path('robots.txt'),
        ], 'bot-protection-robots');

        // Nginx / Apache server config primjeri — u project root pod bot-protection/
        $this->publishes([
            __DIR__ . '/../resources/stubs/server' => base_path('bot-protection'),
        ], 'bot-protection-server');

        // Sve odjednom
        $this->publishes([
            __DIR__ . '/../config/bot-protection.php' => config_path('bot-protection.php'),
            __DIR__ . '/../resources/stubs/server' => base_path('bot-protection'),
        ], 'bot-protection');
    }

    /**
     * Auto-registriraj middleware u konfiguriranu grupu.
     *
     * Koristi Router::pushMiddlewareToGroup() koji radi identično
     * u Laravel 10, 11, 12 i 13.
     */
    protected function registerMiddleware(): void
    {
        if (!config('bot-protection.auto_register', true)) {
            return;
        }

        /** @var Router $router */
        $router = $this->app->make(Router::class);

        $group = (string) config('bot-protection.middleware_group', 'web');

        $router->pushMiddlewareToGroup($group, BotProtectionMiddleware::class);
    }

    /**
     * Registriraj Artisan komande.
     */
    protected function registerCommands(): void
    {
        if (!$this->app->runningInConsole()) {
            return;
        }

        $this->commands([
            TestBotProtectionCommand::class,
        ]);
    }
}
