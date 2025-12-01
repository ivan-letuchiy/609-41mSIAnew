<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate; // Добавили фасад Gate
use App\Models\User; // Добавили модель User

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
        // Правило: создавать дома может только админ
        Gate::define('create-house', function (User $user) {
            return $user->role === 'admin';
        });

        // Правило: создавать собрания может только админ
        Gate::define('create-meeting', function (User $user) {
            return $user->role === 'admin';
        });
    }
}
