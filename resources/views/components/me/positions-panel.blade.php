<section class="rounded-2xl border border-white/10 bg-slate-900/70 p-5">
  @php
    $statusLabels = [
      'open' => '持有中',
      'redeeming' => '赎回中',
      'redeemed' => '已赎回',
    ];
  @endphp

  <h2 class="text-base font-semibold">持仓产品</h2>

  @if (count($positions) === 0)
    <div class="mt-4 rounded-xl border border-dashed border-white/20 bg-slate-950/40 p-4 text-sm text-slate-400">
      暂无持仓产品
    </div>
  @else
    <ul class="mt-4 -mx-5 space-y-3 [overflow-anchor:none]">
      @foreach ($positions as $position)
        @php
          $panelId = 'position-profit-panel-'.$position['id'];
        @endphp
        <li class="rounded-xl border border-white/10 bg-slate-950/40 p-4">
          <button
            type="button"
            class="w-full text-left"
            data-profit-toggle
            data-target="#{{ $panelId }}"
            aria-expanded="false"
            aria-controls="{{ $panelId }}"
          >
            <div class="flex items-center justify-between gap-3">
              <p class="font-medium text-slate-100">{{ $position['name'] }}</p>
              <span class="rounded-full border border-white/15 px-2 py-0.5 text-xs text-slate-300" data-status-key="{{ $position['status'] }}">{{ $statusLabels[$position['status']] ?? $position['status'] }}</span>
            </div>
          </button>

          <div class="mt-2 flex items-center justify-between gap-3">
            <p class="text-sm text-slate-300">本金：{{ $position['principal'] }}</p>
            <a href="/me/positions/{{ $position['id'] }}" class="text-sm text-cyan-300 underline underline-offset-4">查看订单</a>
          </div>

          <div id="{{ $panelId }}" class="mt-3 hidden border-t border-white/10 pt-3">
            <div class="rounded-lg border border-white/10 bg-slate-900/50 p-3">
              <p class="text-xs text-cyan-300">最近3天收益</p>
              <div class="mt-2 space-y-1 text-xs text-slate-300">
                @if (count($position['recent_profits']) === 0)
                  <p class="text-slate-500">暂无收益记录</p>
                @else
                  @foreach ($position['recent_profits'] as $row)
                    <div class="flex items-center justify-between">
                      <span>{{ $row['date'] }}</span>
                      <span class="text-emerald-300">{{ $row['profit'] }}</span>
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
