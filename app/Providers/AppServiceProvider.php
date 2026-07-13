<?php

namespace App\Providers;

use App\Contracts\GoogleCalendarService as GoogleCalendarServiceContract;
use App\Services\GoogleCalendarService;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(GoogleCalendarServiceContract::class, GoogleCalendarService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('components.nav', function ($view): void {
            $user = auth()->user();

            $view->with(
                'hasUnreadNotifications',
                $user?->unreadNotifications()->exists() ?? false,
            );

            $view->with(
                'hasUnreadMessages',
                $user?->hasUnreadMessages() ?? false,
            );
        });
    }
}
