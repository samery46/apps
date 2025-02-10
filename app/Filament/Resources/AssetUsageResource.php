<?php

namespace App\Filament\Resources;

use App\Exports\AssetUsageExport;
use App\Filament\Resources\AssetUsageResource\Pages;
use App\Filament\Resources\AssetUsageResource\RelationManagers;
use App\Models\Asset;
use App\Models\AssetUsage;
use App\Models\Karyawan;
use App\Models\Plant;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;
use Filament\Tables\Filters\SelectFilter;
// use App\Models\Plant;
use Filament\Tables\Filters\TextFilter;

class AssetUsageResource extends Resource
{
    protected static ?string $model = AssetUsage::class;

    protected static ?string $pluralModelLabel = 'pemakaian asset';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Transaksi';

    protected static ?int $navigationSort = 135;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Asset')
                    ->description('Informasi Asset')
                    ->schema([
                        Forms\Components\Select::make('asset_id')
                            ->label('Nomor')
                            ->relationship('asset', 'nomor')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->getSearchResultsUsing(function (string $search) {
                                return Asset::where('nomor', 'like', "%{$search}%")
                                    ->limit(5)
                                    ->pluck('nomor', 'id')
                                    ->toArray();
                            })
                            ->getOptionLabelUsing(fn($value) => Asset::find($value)->nomor ?? null)
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $asset = Asset::find($state);
                                    if ($asset) {
                                        $set('kode', $asset->plant->kode ?? null);
                                        $set('nama', $asset->nama);
                                        $set('serial_number', $asset->serial_number);
                                    }
                                }
                            })
                            ->afterStateUpdated(function ($state, callable $set) {
                                // Cek apakah aset sedang digunakan
                                $isInUse = AssetUsage::where('asset_id', $state)
                                    ->whereNull('end_date')
                                    ->exists();

                                if ($isInUse) {
                                    Notification::make()
                                        ->title('Aset ini sedang digunakan!')
                                        ->body('Aset ini belum dikembalikan dan tidak bisa dipinjam lagi sampai dikembalikan.')
                                        ->danger()
                                        ->persistent() // Membuat notifikasi tetap tampil sampai ditutup oleh user
                                        ->send();

                                    $set('asset_in_use', true);
                                } else {
                                    $set('asset_in_use', false);
                                }
                            }),
                        // Field hidden untuk validasi aset yang sedang digunakan
                        Forms\Components\Hidden::make('asset_in_use')
                            ->default(false),
                        Forms\Components\TextInput::make('kode')
                            ->label('Kode Plant')
                            ->disabled()
                            ->afterStateHydrated(function ($state, callable $set, $record) {
                                if ($record && $record->asset) {
                                    $set('kode', $record->asset->plant->kode);
                                }
                            }),
                        Forms\Components\TextInput::make('nama')
                            ->label('Nama Asset')
                            ->disabled()
                            ->afterStateHydrated(function ($state, callable $set, $record) {
                                if ($record && $record->asset) {
                                    $set('nama', $record->asset->nama);
                                }
                            }),
                        Forms\Components\TextInput::make('serial_number')
                            ->label('Serial Number')
                            ->disabled()
                            ->afterStateHydrated(function ($state, callable $set, $record) {
                                if ($record && $record->asset) {
                                    $set('serial_number', $record->asset->serial_number);
                                }
                            }),
                    ])->columns(4),

                Forms\Components\Section::make('Karyawan')
                    ->description('Informasi Karyawan')
                    ->schema([
                        Forms\Components\TextInput::make('nik')
                            ->label('NIK')
                            ->disabled()
                            ->afterStateHydrated(function ($state, callable $set, $record) {
                                if ($record && $record->karyawan) {
                                    $set('nik', $record->karyawan->nik);
                                }
                            }),

                        Forms\Components\Select::make('karyawan_id')
                            ->label('Pengguna')
                            ->placeholder('Cari nama karyawan')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->getSearchResultsUsing(function (string $search) {
                                return Karyawan::where(function ($query) use ($search) {
                                    $query->where('nama', 'like', "%{$search}%");
                                })
                                    ->get(['nama', 'id'])
                                    ->mapWithKeys(function ($karyawan) {
                                        return [$karyawan->id => $karyawan->nama];
                                    })
                                    ->toArray();
                            })
                            ->getOptionLabelUsing(function ($value) {
                                $karyawan = Karyawan::find($value);
                                return $karyawan ? $karyawan->nama : null;
                            })
                            ->reactive() // Memicu reactivity ketika asset dipilih
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $karyawan = Karyawan::find($state);
                                    if ($karyawan) {
                                        $set('nik', $karyawan->nik);
                                        $set('departemen', $karyawan->departemen->kode);
                                        $set('job_title', $karyawan->job_title);
                                    }
                                }
                            }),

                        Forms\Components\TextInput::make('departemen')
                            ->label('Departemen')
                            ->disabled()
                            ->afterStateHydrated(function ($state, callable $set, $record) {
                                if ($record && $record->karyawan) {
                                    $set('departemen', $record->karyawan->Departemen->kode);
                                }
                            }),
                        Forms\Components\TextInput::make('job_title')
                            ->label('Job Title')
                            ->disabled()
                            ->afterStateHydrated(function ($state, callable $set, $record) {
                                if ($record && $record->karyawan) {
                                    $set('job_title', $record->karyawan->job_title);
                                }
                            }),
                    ])
                    ->columns(2),


                Forms\Components\Section::make('Pemakaian')
                    ->description('Informasi Pemakaian')
                    ->schema([
                        Forms\Components\DatePicker::make('start_date')
                            ->label('Tanggal Pakai')
                            ->default(Carbon::today()->format('Y-m-d'))
                            ->required(),
                        Forms\Components\DatePicker::make('end_date')
                            ->label('Tanggal Selesai')
                            ->default(Carbon::today()->format('Y-m-d'))
                            ->hiddenOn('create'),
                        Forms\Components\RichEditor::make('notes')
                            ->label('Keterangan')
                            ->columnSpanFull(),
                    ])
                    ->columns(4),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\TextColumn::make('asset.plant.kode')
                    ->label('Plant')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('asset.nomor')
                    ->label('No. Asset')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('asset.nama')
                    ->label('Description')
                    ->sortable()
                    ->limit(40)
                    ->wrap()
                    ->searchable(),
                Tables\Columns\TextColumn::make('asset.serial_number')
                    ->label('Serial Number')
                    ->sortable()
                    ->wrap()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('karyawan.nik')
                    ->label('NIK')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('karyawan.nama')
                    ->sortable()
                    ->searchable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('karyawan.job_title')
                    ->label('Job Title')
                    ->sortable()
                    ->searchable()
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('karyawan.departemen.kode')
                    ->label('Dept')
                    ->numeric()
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('notes')
                    ->label('Keterangan')
                    ->sortable()
                    ->formatStateUsing(function ($state) {
                        // Menghilangkan semua tag HTML dari teks
                        return strip_tags($state);
                    })
                    ->limit(30)
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Tgl Pakai|Selesai')
                    ->description(
                        fn(AssetUsage $record): string =>
                        $record->end_date ? Carbon::parse($record->end_date)
                            ->translatedFormat('d F Y') : 'Belum dikembalikan'
                    )
                    ->formatStateUsing(fn($state) => $state ? Carbon::parse($state)->translatedFormat('d F Y') : '-')
                    // ->date()
                    ->color('red')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('plant')
                    ->label('Plant')
                    ->options(Plant::pluck('kode', 'kode')->toArray()) // Menampilkan daftar kode plant
                    ->query(function (Builder $query, array $data) {
                        if ($data['value']) {
                            $query->whereHas('asset.plant', function ($query) use ($data) {
                                $query->where('kode', $data['value']); // Memfilter berdasarkan kode plant yang dipilih
                            });
                        }
                    }),
                SelectFilter::make('end_date_filter')
                    ->label('Selesai')
                    ->options([
                        'kosong' => 'Belum Kembali',
                        'tidak_kosong' => 'Sudah Kembali',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if ($data['value'] === 'kosong') {
                            $query->whereNull('end_date');
                        } elseif ($data['value'] === 'tidak_kosong') {
                            $query->whereNotNull('end_date');
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
                BulkAction::make('export')
                    ->label('Export')
                    ->color('info')
                    ->action(function ($records) {
                        $recordIds = $records->pluck('id')->toArray();
                        $date = date('Y-m-d'); // Mendapatkan tanggal saat ini dalam format YYYY-MM-DD
                        $fileName = "Pemakaian-Asset-{$date}.xlsx"; // Menambahkan tanggal pada nama file
                        return Excel::download(new AssetUsageExport($recordIds), $fileName);
                    }),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAssetUsages::route('/'),
            'create' => Pages\CreateAssetUsage::route('/create'),
            'edit' => Pages\EditAssetUsage::route('/{record}/edit'),
        ];
    }

    public static function mutateFormDataBeforeCreate(array $data): array
    {
        if ($data['asset_in_use']) {
            Notification::make()
                ->title('Aset ini sedang digunakan!')
                ->body('Aset ini belum dikembalikan dan tidak bisa dipinjam lagi sampai dikembalikan.')
                ->danger()
                ->send();
        }

        return $data;
    }

    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'delete',
            'delete_any'
        ];
    }
}
