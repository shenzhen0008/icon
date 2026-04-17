<?php

namespace App\Modules\Help\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Help\Services\LocalizedHelpFaqService;
use Illuminate\Contracts\View\View;

class HelpPageController extends Controller
{
    public function __construct(private readonly LocalizedHelpFaqService $localizedHelpFaqService)
    {
    }

    public function __invoke(): View
    {
        $faqs = $this->localizedHelpFaqService->listFaqs();

        if ($faqs === []) {
            /** @var array<int, array{question: string, answer: string}> $fallbackFaqs */
            $fallbackFaqs = config('help.faqs', []);
            $faqs = $fallbackFaqs;
        }

        return view('help.index', [
            'faqs' => $faqs,
        ]);
    }
}
