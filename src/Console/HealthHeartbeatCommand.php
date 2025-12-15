<?php

namespace Getecz\LaravelHealth\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class HealthHeartbeatCommand extends Command
{
    protected $signature = 'getecz:health-heartbeat';
    protected $description = 'Write cron heartbeat used by Getecz health monitor';

    public function handle(): int
    {
        Cache::put('getecz_health_heartbeat', now()->toIso8601String(), 60 * 24);
        $this->info('Heartbeat written: ' . now()->toIso8601String());
        return self::SUCCESS;
    }
}
