<?php

namespace App\Modules\ClientEnv\Services;

use Illuminate\Http\Request;

class ClientEnvDetectorService
{
    /**
     * @return array<string, mixed>
     */
    public function detect(Request $request): array
    {
        $userAgent = trim((string) $request->userAgent());
        $ua = strtolower($userAgent);

        $isBot = $this->isBot($ua);
        $isTablet = $this->isTablet($ua);
        $isMobile = !$isTablet && $this->isMobile($ua);
        $deviceType = $this->resolveDeviceType($userAgent, $isBot, $isTablet, $isMobile);

        return [
            'device_type' => $deviceType,
            'is_mobile' => $deviceType === 'mobile',
            'is_tablet' => $deviceType === 'tablet',
            'is_desktop' => $deviceType === 'desktop',
            'is_webview' => $this->isWebView($ua),
            'browser' => $this->detectBrowser($userAgent),
            'os' => $this->detectOs($userAgent),
            'source' => 'user_agent',
        ];
    }

    private function isBot(string $ua): bool
    {
        if ($ua === '') {
            return false;
        }

        return (bool) preg_match('/bot|crawler|spider|slurp|bingpreview|facebookexternalhit/i', $ua);
    }

    private function isTablet(string $ua): bool
    {
        if ($ua === '') {
            return false;
        }

        return (bool) preg_match('/ipad|tablet|kindle|playbook|silk|android(?!.*mobile)/i', $ua);
    }

    private function isMobile(string $ua): bool
    {
        if ($ua === '') {
            return false;
        }

        return (bool) preg_match('/mobile|iphone|ipod|android|windows phone|opera mini|iemobile/i', $ua);
    }

    private function resolveDeviceType(string $userAgent, bool $isBot, bool $isTablet, bool $isMobile): string
    {
        if ($userAgent === '') {
            return 'unknown';
        }

        if ($isBot) {
            return 'bot';
        }

        if ($isTablet) {
            return 'tablet';
        }

        if ($isMobile) {
            return 'mobile';
        }

        return 'desktop';
    }

    /**
     * @return array{name: string, version: string}
     */
    private function detectBrowser(string $userAgent): array
    {
        $rules = [
            'Edge' => '/edg(?:e|ios|a)?\/([0-9.]+)/i',
            'Opera' => '/(?:opr|opera)\/([0-9.]+)/i',
            'UC Browser' => '/ucbrowser\/([0-9.]+)/i',
            'QQ Browser' => '/qqbrowser\/([0-9.]+)/i',
            'WeChat' => '/micromessenger\/([0-9.]+)/i',
            'Chrome' => '/(?:crios|chrome)\/([0-9.]+)/i',
            'Firefox' => '/(?:fxios|firefox)\/([0-9.]+)/i',
            'Safari' => '/version\/([0-9.]+).*safari/i',
            'Internet Explorer' => '/(?:msie\s|rv:)([0-9.]+)/i',
        ];

        foreach ($rules as $name => $pattern) {
            if (preg_match($pattern, $userAgent, $matches) === 1) {
                return [
                    'name' => $name,
                    'version' => (string) ($matches[1] ?? 'unknown'),
                ];
            }
        }

        return [
            'name' => 'unknown',
            'version' => 'unknown',
        ];
    }

    /**
     * @return array{name: string, version: string}
     */
    private function detectOs(string $userAgent): array
    {
        $rules = [
            'Android' => '/Android\s([0-9._]+)/i',
            'iOS' => '/(?:iPhone|CPU iPhone OS|CPU OS|iPad; CPU OS)\s([0-9_]+)/i',
            'Windows' => '/Windows NT\s([0-9.]+)/i',
            'macOS' => '/Mac OS X\s([0-9_]+)/i',
            'HarmonyOS' => '/HarmonyOS\s([0-9.]+)/i',
        ];

        foreach ($rules as $name => $pattern) {
            if (preg_match($pattern, $userAgent, $matches) === 1) {
                return [
                    'name' => $name,
                    'version' => str_replace('_', '.', (string) ($matches[1] ?? 'unknown')),
                ];
            }
        }

        if (stripos($userAgent, 'Linux') !== false) {
            return ['name' => 'Linux', 'version' => 'unknown'];
        }

        return ['name' => 'unknown', 'version' => 'unknown'];
    }

    private function isWebView(string $ua): bool
    {
        if ($ua === '') {
            return false;
        }

        $keywords = array_map(
            static fn (mixed $keyword): string => strtolower((string) $keyword),
            (array) config('client_env.webview_keywords', [])
        );

        foreach ($keywords as $keyword) {
            if ($keyword !== '' && str_contains($ua, $keyword)) {
                return true;
            }
        }

        $isIosWebKitWithoutSafari = str_contains($ua, 'applewebkit')
            && (str_contains($ua, 'iphone') || str_contains($ua, 'ipad'))
            && !str_contains($ua, 'safari');

        $isAndroidWv = str_contains($ua, '; wv)')
            || (str_contains($ua, 'android') && str_contains($ua, 'version/') && str_contains($ua, 'chrome/'));

        return $isIosWebKitWithoutSafari || $isAndroidWv;
    }
}
