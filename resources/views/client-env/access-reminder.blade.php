@php
  $currentLocale = app()->getLocale();
  $languageDisplayMap = [
      'zh-CN' => ['code' => 'ZH', 'flag' => asset('images/flags/cn.svg')],
      'en' => ['code' => 'EN', 'flag' => asset('images/flags/us.svg')],
      'ja' => ['code' => 'JA', 'flag' => asset('images/flags/jp.svg')],
      'ko' => ['code' => 'KO', 'flag' => asset('images/flags/kr.svg')],
      'de' => ['code' => 'DE', 'flag' => asset('images/flags/de.svg')],
      'fr' => ['code' => 'FR', 'flag' => asset('images/flags/fr.svg')],
      'pt' => ['code' => 'PT', 'flag' => asset('images/flags/br.svg')],
      'es' => ['code' => 'ES', 'flag' => asset('images/flags/es.svg')],
  ];
  $currentLanguageDisplay = $languageDisplayMap[$currentLocale] ?? $languageDisplayMap['EN'];
@endphp

<!doctype html>
<html lang="{{ __('pages/client-env-access-reminder.html_lang') }}" data-theme="{{ config('themes.active') }}">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
  <title>{{ __('pages/client-env-access-reminder.meta_title', ['app_name' => config('app.name')]) }}</title>
  <x-meta.favicons />
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-theme text-theme">
  <x-layout.background-glow />

  <main class="mx-auto flex min-h-screen w-full max-w-2xl items-center px-4 py-12">
    <section
      class="relative w-full rounded-3xl p-6 shadow-xl md:p-8"
      style="background-color:#0A5CFF;color:#FFFFFF;"
    >
      <div class="absolute right-4 top-4">
        <button
          id="language-toggle"
          type="button"
          aria-label="{{ __('pages/home.nav.language_toggle') }}"
          aria-haspopup="true"
          aria-expanded="false"
          aria-controls="language-menu"
          class="inline-flex items-center justify-center gap-1 rounded-full bg-white/20 px-2 py-1 text-white transition hover:bg-white/30"
        >
          <img
            src="{{ $currentLanguageDisplay['flag'] }}"
            alt=""
            class="h-4 w-5 shrink-0 rounded-[2px] object-cover"
            aria-hidden="true"
            data-language-current-flag
          >
          <span class="text-xs font-semibold uppercase leading-none text-white" data-language-current-code>{{ $currentLanguageDisplay['code'] }}</span>
        </button>
        <div
          id="language-menu"
          class="absolute left-0 top-full z-40 mt-2 hidden w-[148px] overflow-hidden rounded-lg border border-black/10 bg-white py-1 shadow-xl"
          role="menu"
          aria-labelledby="language-toggle"
        >
          <button type="button" class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm text-black transition hover:bg-black/5" role="menuitem" data-language-option data-language-code="EN" data-language-locale="en" data-language-flag="{{ asset('images/flags/us.svg') }}"><img src="{{ asset('images/flags/us.svg') }}" alt="" class="h-4 w-5 shrink-0 rounded-[2px] object-cover" aria-hidden="true"><span>English</span></button>
          <button type="button" class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm text-black transition hover:bg-black/5" role="menuitem" data-language-option data-language-code="JA" data-language-locale="ja" data-language-flag="{{ asset('images/flags/jp.svg') }}"><img src="{{ asset('images/flags/jp.svg') }}" alt="" class="h-4 w-5 shrink-0 rounded-[2px] object-cover" aria-hidden="true"><span>日本語</span></button>
          <button type="button" class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm text-black transition hover:bg-black/5" role="menuitem" data-language-option data-language-code="KO" data-language-locale="ko" data-language-flag="{{ asset('images/flags/kr.svg') }}"><img src="{{ asset('images/flags/kr.svg') }}" alt="" class="h-4 w-5 shrink-0 rounded-[2px] object-cover" aria-hidden="true"><span>한국어</span></button>
          <button type="button" class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm text-black transition hover:bg-black/5" role="menuitem" data-language-option data-language-code="DE" data-language-locale="de" data-language-flag="{{ asset('images/flags/de.svg') }}"><img src="{{ asset('images/flags/de.svg') }}" alt="" class="h-4 w-5 shrink-0 rounded-[2px] object-cover" aria-hidden="true"><span>Deutsch</span></button>
          <button type="button" class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm text-black transition hover:bg-black/5" role="menuitem" data-language-option data-language-code="FR" data-language-locale="fr" data-language-flag="{{ asset('images/flags/fr.svg') }}"><img src="{{ asset('images/flags/fr.svg') }}" alt="" class="h-4 w-5 shrink-0 rounded-[2px] object-cover" aria-hidden="true"><span>Français</span></button>
          <button type="button" class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm text-black transition hover:bg-black/5" role="menuitem" data-language-option data-language-code="PT" data-language-locale="pt" data-language-flag="{{ asset('images/flags/br.svg') }}"><img src="{{ asset('images/flags/br.svg') }}" alt="" class="h-4 w-5 shrink-0 rounded-[2px] object-cover" aria-hidden="true"><span>Português</span></button>
          <button type="button" class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm text-black transition hover:bg-black/5" role="menuitem" data-language-option data-language-code="ES" data-language-locale="es" data-language-flag="{{ asset('images/flags/es.svg') }}"><img src="{{ asset('images/flags/es.svg') }}" alt="" class="h-4 w-5 shrink-0 rounded-[2px] object-cover" aria-hidden="true"><span>Español</span></button>
          <button type="button" class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm text-black transition hover:bg-black/5" role="menuitem" data-language-option data-language-code="ZH" data-language-locale="zh-CN" data-language-flag="{{ asset('images/flags/cn.svg') }}"><img src="{{ asset('images/flags/cn.svg') }}" alt="" class="h-4 w-5 shrink-0 rounded-[2px] object-cover" aria-hidden="true"><span>中文</span></button>
        </div>
      </div>

      <img
        src="{{ asset('images/img_unable_connect.png') }}"
        alt="{{ __('pages/client-env-access-reminder.image_alt') }}"
        class="mx-auto mb-6 h-auto"
        style="width:220px;"
      >
      <h1 class="text-center text-scale-display font-semibold text-white">{{ __('pages/client-env-access-reminder.title') }}</h1>
      <p class="mt-4 text-center text-scale-body leading-7 text-white/90">
        {{ __('pages/client-env-access-reminder.description') }}
      </p>

      <div class="flex flex-col items-center gap-3" style="margin-top:30px;">
        <input
          id="copy-home-url-source"
          type="text"
          value="{{ $homeUrl }}"
          readonly
          class="sr-only"
          tabindex="-1"
          aria-hidden="true"
        >
        <button
          id="copy-home-url-button"
          type="button"
          class="inline-flex items-center justify-center self-center whitespace-nowrap rounded-lg bg-white px-3 py-2 text-sm font-semibold text-black transition hover:bg-white/90"
        >
          {{ __('pages/client-env-access-reminder.copy_button') }}
        </button>
      </div>

      <p id="copy-feedback" class="mt-3 text-center text-scale-ui text-white/90" role="status" aria-live="polite"></p>
    </section>
  </main>

  <script>
    (() => {
      const button = document.getElementById('copy-home-url-button');
      const source = document.getElementById('copy-home-url-source');
      const urlText = source?.value?.trim() ?? '';
      const feedback = document.getElementById('copy-feedback');
      const languageToggle = document.getElementById('language-toggle');
      const languageMenu = document.getElementById('language-menu');
      const languageCurrentFlag = document.querySelector('[data-language-current-flag]');
      const languageCurrentCode = document.querySelector('[data-language-current-code]');
      const languageOptions = document.querySelectorAll('[data-language-option]');
      const i18n = {
        copyFailedEmpty: @json(__('pages/client-env-access-reminder.copy_failed_empty')),
        copied: @json(__('pages/client-env-access-reminder.copy_success')),
        copyFailedManual: @json(__('pages/client-env-access-reminder.copy_failed_manual')),
      };

      const setFeedback = (message) => {
        if (feedback) feedback.textContent = message;
      };

      const fallbackCopy = (text) => {
        const textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.setAttribute('readonly', '');
        textarea.style.position = 'absolute';
        textarea.style.left = '-9999px';
        document.body.appendChild(textarea);
        textarea.select();
        const copied = document.execCommand('copy');
        document.body.removeChild(textarea);
        return copied;
      };

      button?.addEventListener('click', async () => {
        if (!urlText) {
          setFeedback(i18n.copyFailedEmpty);
          return;
        }

        try {
          if (navigator.clipboard && window.isSecureContext) {
            await navigator.clipboard.writeText(urlText);
            setFeedback(i18n.copied);
            return;
          }

          const copied = fallbackCopy(urlText);
          setFeedback(copied ? i18n.copied : i18n.copyFailedManual);
        } catch (_) {
          setFeedback(i18n.copyFailedManual);
        }
      });

      const closeLanguageMenu = () => {
        if (!languageToggle || !languageMenu) return;
        languageMenu.classList.add('hidden');
        languageToggle.setAttribute('aria-expanded', 'false');
      };

      languageToggle?.addEventListener('click', (event) => {
        event.stopPropagation();
        if (!languageMenu || !languageToggle) return;
        const nextExpanded = languageMenu.classList.contains('hidden');
        languageMenu.classList.toggle('hidden', !nextExpanded);
        languageToggle.setAttribute('aria-expanded', nextExpanded ? 'true' : 'false');
      });

      languageOptions.forEach((option) => {
        option.addEventListener('click', () => {
          const nextFlag = option.getAttribute('data-language-flag');
          const nextCode = option.getAttribute('data-language-code');
          const nextLocale = option.getAttribute('data-language-locale');

          if (nextFlag && languageCurrentFlag instanceof HTMLImageElement) {
            languageCurrentFlag.src = nextFlag;
          }
          if (nextCode && languageCurrentCode) {
            languageCurrentCode.textContent = nextCode;
          }

          closeLanguageMenu();

          if (nextLocale) {
            const nextUrl = new URL(window.location.href);
            nextUrl.searchParams.set('locale', nextLocale);
            window.location.assign(nextUrl.toString());
          }
        });
      });

      document.addEventListener('click', (event) => {
        if (!languageToggle || !languageMenu) return;
        if (languageToggle.contains(event.target) || languageMenu.contains(event.target)) return;
        closeLanguageMenu();
      });

      document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') closeLanguageMenu();
      });
    })();
  </script>
</body>
</html>
