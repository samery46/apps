<?php

namespace App\Filament\Resources\UidsapResource\Pages;

use App\Filament\Resources\UidsapResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUidsap extends EditRecord
{
    protected static string $resource = UidsapResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
