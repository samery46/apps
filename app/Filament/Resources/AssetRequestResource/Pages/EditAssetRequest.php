<?php

namespace App\Filament\Resources\AssetRequestResource\Pages;

use App\Filament\Resources\AssetRequestResource;
use App\Models\ApprovalSetting;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditAssetRequest extends EditRecord
{
    protected static string $resource = AssetRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];

        $record = $this->record;

        $currentApprovalLevel = $record->approval_level;
        $plantId = $record->plant_id;
        $userId = Auth::id();

        $expectedApprover = ApprovalSetting::where('plant_id', $plantId)
            ->where('level', $currentApprovalLevel + 1)
            ->where('user_id', $userId)
            ->first();

        if ($expectedApprover && $record->status === 'pending') {
            $actions[] = Actions\Action::make('approve')
                ->label('Approve')
                ->requiresConfirmation()
                ->action(function () use ($record) {
                    $record->approval_level++;

                    if ($record->approval_level >= 3) {
                        $record->status = 'approved';
                    }

                    $record->save();
                    $this->notify('success', 'Request approved successfully.');
                });

            $actions[] = Actions\Action::make('reject')
                ->label('Reject')
                ->requiresConfirmation()
                ->color('danger')
                ->action(function () use ($record) {
                    $record->status = 'rejected';
                    $record->save();
                    $this->notify('danger', 'Request rejected.');
                });
        }

        return $actions;




    }
}
