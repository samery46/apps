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
use App\Models\User;
use Filament\Resources\Pages\ViewRecord;
use Filament\Resources\Resource;
use Filament\Forms;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Actions\BulkAction;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AssetRequestExport;
use App\Models\AssetRequest;
use Illuminate\Database\Eloquent\Builder;



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
                TextColumn::make('user.name')
                    ->label('Created By')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
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

                SelectFilter::make('plant_id')
                        ->label('Filter by Plant')
                        ->options(function () {
                            if (auth()->check() && auth()->user()->id === 1) {
                                // Admin dapat melihat semua plant
                                return AssetRequest::with('plant')
                                    ->get()
                                    ->sortBy(fn ($item) => $item->plant->kode)
                                    ->mapWithKeys(fn ($item) => [$item->plant_id => "{$item->plant->kode} - {$item->plant->nama}"])
                                    ->toArray();
                            } else {
                                return auth()->user()->plants
                                    ->sortBy('kode')
                                    ->mapWithKeys(fn ($plant) => [$plant->id => "{$plant->kode} - {$plant->nama}"])
                                    ->toArray();
                            }
                        }),

            ])
            ->bulkActions([
                BulkAction::make('export')
                    ->label('Export')
                    ->color('info')
                    ->action(function ($records) {
                        $recordIds = $records->pluck('id')->toArray();
                        $date = date('Y-m-d'); // Mendapatkan tanggal saat ini dalam format YYYY-MM-DD
                        $fileName = "AssetRequest-{$date}.xlsx"; // Menambahkan tanggal pada nama file
                        return Excel::download(new AssetRequestExport($recordIds), $fileName);
                    }),
            ]);

    }

    public function getTableQuery(): Builder
    {
        $query = AssetRequest::query();

        // Menerapkan filter berdasarkan akses plant_id pengguna
        if (auth()->check() && auth()->user()->id === 1) {
            // Jika user memiliki ID 1, dianggap sebagai admin
            // Tidak ada filter tambahan yang diterapkan karena admin bisa mengakses semua plant
        } else {
            // Jika bukan user dengan ID 1, ambil plant yang dimiliki oleh user
            $userPlantIds = auth()->user()->plants->pluck('id')->toArray();
            $query->whereIn('plant_id', $userPlantIds);
        }

        return $query;
    }

}
