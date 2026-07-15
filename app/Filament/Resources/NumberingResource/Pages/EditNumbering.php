<?php

namespace App\Filament\Resources\NumberingResource\Pages;

use App\Filament\Resources\NumberingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditNumbering extends EditRecord
{
    protected static string $resource = NumberingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
