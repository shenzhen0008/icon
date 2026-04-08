<!DOCTYPE html>
<html lang="en" data-theme="{{ config('themes.active') }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>Confirm Password</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-theme text-theme">
    <x-nav.top />

    <main class="mx-auto max-w-md px-6 pb-28 pt-8 md:pb-10">
        <h1 class="mb-6 text-2xl font-semibold">确认密码</h1>

        <form method="POST" action="/confirm-password" class="space-y-4 rounded-lg border border-theme bg-theme-secondary p-4">
            @csrf

            <div>
                <label class="mb-1 block text-sm" for="password">密码</label>
                <input id="password" type="password" name="password" class="w-full rounded border border-theme bg-theme-secondary p-2" required>
                @error('password')<p class="mt-1 text-sm text-[rgb(var(--theme-rose))]">{{ $message }}</p>@enderror
            </div>

            <button class="w-full rounded bg-[rgb(var(--theme-primary))] px-4 py-2 font-medium text-theme-secondary">确认</button>
        </form>
    </main>

    <x-nav.mobile />
</body>
</html>
