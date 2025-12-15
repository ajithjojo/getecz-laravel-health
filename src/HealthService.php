<?php

namespace Getecz\LaravelHealth;

use Getecz\LaravelHealth\Checks\CheckInterface;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Arr;

class HealthService
{
    public function __construct(protected Application $app)
    {
    }

    public function run(): array
    {
        $results = [];
        $checks = config('getecz-health.checks', []);

        foreach ($checks as $checkClass) {
            try {
                if (!class_exists($checkClass)) {
                    continue;
                }

                $check = $this->app->make($checkClass);
                if (!$check instanceof CheckInterface) {
                    continue;
                }

                $payload = $check->run();
                $results[$checkClass::key()] = array_merge([
                    'label' => $checkClass::label(),
                ], $payload);
            } catch (\Throwable $e) {
                $results[$checkClass::key()] = [
                    'label' => method_exists($checkClass, 'label') ? $checkClass::label() : class_basename($checkClass),
                    'status' => 'fail',
                    'message' => $e->getMessage(),
                    'meta' => ['exception' => get_class($e)],
                    'time_ms' => null,
                ];
            }
        }

        $summary = [
            'ok' => count(array_filter($results, fn($r) => ($r['status'] ?? null) === 'ok')),
            'warn' => count(array_filter($results, fn($r) => ($r['status'] ?? null) === 'warn')),
            'fail' => count(array_filter($results, fn($r) => ($r['status'] ?? null) === 'fail')),
            'skip' => count(array_filter($results, fn($r) => ($r['status'] ?? null) === 'skip')),
        ];

        $overall = $summary['fail'] > 0 ? 'fail' : ($summary['warn'] > 0 ? 'warn' : 'ok');

        return [
            'overall' => $overall,
            'summary' => $summary,
            'checks' => $results,
            'app' => [
                'name' => config('app.name'),
                'env' => config('app.env'),
                'debug' => (bool) config('app.debug'),
                'url' => config('app.url'),
                'php' => PHP_VERSION,
                'laravel' => $this->app->version(),
            ],
            'generated_at' => now()->toIso8601String(),
        ];
    }
}
