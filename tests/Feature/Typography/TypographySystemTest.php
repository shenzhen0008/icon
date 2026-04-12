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
}
