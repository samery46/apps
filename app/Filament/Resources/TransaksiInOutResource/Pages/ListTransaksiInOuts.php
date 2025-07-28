<?php

namespace App\Filament\Resources\TransaksiInOutResource\Pages;

use App\Filament\Resources\TransaksiInOutResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTransaksiInOuts extends ListRecords
{
    protected static string $resource = TransaksiInOutResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
