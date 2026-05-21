<?php

namespace Mkopcic\BotProtection\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TestBotProtectionCommand extends Command
{
    /**
     * Signature with subcommand-style first argument.
     *
     *   php artisan bot-protection:test url https://example.com
     *   php artisan bot-protection:test url https://example.com --agent=GPTBot
     *   php artisan bot-protection:test url https://example.com --all
     *   php artisan bot-protection:test config
     */
    protected $signature = 'bot-protection:test
        {action : Action to run — url | config}
        {url? : URL koji se testira (samo za action=url)}
        {--agent= : Specifični User-Agent za test (samo url)}
        {--all : Testiraj sve agente iz config (samo url)}
        {--timeout=10 : HTTP timeout u sekundama (samo url)}';

    protected $description = 'Test bot protection — pošalji request s bot UA ili dump-aj konfiguraciju';

    public function handle(): int
    {
        $action = (string) $this->argument('action');

        return match ($action) {
            'url'    => $this->runUrlTest(),
            'config' => $this->runConfigDump(),
            default  => $this->invalidAction($action),
        };
    }

    protected function invalidAction(string $action): int
    {
        $this->error("Nepoznata akcija: '{$action}'. Koristi 'url' ili 'config'.");
        $this->line('');
        $this->line('Primjeri:');
        $this->line('  php artisan bot-protection:test config');
        $this->line('  php artisan bot-protection:test url https://example.com');
        $this->line('  php artisan bot-protection:test url https://example.com --agent=GPTBot');
        $this->line('  php artisan bot-protection:test url https://example.com --all');

        return self::INVALID;
    }

    /**
     * Action: url — pošalji HTTP request(e) i provjeri response.
     */
    protected function runUrlTest(): int
    {
        $url = (string) $this->argument('url');

        if ($url === '') {
            $this->error('URL je obavezan za action=url.');
            $this->line('Primjer: php artisan bot-protection:test url https://example.com');
            return self::INVALID;
        }

        $timeout = (int) $this->option('timeout');
        $agents = $this->resolveAgentsToTest();

        $this->info("Testiranje: {$url}");
        $this->info('Broj agenata: ' . count($agents));
        $this->line('');

        $blocked = 0;
        $allowed = 0;
        $failed = 0;

        foreach ($agents as $agent) {
            try {
                $response = Http::withHeaders([
                    'User-Agent' => $agent,
                ])->timeout($timeout)->get($url);

                $status = $response->status();
                $isBlocked = $status >= 400 && $status < 500;
                $xRobots = $response->header('X-Robots-Tag');

                if ($isBlocked) {
                    $blocked++;
                    $this->line(sprintf(
                        "  <fg=green>✓ BLOCKED</> [%d] %s",
                        $status,
                        $agent
                    ));
                } else {
                    $allowed++;
                    $this->line(sprintf(
                        "  <fg=red>✗ ALLOWED</> [%d] %s%s",
                        $status,
                        $agent,
                        $xRobots ? "  (X-Robots-Tag: {$xRobots})" : ''
                    ));
                }
            } catch (\Throwable $e) {
                $failed++;
                $this->line(sprintf(
                    "  <fg=yellow>! ERROR</>   %s — %s",
                    $agent,
                    $e->getMessage()
                ));
            }
        }

        $this->line('');
        $this->line('───────────────────────────────────────');
        $this->line(sprintf(
            '<fg=green>Blocked:</> %d   <fg=red>Allowed:</> %d   <fg=yellow>Errors:</> %d',
            $blocked,
            $allowed,
            $failed
        ));

        return $allowed === 0 ? self::SUCCESS : self::FAILURE;
    }

    /**
     * Action: config — ispiši trenutnu konfiguraciju.
     */
    protected function runConfigDump(): int
    {
        $this->info('Bot Protection — trenutna konfiguracija');
        $this->line('');

        $this->line(sprintf('  Enabled:                  %s', $this->boolStr(config('bot-protection.enabled'))));
        $this->line(sprintf('  Auto-register middleware: %s', $this->boolStr(config('bot-protection.auto_register'))));
        $this->line(sprintf('  Middleware group:         %s', (string) config('bot-protection.middleware_group')));
        $this->line(sprintf('  Block status:             %s', (string) config('bot-protection.block_status')));
        $this->line(sprintf('  Block message:            %s', (string) config('bot-protection.block_message')));
        $this->line(sprintf('  X-Robots-Tag:             %s', (string) (config('bot-protection.x_robots_tag') ?: '(disabled)')));
        $this->line(sprintf('  Block empty UA:           %s', $this->boolStr(config('bot-protection.block_empty_user_agent'))));

        $allowed = (array) config('bot-protection.allowed_ips', []);
        $this->line(sprintf('  Allowed IPs:              %s', $allowed === [] ? '(none)' : implode(', ', $allowed)));

        $blocked = (array) config('bot-protection.blocked_agents', []);
        $this->line('');
        $this->line(sprintf('  Blocked agents (%d):', count($blocked)));
        foreach ($blocked as $agent) {
            $this->line("    • {$agent}");
        }

        return self::SUCCESS;
    }

    /**
     * @return array<int, string>
     */
    protected function resolveAgentsToTest(): array
    {
        if ($this->option('all')) {
            $list = (array) config('bot-protection.blocked_agents', []);
            return array_values(array_filter(array_map('strval', $list)));
        }

        $custom = $this->option('agent');
        if (is_string($custom) && $custom !== '') {
            return [$custom];
        }

        // Default: 3 reprezentativna agenta
        return ['GPTBot', 'ClaudeBot', 'PerplexityBot'];
    }

    protected function boolStr(mixed $value): string
    {
        return $value ? 'true' : 'false';
    }
}
