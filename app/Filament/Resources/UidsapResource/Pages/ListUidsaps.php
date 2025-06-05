<?php

namespace App\Filament\Resources\UidsapResource\Pages;

use App\Filament\Resources\UidsapResource;
use App\Imports\UidsapImport;
use App\Models\Uidsap;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Forms\Components\FileUpload;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Facades\Excel;

class ListUidsaps extends ListRecords
{
    protected static string $resource = UidsapResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('New UID SAP'),
            Action::make('importUidsap')
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
                        Excel::import(new UidsapImport, $file);
                        Notification::make()
                            ->title('UID SAP Imported')
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('UID SAP Failed to Import')
                            ->danger()
                            ->send();
                    }
                }),
            Action::make('Template')
                ->url(route('import-uidsap'))
                ->color('warning'),
        ];
    }


    public function getTableQuery(): Builder
    {
        $query = Uidsap::query(); // Menggunakan UIDSAP sebagai query utama

        if (auth()->check() && auth()->user()->id === 1) {
            // Admin melihat semua data, tanpa filter tambahan
        } else {
            // Filter berdasarkan plant_id yang dimiliki user melalui tabel Karyawan
            $userPlantIds = auth()->user()->plants->pluck('id')->toArray();

            // Pastikan hanya UIDSAP dengan karyawan terkait plant_id milik user yang tampil
            $query->whereHas('karyawan', function ($q) use ($userPlantIds) {
                $q->whereIn('plant_id', $userPlantIds);
            });
        }

        return $query;
    }
}
