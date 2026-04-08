<?php

namespace App\Modules\Balance\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Balance\Models\RechargeReceiver;
use App\Modules\User\Services\TemporaryAccountService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RechargePageController extends Controller
{
    public function __construct(private readonly TemporaryAccountService $temporaryAccountService)
    {
    }

    public function __invoke(Request $request): View
    {
        $isGuest = ! Auth::guard('web')->check();
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
        $defaultAssetCode = old('asset_code', $assetCodes[0] ?? null);

        if ($isGuest) {
            $this->temporaryAccountService->ensureGuestTempUsername($request);
        }

        return view('recharge.index', [
            'isGuest' => $isGuest,
            'assets' => $assets,
            'defaultAssetCode' => $defaultAssetCode,
        ]);
    }
}
