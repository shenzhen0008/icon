<section class="rounded-2xl border border-theme bg-theme-card p-5">
  @php
    $statusLabels = [
      'open' => __('pages/orders.positions.status.open'),
      'redeeming' => __('pages/orders.positions.status.redeeming'),
      'redeemed' => __('pages/orders.positions.status.redeemed'),
    ];
  @endphp

  <h2 class="text-scale-body font-semibold text-theme">{{ __('pages/orders.positions.title') }}</h2>

  @if (count($positions) === 0)
    <div class="mt-4 rounded-xl border border-dashed border-theme bg-theme-secondary/20 p-4 text-scale-body text-theme-secondary">
      {{ __('pages/orders.positions.empty') }}
    </div>
  @else
    <ul class="mt-4 -mx-5 space-y-3 [overflow-anchor:none]">
      @foreach ($positions as $position)
        @php
          $panelId = 'position-profit-panel-'.$position['id'];
        @endphp
        <li class="rounded-xl border border-theme bg-theme-secondary/20 p-4">
          <button
            type="button"
            class="w-full text-left"
            data-profit-toggle
            data-target="#{{ $panelId }}"
            aria-expanded="false"
            aria-controls="{{ $panelId }}"
          >
            <div class="flex items-center justify-between gap-3">
              <p class="font-medium text-theme">{{ $position['name'] }}</p>
              <span class="rounded-full border border-theme px-2 py-0.5 text-scale-micro text-theme-secondary" data-status-key="{{ $position['status'] }}">{{ $statusLabels[$position['status']] ?? $position['status'] }}</span>
            </div>
          </button>

          <div class="mt-2 flex items-center justify-between gap-3">
            <p class="text-scale-body text-theme-secondary">{{ __('pages/orders.positions.principal_prefix') }}{{ $position['principal'] }}</p>
            <a href="/me/positions/{{ $position['id'] }}" class="text-scale-body text-[rgb(var(--theme-primary))] underline underline-offset-4">{{ __('pages/orders.positions.view_order') }}</a>
          </div>

          <div id="{{ $panelId }}" class="mt-3 hidden border-t border-theme pt-3">
            <div class="rounded-lg border border-theme bg-theme-card p-3">
              <p class="text-scale-micro text-[rgb(var(--theme-primary))]">{{ __('pages/orders.positions.recent_profit_title') }}</p>
              <div class="mt-2 space-y-1 text-scale-micro text-theme-secondary">
                @if (count($position['recent_profits']) === 0)
                  <p class="text-theme-secondary">{{ __('pages/orders.positions.recent_profit_empty') }}</p>
                @else
                  @foreach ($position['recent_profits'] as $row)
                    <div class="flex items-center justify-between">
                      <span>{{ $row['date'] }}</span>
                      <span class="text-[rgb(var(--theme-accent))]">{{ $row['profit'] }}</span>
                    </div>
                  @endforeach
                @endif
              </div>
            </div>
          </div>
        </li>
      @endforeach
    </ul>
  @endif
</section>

<script>
  document.querySelectorAll('[data-profit-toggle]').forEach((buttonEl) => {
    buttonEl.addEventListener('click', () => {
      const lockedY = window.scrollY;
      const panel = document.querySelector(buttonEl.dataset.target);
      if (!panel) return;

      const isOpen = !panel.classList.contains('hidden');
      panel.classList.toggle('hidden', isOpen);
      buttonEl.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
      window.scrollTo(0, lockedY);
    });
  });
</script>
