<?php

namespace Getecz\LaravelHealth;

use Getecz\LaravelHealth\Console\HealthSnapshotCommand;
use Getecz\LaravelHealth\Console\HealthHeartbeatCommand;
use Illuminate\Support\ServiceProvider;

class LaravelHealthServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/getecz-health.php', 'getecz-health');

        $this->app->singleton(HealthService::class, function ($app) {
            return new HealthService($app);
        });
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/getecz-health.php');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'getecz-health');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/getecz-health.php' => config_path('getecz-health.php'),
            ], 'getecz-health-config');

            $this->publishes([
                __DIR__ . '/../resources/views' => resource_path('views/vendor/getecz-health'),
            ], 'getecz-health-views');

            $this->publishes([
                __DIR__ . '/../database/migrations' => database_path('migrations'),
            ], 'getecz-health-migrations');

            $this->commands([
                HealthSnapshotCommand::class,
                HealthHeartbeatCommand::class,
            ]);
        }
    }
}
