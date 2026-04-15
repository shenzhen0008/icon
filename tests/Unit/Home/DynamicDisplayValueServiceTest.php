<?php

namespace Tests\Unit\Home;

use App\Modules\Home\Services\DynamicDisplayValueService;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\TestCase;

class DynamicDisplayValueServiceTest extends TestCase
{
    public function test_should_tick_returns_false_when_step_seconds_has_not_elapsed(): void
    {
        $service = new DynamicDisplayValueService();
        $now = CarbonImmutable::parse('2026-04-16 12:00:02');
        $lastTickAt = CarbonImmutable::parse('2026-04-16 12:00:00');

        $this->assertFalse($service->shouldTick($lastTickAt, 3, $now));
    }

    public function test_should_tick_returns_true_when_step_seconds_has_elapsed(): void
    {
        $service = new DynamicDisplayValueService();
        $now = CarbonImmutable::parse('2026-04-16 12:00:03');
        $lastTickAt = CarbonImmutable::parse('2026-04-16 12:00:00');

        $this->assertTrue($service->shouldTick($lastTickAt, 3, $now));
    }

    public function test_next_value_advances_from_current_value_not_base_value(): void
    {
        $service = new DynamicDisplayValueService();

        $nextValue = $service->nextValue(
            currentValue: 105,
            minDelta: -2,
            maxDelta: 5,
            precision: 0,
            randomizer: static fn (float $min, float $max): float => 3
        );

        $this->assertSame(108.0, $nextValue);
    }

    public function test_next_value_respects_min_and_max_bounds(): void
    {
        $service = new DynamicDisplayValueService();

        $cappedToMax = $service->nextValue(
            currentValue: 108,
            minDelta: -2,
            maxDelta: 5,
            minValue: 100,
            maxValue: 110,
            precision: 0,
            randomizer: static fn (float $min, float $max): float => 5
        );

        $cappedToMin = $service->nextValue(
            currentValue: 101,
            minDelta: -5,
            maxDelta: 2,
            minValue: 100,
            maxValue: 110,
            precision: 0,
            randomizer: static fn (float $min, float $max): float => -5
        );

        $this->assertSame(110.0, $cappedToMax);
        $this->assertSame(100.0, $cappedToMin);
    }

    public function test_format_integer_uses_grouping_without_decimals(): void
    {
        $service = new DynamicDisplayValueService();

        $this->assertSame('88,888', $service->format(88888.75, 'integer'));
    }

    public function test_format_money_2_uses_grouping_with_two_decimals(): void
    {
        $service = new DynamicDisplayValueService();

        $this->assertSame('9,999,999.00', $service->format(9999999, 'money_2'));
    }

    public function test_tick_value_updates_when_step_seconds_has_elapsed(): void
    {
        $service = new DynamicDisplayValueService();
        $now = CarbonImmutable::parse('2026-04-16 12:00:03');
        $lastTickAt = CarbonImmutable::parse('2026-04-16 12:00:00');

        $result = $service->tickValue(
            currentValue: 100,
            lastTickAt: $lastTickAt,
            stepSeconds: 3,
            minDelta: 5,
            maxDelta: 5,
            precision: 0,
            now: $now,
        );

        $this->assertSame(105.0, $result['current_value']);
        $this->assertSame('2026-04-16 12:00:03', $result['last_tick_at']->toDateTimeString());
        $this->assertTrue($result['did_tick']);
    }

    public function test_tick_value_keeps_current_value_when_step_seconds_has_not_elapsed(): void
    {
        $service = new DynamicDisplayValueService();
        $now = CarbonImmutable::parse('2026-04-16 12:00:02');
        $lastTickAt = CarbonImmutable::parse('2026-04-16 12:00:00');

        $result = $service->tickValue(
            currentValue: 100,
            lastTickAt: $lastTickAt,
            stepSeconds: 3,
            minDelta: 5,
            maxDelta: 5,
            precision: 0,
            now: $now,
        );

        $this->assertSame(100.0, $result['current_value']);
        $this->assertSame('2026-04-16 12:00:00', $result['last_tick_at']->toDateTimeString());
        $this->assertFalse($result['did_tick']);
    }
}
