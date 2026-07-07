<?php

namespace App\Providers;

use App\Contracts\GoogleCalendarService as GoogleCalendarServiceContract;
use App\Services\GoogleCalendarService;
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
        //
    }
}
