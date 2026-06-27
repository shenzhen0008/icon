<?php

namespace Tests\Feature\Performance;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class PerformanceProbeMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    private string $performanceLogPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->performanceLogPath = storage_path('logs/performance.log');
        @unlink($this->performanceLogPath);
    }

    public function test_performance_probe_is_disabled_by_default_in_testing(): void
    {
        Route::middleware('web')->get('/probe-disabled-test', fn () => response('ok'));

        $this->get('/probe-disabled-test')->assertOk();

        $this->assertFileDoesNotExist($this->performanceLogPath);
    }

    public function test_enabled_performance_probe_logs_request_and_query_summary(): void
    {
        config([
            'performance.enabled' => true,
            'performance.max_logged_queries' => 3,
            'performance.slow_query_threshold_ms' => 0,
        ]);

        User::factory()->create();

        Route::middleware('web')->get('/probe-enabled-test', function () {
            User::query()->count();

            return response('ok');
        });

        $this->get('/probe-enabled-test')->assertOk();

        $this->assertFileExists($this->performanceLogPath);

        $log = file_get_contents($this->performanceLogPath);

        $this->assertIsString($log);
        $this->assertStringContainsString('performance.request', $log);
        $this->assertStringContainsString('/probe-enabled-test', $log);
        $this->assertStringContainsString('db_query_count', $log);
        $this->assertStringContainsString('slow_queries', $log);
    }
}
