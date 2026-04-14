<section class="rounded-2xl border border-theme bg-theme-card p-5">
  <h2 class="text-scale-body font-semibold text-theme">可购买产品</h2>

  @if (count($products) === 0)
    <div class="mt-4 rounded-xl border border-dashed border-theme bg-theme-secondary/20 p-4 text-scale-body text-theme-secondary">
      暂无可购买产品
    </div>
  @else
    <ul class="mt-4 space-y-3">
      @foreach ($products as $product)
        <li class="rounded-xl border border-theme bg-theme-secondary/20 p-4">
          <div class="flex items-center justify-between gap-3">
            <p class="font-medium text-theme">{{ $product['name'] }} <span class="text-scale-micro text-theme-secondary">({{ $product['code'] }})</span></p>
            <span class="rounded-full border border-theme px-2 py-0.5 text-scale-micro text-theme-secondary">限额: {{ $product['limit_range'] }} USDT</span>
          </div>
          <p class="mt-2 text-scale-micro text-theme-secondary">按金额购买，余额不足时会提示失败。</p>

          @if ($isGuest)
            <p class="mt-3 text-scale-body text-theme-secondary">登录后可进入详情页购买。</p>
          @else
            <a href="/products/{{ $product['id'] }}" class="text-scale-ui mt-3 inline-flex h-[clamp(1.9rem,7vw,2.2rem)] w-[clamp(8rem,45vw,10rem)] items-center justify-center rounded-lg px-[clamp(0.6rem,2.5vw,0.9rem)] font-semibold {{ $product['is_eligible'] ? 'bg-[rgb(var(--theme-primary))] text-theme-on-primary' : 'bg-theme-secondary text-theme' }}">
              {{ $product['is_eligible'] ? '前往详情购买' : '前往详情查看' }}
            </a>
          @endif
        </li>
      @endforeach
    </ul>
  @endif
</section>
