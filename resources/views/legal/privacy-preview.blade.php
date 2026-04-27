<!doctype html>
<html lang="{{ app()->getLocale() }}" data-theme="{{ config('themes.active') }}">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
  <title>{{ $document_title ?? 'Privacy Policy' }} | {{ config('app.name') }}</title>
  <x-meta.favicons />
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <style>
    .legal-preview-content > * + * {
      margin-top: 1rem;
    }

    .legal-preview-content h1,
    .legal-preview-content h2,
    .legal-preview-content h3 {
      margin-top: 1.75rem;
      margin-bottom: 0.75rem;
      font-weight: 700;
      line-height: 1.3;
      color: #f8fafc;
    }

    .legal-preview-content h1 {
      font-size: 1.75rem;
    }

    .legal-preview-content h2 {
      border-left: 0.25rem solid #3b82f6;
      padding-left: 0.75rem;
      font-size: 1.5rem;
    }

    .legal-preview-content p {
      line-height: 1.9;
      color: #dbe7ff;
    }

    .legal-preview-content ol,
    .legal-preview-content ul {
      margin-left: 1.5rem;
      color: #dbe7ff;
    }

    .legal-preview-content li + li {
      margin-top: 0.5rem;
    }
  </style>
</head>
<body class="min-h-screen bg-[#060f2f] text-white">
  <main class="mx-auto w-full max-w-4xl px-4 py-8 md:px-8 md:py-12">
    <section class="rounded-2xl border border-[rgba(79,115,255,0.28)] bg-[rgba(7,18,52,0.92)] p-5 shadow-[0_0_0_1px_rgba(59,130,246,0.08),0_20px_55px_rgba(2,6,23,0.45)] md:p-8">
      <h1 class="text-center text-3xl font-semibold tracking-[0.04em] text-slate-50 md:text-5xl">{{ $document_title ?? 'Privacy Policy' }}</h1>

      <div class="mt-6 border-t border-[rgba(148,163,184,0.25)] pt-5 text-center text-sm text-slate-300">
        <p>
          <span class="font-semibold text-slate-200">Last Updated:</span>
          {{ $document_updated_at }}
          <span class="mx-2 text-slate-500">|</span>
          <span class="font-semibold text-slate-200">Language:</span>
          {{ strtoupper($document_resolved_locale) === 'EN' ? 'English' : strtoupper($document_resolved_locale) }}
        </p>
      </div>

      <div class="mt-6 border-t border-[rgba(148,163,184,0.2)] pt-6 legal-preview-content">
        {!! $document_html !!}
      </div>
    </section>
  </main>
</body>
</html>
