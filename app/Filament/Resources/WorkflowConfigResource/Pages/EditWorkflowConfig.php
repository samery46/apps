<?php

namespace App\Filament\Resources\WorkflowConfigResource\Pages;

use App\Filament\Resources\WorkflowConfigResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWorkflowConfig extends EditRecord
{
    protected static string $resource = WorkflowConfigResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
