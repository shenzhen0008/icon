<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class TestingEnvironmentSafetyTest extends TestCase
{
    public function test_phpunit_forces_testing_database_configuration(): void
    {
        $this->assertSame('testing', getenv('APP_ENV'));
        $this->assertSame('icon_market_test', getenv('DB_DATABASE'));

        $configCachePath = (string) getenv('APP_CONFIG_CACHE');

        $this->assertNotSame('', $configCachePath);
        $this->assertStringContainsString('testing', basename($configCachePath));
        $this->assertTrue(is_writable(dirname($configCachePath)));
    }
}
