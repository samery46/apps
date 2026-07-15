<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Support\ServiceProvider;
// use Filament\Panel;
use Filament\Facades\Filament;
use App\Filament\Panel;


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
        // Setel locale Carbon ke Indonesia
        Carbon::setLocale('id');
        Panel::make()
            ->login()
            ->registration()
            ->passwordReset()
            ->emailVerification()
            ->profile();
    }
}
