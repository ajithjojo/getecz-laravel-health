<?php

namespace Getecz\LaravelHealth\Checks;

use Illuminate\Support\Facades\DB;

class DbLatencyCheck implements CheckInterface
{
    public static function key(): string { return 'db'; }
    public static function label(): string { return 'Database'; }

    public function run(): array
    {
        $start = microtime(true);
        try {
            DB::select('select 1');
            $ms = (microtime(true) - $start) * 1000;

            $status = $ms < 50 ? 'ok' : ($ms < 200 ? 'warn' : 'fail');

            return [
                'status' => $status,
                'message' => $status === 'ok'
                    ? 'DB reachable'
                    : ($status === 'warn' ? 'DB slow' : 'DB very slow'),
                'meta' => [
                    'connection' => DB::getDefaultConnection(),
                ],
                'time_ms' => round($ms, 2),
            ];
        } catch (\Throwable $e) {
            $ms = (microtime(true) - $start) * 1000;
            return [
                'status' => 'fail',
                'message' => 'DB error: ' . $e->getMessage(),
                'meta' => [
                    'connection' => DB::getDefaultConnection(),
                ],
                'time_ms' => round($ms, 2),
            ];
        }
    }
}
