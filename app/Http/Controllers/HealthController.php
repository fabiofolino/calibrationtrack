<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

class HealthController extends Controller
{
    /**
     * System health check endpoint
     * 
     * @return JsonResponse
     */
    public function check(): JsonResponse
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'queue' => $this->checkQueue(),
            'storage' => $this->checkStorage(),
            'disk_space' => $this->checkDiskSpace(),
        ];

        $healthy = collect($checks)->every(fn($check) => $check['status'] === 'ok');

        return response()->json([
            'status' => $healthy ? 'healthy' : 'unhealthy',
            'timestamp' => now()->toISOString(),
            'checks' => $checks,
        ], $healthy ? 200 : 503);
    }

    /**
     * Check database connectivity
     */
    private function checkDatabase(): array
    {
        try {
            DB::connection()->getPdo();
            
            // Run a simple query
            $result = DB::select('SELECT 1 as test');
            
            return [
                'status' => 'ok',
                'message' => 'Database connection successful',
                'details' => [
                    'driver' => config('database.default'),
                    'test_query' => !empty($result),
                ]
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Database connection failed',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check queue system
     */
    private function checkQueue(): array
    {
        try {
            // Check if we can connect to the queue
            $connection = Queue::connection();
            
            // For database queue, check if jobs table exists
            if (config('queue.default') === 'database') {
                $jobsTableExists = DB::getSchemaBuilder()->hasTable('jobs');
                $failedJobsTableExists = DB::getSchemaBuilder()->hasTable('failed_jobs');
                
                return [
                    'status' => $jobsTableExists && $failedJobsTableExists ? 'ok' : 'warning',
                    'message' => $jobsTableExists && $failedJobsTableExists ? 'Queue system operational' : 'Queue tables missing',
                    'details' => [
                        'driver' => config('queue.default'),
                        'jobs_table' => $jobsTableExists,
                        'failed_jobs_table' => $failedJobsTableExists,
                    ]
                ];
            }
            
            return [
                'status' => 'ok',
                'message' => 'Queue system operational',
                'details' => [
                    'driver' => config('queue.default'),
                ]
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Queue system error',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check storage system
     */
    private function checkStorage(): array
    {
        try {
            // Test writing and reading from storage
            $testFile = 'health-check-' . time() . '.txt';
            $testContent = 'Health check test at ' . now();
            
            Storage::disk('public')->put($testFile, $testContent);
            $retrieved = Storage::disk('public')->get($testFile);
            Storage::disk('public')->delete($testFile);
            
            $canWrite = $retrieved === $testContent;
            
            return [
                'status' => $canWrite ? 'ok' : 'error',
                'message' => $canWrite ? 'Storage system operational' : 'Storage write/read failed',
                'details' => [
                    'disk' => 'public',
                    'write_test' => $canWrite,
                ]
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Storage system error',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check disk space
     */
    private function checkDiskSpace(): array
    {
        try {
            $storagePath = storage_path();
            $totalSpace = disk_total_space($storagePath);
            $freeSpace = disk_free_space($storagePath);
            $usedSpace = $totalSpace - $freeSpace;
            $usagePercentage = ($usedSpace / $totalSpace) * 100;
            
            // Consider it healthy if less than 90% used
            $healthy = $usagePercentage < 90;
            
            return [
                'status' => $healthy ? 'ok' : 'warning',
                'message' => $healthy ? 'Disk space sufficient' : 'Disk space running low',
                'details' => [
                    'total_space' => $this->formatBytes($totalSpace),
                    'free_space' => $this->formatBytes($freeSpace),
                    'used_space' => $this->formatBytes($usedSpace),
                    'usage_percentage' => round($usagePercentage, 2) . '%',
                ]
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Could not check disk space',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
