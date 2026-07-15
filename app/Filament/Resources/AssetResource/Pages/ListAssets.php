<?php

namespace App\Filament\Resources\AssetResource\Pages;

use App\Filament\Resources\AssetResource;
use App\Imports\AssetsImport;
use App\Models\Asset;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Facades\Excel;

class ListAssets extends ListRecords
{
    protected static string $resource = AssetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('New Asset'),
            Action::make('importAssets')
                ->label('Import')
                ->icon('heroicon-s-arrow-down-tray')
                ->color('info')
                ->form([
                    FileUpload::make('attachment')
                        ->label('Upload Template Import')
                ])
                ->action(function (array $data) {
                    $file = public_path('storage/' . $data['attachment']);

                    try {
                        Excel::import(new AssetsImport, $file);
                        Notification::make()
                            ->title('Assets Imported')
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Assets Failed to Import')
                            ->danger()
                            ->send();
                    }
                }),
            Action::make('Template')
                ->url(route('import-assets'))
                ->color('warning'),
        ];
    }


    public function getTableQuery(): Builder
    {
    $query = Asset::query()->where('is_aktif', true); // Menambahkan filter agar hanya data aktif

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
