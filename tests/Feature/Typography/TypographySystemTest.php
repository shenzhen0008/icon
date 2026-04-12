<?php

namespace Tests\Feature\Typography;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TypographySystemTest extends TestCase
{
    use RefreshDatabase;

    public function test_global_typography_utilities_are_defined_and_used_on_public_pages(): void
    {
        $css = file_get_contents(resource_path('css/app.css'));

        $this->assertIsString($css);
        $this->assertStringContainsString('.text-scale-ui', $css);
        $this->assertStringContainsString('.text-scale-title', $css);
        $this->assertStringContainsString('.text-scale-micro', $css);
        $this->assertStringContainsString('.text-scale-body', $css);
        $this->assertStringContainsString('.text-scale-display', $css);

        $this->get('/')
            ->assertOk()
            ->assertSee('text-scale-ui', false)
            ->assertSee('text-scale-display', false);

        $this->get('/support')
            ->assertOk()
            ->assertSee('text-scale-micro', false);

        $this->get('/me')
            ->assertOk()
            ->assertSee('text-scale-ui', false)
            ->assertSee('text-scale-body', false);
    }

    public function test_desktop_typography_scales_are_capped_for_large_screens(): void
    {
        $css = file_get_contents(resource_path('css/app.css'));

        $this->assertIsString($css);
        $this->assertStringContainsString('@media (min-width: 1024px)', $css);
        $this->assertStringContainsString('--font-scale-micro: 0.72rem;', $css);
        $this->assertStringContainsString('--font-scale-body: 0.86rem;', $css);
        $this->assertStringContainsString('--font-scale-ui: 0.88rem;', $css);
        $this->assertStringContainsString('--font-scale-title: 0.98rem;', $css);
        $this->assertStringContainsString('--font-scale-display: 1.2rem;', $css);

        $fluidScalePosition = strpos($css, '--font-scale-display: clamp(1.45rem, 5.4vw, 2.2rem);');
        $desktopCapPosition = strrpos($css, '@media (min-width: 1024px)');

        $this->assertIsInt($fluidScalePosition);
        $this->assertIsInt($desktopCapPosition);
        $this->assertGreaterThan($fluidScalePosition, $desktopCapPosition);
    }
}
