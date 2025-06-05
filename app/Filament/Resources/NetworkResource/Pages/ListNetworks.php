<?php

namespace App\Filament\Resources\NetworkResource\Pages;

use App\Filament\Resources\NetworkResource;
use App\Imports\NetworkImport;
use App\Models\Network;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Facades\Excel;

class ListNetworks extends ListRecords
{
    protected static string $resource = NetworkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('New Network'),
            Action::make('importNetwork')
                ->label('Import')
                ->icon('heroicon-s-arrow-down-tray')
                ->color('info')
                ->form([
                    FileUpload::make('attachment')
                        ->label('Upload Template Network')
                ])
                ->action(function (array $data) {
                    $file = public_path('storage/' . $data['attachment']);

                    try {
                        Excel::import(new NetworkImport, $file);
                        Notification::make()
                            ->title('Network Imported')
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Network Failed to Import')
                            ->danger()
                            ->send();
                    }
                }),
            Action::make('Template')
                ->url(route('import-network'))
                ->color('warning'),
        ];
    }

        public function getTableQuery(): Builder
    {

    // $query = Network::query()->where('is_aktif', true); // Menambahkan filter agar hanya data aktif
    $query = Network::query();
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
