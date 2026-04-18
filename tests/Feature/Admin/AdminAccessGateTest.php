<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessGateTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_when_accessing_admin_panel(): void
    {
        $this->get('/admin')
            ->assertRedirect('/admin/login');
    }

    public function test_admin_login_page_uses_username_field_instead_of_email(): void
    {
        $this->get('/admin/login')
            ->assertOk()
            ->assertSee('data.username', false)
            ->assertDontSee('data.email', false);
    }

    public function test_authenticated_non_admin_user_is_redirected_to_admin_login(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/admin')
            ->assertRedirect('/admin/login');
    }

    public function test_authenticated_admin_user_can_access_admin_panel(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $this->actingAs($admin)
            ->get('/admin')
            ->assertOk();
    }
}
