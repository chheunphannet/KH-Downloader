<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Tests\TestCase;

class MetricsRouteTest extends TestCase
{
    use RefreshDatabase;

    public function test_metrics_requires_admin_token(): void
    {
        config(['services.admin_api.token' => 'test-token']);

        $this->getJson('/api/metrics')->assertForbidden();
        $this->withHeader('Authorization', 'Bearer wrong-token')
            ->getJson('/api/metrics')
            ->assertForbidden();
    }

    public function test_metrics_returns_data_with_valid_admin_token(): void
    {
        config(['services.admin_api.token' => 'test-token']);

        Redis::shouldReceive('get')
            ->once()
            ->with('active_downloads_count')
            ->andReturn('2');

        DB::table('site_metrics')->insert([
            [
                'site_name' => 'khfullhd',
                'total_processes' => 3,
                'updated_at' => now(),
            ],
            [
                'site_name' => 'khanime',
                'total_processes' => 5,
                'updated_at' => now(),
            ],
        ]);

        DB::table('user_logs')->insert([
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Feature test',
            'site_requested' => 'khfullhd',
            'page_url' => 'https://example.com/video',
            'created_at' => now(),
        ]);

        $this->withHeader('Authorization', 'Bearer test-token')
            ->getJson('/api/metrics')
            ->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('summary.current_server_load', '2 / 5')
            ->assertJsonPath('summary.grand_total_processed', 8)
            ->assertJsonPath('by_site.0.site_name', 'khfullhd')
            ->assertJsonPath('by_site.0.total_requests', 3)
            ->assertJsonPath('recent_activity.0.site', 'khfullhd');
    }
}
