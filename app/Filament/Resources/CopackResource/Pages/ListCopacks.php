<?php

namespace App\Filament\Resources\CopackResource\Pages;

use App\Filament\Resources\CopackResource;
use App\Models\Copack;
use Filament\Actions;
// use Filament\Forms\Components\Builder;
use Filament\Resources\Pages\ListRecords;
// use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListCopacks extends ListRecords
{
    protected static string $resource = CopackResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('New Stock Copack'),
        ];
    }

    public function getTableQuery(): Builder
    {
        $query = Copack::query();

        // Menerapkan filter berdasarkan akses plant_id pengguna
        if (auth()->check() && auth()->user()->id === 1) {
            // Jika user memiliki ID 1, dianggap sebagai admin
            // Tidak ada filter tambahan yang diterapkan karena admin bisa mengakses semua plant
        } else {
            // Jika bukan user dengan ID 1, ambil plant yang dimiliki oleh user
            $userPlantIds = auth()->user()->plants->pluck('id')->toArray();
            $query->whereIn('plant_id', $userPlantIds);
        }

        return $query;
    }
}
