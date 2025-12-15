<?php

namespace Getecz\LaravelHealth\Checks;

use Illuminate\Support\Facades\Cache;

class CronHeartbeatCheck implements CheckInterface
{
    public static function key(): string { return 'cron'; }
    public static function label(): string { return 'Cron'; }

    public function run(): array
    {
        $key = 'getecz_health_heartbeat';
        $maxMinutes = (int) config('getecz-health.cron_max_minutes', 10);
        $start = microtime(true);

        $last = Cache::get($key);
        $ms = (microtime(true) - $start) * 1000;

        if (!$last) {
            return [
                'status' => 'warn',
                'message' => 'No heartbeat seen yet',
                'meta' => [
                    'how_to_fix' => 'Schedule an HTTP hit to /' . config('getecz-health.route_prefix') . '/heartbeat or run: php artisan getecz:health-heartbeat',
                ],
                'time_ms' => round($ms, 2),
            ];
        }

        try {
            $lastAt = \Illuminate\Support\Carbon::parse($last);
        } catch (\Throwable $e) {
            return [
                'status' => 'warn',
                'message' => 'Invalid heartbeat value',
                'meta' => ['value' => $last],
                'time_ms' => round($ms, 2),
            ];
        }

        $diff = $lastAt->diffInMinutes(now());
        $status = $diff <= $maxMinutes ? 'ok' : ($diff <= ($maxMinutes * 3) ? 'warn' : 'fail');

        return [
            'status' => $status,
            'message' => $status === 'ok' ? 'Cron heartbeat ok' : 'Cron heartbeat stale',
            'meta' => [
                'last' => $lastAt->toIso8601String(),
                'minutes_ago' => $diff,
                'max_minutes' => $maxMinutes,
            ],
            'time_ms' => round($ms, 2),
        ];
    }
}
