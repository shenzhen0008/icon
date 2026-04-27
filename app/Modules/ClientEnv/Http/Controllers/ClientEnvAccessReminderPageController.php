<?php

namespace App\Modules\ClientEnv\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\ClientEnv\Models\ClientEnvDecisionSetting;
use App\Modules\ClientEnv\Services\ClientEnvDecisionService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Throwable;

class ClientEnvAccessReminderPageController extends Controller
{
    public function __invoke(Request $request, ClientEnvDecisionService $decisionService): View|RedirectResponse
    {
        if (! $this->isDecisionFeatureEnabled()) {
            return redirect()->to(url('/'));
        }

        $decision = $decisionService->decide([
            'user_agent' => (string) $request->userAgent(),
        ]);

        if ((string) ($decision['decision'] ?? '') === 'allow') {
            return redirect()->to(url('/'));
        }

        return view('client-env.access-reminder', [
            'homeUrl' => url('/'),
        ]);
    }

    private function isDecisionFeatureEnabled(): bool
    {
        if (! (bool) config('client_env.decision.enabled', true)) {
            return false;
        }

        try {
            if (! Schema::hasTable('client_env_decision_settings')) {
                return true;
            }

            $setting = ClientEnvDecisionSetting::query()->find(1);
            if ($setting === null) {
                $setting = ClientEnvDecisionSetting::query()->orderByDesc('id')->first();
            }
            if ($setting === null) {
                return true;
            }

            return (bool) $setting->is_enabled;
        } catch (Throwable) {
            return true;
        }
    }
}
