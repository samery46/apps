<?php

namespace App\Filament\Resources\UidsapResource\Pages;

use App\Filament\Resources\UidsapResource;
use App\Imports\UidsapImport;
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


    // Query untuk memfilter/tidak menampilkan perangkat yang tidak aktif
    // protected function getTableQuery(): Builder
    // {
    //     return parent::getTableQuery()->where('is_aktif', true);
    // }
}
