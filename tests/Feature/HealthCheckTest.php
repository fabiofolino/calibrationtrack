<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HealthCheckTest extends TestCase
{
    use RefreshDatabase;

    public function test_health_check_endpoint_is_accessible(): void
    {
        $response = $this->get('/healthz');

        $response->assertOk();
        $response->assertJsonStructure([
            'status',
            'timestamp',
            'checks' => [
                'database' => ['status', 'message'],
                'queue' => ['status', 'message'],
                'storage' => ['status', 'message'],
                'disk_space' => ['status', 'message'],
            ]
        ]);
    }

    public function test_health_check_returns_healthy_status_when_all_systems_ok(): void
    {
        Storage::fake('public');

        $response = $this->get('/healthz');

        $response->assertOk();
        $data = $response->json();
        
        $this->assertEquals('healthy', $data['status']);
        $this->assertEquals('ok', $data['checks']['database']['status']);
        $this->assertEquals('ok', $data['checks']['storage']['status']);
    }

    public function test_health_check_includes_database_details(): void
    {
        $response = $this->get('/healthz');

        $response->assertOk();
        $data = $response->json();
        
        $this->assertArrayHasKey('details', $data['checks']['database']);
        $this->assertArrayHasKey('driver', $data['checks']['database']['details']);
        $this->assertEquals('sqlite', $data['checks']['database']['details']['driver']);
    }

    public function test_health_check_includes_disk_space_details(): void
    {
        $response = $this->get('/healthz');

        $response->assertOk();
        $data = $response->json();
        
        $this->assertArrayHasKey('details', $data['checks']['disk_space']);
        $this->assertArrayHasKey('total_space', $data['checks']['disk_space']['details']);
        $this->assertArrayHasKey('free_space', $data['checks']['disk_space']['details']);
        $this->assertArrayHasKey('usage_percentage', $data['checks']['disk_space']['details']);
    }

    public function test_health_check_returns_503_when_unhealthy(): void
    {
        // Mock a database connection failure
        DB::shouldReceive('connection->getPdo')
            ->once()
            ->andThrow(new \Exception('Connection failed'));

        $response = $this->get('/healthz');

        $response->assertStatus(503);
        $data = $response->json();
        
        $this->assertEquals('unhealthy', $data['status']);
        $this->assertEquals('error', $data['checks']['database']['status']);
    }

    public function test_health_check_handles_storage_errors(): void
    {
        Storage::shouldReceive('disk')
            ->with('public')
            ->andThrow(new \Exception('Storage error'));

        $response = $this->get('/healthz');

        $data = $response->json();
        $this->assertEquals('error', $data['checks']['storage']['status']);
        $this->assertStringContainsString('Storage system error', $data['checks']['storage']['message']);
    }

    public function test_health_check_includes_timestamp(): void
    {
        $before = now();
        
        $response = $this->get('/healthz');
        
        $after = now();
        $data = $response->json();
        
        $timestamp = \Carbon\Carbon::parse($data['timestamp']);
        $this->assertTrue($timestamp->between($before, $after));
    }
}
