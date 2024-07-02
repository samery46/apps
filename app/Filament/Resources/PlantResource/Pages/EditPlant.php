<?php

namespace App\Filament\Resources\PlantResource\Pages;

use App\Filament\Resources\PlantResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPlant extends EditRecord
{
    protected static string $resource = PlantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }


    // Digunakan untuk PHPMailer : AutoEmail setelah update
    protected function afterSave(): void
    {
        // parent::afterSave();
        // $this->record->afterSave($this->record);

        // parent::updated();
        $this->record->updated($this->record);
    }
}
