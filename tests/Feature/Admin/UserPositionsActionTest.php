<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Modules\Position\Models\Position;
use App\Modules\Product\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;

class UserPositionsActionTest extends AdminPanelTestCase
{
    use RefreshDatabase;

    /**
     * 成功路径：管理员在后台用户管理列表可以看到“购买记录”按钮，并且弹窗视图能正确展示用户购买的产品详情。
     */
    public function test_admin_can_see_view_positions_action_and_render_user_purchased_products(): void
    {
        $user = User::factory()->create([
            'username' => 'buyer_test',
        ]);

        $product = Product::query()->create([
            'name' => 'High Yield Strategy A',
            'code' => 'HYSA',
            'unit_price' => 500,
            'is_active' => true,
        ]);

        $position = Position::query()->create([
            'order_no' => '987654',
            'user_id' => $user->id,
            'product_id' => $product->id,
            'principal' => 1500.00,
            'status' => 'open',
            'opened_at' => now()->subDay(),
        ]);

        // 验证用户管理页面中存在“购买记录”按钮
        $this->get('/admin/users')
            ->assertOk()
            ->assertSee('购买记录')
            ->assertSee('buyer_test');

        // 验证弹窗视图渲染正确包含订单号、产品名、金额和状态
        $view = $this->view('filament.users.positions-modal', [
            'positions' => collect([$position]),
        ]);

        $view->assertSee('987654');
        $view->assertSee('High Yield Strategy A');
        $view->assertSee('1,500.00');
        $view->assertSee('USDT');
        $view->assertSee('持有中');
    }

    /**
     * 权限拒绝路径：非管理员无法访问后台或没有后台访问权限。
     */
    public function test_non_admin_cannot_access_admin_panel(): void
    {
        $normalUser = User::factory()->create([
            'is_admin' => false,
        ]);

        $this->actingAs($normalUser);

        $this->assertFalse(Gate::forUser($normalUser)->allows('access-admin'));
    }

    /**
     * 边界/空数据/容错路径：用户无购买记录或产品信息缺失时的容错展示。
     */
    public function test_renders_empty_state_and_handles_missing_product_gracefully(): void
    {
        // 1. 无记录时展示空提示
        $emptyView = $this->view('filament.users.positions-modal', [
            'positions' => collect(),
        ]);
        $emptyView->assertSee('暂无产品购买记录');

        // 2. 产品信息缺失时的容错（如 null）
        $positionWithoutProduct = new Position([
            'order_no' => '111222',
            'principal' => 200.00,
            'status' => 'closed',
            'opened_at' => now(),
            'closed_at' => now(),
        ]);
        $positionWithoutProduct->setRelation('product', null);

        $viewWithMissingProduct = $this->view('filament.users.positions-modal', [
            'positions' => collect([$positionWithoutProduct]),
        ]);

        $viewWithMissingProduct->assertSee('111222');
        $viewWithMissingProduct->assertSee('--');
        $viewWithMissingProduct->assertSee('已结清');
    }
}
