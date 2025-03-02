<?php

namespace App\Filament\Resources\PinjamResource\Pages;

use App\Filament\Resources\PinjamResource;
use App\Models\Pinjam;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;

class CreatePinjam extends CreateRecord
{
    protected static string $resource = PinjamResource::class;

    // Digunakan untuk PHPMailer : AutoEmail setelah Create
    protected function afterCreate(): void
    {
        $this->record->created($this->record);
    }
}
