<?php

namespace App\Filament\Resources\ViewStockResource\Pages;

use App\Filament\Resources\ViewStockResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditViewStock extends EditRecord
{
    protected static string $resource = ViewStockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
