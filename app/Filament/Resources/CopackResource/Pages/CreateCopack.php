<?php

namespace App\Filament\Resources\CopackResource\Pages;

use App\Filament\Resources\CopackResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCopack extends CreateRecord
{
    protected static string $resource = CopackResource::class;

    public function getTitle(): string
    {
        return 'Create Stock Copack';
    }
    public static function created($record)
    {
        return redirect()->route('filament.admin.resources.copacks.index'); // Redirect to the list page
    }
}
