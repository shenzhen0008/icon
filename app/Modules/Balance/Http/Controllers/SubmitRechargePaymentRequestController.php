<?php

namespace App\Modules\Balance\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Balance\Http\Requests\StoreRechargePaymentRequest;
use App\Modules\Balance\Models\RechargePaymentRequest;
use App\Modules\Balance\Models\RechargeReceiver;
use Illuminate\Http\RedirectResponse;

class SubmitRechargePaymentRequestController extends Controller
{
    public function __invoke(StoreRechargePaymentRequest $request): RedirectResponse
    {
        $user = $request->user();
        if ($user === null) {
            abort(401);
        }

        $assetCode = (string) $request->validated('asset_code');
        $allowedAssetCodes = (array) config('recharge.allowed_receive_assets', []);
        $receiver = RechargeReceiver::query()
            ->where('asset_code', $assetCode)
            ->where('is_active', true)
            ->whereIn('asset_code', $allowedAssetCodes)
            ->first();

        if ($receiver === null) {
            return redirect('/recharge')->withErrors([
                'asset_code' => '所选币种已停用，请重新选择。',
            ])->withInput();
        }

        $receiptImagePath = $request->file('receipt_image')?->store('recharge-receipts', 'public');
        if ($receiptImagePath === null) {
            return redirect('/recharge')->withErrors([
                'receipt_image' => '付款截图上传失败，请重试。',
            ])->withInput();
        }

        RechargePaymentRequest::query()->create([
            'user_id' => $user->id,
            'contact_account' => (string) $user->username,
            'asset_code' => $assetCode,
            'payment_amount' => (string) $request->validated('payment_amount'),
            'currency' => (string) $receiver->asset_code,
            'network' => (string) $receiver->network,
            'receipt_address' => (string) $receiver->address,
            'receipt_image_path' => $receiptImagePath,
            'status' => 'pending',
            'user_note' => $request->validated('user_note'),
            'submitted_at' => now(),
        ]);

        return redirect('/recharge')->with('success', '充值申请已提交，管理员核实后会手动入账。');
    }
}
