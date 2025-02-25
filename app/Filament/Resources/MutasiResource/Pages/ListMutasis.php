<?php

namespace App\Filament\Resources\MutasiResource\Pages;

use App\Filament\Resources\MutasiResource;
use App\Models\Mutasi;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListMutasis extends ListRecords
{
    protected static string $resource = MutasiResource::class;

    public function getTitle(): string
    {
        return 'List Mutasi Bank';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('New Mutasi Bank'),
        ];
    }
    public function getTableQuery(): Builder
    {
        $query = Mutasi::query();

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
