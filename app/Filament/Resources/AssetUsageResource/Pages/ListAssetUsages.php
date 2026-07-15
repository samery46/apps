<?php

namespace App\Filament\Resources\AssetUsageResource\Pages;

use App\Filament\Resources\AssetUsageResource;
use App\Models\Asset;
use App\Models\AssetUsage;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

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

    public function getTableQuery(): Builder
{
    $query = AssetUsage::query(); // Query utama dari ServiceRequest

    if (auth()->check() && auth()->user()->id === 1) {
        // Admin melihat semua data, tanpa filter tambahan
    } else {
        // Ambil plant_id yang dimiliki user
        $userPlantIds = auth()->user()->plants->pluck('id')->toArray();

        // Pastikan hanya ServiceRequest dengan asset yang terkait plant_id user yang tampil
        $query->whereHas('asset', function ($q) use ($userPlantIds) {
            $q->whereIn('plant_id', $userPlantIds);
        });
    }

    return $query;
}



}
