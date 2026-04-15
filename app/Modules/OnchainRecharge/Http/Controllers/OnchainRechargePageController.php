<?php

namespace App\Modules\OnchainRecharge\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Balance\Models\RechargeReceiver;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class OnchainRechargePageController extends Controller
{
    public function __invoke(Request $request): View
    {
        $assets = RechargeReceiver::query()
            ->where('is_active', true)
            ->orderBy('sort')
            ->orderBy('id')
            ->get()
            ->mapWithKeys(fn (RechargeReceiver $receiver): array => [
                $receiver->asset_code => [
                    'code' => $receiver->asset_code,
                    'name' => $receiver->asset_name,
                    'network' => $receiver->network,
                    'address' => $receiver->address,
                ],
            ])
            ->all();

        $assetCodes = array_keys($assets);

        return view('onchain-recharge.index', [
            'assets' => $assets,
            'defaultAssetCode' => old('asset_code', $assetCodes[0] ?? null),
            'defaultChainId' => (string) ($request->query('chain_id') ?? config('web3.payment.chain_id', config('web3.default_chain_id', '56'))),
            'defaultFromAddress' => (string) ($request->query('from_address') ?? ''),
            'paymentConfig' => [
                'chain_id' => (string) config('web3.payment.chain_id', '56'),
                'token_address' => (string) config('web3.payment.token_address', ''),
            ],
        ]);
    }
}
