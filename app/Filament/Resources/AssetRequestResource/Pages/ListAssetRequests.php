<?php

namespace App\Filament\Resources\AssetRequestResource\Pages;

use App\Filament\Resources\AssetRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Filters\SelectFilter;
use Filament\Notifications\Notification;

use Filament\Resources\Pages\ViewRecord;
use Filament\Resources\Resource;
use Filament\Forms;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Carbon\Carbon;

class ListAssetRequests extends ListRecords
{
    protected static string $resource = AssetRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function table(Table $table): Table
     {
        return $table
            ->columns([
                TextColumn::make('status')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(function ($state) {
                        return match ($state) {
                            'pending' => 'Pending',
                            'approved' => 'Approved',
                            'rejected' => 'Rejected',
                            default => ucfirst($state),
                        };
                    }),
                TextColumn::make('document_number')
                    ->label('No. Document')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('plant.kode')
                    ->label('Plant')
                    ->sortable()
                    ->searchable()
                    ->getStateUsing(function ($record) {
                        return $record->plant->kode . ' - ' . $record->plant->nama;
                    }),
                TextColumn::make('assetGroup.name')
                    ->label('Group')
                    ->sortable()
                    ->searchable()
                    ->formatStateUsing(fn ($state, $record) => $record->assetGroup?->asset_group . ' - ' . $record->assetGroup?->name),
                TextColumn::make('fixed_asset_number')
                    ->label('Nomor Asset')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('costCenter.cost_center')
                    ->label('Cost Center')
                    ->sortable()
                    ->searchable()
                                        ->formatStateUsing(fn ($state, $record) => $record->costCenter?->cost_center . ' - ' . $record->costCenter?->name),
                TextColumn::make('item_name')
                    ->label('Nama Barang')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('condition')
                    ->label('Status')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('quantity')
                    ->label('Qty')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('year_of_manufacture')
                    ->label('Tahun')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('supplier')
                    ->label('Pemasok')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('country_of_origin')
                    ->label('Asal Negara')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('expected_usage')
                    ->label('Est Penggunaan')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('expected_arrival')
                    ->label('Est Kedatangan')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('location')
                    ->label('Lokasi')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('approvals')
                ->label('Approval History')
                ->getStateUsing(function ($record) {
                    return $record->approvals->map(function ($a) {
                        $status = ucfirst($a->status);

                        // Jika masih pending, tampilkan "Pending"
                        if ($a->status === 'pending') {
                            return "{$a->user->name}: Pending";
                        }

                        // Jika sudah approve/reject, tampilkan tanggal
                        $date = $a->approved_at
                            ? Carbon::parse($a->approved_at)->format('Y-m-d')
                            : '-';

                        return "{$a->user->name}: {$status} ({$date})";

                    })->implode(', ');
                })
                ->toggleable(isToggledHiddenByDefault: true),

            ])
            ->actions([
                Action::make('approve')
                    ->label('Approve')
                    ->requiresConfirmation()
                    ->color('success')
                    ->visible(fn ($record) =>
                        $record->status !== 'rejected' &&
                        $record->isUserCurrentApprover(auth()->user())
                    )
                    ->action(function ($record) {
                        $user = auth()->user();
                        $currentLevel = (int) $record->currentApprovalLevel();

                        try {
                            $record->approve($currentLevel, $user->id);
                            Notification::make()
                                ->title('Approved successfully!')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Approval failed: ' . $e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                Action::make('reject')
                    ->label('Reject')
                    ->color('danger')
                    ->form([
                        Textarea::make('note')->label('Rejection Note')->required(),
                    ])
                    ->visible(fn ($record) =>
                        $record->status !== 'rejected' &&
                        $record->isUserCurrentApprover(auth()->user())
                    )
                    ->action(function ($record, array $data) {
                        $approval = $record->approvals()
                            ->where('user_id', auth()->id())
                            ->where('status', 'pending')
                            ->first();

                        if ($approval) {
                            $approval->update([
                                'status' => 'rejected',
                                'approved_at' => now(),
                                'note' => $data['note'],
                            ]);

                            $record->update(['status' => 'rejected']);
                        }

                        Notification::make()
                            ->title('Rejected successfully!')
                            ->danger()
                            ->send();
                    }),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'in_review' => 'In Review',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),
            ]);
    }
}
