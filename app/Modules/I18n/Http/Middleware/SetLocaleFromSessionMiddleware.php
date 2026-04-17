<?php

namespace App\Modules\I18n\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocaleFromSessionMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $supportedLocales = config('i18n.supported_locales', []);
        $sessionKey = (string) config('i18n.session_key', 'locale');
        $queryKey = (string) config('i18n.query_key', 'locale');

        $requestedLocale = $request->query($queryKey);
        if (is_string($requestedLocale) && in_array($requestedLocale, $supportedLocales, true)) {
            $request->session()->put($sessionKey, $requestedLocale);
        }

        $sessionLocale = $request->session()->get($sessionKey);
        $defaultLocale = (string) config('i18n.default_locale', config('app.locale'));
        $resolvedLocale = is_string($sessionLocale) && in_array($sessionLocale, $supportedLocales, true)
            ? $sessionLocale
            : $defaultLocale;

        app()->setLocale($resolvedLocale);
        app('translator')->setFallback((string) config('i18n.fallback_locale', 'zh-CN'));

        return $next($request);
    }
}
