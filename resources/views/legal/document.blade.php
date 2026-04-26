<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{{ $document_title }} | {{ config('app.name') }}</title>
</head>
<body>
  @php
    $localeQuery = 'locale='.urlencode(app()->getLocale());
  @endphp
  <main>
    <p>
      <a href="/?{{ $localeQuery }}">{{ config('app.name') }}</a>
      |
      <a href="/privacy?{{ $localeQuery }}">{{ __('pages/legal.privacy_title') }}</a>
      |
      <a href="/terms?{{ $localeQuery }}">{{ __('pages/legal.terms_title') }}</a>
    </p>
    <h1>{{ $document_title }}</h1>
    <p>{{ __('pages/legal.last_updated') }}: {{ $document_updated_at }}</p>
    <p>{{ __('pages/legal.display_locale') }}: {{ $document_resolved_locale }}</p>
    <hr>
    {!! $document_html !!}
  </main>
</body>
</html>
