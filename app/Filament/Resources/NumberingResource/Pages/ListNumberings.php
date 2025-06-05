<?php

namespace App\Filament\Resources\NumberingResource\Pages;

use App\Filament\Resources\NumberingResource;
use App\Models\Numbering;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListNumberings extends ListRecords
{
    protected static string $resource = NumberingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('New Penomoran Surat'),
        ];
    }

    // kode untuk memfilter list sesuai dengan akses plant masing2
    public function getTableQuery(): Builder
    {
        $query = Numbering::query();

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
