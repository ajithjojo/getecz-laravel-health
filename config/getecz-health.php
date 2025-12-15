<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Enable / Disable
    |--------------------------------------------------------------------------
    |
    | You can fully disable all endpoints in production by setting:
    | GETECZ_HEALTH_ENABLED=false
    */
    'enabled' => (bool) env('GETECZ_HEALTH_ENABLED', true),

    /*
    | Route prefix
    */
    'route_prefix' => env('GETECZ_HEALTH_ROUTE_PREFIX', 'getecz-health'),

    /*
    | Middleware
    |
    | Suggested: ['web', '\\Getecz\\LaravelHealth\\Http\\Middleware\\AuthorizeHealth::class']
    */
    'middleware' => ['web', \Getecz\LaravelHealth\Http\Middleware\AuthorizeHealth::class],

    /*
    | Access token (optional)
    |
    | If set, requests must include ?token=... or header X-Getecz-Health-Token
    */
    'token' => env('GETECZ_HEALTH_TOKEN', null),

    /*
    | Allowed IPs (optional)
    */
    'allowed_ips' => array_filter(array_map('trim', explode(',', (string) env('GETECZ_HEALTH_ALLOWED_IPS', '')))),

    /*
    | Auto refresh seconds (dashboard)
    */
    'refresh_seconds' => (int) env('GETECZ_HEALTH_REFRESH_SECONDS', 10),

    /*
    | Store snapshots in DB (requires publishing and running migrations)
    */
    'store_history' => (bool) env('GETECZ_HEALTH_STORE_HISTORY', false),

    /*
    | History retention in days (only used by the snapshot command)
    */
    'history_retention_days' => (int) env('GETECZ_HEALTH_HISTORY_RETENTION_DAYS', 14),

    /*
    | Include system metrics (CPU load / memory) when available.
    | On shared hosting this may be limited.
    */
    'include_system_metrics' => (bool) env('GETECZ_HEALTH_INCLUDE_SYSTEM_METRICS', true),

    /*
    | Queue name for backlog check (best effort)
    */
    'queue_name' => env('GETECZ_HEALTH_QUEUE_NAME', 'default'),

    /*
    | Cron heartbeat stale threshold in minutes
    */
    'cron_max_minutes' => (int) env('GETECZ_HEALTH_CRON_MAX_MINUTES', 10),

    /*
    | Which checks to run (you can disable checks by removing them)
    */
    'checks' => [
        \Getecz\LaravelHealth\Checks\DbLatencyCheck::class,
        \Getecz\LaravelHealth\Checks\CacheLatencyCheck::class,
        \Getecz\LaravelHealth\Checks\StorageCheck::class,
        \Getecz\LaravelHealth\Checks\QueueBacklogCheck::class,
        \Getecz\LaravelHealth\Checks\CronHeartbeatCheck::class,
        \Getecz\LaravelHealth\Checks\SystemMetricsCheck::class,
    ],
];
