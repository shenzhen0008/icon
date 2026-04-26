<!doctype html>
<html lang="{{ __('pages/trade-records.html_lang') }}" data-theme="{{ config('themes.active') }}">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
  <title>{{ __('pages/trade-records.meta_title', ['app_name' => config('app.name')]) }}</title>
  <x-meta.favicons />
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-theme text-theme">
  <x-layout.background-glow />
  <x-nav.top />
  @php
    $localeQuery = 'locale='.urlencode(app()->getLocale());
    $modeLabel = $mode === 'demo'
      ? __('pages/trade-records.mode.demo')
      : __('pages/trade-records.mode.live');
    $middleEllipsis = static function (?string $value, int $head = 3, int $tail = 3): string {
      $text = trim((string) $value);
      if ($text === '' || $text === '--') {
        return '--';
      }

      $length = mb_strlen($text);
      if ($length <= ($head + $tail + 4)) {
        return $text;
      }

      return mb_substr($text, 0, $head).'....'.mb_substr($text, -$tail);
    };
    $formatMobileTime = static function (?string $value): string {
      $text = trim((string) $value);
      if ($text === '' || $text === '--') {
        return '--';
      }

      try {
        return \Illuminate\Support\Carbon::parse($text)->format('m-d H:i');
      } catch (\Throwable $e) {
        $length = mb_strlen($text);
        if ($length <= 11) {
          return $text;
        }

        return mb_substr($text, 0, 5).'..'.mb_substr($text, -5);
      }
    };
    $eventTypeLabels = [
      'purchase_debit' => __('pages/trade-records.event_type.purchase_debit'),
      'principal_return_credit' => __('pages/trade-records.event_type.principal_return_credit'),
      'withdrawal_debit' => __('pages/trade-records.event_type.withdrawal_debit'),
      'withdrawal_refund' => __('pages/trade-records.event_type.withdrawal_refund'),
    ];
    $statusLabels = [
      'completed' => __('pages/trade-records.status.completed'),
      'pending' => __('pages/trade-records.status.pending'),
      'approved' => __('pages/trade-records.status.approved'),
      'rejected' => __('pages/trade-records.status.rejected'),
      'refunded' => __('pages/trade-records.status.refunded'),
      'cancelled' => __('pages/trade-records.status.cancelled'),
    ];
  @endphp

  <main class="mx-auto w-full max-w-4xl px-4 pb-28 pt-8 md:pb-10">
    <section class="rounded-2xl border border-theme bg-theme-card p-5">
      <div class="flex items-center justify-between gap-3">
        <div>
          <h1 class="text-scale-title font-semibold text-theme">{{ __('pages/trade-records.title') }}</h1>
          <p class="mt-2 text-scale-body text-theme-secondary">{{ __('pages/trade-records.intro', ['mode' => $modeLabel]) }}</p>
        </div>
        <a href="/?{{ $localeQuery }}" class="rounded-lg border border-theme px-3 py-2 text-scale-body text-theme-secondary hover:text-theme">{{ __('pages/trade-records.back_home') }}</a>
      </div>
    </section>

    <section class="mt-5 overflow-hidden rounded-2xl border border-theme bg-theme-card">
      <table class="min-w-full table-fixed text-scale-body">
        <thead class="bg-theme-secondary/80 text-theme-secondary">
          <tr>
            <th class="w-[18%] px-2 py-3 text-left font-medium md:px-4">{{ __('pages/trade-records.columns.type') }}</th>
            <th class="w-[28%] px-2 py-3 text-left font-medium md:px-4">{{ __('pages/trade-records.columns.content') }}</th>
            <th class="w-[18%] px-2 py-3 text-right font-medium md:px-4">{{ __('pages/trade-records.columns.amount_usdt') }}</th>
            <th class="w-[16%] px-2 py-3 text-center font-medium md:px-4">{{ __('pages/trade-records.columns.status') }}</th>
            <th class="w-[20%] px-2 py-3 text-right font-medium whitespace-nowrap md:px-4">
              <span class="md:hidden">{{ __('pages/trade-records.columns.time_mobile') }}</span>
              <span class="hidden md:inline">{{ __('pages/trade-records.columns.time') }}</span>
            </th>
          </tr>
        </thead>
        <tbody class="bg-theme-secondary/40">
          @forelse ($records as $record)
            <tr class="border-t border-theme">
              <td class="px-2 py-3 text-theme md:px-4">{{ $eventTypeLabels[$record['event_type'] ?? ''] ?? ($record['event_type'] ?? '--') }}</td>
              <td class="px-2 py-3 text-theme md:px-4" title="{{ $record['title'] ?? '--' }}">
                <span class="md:hidden">{{ $middleEllipsis($record['title'] ?? '--') }}</span>
                <span class="hidden md:inline">{{ $record['title'] ?? '--' }}</span>
              </td>
              <td class="px-2 py-3 text-right text-theme md:px-4">{{ $record['amount'] ?? '0.00' }}</td>
              <td class="px-2 py-3 text-center text-theme-secondary md:px-4">{{ $statusLabels[$record['status'] ?? ''] ?? ($record['status'] ?? '--') }}</td>
              <td class="px-2 py-3 text-right text-theme-secondary whitespace-nowrap overflow-hidden text-ellipsis md:px-4" title="{{ $record['occurred_at'] ?? '--' }}">
                <span class="md:hidden">{{ $formatMobileTime($record['occurred_at'] ?? '--') }}</span>
                <span class="hidden md:inline">{{ $record['occurred_at'] ?? '--' }}</span>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="px-4 py-6 text-center text-theme-secondary">{{ __('pages/trade-records.empty') }}</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </section>

    @if ($pagination?->hasPages())
      <div class="mt-4">{{ $pagination->links() }}</div>
    @endif
  </main>

  <x-nav.mobile />
</body>
</html>
