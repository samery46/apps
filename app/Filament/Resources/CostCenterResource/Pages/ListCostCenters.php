<?php

namespace App\Filament\Resources\CostCenterResource\Pages;

use App\Filament\Resources\CostCenterResource;
use App\Models\CostCenter;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListCostCenters extends ListRecords
{
    protected static string $resource = CostCenterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

public function getTableQuery(): Builder
    {

    // $query = Network::query()->where('is_aktif', true); // Menambahkan filter agar hanya data aktif
    $query = CostCenter::query();
    // Menerapkan filter berdasarkan akses plant_id pengguna
    if (auth()->check() && auth()->user()->id === 1) {
        // Jika user adalah admin, tidak ada filter tambahan
    } else {
        // Jika bukan admin, hanya ambil plant yang dimiliki oleh user
        $userPlantIds = auth()->user()->plants->pluck('id')->toArray();
        $query->whereIn('plant_id', $userPlantIds);
    }

    return $query;
    }

}
