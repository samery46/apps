<?php

namespace App\Filament\Resources\KategoriResource\Pages;

use App\Filament\Resources\KategoriResource;
use App\Imports\KategoriImport;
use App\Models\Kategori;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Facades\Excel;

class ListKategoris extends ListRecords
{
    protected static string $resource = KategoriResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('New Kategori'),
            Action::make('importKategori')
                ->label('Import')
                ->icon('heroicon-s-arrow-down-tray')
                ->color('info')
                ->form([
                    FileUpload::make('attachment')
                        ->label('Upload Template Kategori')
                ])
                ->action(function (array $data) {
                    $file = public_path('storage/' . $data['attachment']);

                    try {
                        Excel::import(new KategoriImport, $file);
                        Notification::make()
                            ->title('Kategori Imported')
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Kategori Failed to Import')
                            ->danger()
                            ->send();
                    }
                }),
            Action::make('Template')
                ->url(route('import-kategori'))
                ->color('warning'),
        ];
    }

    // public function getTableQuery(): Builder
    // {

    // // $query = Network::query()->where('is_aktif', true); // Menambahkan filter agar hanya data aktif
    // $query = Kategori::query();
    // // Menerapkan filter berdasarkan akses plant_id pengguna
    // if (auth()->check() && auth()->user()->id === 1) {
    //     // Jika user adalah admin, tidak ada filter tambahan
    // } else {
    //     // Jika bukan admin, hanya ambil plant yang dimiliki oleh user
    //     $userPlantIds = auth()->user()->plants->pluck('id')->toArray();
    //     $query->whereIn('plant_id', $userPlantIds);
    // }

    // return $query;
    // }
}
