<?php

namespace App\Providers;

use Filament\Facades\Filament;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Illuminate\Support\ServiceProvider;
use App\Filament\Resources\UserResource;
use BezhanSalleh\FilamentShield\Resources\RoleResource;

class FilamentServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Filament::serving(function () {
        //     Filament::registerNavigationGroups([
        //         NavigationGroup::make('User Management')
        //             ->items([
        //                 NavigationItem::make('Users')
        //                     ->icon('heroicon-o-users')
        //                     ->url(UserResource::getUrl('index'))
        //                     ->sort(1),

        //                 NavigationItem::make('Roles')
        //                     ->icon('heroicon-o-shield-check')
        //                     ->url(RoleResource::getUrl('index'))
        //                     ->sort(2),

        //                 // Tambahkan item lainnya di sini
        //             ]),
        //     ]);
        // });

        // Filament::registerNavigationItems([
        //     NavigationItem::make('Users')
        //         ->group('User Management')
        //         ->icon('heroicon-o-users')
        //         ->url(UserResource::getUrl('index'))
        //         ->sort(1),
        //     NavigationItem::make('Roles')
        //         ->group('User Management')
        //         ->icon('heroicon-o-shield-check')
        //         ->url(RoleResource::getUrl('index'))
        //         ->sort(2),
        //     // Tambahkan item lainnya di sini
        // ]);
    }
}
