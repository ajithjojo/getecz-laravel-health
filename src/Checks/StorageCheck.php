<?php

namespace Getecz\LaravelHealth\Checks;

use Illuminate\Support\Facades\Storage;

class StorageCheck implements CheckInterface
{
    public static function key(): string { return 'storage'; }
    public static function label(): string { return 'Storage'; }

    public function run(): array
    {
        $disk = config('filesystems.default');
        $path = 'getecz-health/.write-test-' . bin2hex(random_bytes(6)) . '.txt';
        $start = microtime(true);

        try {
            $ok = Storage::disk($disk)->put($path, 'ok');
            $exists = Storage::disk($disk)->exists($path);
            Storage::disk($disk)->delete($path);

            $ms = (microtime(true) - $start) * 1000;

            $status = ($ok && $exists) ? 'ok' : 'fail';

            $free = null;
            try {
                // Best-effort free disk space for local disks
                if ($disk === 'local') {
                    $localPath = storage_path('app');
                    $free = @disk_free_space($localPath);
                }
            } catch (\Throwable $e) {
                // ignore
            }

            return [
                'status' => $status,
                'message' => $status === 'ok' ? 'Storage writable' : 'Storage not writable',
                'meta' => [
                    'disk' => $disk,
                    'free_bytes' => $free,
                ],
                'time_ms' => round($ms, 2),
            ];
        } catch (\Throwable $e) {
            $ms = (microtime(true) - $start) * 1000;
            return [
                'status' => 'fail',
                'message' => 'Storage error: ' . $e->getMessage(),
                'meta' => ['disk' => $disk],
                'time_ms' => round($ms, 2),
            ];
        }
    }
}
