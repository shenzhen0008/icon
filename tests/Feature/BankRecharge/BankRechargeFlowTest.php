<?php

namespace Tests\Feature\BankRecharge;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BankRechargeFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_bank_entry_redirects_to_login(): void
    {
        $this->get('/recharge/bank/entry')->assertRedirect('/login');
    }

    public function test_bank_entry_redirects_to_bank_page_for_authenticated_user(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/recharge/bank/entry')
            ->assertRedirect('/recharge/bank');
    }

    public function test_authenticated_user_can_submit_bank_recharge_request(): void
    {
        Storage::fake('public');
        config()->set('recharge.bank_receivers', [
            'test_a' => [
                'code' => 'A',
                'enabled' => true,
                'bank_name' => 'ABC',
                'account_name' => 'ICON MARKET',
                'card_number' => '6225880011223344',
                'branch_name' => 'Main',
                'sort' => 10,
            ],
        ]);

        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/recharge/bank/requests', [
                'receiver_key' => 'test_a',
                'payment_amount' => '188.88',
                'receipt_image' => UploadedFile::fake()->image('bank.png'),
                'user_note' => 'mobile transfer',
            ])
            ->assertRedirect('/recharge/bank?mode=receive')
            ->assertSessionHas('success', '充值申请已提交，管理员核实后会手动入账。');

        $this->assertDatabaseHas('recharge_payment_requests', [
            'user_id' => $user->id,
            'channel' => 'bank_card_manual',
            'asset_code' => 'BANK',
            'currency' => 'CNY',
            'network' => 'BANK_TRANSFER',
            'payment_amount' => '188.88',
            'receipt_address' => '6225880011223344',
        ]);
    }

    public function test_bank_withdrawal_requires_bank_card_fields_for_bank_card_network(): void
    {
        $user = User::factory()->create([
            'balance' => '500.00',
        ]);

        $this->actingAs($user)
            ->from('/recharge/bank?mode=send')
            ->post('/withdrawal-requests', [
                'asset_code' => 'BANK',
                'network' => 'BANK_CARD',
                'amount' => '100.00',
            ])
            ->assertRedirect('/recharge/bank?mode=send')
            ->assertSessionHasErrors([
                'bank_name',
                'account_name',
                'card_number',
            ]);

        $this->assertDatabaseCount('withdrawal_requests', 0);
    }

    public function test_authenticated_user_can_submit_bank_withdrawal_via_shared_endpoint(): void
    {
        $user = User::factory()->create([
            'balance' => '500.00',
        ]);

        $this->actingAs($user)
            ->from('/recharge/bank?mode=send')
            ->post('/withdrawal-requests', [
                'asset_code' => 'BANK',
                'network' => 'BANK_CARD',
                'bank_name' => 'China Bank',
                'account_name' => 'Zhang San',
                'card_number' => '6222021234567890123',
                'branch_name' => 'Nanshan Branch',
                'reserved_phone' => '13800138000',
                'bank_note' => 'urgent',
                'amount' => '100.00',
            ])
            ->assertRedirect('/recharge/bank?mode=send')
            ->assertSessionHas('success');

        $this->assertDatabaseHas('withdrawal_requests', [
            'user_id' => $user->id,
            'asset_code' => 'BANK',
            'network' => 'BANK_CARD',
            'destination_address' => '6222021234567890123',
            'amount' => '100.00',
            'status' => 'pending',
        ]);

        $row = \DB::table('withdrawal_requests')->where('user_id', $user->id)->first();
        $this->assertNotNull($row);
        $this->assertIsString($row->meta_json);

        $meta = json_decode($row->meta_json, true);
        $this->assertIsArray($meta);
        $this->assertSame('China Bank', $meta['bank_name'] ?? null);
        $this->assertSame('Zhang San', $meta['account_name'] ?? null);
        $this->assertSame('6222021234567890123', $meta['card_number'] ?? null);
        $this->assertSame('bank_recharge_page', $meta['submitted_from'] ?? null);
    }
}
