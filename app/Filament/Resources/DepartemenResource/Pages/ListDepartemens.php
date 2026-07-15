<?php

namespace App\Filament\Resources\DepartemenResource\Pages;

use App\Filament\Resources\DepartemenResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListDepartemens extends ListRecords
{
    protected static string $resource = DepartemenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
            ->label('New Departemen'),
        ];
    }

    // Query untuk memfilter/tidak menampilkan departemen dengan parent / departemen_id = null
    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()->whereNotNull('departemen_id');
    }
}
