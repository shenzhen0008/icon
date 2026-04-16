<!doctype html>
<html lang="zh-CN" data-theme="{{ config('themes.active') }}">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
  <title>我的 | Icon Market</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-theme text-theme">
  <x-layout.background-glow />
  <x-nav.top />

  <main class="mx-auto w-full max-w-4xl px-4 pb-28 pt-8 md:pb-10">
    <x-home.hero :payment-config="[]" :payment-assets="[]" :is-guest="$isGuest" :show-title="false" :show-subtitle="false" />

    <div class="space-y-5">
      <x-me.payment-method-panel />
      <x-me.account-panel :profile="$profile" :is-guest="$isGuest" />

      @if ($isGuest)
        <x-me.guest-auth-panel />
      @endif
    </div>

  </main>

  <x-nav.mobile />

  @if ($isGuest)
    <x-auth.activate-pin-modal
      modal-id="activate-modal"
      open-button-id="open-activate-modal"
      :invite-code="app(\App\Modules\Referral\Support\InviteCodeResolver::class)->currentForForm(request())"
    />
  @endif
</body>
</html>
