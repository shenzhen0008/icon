<!doctype html>
<html lang="{{ __('pages/home.html_lang') }}" data-theme="{{ config('themes.active') }}">
<head>
  <meta charset="UTF-8">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
  <title>{{ __('pages/home.meta_title', ['app_name' => config('app.name')]) }}</title>
  <x-meta.favicons />
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-theme text-theme">
  <x-layout.background-glow />

  <x-nav.top />

  <main class="relative mx-auto w-full max-w-4xl px-4 pb-28 pt-[12rem] md:pb-12 md:pt-[30rem]">
    <div aria-hidden="true" class="pointer-events-none absolute inset-x-0 top-0 -z-10 overflow-visible">
      <div class="absolute inset-x-0 top-0 h-[14rem] bg-gradient-to-b from-[#0f47d9] via-[#2b66f6]/95 via-45% to-transparent md:h-[36rem]"></div>
      <img
        src="{{ asset('images/home.png') }}"
        alt=""
        class="relative h-auto w-full object-contain object-top opacity-82 saturate-125"
      >
    </div>

    <x-home.hero :summary="$summary" :payment-config="$paymentConfig" :payment-assets="$homePaymentAssets" :is-guest="$isGuest" :show-record-buttons="false" />
    <x-home.stats :summary="$summary" />
    <x-home.exchange-metrics :metrics="$metrics" :shared-profit="$sharedExchangeProfit" />
    <x-home.friendly-links />
  </main>

  <x-nav.mobile />

  @if ($isGuest)
    <x-auth.activate-pin-modal
      modal-id="home-activate-modal"
      :invite-code="app(\App\Modules\Referral\Support\InviteCodeResolver::class)->currentForForm(request())"
    />
  @endif
</body>
</html>
