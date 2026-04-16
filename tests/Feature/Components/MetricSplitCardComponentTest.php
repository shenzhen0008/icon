<?php

namespace Tests\Feature\Components;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class MetricSplitCardComponentTest extends TestCase
{
    public function test_component_renders_two_sections_by_default(): void
    {
        $html = Blade::render(<<<'BLADE'
<x-ui.metric-split-card>
    <x-slot:left>
        <p>LEFT VALUE</p>
    </x-slot:left>
    <x-slot:right>
        <p>RIGHT VALUE</p>
    </x-slot:right>
</x-ui.metric-split-card>
BLADE);

        $this->assertStringContainsString('LEFT VALUE', $html);
        $this->assertStringContainsString('RIGHT VALUE', $html);
        $this->assertStringContainsString('grid grid-cols-2 gap-3', $html);
        $this->assertStringContainsString('border-l border-theme', $html);
    }

    public function test_component_can_hide_right_section_with_prop(): void
    {
        $html = Blade::render(<<<'BLADE'
<x-ui.metric-split-card :show-right="false">
    <x-slot:left>
        <p>LEFT ONLY</p>
    </x-slot:left>
    <x-slot:right>
        <p>RIGHT HIDDEN</p>
    </x-slot:right>
</x-ui.metric-split-card>
BLADE);

        $this->assertStringContainsString('LEFT ONLY', $html);
        $this->assertStringNotContainsString('RIGHT HIDDEN', $html);
        $this->assertStringContainsString('grid grid-cols-1 gap-3', $html);
        $this->assertStringNotContainsString('border-l border-theme', $html);
    }

    public function test_component_can_render_top_slot_above_grid(): void
    {
        $html = Blade::render(<<<'BLADE'
<x-ui.metric-split-card>
    <x-slot:top>
        <p>AVAILABLE BALANCE</p>
    </x-slot:top>
    <x-slot:left>
        <p>LEFT</p>
    </x-slot:left>
    <x-slot:right>
        <p>RIGHT</p>
    </x-slot:right>
</x-ui.metric-split-card>
BLADE);

        $this->assertStringContainsString('AVAILABLE BALANCE', $html);
        $this->assertStringContainsString('mb-3 border-b border-theme pb-3', $html);
        $this->assertTrue(
            strpos($html, 'AVAILABLE BALANCE') < strpos($html, 'grid grid-cols-2 gap-3'),
            'Top slot should render before metric grid.'
        );
    }
}
