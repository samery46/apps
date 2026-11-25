<?php

namespace App\Filament\Resources\ViewStockResource\Pages;

use App\Filament\Resources\ViewStockResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListViewStocks extends ListRecords
{
    protected static string $resource = ViewStockResource::class;

    public function getTableRecordKey($record): string
    {
        return (string) ($record->id ?? ($record->barang_id . '-' . $record->plant_id));
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
