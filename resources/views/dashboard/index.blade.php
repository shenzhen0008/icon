<!DOCTYPE html>
<html lang="en" data-theme="{{ config('themes.active') }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>Dashboard</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-theme text-theme">
    <x-layout.background-glow />
    <x-nav.top />

    <main class="mx-auto max-w-2xl px-6 pb-28 pt-8 md:pb-10">
        <h1 class="mb-2 text-scale-display font-semibold">用户中心</h1>
        <p class="text-theme-secondary">当前用户：{{ $user?->username }}</p>

        <div class="mt-6 flex gap-3">
            <a href="/sensitive" class="rounded bg-theme-secondary px-4 py-2">敏感页（需二次验密）</a>
            <form method="POST" action="/logout">
                @csrf
                <button class="rounded bg-[rgb(var(--theme-rose))] px-4 py-2 text-theme-secondary">退出登录</button>
            </form>
        </div>
    </main>

    <x-nav.mobile />
</body>
</html>
