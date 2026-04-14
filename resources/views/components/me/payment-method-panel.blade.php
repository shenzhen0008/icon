<section class="rounded-2xl border border-theme bg-theme-card p-5">
  <h2 class="text-scale-body font-semibold text-theme">存取款方式</h2>
  <p class="mt-1 text-scale-micro text-theme-secondary">请选择一种存款和提款方式。</p>

  <div class="mt-4 space-y-3">
    <label class="flex cursor-pointer items-center justify-between gap-3 rounded-xl border border-theme bg-theme-secondary/20 p-4 transition hover:border-[rgb(var(--theme-primary))]/45">
      <span class="flex items-center gap-2 text-scale-body text-theme">
        <img src="/images/coin.png" alt="" class="h-5 w-5 object-contain" aria-hidden="true">
        <span>加密货币</span>
      </span>
      <input type="radio" name="payment-method" value="crypto" class="h-4 w-4 border-theme text-[rgb(var(--theme-primary))] focus:ring-[rgb(var(--theme-primary))]/35" checked>
    </label>
    <label class="flex cursor-pointer items-center justify-between gap-3 rounded-xl border border-theme bg-theme-secondary/20 p-4 transition hover:border-[rgb(var(--theme-primary))]/45">
      <span class="flex items-center gap-2 text-scale-body text-theme">
        <img src="/images/card.png" alt="" class="h-5 w-5 object-contain" aria-hidden="true">
        <span>银行卡</span>
      </span>
      <input type="radio" name="payment-method" value="bank-card" class="h-4 w-4 border-theme text-[rgb(var(--theme-primary))] focus:ring-[rgb(var(--theme-primary))]/35">
    </label>
  </div>

  <button type="button" class="text-scale-ui mx-auto mt-4 flex h-10 min-w-[6.5rem] items-center justify-center rounded-lg bg-[rgb(var(--theme-primary))] px-4 font-semibold text-theme-on-primary transition hover:opacity-90">
    下一步
  </button>
</section>
