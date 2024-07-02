<?php

namespace App\Filament\Resources\PerangkatResource\Pages;

use App\Filament\Resources\PerangkatResource;
use App\Imports\PerangkatsImport;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Facades\Excel;

class ListPerangkats extends ListRecords
{
    protected static string $resource = PerangkatResource::class;

    protected function getHeaderActions(): array
    {
        return [
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
                        Excel::import(new PerangkatsImport, $file);
                        Notification::make()
                            ->title('Perangkat Imported')
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Perangkats Failed to Import')
                            ->danger()
                            ->send();
                    }
                }),
            // Action::make('Download Template')
            //     ->url(route('export-categories'))
            //     ->color('warning'),
            Actions\CreateAction::make(),

        ];
    }

    // Query untuk memfilter/tidak menampilkan perangkat yang tidak aktif
    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()->where('is_aktif', true);
    }
}
