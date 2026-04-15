<?php

namespace App\Modules\Home\Services;

use Carbon\CarbonInterface;
use Carbon\CarbonImmutable;
use InvalidArgumentException;

class DynamicDisplayValueService
{
    public function shouldTick(?CarbonInterface $lastTickAt, int $stepSeconds, ?CarbonInterface $now = null): bool
    {
        if ($stepSeconds <= 0) {
            throw new InvalidArgumentException('stepSeconds must be greater than 0.');
        }

        if ($lastTickAt === null) {
            return true;
        }

        $currentTime = $now ?? now();

        return $lastTickAt->diffInSeconds($currentTime) >= $stepSeconds;
    }

    public function nextValue(
        float $currentValue,
        float $minDelta,
        float $maxDelta,
        ?float $minValue = null,
        ?float $maxValue = null,
        int $precision = 2,
        ?callable $randomizer = null,
    ): float {
        if ($minDelta > $maxDelta) {
            throw new InvalidArgumentException('minDelta must be less than or equal to maxDelta.');
        }

        if ($precision < 0) {
            throw new InvalidArgumentException('precision must be greater than or equal to 0.');
        }

        $delta = $randomizer !== null
            ? (float) $randomizer($minDelta, $maxDelta)
            : $this->randomFloat($minDelta, $maxDelta, $precision);

        $nextValue = round($currentValue + $delta, $precision);

        if ($minValue !== null) {
            $nextValue = max($minValue, $nextValue);
        }

        if ($maxValue !== null) {
            $nextValue = min($maxValue, $nextValue);
        }

        return round($nextValue, $precision);
    }

    public function format(float|int|string $value, string $formatType): string
    {
        $numeric = (float) preg_replace('/[^0-9.\-]/', '', (string) $value);

        return match ($formatType) {
            'integer' => number_format((int) $numeric, 0, '.', ','),
            'money_2' => number_format($numeric, 2, '.', ','),
            default => throw new InvalidArgumentException("Unsupported format type [{$formatType}]."),
        };
    }

    public function tickValue(
        float $currentValue,
        ?CarbonInterface $lastTickAt,
        int $stepSeconds,
        float $minDelta,
        float $maxDelta,
        int $precision = 2,
        ?CarbonInterface $now = null,
    ): array {
        $currentTime = $now ?? now();

        if (! $this->shouldTick($lastTickAt, $stepSeconds, $currentTime)) {
            return [
                'current_value' => round($currentValue, $precision),
                'last_tick_at' => $lastTickAt?->toImmutable() ?? CarbonImmutable::instance($currentTime),
                'did_tick' => false,
            ];
        }

        return [
            'current_value' => $this->nextValue(
                currentValue: $currentValue,
                minDelta: $minDelta,
                maxDelta: $maxDelta,
                precision: $precision,
            ),
            'last_tick_at' => CarbonImmutable::instance($currentTime),
            'did_tick' => true,
        ];
    }

    private function randomFloat(float $min, float $max, int $precision): float
    {
        $multiplier = 10 ** $precision;

        return random_int((int) round($min * $multiplier), (int) round($max * $multiplier)) / $multiplier;
    }
}
