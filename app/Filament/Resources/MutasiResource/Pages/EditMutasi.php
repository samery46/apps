<?php

namespace App\Filament\Resources\MutasiResource\Pages;

use App\Filament\Resources\MutasiResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMutasi extends EditRecord
{
    protected static string $resource = MutasiResource::class;


    public function getTitle(): string
    {
        return 'Edit Mutasi Bank';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
