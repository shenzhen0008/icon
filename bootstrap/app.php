<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withCommands([
        \App\Modules\Referral\Console\Commands\ProcessReferralCommissionCommand::class,
        \App\Modules\Settlement\Console\Commands\ProcessDailySettlementCommand::class,
    ])
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->encryptCookies(except: [
            'referral_invite_code',
        ]);

        $middleware->web(append: [
            \App\Modules\I18n\Http\Middleware\SetLocaleFromSessionMiddleware::class,
            \App\Modules\Referral\Http\Middleware\CaptureInviteCodeMiddleware::class,
            \App\Modules\ClientEnv\Http\Middleware\ClientEnvProbeMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
