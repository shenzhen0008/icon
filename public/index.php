<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

$__entryProbeStartedAt = LARAVEL_START;
$__entryProbeLastMark = $__entryProbeStartedAt;
$__entryProbeStages = [];
$__entryProbeLogged = false;
$__entryProbeMark = static function (string $name) use (&$__entryProbeStages, &$__entryProbeLastMark): void {
    $now = microtime(true);
    $__entryProbeStages[$name.'_ms'] = round(($now - $__entryProbeLastMark) * 1000, 2);
    $__entryProbeLastMark = $now;
};

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

$__entryProbeMark('autoload');

$__entryProbeShouldLog = static function (): bool {
    $value = $_ENV['PERFORMANCE_PROBE_ENABLED']
        ?? $_SERVER['PERFORMANCE_PROBE_ENABLED']
        ?? getenv('PERFORMANCE_PROBE_ENABLED');

    return filter_var($value, FILTER_VALIDATE_BOOLEAN);
};

$__entryProbeLog = static function () use (
    &$__entryProbeLogged,
    $__entryProbeShouldLog,
    $__entryProbeStartedAt,
    &$__entryProbeStages
): void {
    if ($__entryProbeLogged || ! $__entryProbeShouldLog()) {
        return;
    }

    $__entryProbeLogged = true;
    $logDirectory = __DIR__.'/../storage/logs';

    if (! is_dir($logDirectory) || ! is_writable($logDirectory)) {
        return;
    }

    $entry = [
        'timestamp' => date(DATE_ATOM),
        'method' => $_SERVER['REQUEST_METHOD'] ?? null,
        'uri' => $_SERVER['REQUEST_URI'] ?? null,
        'total_ms' => round((microtime(true) - $__entryProbeStartedAt) * 1000, 2),
        'stages' => $__entryProbeStages,
        'memory_peak_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
    ];

    file_put_contents(
        $logDirectory.'/php-entry-performance.log',
        json_encode($entry, JSON_UNESCAPED_SLASHES).PHP_EOL,
        FILE_APPEND | LOCK_EX
    );
};

register_shutdown_function($__entryProbeLog);

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';
$__entryProbeMark('bootstrap');

$request = Request::capture();
$__entryProbeMark('request_capture');

$app->handleRequest($request);
$__entryProbeMark('handle_request');
$__entryProbeLog();
