<?php

namespace App\Modules\Support\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class SupportPageController extends Controller
{
    public function __invoke(): View
    {
        $propertyId = (string) config('support.tawk.property_id');
        $widgetId = (string) config('support.tawk.widget_id');

        $tawkEnabled = config('support.tawk.enabled')
            && filled($propertyId)
            && filled($widgetId);

        return view('support.index', [
            'tawkEnabled' => $tawkEnabled,
            'embedUrl' => $tawkEnabled
                ? sprintf('https://embed.tawk.to/%s/%s', $propertyId, $widgetId)
                : null,
        ]);
    }
}
