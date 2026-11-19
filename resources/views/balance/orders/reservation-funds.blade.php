<section class="rounded-2xl border border-theme bg-theme-card p-5">
  <h2 class="text-scale-body font-semibold text-theme">{{ __('pages/orders.fund_orders.title') }}</h2>

  <ul class="mt-4 space-y-3">
    @foreach ($orders as $order)
      <li class="rounded-xl border border-theme bg-theme-secondary/20 p-4">
        <p class="break-all font-medium text-theme" dir="auto">{{ $order->name }}</p>
        <div class="mt-2 flex flex-wrap items-center justify-between gap-2 text-scale-body text-theme-secondary">
          <p>{{ __('pages/orders.fund_orders.'.$order->type) }} <bdi>{{ $order->signedAmount() }} USDT</bdi></p>
          <time datetime="{{ $order->created_at->toAtomString() }}">{{ $order->created_at->format('Y-m-d H:i:s') }}</time>
        </div>
      </li>
    @endforeach
  </ul>
</section>
