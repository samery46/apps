<?php

namespace App\Filament\Resources\KaryawanResource\Pages;

use App\Filament\Resources\KaryawanResource;
use App\Imports\KaryawansImport;
use App\Models\Karyawan;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Facades\Excel;

class ListKaryawans extends ListRecords
{
    protected static string $resource = KaryawanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('New Karyawan'),
            Action::make('importProducts')
                ->label('Import')
                ->icon('heroicon-s-arrow-down-tray')
                ->color('info')
                ->form([
                    FileUpload::make('attachment')
                        ->label('Upload Template')
                ])
                ->action(function (array $data) {
                    $file = public_path('storage/' . $data['attachment']);

                    try {
                        Excel::import(new KaryawansImport, $file);
                        Notification::make()
                            ->title('Karyawan Imported')
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Karyawan Failed to Import')
                            ->danger()
                            ->send();
                    }
                }),
            Action::make('Template')
                ->url(route('import-karyawans'))
                ->color('warning'),
        ];
    }

    // Query untuk memfilter/tidak menampilkan karyawan yang tidak aktif
    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()->where('is_aktif', true);
    }

    // public function getTableQuery(): Builder
    // {
    //     $query = Karyawan::query()->where('is_aktif', true);

    //     // Menerapkan filter berdasarkan akses plant_id pengguna
    //     if (auth()->check() && auth()->user()->id === 1) {
    //         // Jika user memiliki ID 1, dianggap sebagai admin
    //         // Tidak ada filter tambahan yang diterapkan karena admin bisa mengakses semua plant
    //     } else {
    //         // Jika bukan user dengan ID 1, ambil plant yang dimiliki oleh user
    //         $userPlantIds = auth()->user()->plants->pluck('id')->toArray();
    //         $query->whereIn('plant_id', $userPlantIds);
    //     }

    //     return $query;
    // }
}
