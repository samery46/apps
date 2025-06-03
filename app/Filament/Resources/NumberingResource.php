<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NumberingResource\Pages;
use App\Filament\Resources\NumberingResource\RelationManagers;
use App\Models\Departemen;
use App\Models\Numbering;
use App\Models\Plant;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\Filter;

class NumberingResource extends Resource
{
    protected static ?string $model = Numbering::class;

    protected static ?string $pluralModelLabel = 'Penomoran Surat';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Transaksi';

    protected static ?int $navigationSort = 133;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\Group::make()
                    ->schema([

                        Forms\Components\Section::make('Plant')
                            ->description('Informasi Plant Detail')
                            ->schema([
                                Forms\Components\TextInput::make('transaction_number')
                                    ->label('Nomor Surat')
                                    // ->searchable()
                                    ->disabled()
                                    ->columnSpan(1)
                                    ->default(fn () => 'Diisi otomatis saat disimpan'),

                                Forms\Components\DatePicker::make('tgl')
                                    // ->default(now()->format('Y-m-d'))
                                    // ->disabled()
                                    ->columnSpan(1)
                                    ->required(),
                                // Forms\Components\TextInput::make('transaction_number')
                                //     ->label('Nomor Doc')
                                //     ->maxLength(255)
                                //     ->placeholder('Auto Generate')
                                //     ->disabled(),
                                Forms\Components\Select::make('plant_id')
                                    ->label('Plant')
                                    ->placeholder('Cari kode atau nama')
                                    ->required()
                                    ->searchable()
                                    ->columnSpan(2)
                                    ->preload()
                                    ->getSearchResultsUsing(function (string $search) {
                                        // return Plant::where('nama', 'like', "%{$search}%")
                                        return Plant::where(function ($query) use ($search) {
                                            $query->where('nama', 'like', "%{$search}%")
                                                ->orWhere('kode', 'like', "%{$search}%"); // Tambahkan pencarian juga berdasarkan 'kode'
                                        })
                                            // ->limit(5)
                                            ->whereIn('id', auth()->user()->plants->pluck('id')) // Filter berdasarkan hak akses user
                                            ->get(['kode', 'nama', 'id']) // Ambil kolom kode, nama, dan id
                                            ->mapWithKeys(function ($plant) {
                                                return [$plant->id => $plant->kode . ' - ' . $plant->nama]; // Format opsi dengan kode - nama
                                            })
                                            ->toArray();
                                    })
                                    ->getOptionLabelUsing(function ($value) {
                                        $plant = Plant::find($value);
                                        return $plant ? $plant->kode . ' - ' . $plant->nama : null; // Format label dengan kode - nama
                                    }),
                                Forms\Components\Select::make('departemen_id')
                                    ->label('Departemen')
                                    // ->relationship('departemen', 'nama')
                                    ->relationship('departemen', 'nama', function ($query) {
                                        $query->whereNotIn('id', [1, 6, 15, 18]); // query tdk menampilkan Dept GA, MFG, MKT
                                    })
                                    ->searchable()
                                    ->required()
                                    ->columnSpan(2)
                                    ->preload()
                                    ->getSearchResultsUsing(function (string $search) {
                                        return Departemen::where('nama', 'like', "%{$search}%")
                                            ->limit(5)
                                            ->pluck('nama', 'id')
                                            ->toArray();
                                    })
                                    ->getOptionLabelUsing(function ($value) {
                                        return Departemen::find($value)->nama;
                                    }),

                            ])->columns(2),
                    ]),

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Hal')
                            ->description('Informasi Hal Detail')
                            ->schema([
                                Forms\Components\TextInput::make('hal')
                                    ->maxLength(255)
                                    ->columnSpanFull(),
                                Forms\Components\Textarea::make('kepada')
                                    ->autosize(),
                                Forms\Components\Textarea::make('up')
                                    ->label('UP')
                                    ->autosize(),
                                Forms\Components\Textarea::make('alamat')
                                    ->autosize()
                                    ->columnSpanFull(),
                            ])->columns(2),

                    ]),

                Forms\Components\Section::make('Isi')
                    ->description('Informasi Isi Detail')
                    ->schema([
                        Forms\Components\Textarea::make('isi')
                            ->autosize()
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('keterangan')
                            ->autosize(),

                        Forms\Components\FileUpload::make('lampiran')
                            ->label('Lampiran')
                            ->maxSize(1024) // Maksimal ukuran file dalam kilobyte (1 MB)
                            ->acceptedFileTypes([
                                'image/jpeg',
                                'image/png',
                                'image/jpg',
                                'application/pdf' // MIME type untuk file PDF
                            ])
                            ->directory('uploads/numbering') // Lokasi penyimpanan
                            ->preserveFilenames() // Memastikan nama file asli digunakan
                            ->enableDownload() // Opsi untuk mengunduh file
                            ->enableOpen()
                            ->columnSpan(1),

                        // Forms\Components\FileUpload::make('lampiran')
                        //     ->label('Lampiran')
                        //     ->disk('public')
                        //     ->directory('attachments')
                        //     ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png']),
                    ])->columns(2),

                Forms\Components\Toggle::make('is_aktif')
                    ->required(),

                Forms\Components\Hidden::make('user_id')
                    ->default(fn () => Auth::id())
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                 Tables\Columns\TextColumn::make('plant.kode')
                    ->label('Plant')
                    ->searchable()
                    ->getStateUsing(function ($record) {
                        return $record->plant->kode . ' - ' . $record->plant->nama;
                    }),
                Tables\Columns\TextColumn::make('departemen.kode')
                    ->label('Dept.')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tgl')
                    ->label('Tanggal')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('transaction_number')
                    ->label('Nomor Surat')
                    ->searchable(),
                Tables\Columns\TextColumn::make('hal')
                    ->searchable(),
                Tables\Columns\TextColumn::make('kepada')
                    ->searchable(),
                Tables\Columns\TextColumn::make('up')
                    ->label('UP')
                    ->searchable(),
                Tables\Columns\TextColumn::make('alamat')
                    ->searchable(),
                Tables\Columns\ImageColumn::make('lampiran')
                    ->label('Lampiran')
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('keterangan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Create By')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_aktif')
                    ->boolean(),
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
                SelectFilter::make('plant_id')
                    ->label('Filter by Plant')
                    ->options(function () {
                        if (auth()->check() && auth()->user()->id === 1) {
                            // Jika user memiliki ID 1, dianggap sebagai admin
                            return Numbering::with('plant')
                                ->get()
                                ->sortBy(function ($item) {
                                    return $item->plant->kode;
                                })
                                ->mapWithKeys(function ($item) {
                                    return [$item->plant_id => "{$item->plant->kode} - {$item->plant->nama}"];
                                })
                                ->toArray();
                            } else {
                                return auth()->user()->plants
                                    ->sortBy('kode')
                                    ->mapWithKeys(fn ($plant) => [$plant->id => "{$plant->kode} - {$plant->nama}"])
                                    ->toArray();
                            }
                    }),
                    Filter::make('tgl_range')
                        // ->label('Filter Tanggal')
                        ->form([
                            DatePicker::make('start_date')
                                ->label('Dari Tanggal')
                                ->default(Carbon::today()->subDays(6)->toDateString()), // 7 hari terakhir (termasuk hari ini)
                            DatePicker::make('end_date')
                                ->label('Sampai Tanggal')
                                ->default(Carbon::today()->toDateString()), // Set default ke hari ini
                        ])
                        ->query(function ($query, $data) {
                            if (!empty($data['start_date']) && !empty($data['end_date'])) {
                                $query->whereBetween('tgl', [$data['start_date'], $data['end_date']]);
                            } elseif (!empty($data['start_date'])) {
                                $query->whereDate('tgl', '>=', $data['start_date']);
                            } elseif (!empty($data['end_date'])) {
                                $query->whereDate('tgl', '<=', $data['end_date']);
                            }
                        }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListNumberings::route('/'),
            'create' => Pages\CreateNumbering::route('/create'),
            'edit' => Pages\EditNumbering::route('/{record}/edit'),
        ];
    }
}
