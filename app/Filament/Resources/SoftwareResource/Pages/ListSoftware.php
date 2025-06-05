<?php

namespace App\Filament\Resources\SoftwareResource\Pages;

use App\Filament\Resources\SoftwareResource;
use App\Imports\SoftwareImport;
use App\Models\Software;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Facades\Excel;

class ListSoftware extends ListRecords
{
    protected static string $resource = SoftwareResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('New Software'),
            Action::make('importSoftware')
                ->label('Import')
                ->icon('heroicon-s-arrow-down-tray')
                ->color('info')
                ->form([
                    FileUpload::make('attachment')
                        ->label('Upload Template Software')
                ])
                ->action(function (array $data) {
                    $file = public_path('storage/' . $data['attachment']);

                    try {
                        Excel::import(new SoftwareImport, $file);
                        Notification::make()
                            ->title('Software Imported')
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Software Failed to Import')
                            ->danger()
                            ->send();
                    }
                }),
            Action
                ::make('Template')
                ->url(route('import-software'))
                ->color('warning'),
        ];
    }

    public function getTableQuery(): Builder
    {
    $query = Software::query()->where('is_aktif', true); // Menambahkan filter agar hanya data aktif

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
