<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\View as BladeView;

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
        View::composer('components.dashboard-layout', function (BladeView $view): void {
            $user = auth()->user();

            if (! $user) {
                $view->with([
                    'headerNotifications' => collect(),
                    'unreadNotificationCount' => 0,
                ]);

                return;
            }

            $view->with([
                'headerNotifications' => $user->unreadNotifications()->latest()->limit(5)->get(),
                'unreadNotificationCount' => $user->unreadNotifications()->count(),
            ]);
        });
    }
}
