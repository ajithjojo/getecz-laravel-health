<?php

use Getecz\LaravelHealth\Http\Controllers\HealthController;
use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => config('getecz-health.route_prefix', 'getecz-health'),
    'middleware' => config('getecz-health.middleware', ['web']),
], function () {
    Route::get('/', [HealthController::class, 'dashboard'])->name('getecz-health.dashboard');
    Route::get('/json', [HealthController::class, 'json'])->name('getecz-health.json');
    Route::get("/widget", [HealthController::class, "widget"])->name("getecz-health.widget");
    Route::get("/heartbeat", [HealthController::class, "heartbeat"])->name("getecz-health.heartbeat");
});
