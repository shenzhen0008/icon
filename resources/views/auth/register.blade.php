<!DOCTYPE html>
<html lang="en" data-theme="{{ config('themes.active') }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>Activate Account</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-theme text-theme">
    <x-layout.background-glow />
    <x-nav.top />

    <main class="mx-auto max-w-md px-6 pb-28 pt-8 md:pb-10">
        <h1 class="mb-2 text-2xl font-semibold">设置密码激活账号</h1>
        <p class="mb-6 text-sm text-theme-secondary">临时用户名：{{ session(config('user.temp_username_session_key')) }}</p>

        <form method="POST" action="/register" class="space-y-4 rounded-lg border border-theme bg-theme-secondary p-4">
            @csrf

            <div>
                <label class="mb-1 block text-sm" for="password">密码</label>
                <input id="password" type="password" name="password" class="w-full rounded border border-theme bg-theme-secondary p-2" required>
                @error('password')<p class="mt-1 text-sm text-[rgb(var(--theme-rose))]">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="mb-1 block text-sm" for="password_confirmation">确认密码</label>
                <input id="password_confirmation" type="password" name="password_confirmation" class="w-full rounded border border-theme bg-theme-secondary p-2" required>
            </div>

            <button class="w-full rounded bg-[rgb(var(--theme-primary))] px-4 py-2 font-medium text-theme-secondary">激活账号</button>
        </form>
    </main>

    <x-nav.mobile />
</body>
</html>
