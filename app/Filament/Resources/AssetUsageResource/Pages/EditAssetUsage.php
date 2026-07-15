<?php

namespace App\Filament\Resources\AssetUsageResource\Pages;

use App\Filament\Resources\AssetUsageResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAssetUsage extends EditRecord
{
    protected static string $resource = AssetUsageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
