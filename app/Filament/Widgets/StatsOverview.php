<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Plant;
use App\Models\Asset;
use App\Models\User;
use App\Models\AssetRequest;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $user_count = User::count();
        $plant_count = Plant::count();
        $assetRequest_count = AssetRequest::count();
        $asset_count = Asset::count();
        return [
            Stat::make('User', $user_count),
            Stat::make('Plant', $plant_count),
            Stat::make('Asset Request Number', $assetRequest_count),
        ];
    }
}
