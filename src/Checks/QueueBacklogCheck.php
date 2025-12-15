<?php

namespace Getecz\LaravelHealth\Checks;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class QueueBacklogCheck implements CheckInterface
{
    public static function key(): string { return 'queue'; }
    public static function label(): string { return 'Queue'; }

    public function run(): array
    {
        $driver = config('queue.default');
        $queueName = config('getecz-health.queue_name', 'default');
        $backlog = null;
        $start = microtime(true);

        try {
            if ($driver === 'database') {
                $table = config('queue.connections.database.table', 'jobs');
                $backlog = DB::table($table)->count();
            } elseif ($driver === 'redis') {
                $key = 'queues:' . $queueName;
                try {
                    $backlog = (int) Redis::connection()->llen($key);
                } catch (\Throwable $e) {
                    $backlog = null;
                }
            } else {
                return [
                    'status' => 'skip',
                    'message' => 'Backlog check not supported for driver: ' . $driver,
                    'meta' => ['driver' => $driver],
                    'time_ms' => round((microtime(true) - $start) * 1000, 2),
                ];
            }

            $ms = (microtime(true) - $start) * 1000;
            $status = $backlog === null ? 'warn' : ($backlog < 100 ? 'ok' : ($backlog < 1000 ? 'warn' : 'fail'));

            return [
                'status' => $status,
                'message' => $status === 'ok' ? 'Queue ok' : ($status === 'warn' ? 'Queue backlog high' : 'Queue backlog critical'),
                'meta' => [
                    'driver' => $driver,
                    'queue' => $queueName,
                    'backlog' => $backlog,
                ],
                'time_ms' => round($ms, 2),
            ];
        } catch (\Throwable $e) {
            $ms = (microtime(true) - $start) * 1000;
            return [
                'status' => 'warn',
                'message' => 'Queue check error: ' . $e->getMessage(),
                'meta' => ['driver' => $driver, 'queue' => $queueName],
                'time_ms' => round($ms, 2),
            ];
        }
    }
}
