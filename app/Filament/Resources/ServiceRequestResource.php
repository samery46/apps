<?php

namespace App\Filament\Resources;

use App\Exports\ServiceRequestExport;
use App\Filament\Resources\ServiceRequestResource\Pages;
use App\Filament\Resources\ServiceRequestResource\RelationManagers;
use App\Models\Asset;
use App\Models\Plant;
use App\Models\ServiceRequest;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Filament\Tables\Filters\SelectFilter;

class ServiceRequestResource extends Resource
{
    protected static ?string $model = ServiceRequest::class;

    protected static ?string $pluralModelLabel = 'service request';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Transaksi';

    protected static ?int $navigationSort = 134;

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
                            ->getOptionLabelUsing(function ($value) {
                                return Asset::find($value)->nomor;
                            })
                            ->reactive() // Memicu reactivity ketika asset dipilih
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $asset = Asset::find($state);
                                    if ($asset) {
                                        $set('kode', $asset->plant->kode ?? null); // Mengisi Kode Plant
                                        $set('nama', $asset->nama); // Mengisi Nama Asset
                                        $set('serial_number', $asset->serial_number); // Mengisi Serial Number
                                    }
                                }
                            }),
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
                    ])
                    ->columns(4),
                Forms\Components\Section::make('Services')
                    ->description('Informasi Services')
                    ->schema([
                        Forms\Components\RichEditor::make('problem')
                            ->label('Kerusakan')
                            ->columnSpan(3),
                        Forms\Components\DatePicker::make('start_date')
                            ->label('Tanggal Masuk')
                            ->default(Carbon::today()->format('Y-m-d'))
                            ->required(),
                        Forms\Components\DatePicker::make('end_date')
                            ->label('Tanggal Selesai')
                            ->default(Carbon::today()->format('Y-m-d'))
                            ->hiddenOn('create'),
                        Forms\Components\TextInput::make('vendor')
                            ->maxLength(255),
                    ])->columns(3),

                Forms\Components\Section::make('Status')
                    ->schema([

                        Forms\Components\Textarea::make('notes')
                            ->maxLength(255)
                            ->columnSpan(3),
                        Forms\Components\Select::make('status')
                            ->options([
                                'InProgress' => 'InProgress',
                                'Pending' => 'Pending',
                                'Completed' => 'Completed',
                                'Cancel' => 'Cancel'
                            ])->required(),

                        Forms\Components\Select::make('user_id')
                            ->label('Created by')
                            ->relationship('user', 'name', function ($query) {
                                $query->whereIn('id', [2, 3, 4, 7]);
                            })
                            ->required()
                            ->default(Auth::id()),
                    ])->columns(3),

                Forms\Components\Section::make('Spare Parts')->schema([
                    self::getItemsRepeater(),
                ]),
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
                    ->searchable(),
                Tables\Columns\TextColumn::make('problem')
                    ->label('Kerusakan')
                    ->sortable()
                    ->formatStateUsing(function ($state) {
                        // Menghilangkan semua tag HTML dari teks
                        return strip_tags($state);
                    })
                    ->limit(30) // Batasi jumlah karakter yang ditampilkan jika perlu
                    ->wrap() // Jika ingin teks tetap rapi meskipun panjang
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(function ($state) {
                        switch ($state) {
                            case 'Completed':
                                return 'Selesai';
                            case 'InProgress':
                                return 'Dalam Proses'; // Jika status adalah 'in_progress'
                            case 'Pending':
                                return 'Menunggu'; // Jika status adalah 'pending'
                            case 'Cancel':
                                return 'Dibatalkan'; // Jika status adalah 'cancel'
                            default:
                                return 'Tidak Diketahui'; // Untuk status lain yang tidak dikenali
                        }
                    })
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('vendor')
                    ->label('Vendor')
                    ->numeric()
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Tgl Mulai|Selesai')
                    ->description(
                        fn(ServiceRequest $record): string =>
                        $record->end_date ? Carbon::parse($record->end_date)
                            ->translatedFormat('d F Y') : 'Belum Selesai'
                    )
                    ->formatStateUsing(fn($state) => $state ? Carbon::parse($state)->translatedFormat('d F Y') : '-')
                    // ->date()
                    ->color('red')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('notes')
                    ->label('Keterangan')
                    ->sortable()
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Created By')
                    ->sortable()
                    ->searchable()
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),
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

            ])->defaultSort('created_at', 'desc')
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
                        'kosong' => 'Belum Selesai',
                        'tidak_kosong' => 'Sudah Selesai',
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
                        $fileName = "Service-Request-{$date}.xlsx"; // Menambahkan tanggal pada nama file
                        return Excel::download(new ServiceRequestExport($recordIds), $fileName);
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
            'index' => Pages\ListServiceRequests::route('/'),
            'create' => Pages\CreateServiceRequest::route('/create'),
            'edit' => Pages\EditServiceRequest::route('/{record}/edit'),
        ];
    }

    public static function getItemsRepeater(): Repeater
    {
        return Repeater::make('spare_parts')
            // ->relationship()
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nama'),
                Forms\Components\TextInput::make('quantity')
                    ->label('Jumlah')
                    ->numeric(),
                Forms\Components\TextInput::make('price')
                    ->label('Harga Satuan')
                    ->numeric(),
            ])
            ->defaultItems(1)
            ->hiddenLabel()
            ->columns([
                'md' => 3,
            ])
            ->live();
    }

    public function update(Request $request, ServiceRequest $serviceRequest)
    {
        // Validasi dan update service request
        $serviceRequest->update($request->all());

        // Ambil asset yang terkait berdasarkan asset_id
        $asset = Asset::find($serviceRequest->asset_id);

        // Jika asset ditemukan, update status-nya
        if ($asset) {
            // Ambil status terbaru dari service request yang terkait
            $latestStatus = ServiceRequest::where('asset_id', $asset->id)
                ->latest() // Ambil yang terbaru
                ->value('status');
            // Update status di asset
            $asset->status = $latestStatus;
            $asset->save();
        }

        return redirect()->route('service_requests.index')->with('success', 'Service Request updated successfully.');
    }
}
