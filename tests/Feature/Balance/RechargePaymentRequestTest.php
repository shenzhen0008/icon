<?php

namespace Tests\Feature\Balance;

use App\Models\User;
use App\Modules\Balance\Models\RechargeReceiver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RechargePaymentRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_recharge_page_prompts_guest_to_activate_account_before_submission(): void
    {
        $this->createReceiver('USDT', 'TRC20', 'T-usdt');
        $this->createReceiver('USDC', 'TRC20', 'T-usdc');
        $this->createReceiver('BTC', 'Bitcoin', 'bc1-btc');
        $this->createReceiver('ETH', 'Ethereum', '0x-eth');
        $this->createReceiver('DOGE', 'Dogecoin', 'D-doge');

        $this->get('/recharge')
            ->assertOk()
            ->assertSee('RECEIVE充值')
            ->assertSee('SEND提款')
            ->assertSee('CONVERT兑换')
            ->assertSee('data-fund-mode-button="receive"', false)
            ->assertSee('data-fund-mode-button="send"', false)
            ->assertSee('data-fund-mode-button="convert"', false)
            ->assertSee('data-fund-mode-panel="receive"', false)
            ->assertSee('data-fund-mode-panel="send"', false)
            ->assertSee('data-fund-mode-panel="convert"', false)
            ->assertSee('USDT')
            ->assertSee('USDC')
            ->assertSee('BTC')
            ->assertSee('ETH')
            ->assertDontSee('DOGE')
            ->assertSee('设置密码并注册')
            ->assertSee('你当前是访客态，设置密码后即可将临时账号升级为正式账号。');
    }

    public function test_recharge_page_renders_same_compact_home_hero_panel_as_my_center(): void
    {
        $this->createReceiver('USDT', 'TRC20', 'T-usdt');

        $this->get('/recharge')
            ->assertOk()
            ->assertSee('id="home-data-panel"', false)
            ->assertSee('id="hero-mode-badge"', false)
            ->assertSee('id="hero-damo-btn"', false)
            ->assertSee('id="hero-live-btn"', false)
            ->assertDontSee('Welcome to AI Smart Contracts')
            ->assertDontSee('Artificial intelligence trading');
    }

    public function test_authenticated_user_can_submit_recharge_payment_request(): void
    {
        Storage::fake('public');

        $this->createReceiver('ETH', 'Ethereum', '0x-eth');

        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/recharge/requests', [
            'asset_code' => 'ETH',
            'payment_amount' => '100.50',
            'receipt_image' => UploadedFile::fake()->image('receipt.png'),
            'user_note' => 'night transfer',
        ]);

        $response
            ->assertRedirect('/recharge')
            ->assertSessionHas('success', '充值申请已提交，管理员核实后会手动入账。');

        $this->assertDatabaseHas('recharge_payment_requests', [
            'user_id' => $user->id,
            'contact_account' => $user->username,
            'asset_code' => 'ETH',
            'payment_amount' => '100.50',
            'currency' => 'ETH',
            'network' => 'Ethereum',
            'receipt_address' => '0x-eth',
            'status' => 'pending',
        ]);

        $record = \DB::table('recharge_payment_requests')->where('user_id', $user->id)->first();
        $this->assertNotNull($record);
        Storage::disk('public')->assertExists($record->receipt_image_path);
    }

    public function test_guest_cannot_submit_recharge_payment_request(): void
    {
        Storage::fake('public');
        $this->createReceiver('USDT', 'TRC20', 'T-usdt');

        $this->post('/recharge/requests', [
            'asset_code' => 'USDT',
            'payment_amount' => '12.3',
            'receipt_image' => UploadedFile::fake()->image('receipt.png'),
        ])->assertRedirect('/login');
    }

    public function test_submission_rejects_invalid_input(): void
    {
        Storage::fake('public');
        $this->createReceiver('USDT', 'TRC20', 'T-usdt');

        $user = User::factory()->create();

        $this->actingAs($user)
            ->from('/recharge')
            ->post('/recharge/requests', [
                'asset_code' => 'FAKE',
                'payment_amount' => '0',
            ])
            ->assertRedirect('/recharge')
            ->assertSessionHasErrors(['asset_code', 'payment_amount', 'receipt_image']);
    }

    public function test_inactive_receiver_is_hidden_on_recharge_page_and_cannot_be_submitted(): void
    {
        Storage::fake('public');

        $this->createReceiver('USDT', 'TRC20', 'T-usdt', isActive: true);
        $this->createReceiver('DOGE', 'Dogecoin', 'D-doge', isActive: false);

        $this->get('/recharge')
            ->assertOk()
            ->assertSee('USDT')
            ->assertDontSee('DOGE');

        $user = User::factory()->create();

        $this->actingAs($user)
            ->from('/recharge')
            ->post('/recharge/requests', [
                'asset_code' => 'DOGE',
                'payment_amount' => '10',
                'receipt_image' => UploadedFile::fake()->image('receipt.png'),
            ])
            ->assertRedirect('/recharge')
            ->assertSessionHasErrors(['asset_code']);
    }

    public function test_active_receiver_outside_allowed_receive_assets_is_hidden_and_cannot_be_submitted(): void
    {
        Storage::fake('public');

        $this->createReceiver('USDT', 'TRC20', 'T-usdt', isActive: true);
        $this->createReceiver('DOGE', 'Dogecoin', 'D-doge', isActive: true);

        $this->get('/recharge')
            ->assertOk()
            ->assertSee('USDT')
            ->assertDontSee('DOGE');

        $user = User::factory()->create();

        $this->actingAs($user)
            ->from('/recharge')
            ->post('/recharge/requests', [
                'asset_code' => 'DOGE',
                'payment_amount' => '10',
                'receipt_image' => UploadedFile::fake()->image('receipt.png'),
            ])
            ->assertRedirect('/recharge')
            ->assertSessionHasErrors(['asset_code']);
    }

    private function createReceiver(string $assetCode, string $network, string $address, bool $isActive = true): RechargeReceiver
    {
        return RechargeReceiver::query()->updateOrCreate([
            'asset_code' => $assetCode,
        ], [
            'asset_name' => $assetCode,
            'network' => $network,
            'address' => $address,
            'is_active' => $isActive,
            'sort' => 0,
        ]);
    }
}
