<?php

namespace Getecz\LaravelHealth\Checks;

class SystemMetricsCheck implements CheckInterface
{
    public static function key(): string { return 'system'; }
    public static function label(): string { return 'System'; }

    public function run(): array
    {
        if (!config('getecz-health.include_system_metrics', true)) {
            return [
                'status' => 'skip',
                'message' => 'System metrics disabled',
                'meta' => [],
                'time_ms' => null,
            ];
        }

        $start = microtime(true);
        $meta = [];

        // Load average (Linux/macOS)
        $load = null;
        if (function_exists('sys_getloadavg')) {
            $avg = @sys_getloadavg();
            if (is_array($avg) && isset($avg[0])) {
                $load = (float) $avg[0];
                $meta['load_1m'] = $avg[0];
                $meta['load_5m'] = $avg[1] ?? null;
                $meta['load_15m'] = $avg[2] ?? null;
            }
        }

        // Memory (Linux best effort)
        $mem = $this->readLinuxMeminfo();
        if ($mem) {
            $meta = array_merge($meta, $mem);
        }

        $ms = (microtime(true) - $start) * 1000;

        $status = 'ok';
        $message = 'System metrics ok';

        // Very rough thresholds
        if ($load !== null) {
            $cores = $this->cpuCores();
            $meta['cpu_cores'] = $cores;
            if ($cores && $load > ($cores * 2)) {
                $status = 'warn';
                $message = 'High CPU load';
            }
        }

        if (isset($meta['mem_used_percent']) && $meta['mem_used_percent'] !== null) {
            $p = (float) $meta['mem_used_percent'];
            if ($p > 90) {
                $status = 'fail';
                $message = 'Memory critical';
            } elseif ($p > 80 && $status !== 'fail') {
                $status = 'warn';
                $message = 'Memory high';
            }
        }

        if (empty($meta)) {
            return [
                'status' => 'skip',
                'message' => 'System metrics not available',
                'meta' => [],
                'time_ms' => round($ms, 2),
            ];
        }

        return [
            'status' => $status,
            'message' => $message,
            'meta' => $meta,
            'time_ms' => round($ms, 2),
        ];
    }

    private function cpuCores(): ?int
    {
        // Linux
        $n = @trim((string) @shell_exec('nproc 2>/dev/null'));
        if (is_numeric($n) && (int) $n > 0) {
            return (int) $n;
        }

        // Fallback
        return null;
    }

    private function readLinuxMeminfo(): ?array
    {
        $path = '/proc/meminfo';
        if (!is_readable($path)) {
            return null;
        }

        $contents = @file_get_contents($path);
        if (!$contents) {
            return null;
        }

        $totalKb = null;
        $availableKb = null;

        foreach (explode("\n", $contents) as $line) {
            if (str_starts_with($line, 'MemTotal:')) {
                $totalKb = (int) preg_replace('/[^0-9]/', '', $line);
            }
            if (str_starts_with($line, 'MemAvailable:')) {
                $availableKb = (int) preg_replace('/[^0-9]/', '', $line);
            }
        }

        if (!$totalKb || !$availableKb) {
            return null;
        }

        $usedKb = $totalKb - $availableKb;
        $usedPercent = $totalKb > 0 ? round(($usedKb / $totalKb) * 100, 2) : null;

        return [
            'mem_total_kb' => $totalKb,
            'mem_available_kb' => $availableKb,
            'mem_used_kb' => $usedKb,
            'mem_used_percent' => $usedPercent,
        ];
    }
}
