<section class="rounded-2xl border border-white/10 bg-slate-900/70 p-5">
  <h2 class="text-base font-semibold">账号信息</h2>

  <div class="mt-4 rounded-xl border border-white/10 bg-slate-950/60 p-4">
    <p class="text-xs text-slate-400">{{ $profile['label'] }}</p>
    <p class="mt-1 break-all text-lg font-semibold tracking-wider text-cyan-200">{{ $profile['id'] }}</p>
  </div>

  <dl class="mt-4 grid grid-cols-3 gap-3 text-sm">
    <div class="rounded-lg border border-white/10 bg-slate-950/40 p-3">
      <dt class="text-slate-400">账号状态</dt>
      <dd class="mt-1 text-slate-100">{{ $profile['status'] }}</dd>
    </div>
    <div class="rounded-lg border border-white/10 bg-slate-950/40 p-3">
      <dt class="text-slate-400">创建时间</dt>
      <dd class="mt-1 text-slate-100">{{ $profile['created_at'] }}</dd>
    </div>
    @if (! $isGuest)
      <div class="rounded-xl border border-rose-500/20 bg-rose-500/10 p-3">
        <form method="POST" action="/logout">
          @csrf
          <button class="rounded-lg bg-rose-400 px-4 py-2 text-sm font-semibold text-slate-950">退出登录</button>
        </form>
      </div>
    @endif
  </dl>
</section>
