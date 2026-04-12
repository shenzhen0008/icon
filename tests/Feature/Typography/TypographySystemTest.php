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
        $this->assertStringContainsString('.text-fluid-nav', $css);
        $this->assertStringContainsString('.text-fluid-action', $css);
        $this->assertStringContainsString('.text-fluid-2xs', $css);

        $this->get('/')
            ->assertOk()
            ->assertSee('text-fluid-brand', false)
            ->assertSee('text-fluid-nav', false);

        $this->get('/support')
            ->assertOk()
            ->assertSee('text-fluid-2xs', false);

        $this->get('/me')
            ->assertOk()
            ->assertSee('text-fluid-action', false);
    }
}
