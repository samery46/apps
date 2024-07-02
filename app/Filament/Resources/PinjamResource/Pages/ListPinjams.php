<?php

namespace App\Filament\Resources\PinjamResource\Pages;

use App\Filament\Resources\PinjamResource;
use App\Imports\PinjamsImport;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Facades\Excel;

class ListPinjams extends ListRecords
{
    protected static string $resource = PinjamResource::class;

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
                        Excel::import(new PinjamsImport, $file);
                        Notification::make()
                            ->title('Pinjam Imported')
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Pinjams Failed to Import')
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


    // Query untuk memfilter/tidak menampilkan transaksi peminjaman yang sudah complete
    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()
            // ->where('is_complete', false)
            // ->orderBy('is_complete', 'desc');
        ->orderBy('created_at', 'desc');
    }
}
