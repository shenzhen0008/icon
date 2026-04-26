<?php

namespace App\Modules\Legal\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Legal\Services\LoadLegalDocumentService;
use Illuminate\Contracts\View\View;

class TermsOfServicePageController extends Controller
{
    public function __construct(private readonly LoadLegalDocumentService $loadLegalDocumentService)
    {
    }

    public function __invoke(): View
    {
        $payload = $this->loadLegalDocumentService->handle('terms', app()->getLocale());

        return view('legal.document', [
            'document_type' => 'terms',
            'document_title' => (string) __('pages/legal.terms_title'),
            'document_html' => $payload['html'],
            'document_updated_at' => $payload['updated_at'],
            'document_resolved_locale' => $payload['resolved_locale'],
        ]);
    }
}
