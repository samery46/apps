<?php

namespace App\Filament\Resources\WorkflowConfigResource\Pages;

use App\Filament\Resources\WorkflowConfigResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWorkflowConfigs extends ListRecords
{
    protected static string $resource = WorkflowConfigResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
