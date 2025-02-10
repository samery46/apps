<?php

namespace App\Filament\Resources\MutasiResource\Pages;

use App\Filament\Resources\MutasiResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMutasis extends ListRecords
{
    protected static string $resource = MutasiResource::class;

    public function getTitle(): string
    {
        return 'List Mutasi Bank';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('New Mutasi Bank'),
        ];
    }
}
