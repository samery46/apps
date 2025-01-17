<?php

namespace App\Filament\Resources\PinjamResource\Pages;

use App\Filament\Resources\PinjamResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPinjam extends EditRecord
{
    protected static string $resource = PinjamResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    // Digunakan untuk PHPMailer : AutoEmail setelah update
    protected function afterSave(): void
    {
        $this->record->updated($this->record);
    }
}
