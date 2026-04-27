<?php

namespace App\Modules\Legal\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Legal\Services\LoadLegalDocumentService;
use Illuminate\Contracts\View\View;

class PrivacyPolicyPageController extends Controller
{
    public function __construct(private readonly LoadLegalDocumentService $loadLegalDocumentService)
    {
    }

    public function __invoke(): View
    {
        $payload = $this->loadLegalDocumentService->handle('privacy', app()->getLocale());

        return view('legal.privacy-preview', [
            'document_title' => (string) __('pages/legal.privacy_title'),
            'document_html' => $payload['html'],
            'document_updated_at' => $payload['updated_at'],
            'document_resolved_locale' => $payload['resolved_locale'],
        ]);
    }
}
