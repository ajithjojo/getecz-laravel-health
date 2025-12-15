<?php

namespace Getecz\LaravelHealth\Checks;

interface CheckInterface
{
    /**
     * A stable slug used as array key.
     */
    public static function key(): string;

    /**
     * Human readable label.
     */
    public static function label(): string;

    /**
     * Run check.
     *
     * Return shape:
     *  [
     *    'status' => 'ok'|'warn'|'fail'|'skip',
     *    'message' => string,
     *    'meta' => array,
     *    'time_ms' => float|null,
     *  ]
     */
    public function run(): array;
}
