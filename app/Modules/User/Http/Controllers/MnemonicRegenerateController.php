<?php

namespace App\Modules\User\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\User\Services\MnemonicAuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MnemonicRegenerateController extends Controller
{
    public function __construct(private readonly MnemonicAuthService $mnemonicAuthService)
    {
    }

    public function __invoke(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user === null) {
            return redirect('/login');
        }

        $mnemonicPhrase = $this->mnemonicAuthService->generateAndAssignToUser($user);

        return redirect('/me/mnemonic')->with('generated_mnemonic_phrase', $mnemonicPhrase);
    }
}
