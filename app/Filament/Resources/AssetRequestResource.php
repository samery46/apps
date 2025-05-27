<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AssetRequestResource\Pages;
use App\Filament\Resources\AssetRequestResource\RelationManagers;
use App\Models\AssetRequest;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\Plant;
use App\Models\CostCenter;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\ViewAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Section;
use App\Models\ApprovalSetting;
use App\Models\AssetGroup;
use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ViewRecord;

class AssetRequestResource extends Resource
{
    protected static ?string $model = AssetRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Plant')
                ->description('Detail Plant')
                ->schema([
                    Forms\Components\TextInput::make('document_number')
                        ->label('Nomor Transaksi')
                        // ->searchable()
                        ->disabled()
                        ->columnSpan(2)
                        ->default(fn () => 'Diisi otomatis saat disimpan'),
                    Forms\Components\Select::make('plant_id')
                        ->label('Plant')
                        ->placeholder('Cari kode atau nama plant')
                        ->required()
                        ->columnSpan(3)
                        ->searchable()
                        ->preload()
                        ->getSearchResultsUsing(function (string $search) {
                            // return Plant::where('nama', 'like', "%{$search}%")
                            return Plant::where(function ($query) use ($search) {
                                $query->where('nama', 'like', "%{$search}%")
                                    ->orWhere('kode', 'like', "%{$search}%"); // Tambahkan pencarian juga berdasarkan 'kode'
                            })
                                ->get(['kode', 'nama', 'id']) // Ambil kolom kode, nama, dan id
                                ->mapWithKeys(function ($plant) {
                                    return [$plant->id => $plant->kode . ' - TSP ' . $plant->nama]; // Format opsi dengan kode - nama
                                })
                                ->toArray();
                        })
                        ->getOptionLabelUsing(function ($value) {
                            $plant = Plant::find($value);
                            return $plant ? $plant->kode . ' - TSP ' . $plant->nama : null; // Format label dengan kode - nama
                        })
                        ->disabled(fn ($record) => $record && $record->status === 'approved'),
                ])->columns(8),

            Forms\Components\Section::make('Asset')
                ->description('Detail Asset')
                ->schema([
                    Forms\Components\Select::make('asset_group_id')
                    // ->relationship('asset_group', 'name')
                    ->label('Kelompok Aktiva Tetap')
                    ->placeholder('Cari kode Group Asset')
                    ->required()
                    ->columnSpan(3)
                    ->searchable()
                    ->preload()
                    ->getSearchResultsUsing(function (string $search) {
                        // return Plant::where('nama', 'like', "%{$search}%")
                        return AssetGroup::where(function ($query) use ($search) {
                            $query->where('name', 'like', "%{$search}%")
                                ->orWhere('asset_group', 'like', "%{$search}%"); // Tambahkan pencarian juga berdasarkan 'kode'
                        })
                            // ->limit(5)
                            ->get(['asset_group', 'name', 'id']) // Ambil kolom kode, nama, dan id
                            ->mapWithKeys(function ($assetGroup) {
                                return [$assetGroup->id => $assetGroup->asset_group . ' - ' . $assetGroup->name]; // Format opsi dengan kode - nama
                            })
                            ->toArray();
                    })
                    ->getOptionLabelUsing(function ($value) {
                        $assetGroup = AssetGroup::find($value);
                        return $assetGroup ? $assetGroup->asset_group . ' - ' . $assetGroup->name : null; // Format label dengan kode - nama
                    })
                    ->disabled(fn ($record) => $record && $record->status === 'approved'),
                    Forms\Components\Select::make('type')
                        ->label('Jenis')
                        ->options([
                            'Aktiva Tetap' => 'Aktiva Tetap',
                            'CIP' => 'CIP',
                            'Sub Asset' => 'Sub Asset',
                        ])
                        ->required()
                        ->columnSpan(2)
                        ->disabled(fn ($record) => $record && $record->status === 'approved'),
                    Forms\Components\TextInput::make('fixed_asset_number')
                        ->label('Nomor Aktiva Tetap')
                        ->columnSpan(2)
                        ->maxLength(255)
                        ->disabled(fn ($record) => $record && $record->status === 'approved'),
                    Forms\Components\TextInput::make('sub_asset_number')
                        ->label('Nomor Sub Asset')
                        ->columnSpan(2)
                        ->maxLength(255)
                        ->disabled(fn ($record) => $record && $record->status === 'approved'),
                    Forms\Components\TextInput::make('cea_number')
                        ->label('Nomor CEA')
                        ->maxLength(255)
                        ->columnSpan(3)
                        ->disabled(fn ($record) => $record && $record->status === 'approved'),
                    Forms\Components\TextInput::make('usage_period')
                        ->label('Penggunaan')
                        ->columnSpan(1)
                        ->maxLength(255)
                        ->disabled(fn ($record) => $record && $record->status === 'approved'),
                    Forms\Components\TextInput::make('quantity')
                        ->label('Qty')
                        ->columnSpan(1)
                        ->maxLength(255)
                        ->disabled(fn ($record) => $record && $record->status === 'approved'),
                    Forms\Components\Select::make('cost_center_id')
                        ->label('Cost Center')
                        ->placeholder('Cari cost center')
                        ->required()
                        ->searchable()
                        ->columnSpan(4)
                        ->preload()
                        ->getSearchResultsUsing(function (string $search) {
                            // return Plant::where('nama', 'like', "%{$search}%")
                            return CostCenter::where(function ($query) use ($search) {
                                $query->where('cost_center', 'like', "%{$search}%")
                                    ->orWhere('name', 'like', "%{$search}%"); // Tambahkan pencarian juga berdasarkan 'name'
                            })
                                ->get(['cost_center', 'name', 'id']) // Ambil kolom kode, nama, dan id
                                ->mapWithKeys(function ($costcenter) {
                                    return [$costcenter->id => $costcenter->cost_center . ' - ' . $costcenter->name]; // Format opsi dengan kode - nama
                                })
                                ->toArray();
                        })
                        ->getOptionLabelUsing(function ($value) {
                            $costcenter = CostCenter::find($value);
                            return $costcenter ? $costcenter->cost_center . ' - ' . $costcenter->name : null; // Format label dengan kode - nama
                        })
                        ->disabled(fn ($record) => $record && $record->status === 'approved'),
                ])->columns(8),
            Forms\Components\Section::make('Informasi')
                ->description('Detail Barang')
                ->schema([
                    Forms\Components\Select::make('condition')
                        ->label('Status Barang')
                        ->options([
                            'Baru' => 'Baru',
                            'Bekas' => 'Bekas',
                        ])
                        ->required()
                        ->columnSpan(2)
                        ->disabled(fn ($record) => $record && $record->status === 'approved'),
                    Forms\Components\TextInput::make('item_name')
                        ->label('Nama Barang')
                        ->maxLength(255)
                        ->columnSpan(4)
                        ->disabled(fn ($record) => $record && $record->status === 'approved'),
                    Forms\Components\TextInput::make('country_of_origin')
                        ->label('Asal Negara')
                        ->maxLength(255)
                        ->columnSpan(2)
                        ->disabled(fn ($record) => $record && $record->status === 'approved'),
                    Forms\Components\TextInput::make('supplier')
                        ->label('Supplier')
                        ->maxLength(255)
                        ->columnSpan(3)
                        ->disabled(fn ($record) => $record && $record->status === 'approved'),
                    Forms\Components\TextInput::make('year_of_manufacture')
                        ->label('Pembuatan')
                        ->columnSpan(1)
                        ->placeholder('Isi tahun')
                        ->disabled(fn ($record) => $record && $record->status === 'approved'),
                    Forms\Components\DatePicker::make('expected_arrival')
                        ->label('Estimasi Kedatangan')
                        ->columnSpan(2)
                        ->disabled(fn ($record) => $record && $record->status === 'approved'),
                    Forms\Components\DatePicker::make('expected_usage')
                        ->label('Estimasi Penggunaan')
                        ->columnSpan(2)
                        ->disabled(fn ($record) => $record && $record->status === 'approved'),
                ])->columns(8),
            Forms\Components\Section::make('Lokasi')
                ->description('Detail Lokasi')
                ->schema([
                    Forms\Components\Textarea::make('location')
                        ->columnSpan(4)
                        ->disabled(fn ($record) => $record && $record->status === 'approved'),
                    Forms\Components\Textarea::make('description')
                        ->label('Catatan')
                        ->columnSpan(4),
                    Forms\Components\Select::make('status')
                        ->options([
                            'draft' => 'Draft',
                            'in_review' => 'In Review',
                            'approved' => 'Approved',
                            'rejected' => 'Rejected',
                        ])
                        ->disabled(),
                ])->columns(8),
            Forms\Components\Section::make('Riwayat Approval')
                ->schema([
                    self::getApprovalHistoryRepeater(),
                ])
                ->collapsed()
                ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
        ->columns([
            Tables\Columns\TextColumn::make('document_number')
                ->label('No. Document')
                ->searchable(),
            Tables\Columns\TextColumn::make('plant.kode')
                ->label('Plant')
                ->searchable()
                ->getStateUsing(function ($record) {
                    return $record->plant->kode . ' - ' . $record->plant->nama;
                }),
            Tables\Columns\TextColumn::make('fixed_asset_group')
                ->label('Group')
                ->searchable(),
            Tables\Columns\TextColumn::make('fixed_asset_number')
                ->label('Nomor Asset')
                ->searchable(),
            Tables\Columns\TextColumn::make('costCenter.cost_center')
                ->label('Cost Center')
                ->searchable(),
            Tables\Columns\TextColumn::make('item_name')
                ->label('Nama Barang')
                ->searchable(),
            Tables\Columns\TextColumn::make('condition')
                ->label('Status')
                ->searchable(),
            Tables\Columns\TextColumn::make('quantity')
                ->numeric()
                ->sortable(),
            Tables\Columns\TextColumn::make('year_of_manufacture')
                ->label('Tahun')
                ->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('supplier')
                ->label('Pemasok')
                ->searchable()
                ->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('country_of_origin')
                ->label('Asal Negara')
                ->searchable()
                ->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('expected_usage')
                ->label('Est Penggunaan')
                ->date()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('expected_arrival')
                ->label('Est Kedatangan')
                ->date()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('location')
                ->label('Lokasi')
                ->searchable()
                ->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('status')
                ->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('created_at')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('updated_at')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('approvals')
                ->label('Approval History')
                ->getStateUsing(function ($record) {
                    return $record->approvals->map(fn($a) =>
                        $a->user->name . ': ' . ucfirst($a->status)
                    )->implode(', ');
                }),

        ])
            ->filters([
                //
            ])
            ->actions([
                ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    // ->requiresConfirmation()
                    ->modalHeading('Konfirmasi Approval')
                    ->modalDescription('Silakan masukkan catatan sebelum menyetujui permintaan ini.')
                    ->visible(fn ($record) => $record->isPendingApprovalFor(auth()->user()))
                    // ➕ Tambahkan form untuk isi catatan
                    ->form([
                        Forms\Components\Textarea::make('note')
                            ->label('Catatan Approval')
                            ->placeholder('Tulis catatan approval (opsional)...')
                            ->rows(3)
                            ->required(false),
                        ])
                    // ➕ Tambahkan $data ke parameter action
                    ->action(function ($record, array $data) {
                        $user = auth()->user();
                        $level = $record->currentApprovalLevel() + 1;

                        $record->approvals()->create([
                            'user_id' => $user->id,
                            'level' => $level,
                            'status' => 'approved',
                            'note' => $data['note'] ?? null,
                            'approved_at' => now(),
                        ]);

                        if ($level >= 3) {
                            $record->update(['status' => 'approved']);
                        } else {
                            $record->update(['status' => 'pending']);
                        }
                    }),
                Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->isPendingApprovalFor(auth()->user()))
                    ->form([
                        Forms\Components\Textarea::make('note')
                            ->label('Alasan penolakan')
                            ->required(),
                    ])
                    ->action(function ($record, array $data) {
                        $user = auth()->user();
                        $record->approvals()->create([
                            'user_id' => $user->id,
                            'level' => $record->currentApprovalLevel() + 1,
                            'status' => 'rejected',
                            'note' => $data['note'],
                            'approved_at' => now(),
                        ]);

                        $record->update(['status' => 'rejected']);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    protected static function approve(AssetRequest $record): void
    {
        $record->approvals()->create([
            'user_id' => Auth::id(),
            'level' => $record->currentApprovalLevel() + 1,
            'status' => 'approved',
            'approved_at' => now(),
        ]);

        if ($record->currentApprovalLevel() >= 3) {
            $record->update(['status' => 'approved']);
        } else {
            $record->update(['status' => 'in_review']);
        }
    }


    public function currentApprovalLevel(): int
    {
        return $this->approvals()->count();
    }

    // public function isPendingApprovalFor(User $user): bool
    // {
    //     $expected = \App\Models\ApprovalSetting::where('plant_id', $this->plant_id)
    //         ->where('level', $this->currentApprovalLevel() + 1)
    //         ->where('user_id', $user->id)
    //         ->exists();
    //     return $this->status === 'in_review' && $expected;
    // }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAssetRequests::route('/'),
            'create' => Pages\CreateAssetRequest::route('/create'),
            'edit' => Pages\EditAssetRequest::route('/{record}/edit'),
        ];
    }


    // public static function view(): ViewRecord
    // {
    //     return ViewRecord::make()
    //         ->headerActions([]) // jika tidak ingin ada tombol tambahan di header
    //         ->columns([
    //         ])
    //         ->form([
    //         ])
    //         ->extraTab('Approval History', static function ($record) {
    //             return Tables\Table::make()
    //                 ->query($record->approvals()->with('user'))
    //                 ->columns([
    //                     TextColumn::make('user.name')
    //                         ->label('Approver'),
    //                     TextColumn::make('level')
    //                         ->label('Level'),
    //                     TextColumn::make('status')
    //                         ->badge()
    //                         ->colors([
    //                             'success' => 'approved',
    //                             'danger' => 'rejected',
    //                             'warning' => 'pending',
    //                         ]),
    //                     TextColumn::make('note')
    //                         ->label('Catatan')
    //                         ->wrap(),
    //                     TextColumn::make('approved_at')
    //                         ->label('Tanggal Approve')
    //                         ->dateTime()
    //                         ->placeholder('Belum disetujui'),
    //                 ]);
    //         });
    // }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with('approvals.user')
            ->orderByRaw("
                CASE
                    WHEN EXISTS (
                        SELECT 1
                        FROM asset_approvals
                        WHERE asset_approvals.asset_request_id = asset_requests.id
                        AND asset_approvals.status = 'pending'
                        AND asset_approvals.user_id = ?
                    ) THEN 0
                    ELSE 1
                END ASC", [Auth::id()])
            ->orderBy('created_at', 'desc'); // setelah itu berdasarkan tanggal dibuat
    }

    public static function getApprovalHistoryRepeater(): Repeater
    {
        return Repeater::make('approvals')
            ->label('Riwayat Approval')
            ->relationship('approvals') // relasi dari AssetRequest
            ->schema([
                TextInput::make('level')
                    ->label('Level Approval')
                    ->columnSpan(2)
                    ->disabled(),
                Select::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ])
                    ->columnSpan(2)
                    ->disabled(),

                DateTimePicker::make('approved_at')
                    ->label('Waktu Approval')
                    ->columnSpan(2)
                    ->disabled(),

                Textarea::make('note')
                    ->label('Catatan')
                    ->columnSpan(2)
                    ->disabled(),
            ])
            ->columns(8)
            ->disabled()
            ->hiddenLabel();
    }
}
