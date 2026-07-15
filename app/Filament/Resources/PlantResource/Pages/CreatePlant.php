<?php

namespace App\Filament\Resources\PlantResource\Pages;

use App\Filament\Resources\PlantResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePlant extends CreateRecord
{
    protected static string $resource = PlantResource::class;



    // Digunakan untuk PHPMailer : AutoEmail setelah Create
    protected function afterCreate(): void
    {
        // parent::afterCreate();
        // $this->record->afterCreate($this->record);

        // parent::afterCreate();
        $this->record->created($this->record);
    }
}
