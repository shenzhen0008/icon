<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Tests\TestCase;

abstract class AdminPanelTestCase extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create([
            'is_admin' => true,
        ]));
    }
}

