<?php

namespace Getecz\LaravelHealth\Console;

use Getecz\LaravelHealth\HealthService;
use Getecz\LaravelHealth\Models\HealthSnapshot;
use Illuminate\Console\Command;

class HealthSnapshotCommand extends Command
{
    protected $signature = 'getecz:health-snapshot {--prune : Prune old snapshots based on retention}';
    protected $description = 'Run Getecz health checks and store a snapshot in the database.';

    public function handle(HealthService $health): int
    {
        if (!config('getecz-health.store_history', false)) {
            $this->error('getecz-health.store_history is false. Enable it in config/env...');
            return self::FAILURE;
        }

        $data = $health->run();

        try {
            HealthSnapshot::create([
                'overall' => $data['overall'] ?? null,
                'payload' => $data,
            ]);
            $this->info('Snapshot stored (' . ($data['overall'] ?? 'unknown') . ')');
        } catch (\Throwable $e) {
            $this->error('Failed to store snapshot: ' . $e->getMessage());
            return self::FAILURE;
        }

        if ($this->option('prune')) {
            $days = (int) config('getecz-health.history_retention_days', 14);
            $cutoff = now()->subDays($days);
            $deleted = HealthSnapshot::where('created_at', '<', $cutoff)->delete();
            $this->info("Pruned {$deleted} snapshots older than {$days} days.");
        }

        return self::SUCCESS;
    }
}
