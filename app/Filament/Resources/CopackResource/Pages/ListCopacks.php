<?php

namespace App\Filament\Resources\CopackResource\Pages;

use App\Filament\Resources\CopackResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCopacks extends ListRecords
{
    protected static string $resource = CopackResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('New Stock Copack'),
        ];
    }
}
