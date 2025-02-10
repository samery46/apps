<?php

namespace App\Filament\Resources\AssetUsageResource\Pages;

use App\Filament\Resources\AssetUsageResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAssetUsages extends ListRecords
{
    protected static string $resource = AssetUsageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('New Asset Usage'),
        ];
    }
}
