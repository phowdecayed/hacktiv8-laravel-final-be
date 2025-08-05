<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class HealthCheckController extends Controller
{
    public function check()
    {
        $health = [
            'status' => 'healthy',
            'timestamp' => now()->toISOString(),
            'checks' => [],
        ];

        // Cek koneksi database
        try {
            DB::connection()->getPdo();
            $health['checks']['database'] = [
                'status' => 'healthy',
                'message' => 'Database connection successful',
            ];
        } catch (Exception $e) {
            $health['status'] = 'unhealthy';
            $health['checks']['database'] = [
                'status' => 'unhealthy',
                'message' => 'Database connection failed: '.$e->getMessage(),
            ];
        }

        // Cek cache (Redis/Memcached/File)
        try {
            Cache::put('health_check', 'test', 1); // Simpan selama 1 menit
            $cached = Cache::get('health_check');
            if ($cached === 'test') {
                $health['checks']['cache'] = [
                    'status' => 'healthy',
                    'message' => 'Cache system working',
                ];
            } else {
                throw new Exception('Cache get/set failed');
            }
        } catch (Exception $e) {
            $health['status'] = 'unhealthy';
            $health['checks']['cache'] = [
                'status' => 'unhealthy',
                'message' => 'Cache system failed: '.$e->getMessage(),
            ];
        }

        // Cek disk space (minimal 100MB)
        $diskFree = disk_free_space(storage_path());
        $diskTotal = disk_total_space(storage_path());
        $diskUsagePercent = (($diskTotal - $diskFree) / $diskTotal) * 100;

        if ($diskFree > 100 * 1024 * 1024) { // 100MB
            $health['checks']['disk'] = [
                'status' => 'healthy',
                'message' => 'Disk space OK',
                'usage_percent' => round($diskUsagePercent, 2).'%',
                'free' => round($diskFree / 1024 / 1024 / 1024, 2).' GB',
            ];
        } else {
            $health['status'] = 'warning';
            $health['checks']['disk'] = [
                'status' => 'warning',
                'message' => 'Low disk space',
                'usage_percent' => round($diskUsagePercent, 2).'%',
                'free' => round($diskFree / 1024 / 1024 / 1024, 2).' GB',
            ];
        }

        // Cek PHP memory limit
        $memoryLimit = ini_get('memory_limit');
        $memoryUsage = memory_get_usage(true);
        $memoryLimitBytes = $this->return_bytes($memoryLimit);
        $memoryUsagePercent = ($memoryUsage / $memoryLimitBytes) * 100;

        $health['checks']['memory'] = [
            'status' => 'healthy',
            'message' => 'Memory usage OK',
            'usage_percent' => round($memoryUsagePercent, 2).'%',
            'limit' => $memoryLimit,
            'current' => round($memoryUsage / 1024 / 1024, 2).' MB',
        ];

        // Cek PHP version
        $health['checks']['php'] = [
            'status' => 'healthy',
            'message' => 'PHP version: '.PHP_VERSION,
            'version' => PHP_VERSION,
        ];

        // Cek Laravel version
        $health['checks']['laravel'] = [
            'status' => 'healthy',
            'message' => 'Laravel version: '.app()->version(),
            'version' => app()->version(),
        ];

        return response()->json($health);
    }

    // Helper function untuk konversi memory limit ke bytes
    private function return_bytes($val)
    {
        $val = trim($val);
        $last = strtolower($val[strlen($val) - 1]);
        $val = (int) $val;
        switch ($last) {
            case 'g':
                $val *= 1024;
            case 'm':
                $val *= 1024;
            case 'k':
                $val *= 1024;
        }

        return $val;
    }
}
