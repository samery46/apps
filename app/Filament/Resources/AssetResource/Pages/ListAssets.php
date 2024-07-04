<?php

namespace App\Filament\Resources\AssetResource\Pages;

use App\Filament\Resources\AssetResource;
use App\Imports\AssetsImport;
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
}
