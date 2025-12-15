<?php

namespace Getecz\LaravelHealth\Http\Controllers;

use Getecz\LaravelHealth\HealthService;
use Getecz\LaravelHealth\Models\HealthSnapshot;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;

class HealthController extends Controller
{
    public function __construct(protected HealthService $health)
    {
    }

    public function dashboard(Request $request)
    {
        $data = $this->health->run();
        $this->maybeStoreSnapshot($data);

        return view('getecz-health::dashboard', [
            'data' => $data,
            'refreshSeconds' => (int) config('getecz-health.refresh_seconds', 10),
            'routePrefix' => config('getecz-health.route_prefix', 'getecz-health'),
            'token' => config('getecz-health.token'),
        ]);
    }

    public function json(Request $request)
    {
        $data = $this->health->run();
        $this->maybeStoreSnapshot($data);
        return response()->json($data);
    }

    public function widget(Request $request)
    {
        $data = $this->health->run();
        return view('getecz-health::widget', [
            'data' => $data,
            'routePrefix' => config('getecz-health.route_prefix', 'getecz-health'),
            'token' => config('getecz-health.token'),
        ]);
    }

    public function heartbeat(Request $request)
    {
        Cache::put('getecz_health_heartbeat', now()->toIso8601String(), 60 * 24);
        return response()->json(['ok' => true, 'heartbeat' => now()->toIso8601String()]);
    }

    protected function maybeStoreSnapshot(array $data): void
    {
        if (!config('getecz-health.store_history', false)) {
            return;
        }

        // If migration not present, fail silently (do not break dashboard)
        try {
            if (class_exists(HealthSnapshot::class)) {
                HealthSnapshot::create([
                    'overall' => $data['overall'] ?? null,
                    'payload' => $data,
                ]);
            }
        } catch (\Throwable $e) {
            // ignore
        }
    }
}
