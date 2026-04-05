<section class="rounded-2xl border border-white/10 bg-slate-900/70 p-5">
  <h2 class="text-base font-semibold">持仓产品</h2>

  @if (count($positions) === 0)
    <div class="mt-4 rounded-xl border border-dashed border-white/20 bg-slate-950/40 p-4 text-sm text-slate-400">
      暂无持仓产品
    </div>
  @else
    <ul class="mt-4 space-y-3">
      @foreach ($positions as $position)
        <li class="rounded-xl border border-white/10 bg-slate-950/40 p-4">
          <div class="flex items-center justify-between gap-3">
            <p class="font-medium text-slate-100">{{ $position['name'] }}</p>
            <span class="rounded-full border border-white/15 px-2 py-0.5 text-xs text-slate-300">{{ $position['status'] }}</span>
          </div>
          <p class="mt-2 text-sm text-slate-300">本金：{{ $position['principal'] }}</p>
        </li>
      @endforeach
    </ul>
  @endif
</section>
