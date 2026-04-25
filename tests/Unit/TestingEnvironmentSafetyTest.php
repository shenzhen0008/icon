<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class TestingEnvironmentSafetyTest extends TestCase
{
    public function test_phpunit_forces_testing_database_configuration(): void
    {
        $this->assertSame('testing', getenv('APP_ENV'));
        $this->assertSame('icon_market_test', getenv('DB_DATABASE'));
        $this->assertSame('/tmp/icon-market-testing-config.php', getenv('APP_CONFIG_CACHE'));
    }
}

