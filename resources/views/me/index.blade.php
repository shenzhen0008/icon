<!doctype html>
<html lang="{{ __('pages/me.html_lang') }}" data-theme="{{ config('themes.active') }}">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
  <title>{{ __('pages/me.meta_title', ['app_name' => config('app.name')]) }}</title>
  <x-meta.favicons />
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-theme text-theme">
  <x-layout.background-glow />
  <x-nav.top />

  <main class="mx-auto w-full max-w-4xl px-4 pb-4 pt-8 md:pb-8">
    <x-home.hero :payment-config="[]" :payment-assets="[]" :is-guest="$isGuest" :show-title="false" :show-subtitle="false" />

    <div class="space-y-5">
      <x-me.payment-method-panel />
      <x-me.account-panel :profile="$profile" :is-guest="$isGuest" :has-mnemonic="$hasMnemonic" />
    </div>

  </main>

  <x-nav.mobile />

  @if ($isGuest)
    <x-auth.activate-pin-modal
      modal-id="activate-modal"
      open-button-id="open-activate-modal"
      :auto-open="true"
      :close-redirect-to="'/?locale='.app()->getLocale()"
      :invite-code="app(\App\Modules\Referral\Support\InviteCodeResolver::class)->currentForForm(request())"
      :title="__('pages/me.activate_modal.title')"
      :close-label="__('pages/me.activate_modal.close')"
      :description="__('pages/me.activate_modal.description')"
      :pin-label="__('pages/me.activate_modal.pin_label')"
      :pin-confirm-label="__('pages/me.activate_modal.pin_confirm_label')"
      :pin-aria-label="__('pages/me.activate_modal.pin_aria')"
      :pin-confirm-aria-label="__('pages/me.activate_modal.pin_confirm_aria')"
      :submit-label="__('pages/me.activate_modal.submit')"
      :login-url="'/login?redirect_to=%2Fme'"
      :login-label="__('pages/me.activate_modal.login')"
      :mismatch-error="__('pages/me.activate_modal.mismatch_error')"
      :incomplete-error="__('pages/me.activate_modal.incomplete_error')"
    />
  @endif

  @if (! $isGuest && $showMnemonicSetupPrompt)
    <dialog id="mnemonic-setup-prompt" class="theme-modal theme-pin-modal">
      <div class="p-5 md:p-6">
        <h2 class="text-scale-title font-semibold">{{ __('pages/me.mnemonic_prompt.title') }}</h2>
        <p class="mt-3 text-scale-body text-theme-secondary">{{ __('pages/me.mnemonic_prompt.description') }}</p>

        <div class="mt-5 flex flex-wrap gap-3">
          <a href="/me/mnemonic" class="text-scale-ui inline-flex h-[clamp(1.9rem,7vw,2.2rem)] items-center justify-center rounded-lg bg-[rgb(var(--theme-primary))] px-4 py-2 font-semibold text-theme-on-primary">{{ __('pages/me.mnemonic_prompt.go_now') }}</a>
          <button id="mnemonic-setup-prompt-later" type="button" class="text-scale-ui inline-flex h-[clamp(1.9rem,7vw,2.2rem)] items-center justify-center rounded-lg border border-theme bg-theme-secondary px-4 py-2 font-semibold text-theme">{{ __('pages/me.mnemonic_prompt.later') }}</button>
        </div>
      </div>
    </dialog>

    <script>
      (() => {
        const modal = document.getElementById('mnemonic-setup-prompt');
        const laterButton = document.getElementById('mnemonic-setup-prompt-later');
        if (!modal || typeof modal.showModal !== 'function') {
          return;
        }
        modal.showModal();
        laterButton?.addEventListener('click', () => modal.close());
      })();
    </script>
  @endif
</body>
</html>
