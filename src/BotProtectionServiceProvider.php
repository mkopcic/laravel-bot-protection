<?php

namespace Mkopcic\BotProtection;

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Mkopcic\BotProtection\Console\Commands\TestBotProtectionCommand;
use Mkopcic\BotProtection\Http\Controllers\RobotsController;
use Mkopcic\BotProtection\Http\Middleware\BotProtectionMiddleware;
use Mkopcic\BotProtection\Support\MetaTags;

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
        $this->registerBladeDirectives();
        $this->registerRobotsRoute();
    }

    /**
     * Registriraj /robots.txt rutu ako je generate_robots_route=true.
     *
     * Ruta se aktivira samo ako web server NE servira public/robots.txt
     * direktno (što je default ponašanje Apache/Nginx za statičke fajlove).
     */
    protected function registerRobotsRoute(): void
    {
        if (!config('bot-protection.generate_robots_route', false)) {
            return;
        }

        Route::get('/robots.txt', RobotsController::class)
            ->withoutMiddleware([BotProtectionMiddleware::class])
            ->name('bot-protection.robots');
    }

    /**
     * Registriraj @botProtectionMeta Blade direktivu.
     */
    protected function registerBladeDirectives(): void
    {
        Blade::directive('botProtectionMeta', function () {
            return '<?php echo \\' . MetaTags::class . '::render(); ?>';
        });
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
     * Laravel 10 koristi Kernel s eksplicitnim $middlewareGroups propertyjem.
     * Router::pushMiddlewareToGroup() tamo ne funkcionira jer Kernel property
     * inicijalizacija pregazi Router state. Rješenje:
     *   - L10: Kernel::appendMiddlewareToGroup()  (detektiramo po metodi)
     *   - L11+: Router::pushMiddlewareToGroup()   (Kernel više ne postoji)
     */
    protected function registerMiddleware(): void
    {
        if (!config('bot-protection.auto_register', true)) {
            return;
        }

        $group = (string) config('bot-protection.middleware_group', 'web');

        $kernel = $this->app->make(\Illuminate\Contracts\Http\Kernel::class);

        if (method_exists($kernel, 'appendMiddlewareToGroup')) {
            // Laravel 10 — idi kroz Kernel
            $kernel->appendMiddlewareToGroup($group, BotProtectionMiddleware::class);
        } else {
            // Laravel 11+ — Kernel ne postoji, koristimo Router
            /** @var Router $router */
            $router = $this->app->make(Router::class);
            $router->pushMiddlewareToGroup($group, BotProtectionMiddleware::class);
        }
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
