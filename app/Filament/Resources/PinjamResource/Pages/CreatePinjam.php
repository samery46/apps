<?php

namespace App\Filament\Resources\PinjamResource\Pages;

use App\Filament\Resources\PinjamResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePinjam extends CreateRecord
{
    protected static string $resource = PinjamResource::class;


    // Digunakan untuk PHPMailer : AutoEmail setelah Create
    protected function afterCreate(): void
    {
        $this->record->created($this->record);
    }
}
