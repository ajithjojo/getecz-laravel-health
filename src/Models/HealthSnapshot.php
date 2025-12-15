<?php

namespace Getecz\LaravelHealth\Models;

use Illuminate\Database\Eloquent\Model;

class HealthSnapshot extends Model
{
    protected $table = 'getecz_health_snapshots';

    protected $fillable = [
        'overall',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
    ];
}
