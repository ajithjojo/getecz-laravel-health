<?php

namespace Getecz\LaravelHealth\Checks;

use Illuminate\Support\Facades\Cache;

class CacheLatencyCheck implements CheckInterface
{
    public static function key(): string { return 'cache'; }
    public static function label(): string { return 'Cache'; }

    public function run(): array
    {
        $key = 'getecz_health_cache_test_' . bin2hex(random_bytes(6));
        $start = microtime(true);
        try {
            Cache::put($key, 'ok', 10);
            $val = Cache::get($key);
            Cache::forget($key);

            $ms = (microtime(true) - $start) * 1000;
            $status = ($val === 'ok') ? ($ms < 30 ? 'ok' : ($ms < 150 ? 'warn' : 'fail')) : 'fail';

            return [
                'status' => $status,
                'message' => $status === 'ok'
                    ? 'Cache ok'
                    : ($status === 'warn' ? 'Cache slow' : 'Cache failing'),
                'meta' => [
                    'driver' => config('cache.default'),
                ],
                'time_ms' => round($ms, 2),
            ];
        } catch (\Throwable $e) {
            $ms = (microtime(true) - $start) * 1000;
            return [
                'status' => 'fail',
                'message' => 'Cache error: ' . $e->getMessage(),
                'meta' => ['driver' => config('cache.default')],
                'time_ms' => round($ms, 2),
            ];
        }
    }
}
