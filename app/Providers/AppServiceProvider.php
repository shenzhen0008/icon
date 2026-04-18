<?php

namespace App\Providers;

use App\Models\User;
use App\Modules\Position\Models\Position;
use App\Policies\PositionPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Position::class, PositionPolicy::class);
        Gate::define('access-admin', fn (User $user): bool => (bool) $user->is_admin);

        RateLimiter::for('register-pin', function (Request $request): Limit {
            $scope = (string) ($request->session()->get('temp_username') ?? 'guest');

            return Limit::perMinute(8)->by($scope.'|'.$request->ip());
        });
    }
}
