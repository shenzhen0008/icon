<?php

use App\Modules\User\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Modules\User\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Modules\User\Http\Controllers\Auth\MnemonicLoginController;
use App\Modules\User\Http\Controllers\Auth\RegisteredUserController;
use App\Modules\Help\Http\Controllers\HelpPageController;
use App\Modules\Product\Http\Controllers\ProductDetailController;
use App\Modules\Product\Http\Controllers\PublicProductCatalogController;
use App\Modules\Product\Http\Controllers\ProductRulesPageController;
use App\Modules\User\Http\Controllers\HomeController;
use App\Modules\User\Http\Controllers\MyCenterController;
use App\Modules\Position\Http\Controllers\PositionOrderPageController;
use App\Modules\Position\Http\Controllers\PositionOrdersPageController;
use App\Modules\Position\Http\Controllers\PurchasePositionController;
use App\Modules\Reservation\Http\Controllers\CancelProductReservationController;
use App\Modules\Reservation\Http\Controllers\SubmitProductReservationController;
use App\Modules\Redemption\Http\Controllers\SubmitPositionRedemptionRequestController;
use App\Modules\Referral\Http\Controllers\ReferralDashboardController;
use App\Modules\Support\Http\Controllers\SupportPageController;
use App\Modules\Support\Http\Controllers\StreamChatAgentPageController;
use App\Modules\Support\Http\Controllers\StreamChatAgentTokenController;
use App\Modules\Support\Http\Controllers\StreamChatGuestTokenController;
use App\Modules\Support\Http\Controllers\StreamChatNotifyTokenController;
use App\Modules\Balance\Http\Controllers\RechargePageController;
use App\Modules\Balance\Http\Controllers\RechargeEntryController;
use App\Modules\Balance\Http\Controllers\SubmitRechargePaymentRequestController;
use App\Modules\BankRecharge\Http\Controllers\BankRechargeEntryController;
use App\Modules\BankRecharge\Http\Controllers\BankRechargePageController;
use App\Modules\BankRecharge\Http\Controllers\SubmitBankRechargeRequestController;
use App\Modules\Withdrawal\Http\Controllers\SubmitWithdrawalRequestController;
use App\Modules\Home\Http\Controllers\HomeSummaryFeedController;
use App\Modules\Home\Http\Controllers\HomeHeroPanelFeedController;
use App\Modules\Home\Http\Controllers\HeroPanelTradeRecordsPageController;
use App\Modules\Home\Http\Controllers\HeroPanelIncomeRecordsPageController;
use App\Modules\OnchainRecharge\Http\Controllers\OnchainRechargePageController;
use App\Modules\OnchainRecharge\Http\Controllers\ReportWalletClientEventController;
use App\Modules\OnchainRecharge\Http\Controllers\SubmitOnchainRechargeRequestController;
use App\Modules\OnchainRecharge\Http\Controllers\AutoSubmitOnchainRechargeRequestController;
use App\Modules\PopupPush\Http\Controllers\MarkPopupConfirmedController;
use App\Modules\PopupPush\Http\Controllers\MarkPopupDismissedController;
use App\Modules\PopupPush\Http\Controllers\MarkPopupShownController;
use App\Modules\Support\Http\Controllers\StreamChatPageController;
use App\Modules\User\Http\Controllers\SensitivePageController;
use App\Modules\User\Http\Controllers\MnemonicPageController;
use App\Modules\User\Http\Controllers\MnemonicRegenerateController;
use App\Modules\ClientEnv\Http\Controllers\CollectClientEnvController;
use App\Modules\ClientEnv\Http\Controllers\DetectClientEnvController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class);
Route::get('/home-summary', HomeSummaryFeedController::class);
Route::get('/home-hero-panel', HomeHeroPanelFeedController::class);
Route::get('/help', HelpPageController::class);
Route::get('/products', PublicProductCatalogController::class);
Route::get('/products/rules', ProductRulesPageController::class);
Route::get('/products/{product}', ProductDetailController::class);
Route::get('/recharge', RechargePageController::class);
Route::get('/recharge/entry', RechargeEntryController::class);
Route::get('/recharge/onchain', OnchainRechargePageController::class);
Route::post('/recharge/onchain/client-events', ReportWalletClientEventController::class)->middleware('throttle:60,1');
Route::get('/me', MyCenterController::class);
Route::get('/referral', ReferralDashboardController::class);
Route::get('/support', SupportPageController::class);
Route::get('/stream-chat', StreamChatPageController::class);
Route::post('/stream-chat/guest-token', StreamChatGuestTokenController::class);
Route::get('/stream-chat/notify-token', StreamChatNotifyTokenController::class);
Route::get('/dev/client-env/detect', DetectClientEnvController::class);
Route::match(['get', 'post'], '/dev/client-env/collect', CollectClientEnvController::class);

Route::middleware('guest')->group(function (): void {
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store'])->middleware('throttle:register-pin');

    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
    Route::post('/login/mnemonic', MnemonicLoginController::class);
});

Route::middleware('auth')->group(function (): void {
    Route::get('/home/hero-panel/trade-records', HeroPanelTradeRecordsPageController::class);
    Route::get('/home/hero-panel/income-records', HeroPanelIncomeRecordsPageController::class);
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::get('/me/mnemonic', MnemonicPageController::class);
    Route::post('/me/mnemonic/regenerate', MnemonicRegenerateController::class);
    Route::post('/recharge/requests', SubmitRechargePaymentRequestController::class);
    Route::get('/recharge/bank/entry', BankRechargeEntryController::class);
    Route::get('/recharge/bank', BankRechargePageController::class);
    Route::post('/recharge/bank/requests', SubmitBankRechargeRequestController::class);
    Route::post('/withdrawal-requests', SubmitWithdrawalRequestController::class);
    Route::post('/recharge/onchain/requests', SubmitOnchainRechargeRequestController::class);
    Route::post('/recharge/onchain/requests/auto', AutoSubmitOnchainRechargeRequestController::class);
    Route::post('/positions/purchase', PurchasePositionController::class);
    Route::post('/products/{product}/reservations', SubmitProductReservationController::class);
    Route::post('/me/reservations/{reservation}/cancel', CancelProductReservationController::class);
    Route::get('/me/orders', PositionOrdersPageController::class);
    Route::get('/me/positions/{position}', PositionOrderPageController::class);
    Route::post('/me/positions/{position}/redemption-requests', SubmitPositionRedemptionRequestController::class);
    Route::middleware('can:access-admin')->group(function (): void {
        Route::get('/stream-chat-agent', StreamChatAgentPageController::class);
        Route::post('/stream-chat-agent/token', StreamChatAgentTokenController::class);
    });
    Route::post('/popup/{campaign}/shown', MarkPopupShownController::class);
    Route::post('/popup/{campaign}/dismiss', MarkPopupDismissedController::class);
    Route::post('/popup/{campaign}/confirm', MarkPopupConfirmedController::class);

    Route::get('/confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');
    Route::post('/confirm-password', [ConfirmablePasswordController::class, 'store']);

    Route::get('/sensitive', SensitivePageController::class)->middleware('password.confirm');
});
