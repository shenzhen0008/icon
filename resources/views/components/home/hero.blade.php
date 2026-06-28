@props([
    'paymentConfig' => [],
    'paymentAssets' => [],
    'isGuest' => false,
    'showTitle' => true,
    'showSubtitle' => true,
    'showRecordButtons' => true,
    'heroPanelPayload' => null,
    'heroPanelPayloads' => null,
])

@php
    $heroPanelPayloadCache = is_array($heroPanelPayloads)
        ? $heroPanelPayloads
        : [];
    if (is_array($heroPanelPayload) && ! isset($heroPanelPayloadCache['demo'])) {
        $heroPanelPayloadCache['demo'] = $heroPanelPayload;
    }
    $heroPanelPayloadCache['demo'] ??= app(\App\Modules\Home\Services\HomeHeroPanelService::class)->resolve('demo');
    $heroPanelPayloadCache['live'] ??= app(\App\Modules\Home\Services\HomeHeroPanelService::class)->resolve('live', auth('web')->id());
    $initialHeroPanelPayload = $heroPanelPayloadCache['demo'];
    $availableBalance = number_format((float) ($initialHeroPanelPayload['available_balance'] ?? 0), 2, '.', ',');
    $localeQuery = 'locale='.urlencode(app()->getLocale());
@endphp

<section
    id="home-data-panel"
    class="mb-8 overflow-hidden rounded-2xl border border-theme bg-theme-card p-5 shadow-xl shadow-theme"
    data-mode-badge-demo="{{ __('pages/home.hero.mode_demo_badge') }}"
    data-mode-badge-live="{{ __('pages/home.hero.mode_live_badge') }}"
    data-locale="{{ app()->getLocale() }}"
    data-live-load-failed="{{ __('pages/home.hero.live_load_failed') }}"
>
    <div class="flex items-start gap-4">
        <div>
            @if ($showTitle)
                <h1 class="text-scale-display font-semibold text-theme">{{ __('pages/home.hero.title') }}</h1>
            @endif
            @if ($showSubtitle)
                <p class="mt-2 text-scale-body text-theme-secondary">{{ __('pages/home.hero.subtitle') }}</p>
            @endif
        </div>
    </div>

    @if ($showRecordButtons)
        <div class="mt-5 grid grid-cols-2 gap-2">
            <a id="hero-trade-record-btn" href="/home/hero-panel/trade-records?mode=demo&{{ $localeQuery }}" class="inline-flex items-center justify-center rounded-lg border border-theme bg-theme-secondary px-3 py-2 text-scale-body font-medium text-theme transition hover:bg-theme-secondary/80">
                {{ __('pages/home.hero.trade_records') }}
            </a>
            <a id="hero-income-record-btn" href="/home/hero-panel/income-records?mode=demo&{{ $localeQuery }}" class="inline-flex items-center justify-center rounded-lg border border-theme bg-theme-secondary px-3 py-2 text-scale-body font-medium text-theme transition hover:bg-theme-secondary/80">
                {{ __('pages/home.hero.income_records') }}
            </a>
        </div>
    @endif

    <x-ui.metric-split-card
        wrapper-class="mt-3 rounded-xl border border-theme bg-theme-secondary/60 p-4"
    >
        <x-slot:top>
            <div class="grid grid-cols-[minmax(0,1fr)_auto_auto] items-center gap-2">
                <p class="min-w-0 text-scale-body text-theme-secondary whitespace-nowrap">{{ __('pages/home.hero.available_balance') }}</p>
                <p id="hero-available-balance" class="justify-self-end font-mono text-scale-title font-semibold leading-none tabular-nums text-theme">
                    ${{ $availableBalance }}
                </p>
                <span id="hero-mode-badge" class="justify-self-end inline-flex w-20 justify-center rounded-full border border-theme bg-theme-secondary/30 px-3 py-1 text-scale-body text-theme">{{ __('pages/home.hero.mode_demo_badge') }}</span>
            </div>
        </x-slot:top>
        <x-slot:left>
                <p class="text-scale-body text-theme-secondary whitespace-nowrap">{{ __('pages/home.hero.total_earnings') }}</p>
                <p id="hero-total-earnings" class="mt-2 h-8 overflow-hidden text-ellipsis whitespace-nowrap font-mono text-scale-title font-semibold leading-none tabular-nums text-theme sm:h-9 text-scale-display">${{ number_format((float) ($initialHeroPanelPayload['total_earnings'] ?? 0), 2, '.', ',') }}</p>
        </x-slot:left>
        <x-slot:right>
                <p class="text-scale-body text-theme-secondary whitespace-nowrap">{{ __('pages/home.hero.earnings_24h') }}</p>
                <p id="hero-earnings-24h" class="mt-2 h-8 overflow-hidden text-ellipsis whitespace-nowrap font-mono text-scale-title font-semibold leading-none tabular-nums text-theme sm:h-9 text-scale-display">${{ number_format((float) ($initialHeroPanelPayload['earnings_24h'] ?? 0), 2, '.', ',') }}</p>
        </x-slot:right>
    </x-ui.metric-split-card>

    {{--
    <div class="mt-5 space-y-3">
        <button
            id="home-onchain-entry"
            type="button"
            data-walletconnect-project-id="{{ config('web3.payment.walletconnect_project_id', '') }}"
            class="inline-flex items-center justify-center rounded-xl bg-[rgb(var(--theme-primary))] px-4 py-3 text-scale-body font-semibold text-theme-on-primary transition hover:bg-[rgb(var(--theme-primary))]/90"
        >Direct Pay (On-chain Recharge)</button>

        <div id="home-quick-pay-panel" class="hidden rounded-xl border border-theme bg-theme-secondary/40 p-3">
            @if (count($paymentAssets) === 0)
                <p class="text-scale-body text-theme-secondary">No EVM asset configuration is currently available. Please contact the administrator to configure token contracts and receiving addresses.</p>
            @else
                <div class="space-y-3">
                    <div class="grid grid-cols-2 gap-2 md:grid-cols-4">
                        @foreach ($paymentAssets as $index => $asset)
                            <button
                                type="button"
                                data-home-asset-button
                                data-asset-code="{{ $asset['code'] }}"
                                data-token-address="{{ $asset['token_address'] }}"
                                data-to-address="{{ $asset['to_address'] }}"
                                data-chain-id="{{ $asset['chain_id'] }}"
                                class="rounded-lg border border-theme bg-theme-card px-3 py-2 text-left text-scale-body text-theme transition hover:bg-theme-secondary/70 @if ($index === 0) border-[rgb(var(--theme-primary))] bg-[rgb(var(--theme-primary))]/10 @endif"
                            >
                                <span class="block font-semibold">{{ $asset['code'] }}</span>
                                <span class="block text-scale-micro text-theme-secondary">{{ $asset['network'] }}</span>
                            </button>
                        @endforeach
                    </div>
                    <p id="home-selected-asset" class="text-scale-micro text-theme-secondary">Selected: {{ $paymentAssets[0]['code'] ?? '--' }}</p>
                    <div class="grid grid-cols-1 gap-2 md:grid-cols-3">
                        <input id="home-payment-amount" type="number" step="0.01" min="0.01" value="{{ $paymentConfig['amount'] ?? '10' }}" class="rounded-lg border border-theme bg-theme-card px-3 py-2 text-theme md:col-span-2" />
                        <div class="rounded-lg border border-theme bg-theme-card px-3 py-2 text-scale-micro text-theme-secondary">When confirming recharge, wallet connection starts automatically and falls back to WalletConnect on failure.</div>
                    </div>
                    <button id="home-pay-confirm-btn" type="button" class="w-full rounded-lg border border-[rgb(var(--theme-primary))]/40 px-3 py-2 text-scale-body font-semibold text-[rgb(var(--theme-primary))] hover:bg-[rgb(var(--theme-primary))]/10">Confirm recharge and open wallet payment</button>
                    <input type="hidden" id="home-is-guest" value="{{ $isGuest ? '1' : '0' }}">
                    <p id="home-pay-feedback" class="hidden text-scale-micro text-[rgb(var(--theme-primary))]"></p>
                </div>
            @endif
        </div>
    </div>
    --}}

    <div class="mt-5 grid grid-cols-2 gap-3">
        <button id="hero-damo-btn" type="button" class="inline-flex items-center justify-center rounded-xl bg-theme-secondary px-4 py-3 text-scale-body font-semibold text-theme transition hover:bg-theme-secondary/80">
            {{ __('pages/home.hero.mode_demo') }}
        </button>
        <button id="hero-live-btn" type="button" class="inline-flex items-center justify-center rounded-xl bg-theme-secondary px-4 py-3 text-scale-body font-semibold text-theme transition hover:bg-theme-secondary/80">
            {{ __('pages/home.hero.mode_live') }}
        </button>
    </div>
</section>

<script id="home-hero-panel-payloads" type="application/json">@json($heroPanelPayloadCache)</script>
