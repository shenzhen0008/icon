<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        self::assertSafeTestingEnvironment();

        parent::setUp();
    }

    private static function assertSafeTestingEnvironment(): void
    {
        $appEnv = (string) (getenv('APP_ENV') ?: '');

        if ($appEnv !== 'testing') {
            throw new RuntimeException('Refuse to run tests when APP_ENV is not testing.');
        }

        $databaseName = (string) (getenv('DB_DATABASE') ?: '');

        if (! self::isSafeTestingDatabaseName($databaseName)) {
            throw new RuntimeException(sprintf(
                'Refuse to run tests with unsafe DB_DATABASE "%s".',
                $databaseName
            ));
        }

        $activeConfigCachePath = (string) (
            getenv('APP_CONFIG_CACHE')
            ?: dirname(__DIR__).'/bootstrap/cache/config.php'
        );

        if (! is_file($activeConfigCachePath)) {
            return;
        }

        $cachedConfig = require $activeConfigCachePath;

        if (! is_array($cachedConfig)) {
            throw new RuntimeException('Invalid cached config format in APP_CONFIG_CACHE.');
        }

        $defaultConnection = $cachedConfig['database']['default'] ?? null;
        $cachedDatabase = is_string($defaultConnection)
            ? ($cachedConfig['database']['connections'][$defaultConnection]['database'] ?? null)
            : null;

        if (is_string($cachedDatabase) && ! self::isSafeTestingDatabaseName($cachedDatabase)) {
            throw new RuntimeException(sprintf(
                'Refuse to run tests with unsafe cached database "%s" from "%s".',
                $cachedDatabase,
                $activeConfigCachePath
            ));
        }
    }

    private static function isSafeTestingDatabaseName(string $databaseName): bool
    {
        if ($databaseName === ':memory:') {
            return true;
        }

        return str_ends_with($databaseName, '_test')
            || str_ends_with($databaseName, '_testing');
    }
}
