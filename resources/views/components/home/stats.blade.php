<section class="mb-8 rounded-2xl border border-theme bg-theme-card p-5">
    <h2 class="text-scale-display font-semibold text-theme">{{ __('pages/home.stats.title') }}</h2>
    <p class="mt-2 text-scale-body text-theme-secondary">{{ __('pages/home.stats.subtitle') }}</p>

    <div class="mt-5 rounded-xl border border-theme bg-theme-secondary/20 p-4">
        <div class="flex items-center justify-between border-b border-theme pb-3">
            <p class="text-scale-body text-theme-secondary">{{ __('pages/home.stats.participant_count') }}</p>
            <p
                class="text-scale-title font-semibold text-[rgb(var(--theme-primary))]"
                id="summary-participant-count"
                data-summary-ticker-base-value="{{ $summary['participant_ticker']['base_value'] ?? '0' }}"
                data-summary-ticker-step-seconds="{{ $summary['participant_ticker']['step_seconds'] ?? 3 }}"
                data-summary-ticker-min-delta="{{ $summary['participant_ticker']['min_delta'] ?? '0' }}"
                data-summary-ticker-max-delta="{{ $summary['participant_ticker']['max_delta'] ?? '0' }}"
                data-summary-ticker-precision="0"
            >{{ $summary['participant_count'] }}</p>
        </div>
        <div class="mt-3 flex items-center justify-between">
            <p class="text-scale-body text-theme-secondary">{{ __('pages/home.stats.total_profit') }}</p>
            <p
                class="text-scale-title font-semibold text-[rgb(var(--theme-accent))]"
                id="summary-total-profit"
                data-summary-ticker-base-value="{{ $summary['profit_ticker']['base_value'] ?? '0.00' }}"
                data-summary-ticker-step-seconds="{{ $summary['profit_ticker']['step_seconds'] ?? 3 }}"
                data-summary-ticker-min-delta="{{ $summary['profit_ticker']['min_delta'] ?? '0.00' }}"
                data-summary-ticker-max-delta="{{ $summary['profit_ticker']['max_delta'] ?? '0.00' }}"
                data-summary-ticker-precision="2"
                data-summary-ticker-suffix="{{ __('pages/home.stats.total_profit_suffix') }}"
            >{{ $summary['total_profit'] }} {{ __('pages/home.stats.total_profit_suffix') }}</p>
        </div>
    </div>
</section>
