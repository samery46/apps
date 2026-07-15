<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Illuminate\Database\Eloquent\Builder;
use Filament\Resources\Pages\ListRecords;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    // Query untuk memfilter/tidak menampilkan karyawan yang tidak aktif
    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()->where('is_aktif', true);
    }
}
