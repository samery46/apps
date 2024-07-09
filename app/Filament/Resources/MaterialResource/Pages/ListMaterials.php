<?php

namespace App\Filament\Resources\MaterialResource\Pages;

use App\Filament\Resources\MaterialResource;
use App\Imports\MaterialsImport;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Maatwebsite\Excel\Facades\Excel;

class ListMaterials extends ListRecords
{
    protected static string $resource = MaterialResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('New Material'),
            Action::make('importMaterials')
                ->label('Import')
                ->icon('heroicon-s-arrow-down-tray')
                ->color('info')
                ->form([
                    FileUpload::make('attachment')
                        ->label('Upload Template Material')
                ])
                ->action(function (array $data) {
                    $file = public_path('storage/' . $data['attachment']);

                    try {
                        Excel::import(new MaterialsImport, $file);
                        Notification::make()
                            ->title('Materials Imported')
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Materials Failed to Import')
                            ->danger()
                            ->send();
                    }
                }),
            Action::make('Template')
                ->url(route('import-material'))
                ->color('warning'),

        ];
    }
}
