<section class="rounded-2xl border border-white/10 bg-slate-900/70 p-5">
  <h2 class="text-base font-semibold">可购买产品</h2>

  @if (count($products) === 0)
    <div class="mt-4 rounded-xl border border-dashed border-white/20 bg-slate-950/40 p-4 text-sm text-slate-400">
      暂无可购买产品
    </div>
  @else
    <ul class="mt-4 space-y-3">
      @foreach ($products as $product)
        <li class="rounded-xl border border-white/10 bg-slate-950/40 p-4">
          <div class="flex items-center justify-between gap-3">
            <p class="font-medium text-slate-100">{{ $product['name'] }} <span class="text-xs text-slate-400">({{ $product['code'] }})</span></p>
            <span class="rounded-full border border-white/15 px-2 py-0.5 text-xs text-slate-300">单价: {{ $product['unit_price'] }}</span>
          </div>
          <p class="mt-2 text-xs text-slate-400">按份购买，余额不足时会提示失败。</p>

          @if ($isGuest)
            <p class="mt-3 text-sm text-slate-400">登录后可进入详情页购买。</p>
          @else
            <a href="/products/{{ $product['id'] }}" class="mt-3 inline-flex rounded-lg px-4 py-2 text-sm font-semibold {{ $product['is_eligible'] ? 'bg-cyan-400 text-slate-950' : 'bg-slate-700 text-slate-200' }}">
              {{ $product['is_eligible'] ? '前往详情购买' : '前往详情查看' }}
            </a>
          @endif
        </li>
      @endforeach
    </ul>
  @endif
</section>
