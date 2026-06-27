<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Database\Connection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class PerformanceProbeMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->shouldProfile($request)) {
            return $next($request);
        }

        $startedAt = microtime(true);
        $connection = $this->startQueryLog();
        $status = null;
        $exception = null;

        try {
            $response = $next($request);
            $status = $response->getStatusCode();

            return $response;
        } catch (Throwable $throwable) {
            $exception = $throwable::class;

            throw $throwable;
        } finally {
            $queries = $connection instanceof Connection ? $connection->getQueryLog() : [];

            if ($connection instanceof Connection) {
                $connection->disableQueryLog();
            }

            $this->logRequest($request, $queries, $startedAt, $status, $exception);
        }
    }

    private function shouldProfile(Request $request): bool
    {
        if (! (bool) config('performance.enabled', false)) {
            return false;
        }

        $path = ltrim($request->path(), '/');

        foreach ((array) config('performance.excluded_paths', []) as $pattern) {
            if (Str::is((string) $pattern, $path)) {
                return false;
            }
        }

        return true;
    }

    private function startQueryLog(): ?Connection
    {
        try {
            $connection = DB::connection();
            $connection->flushQueryLog();
            $connection->enableQueryLog();

            return $connection;
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * @param  array<int, array{query?: string, bindings?: array<int, mixed>, time?: float}>  $queries
     */
    private function logRequest(Request $request, array $queries, float $startedAt, ?int $status, ?string $exception): void
    {
        $durationMs = round((microtime(true) - $startedAt) * 1000, 2);
        $dbTimeMs = round(array_sum(array_map(
            fn (array $query): float => (float) ($query['time'] ?? 0),
            $queries
        )), 2);

        Log::channel((string) config('performance.log_channel', 'performance'))->info('performance.request', [
            'method' => $request->method(),
            'path' => $this->displayPath($request),
            'route_name' => $request->route()?->getName(),
            'route_action' => $request->route()?->getActionName(),
            'status' => $status,
            'exception' => $exception,
            'duration_ms' => $durationMs,
            'db_query_count' => count($queries),
            'db_time_ms' => $dbTimeMs,
            'memory_peak_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
            'slow_queries' => $this->slowQueries($queries),
        ]);
    }

    private function displayPath(Request $request): string
    {
        $path = ltrim($request->path(), '/');

        return $path === '' ? '/' : '/'.$path;
    }

    /**
     * @param  array<int, array{query?: string, bindings?: array<int, mixed>, time?: float}>  $queries
     * @return array<int, array{time_ms: float, sql: string|null, bindings_count: int}>
     */
    private function slowQueries(array $queries): array
    {
        $thresholdMs = (float) config('performance.slow_query_threshold_ms', 50);
        $maxQueries = max(0, (int) config('performance.max_logged_queries', 5));

        $slowQueries = array_filter(
            array_map(function (array $query): array {
                return [
                    'time_ms' => round((float) ($query['time'] ?? 0), 2),
                    'sql' => isset($query['query']) ? (string) $query['query'] : null,
                    'bindings_count' => count((array) ($query['bindings'] ?? [])),
                ];
            }, $queries),
            fn (array $query): bool => $query['time_ms'] >= $thresholdMs
        );

        usort($slowQueries, fn (array $left, array $right): int => $right['time_ms'] <=> $left['time_ms']);

        return array_slice(array_values($slowQueries), 0, $maxQueries);
    }
}
