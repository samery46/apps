<?php

namespace App\Filament\Resources\EpaymentResource\Pages;

use App\Filament\Resources\EpaymentResource;
use App\Imports\EpaymentImport;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Maatwebsite\Excel\Facades\Excel;

class ListEpayments extends ListRecords
{
    protected static string $resource = EpaymentResource::class;

    protected function getHeaderActions(): array
    {
        // return [
        //     Actions\CreateAction::make(),
        // ];

        return [
            Actions\CreateAction::make()
                ->label('New E-Payment'),
            Action::make('importEpayments')
                ->label('Import')
                ->icon('heroicon-s-arrow-down-tray')
                ->color('info')
                ->form([
                    FileUpload::make('attachment')
                        ->label('Upload Template E-Payment')
                ])
                ->action(function (array $data) {
                    $file = public_path('storage/' . $data['attachment']);

                    try {
                        Excel::import(new EpaymentImport, $file);
                        Notification::make()
                            ->title('E-Payments Imported')
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('E-Payments Failed to Import')
                            ->danger()
                            ->send();
                    }
                }),
            Action::make('Template')
                ->url(route('import-epayment'))
                ->color('warning'),

        ];


    }
}
