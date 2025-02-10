<?php

namespace App\Filament\Resources\NumberingResource\Pages;

use App\Filament\Resources\NumberingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListNumberings extends ListRecords
{
    protected static string $resource = NumberingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('New Penomoran Surat'),
        ];
    }
}
