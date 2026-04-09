<?php

namespace App\Providers;

use App\Http\Responses\LoginResponse;
use App\Models\User;
use App\Observers\UserObserver;
use Filament\Auth\Http\Responses\Contracts\LoginResponse as LoginResponseContract;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(LoginResponseContract::class, LoginResponse::class);
    }

    public function boot(): void
    {
        Gate::before(function ($user, $ability) {
            if ($user->hasRole('super_admin')) {
                return true;
            }
            return null;
        });

        User::observe(UserObserver::class);
    }
}
