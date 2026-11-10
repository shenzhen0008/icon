<?php

namespace Tests\Feature\Admin;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Models\User;
use App\Modules\Balance\Models\BalanceLedger;
use App\Modules\Position\Models\Position;
use App\Modules\Product\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

class UserManagementPageTest extends AdminPanelTestCase
{
    use RefreshDatabase;

    public function test_existing_username_cannot_be_changed_by_admin_form(): void
    {
        $user = User::factory()->create(['username' => 'StableCustomer123']);

        Livewire::test(EditUser::class, ['record' => $user->getRouteKey()])
            ->assertFormFieldIsDisabled('username')
            ->fillForm(['username' => 'ChangedCustomer123', 'remark' => 'Updated remark'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('StableCustomer123', $user->fresh()->username);
        $this->assertSame('Updated remark', $user->fresh()->remark);
    }

    public function test_new_user_username_remains_editable_and_can_be_created(): void
    {
        Livewire::test(CreateUser::class)
            ->assertFormFieldIsEnabled('username')
            ->fillForm(['username' => 'NewCustomer123', 'balance' => 0, 'password' => 'password123'])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('users', ['username' => 'NewCustomer123']);
    }

    public function test_guest_can_access_user_management_pages_in_local_environment(): void
    {
        $user = User::factory()->create();

        $this->get('/admin')
            ->assertOk()
            ->assertSee('产品管理')
            ->assertSee('用户管理')
            ->assertSee('客服工作台')
            ->assertSee('/stream-chat-agent', false);
        $this->get('/admin/users')->assertOk();
        $this->get('/admin/users')
            ->assertOk()
            ->assertSee('限购覆盖');
        $this->get('/admin/users/create')
            ->assertOk()
            ->assertSee('备注')
            ->assertDontSee('昵称')
            ->assertDontSee('邮箱')
            ->assertDontSee('邮箱验证时间');
        $this->get('/admin/users/'.$user->id.'/edit')
            ->assertOk()
            ->assertSee('备注')
            ->assertDontSee('昵称')
            ->assertDontSee('邮箱')
            ->assertDontSee('邮箱验证时间');
    }

    public function test_deleting_user_cascades_related_records(): void
    {
        $user = User::factory()->create([
            'balance' => 5000,
        ]);

        $product = Product::query()->create([
            'name' => 'Mobile AMM',
            'code' => 'MAMM',
            'unit_price' => 1000,
            'is_active' => true,
        ]);

        $position = Position::query()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'principal' => 1000,
            'status' => 'open',
            'opened_at' => now(),
        ]);

        BalanceLedger::query()->create([
            'user_id' => $user->id,
            'type' => 'purchase_debit',
            'amount' => -1000,
            'before_balance' => 5000,
            'after_balance' => 4000,
            'biz_ref_type' => 'position',
            'biz_ref_id' => (string) $position->id,
            'occurred_at' => now(),
        ]);

        $user->delete();

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        $this->assertDatabaseMissing('positions', ['id' => $position->id]);
        $this->assertDatabaseCount('balance_ledgers', 0);
    }

    public function test_user_remark_can_be_persisted_via_mass_assignment(): void
    {
        $user = User::factory()->create([
            'remark' => null,
        ]);

        $user->update([
            'remark' => '需要人工跟进',
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'remark' => '需要人工跟进',
        ]);
    }
}
