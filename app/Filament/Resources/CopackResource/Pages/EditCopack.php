<?php

namespace App\Filament\Resources\CopackResource\Pages;

use App\Filament\Resources\CopackResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCopack extends EditRecord
{
    protected static string $resource = CopackResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    public static function updated($record)
    {
        return redirect()->route('filament.admin.resources.copacks.index'); // Redirect to the list page
    }
}
