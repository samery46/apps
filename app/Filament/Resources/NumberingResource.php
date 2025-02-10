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
                                Forms\Components\DatePicker::make('tgl')
                                    // ->default(now()->format('Y-m-d'))
                                    // ->disabled()
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
                                    ->preload()
                                    ->getSearchResultsUsing(function (string $search) {
                                        // return Plant::where('nama', 'like', "%{$search}%")
                                        return Plant::where(function ($query) use ($search) {
                                            $query->where('nama', 'like', "%{$search}%")
                                                ->orWhere('kode', 'like', "%{$search}%"); // Tambahkan pencarian juga berdasarkan 'kode'
                                        })
                                            // ->limit(5)
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
                            ->disk('public')
                            ->directory('attachments')
                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png']),
                    ])->columns(2),

                Forms\Components\Toggle::make('is_aktif')
                    ->required(),

                Forms\Components\Hidden::make('user_id')
                    ->default(fn () => Auth::id())
                    ->required(),
            ]);
    }

    public static function beforeCreate(Form $form, Numbering $numbering)
    {
        $numbering->tgl = now();
        $numbering->transaction_number = self::generateTransactionNumber($numbering->tgl, $numbering->departemen_id, $numbering->plant_id);
    }

    protected static function generateTransactionNumber($date, $departemen_id, $plant_id)
    {
        $departemen = Departemen::find($departemen_id)->kode; // Assuming department has a 'code' field
        $plant = Plant::find($plant_id)->kode; // Assuming plant has a 'code' field
        $year = Carbon::parse($date)->format('Y'); // Format Tahun
        // $monthYear = Carbon::parse($date)->format('m-Y'); // Format Bulan-Tahun

        // Generate unique number part, reset every year
        $latestTransaction = Numbering::whereYear('date', Carbon::parse($date)->year)
            ->where('department_id', $departemen_id)
            ->where('plant_id', $plant_id)
            ->latest('id')
            ->first();
        $number = $latestTransaction ? intval(substr($latestTransaction->transaction_number, 0, 4)) + 1 : 1;
        $numberPart = str_pad($number, 4, '0', STR_PAD_LEFT);

        return "{$numberPart}/{$plant}/{$departemen}/{$year}";
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('plant_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('departemen_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tgl')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('transaction_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('hal')
                    ->searchable(),
                Tables\Columns\TextColumn::make('kepada')
                    ->searchable(),
                Tables\Columns\TextColumn::make('up')
                    ->searchable(),
                Tables\Columns\TextColumn::make('alamat')
                    ->searchable(),
                Tables\Columns\TextColumn::make('lampiran')
                    ->searchable(),
                Tables\Columns\TextColumn::make('keterangan')
                    ->searchable(),
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
                //
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
