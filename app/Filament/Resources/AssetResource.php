<?php

namespace App\Filament\Resources;

use App\Exports\AssetExport;
use App\Filament\Resources\AssetResource\Pages;
use App\Filament\Resources\AssetResource\RelationManagers;
use App\Models\Asset;
use App\Models\Karyawan;
use App\Models\Plant;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Actions\BulkAction;
use Maatwebsite\Excel\Facades\Excel;

class AssetResource extends Resource
{
    protected static ?string $model = Asset::class;

    protected static ?string $pluralModelLabel = 'asset';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Master';

    protected static ?int $navigationSort = 111;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\TextInput::make('nomor')
                            ->label('Nomor Asset')
                            ->placeholder('Isikan Nomor Asset')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('sub')
                            ->label('Sub Asset')
                            ->placeholder('Isikan Sub Asset')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('nama')
                            ->label('Nama Asset')
                            ->placeholder('Isikan Nama Asset')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('tipe')
                            ->label('Type Asset')
                            ->placeholder('Pilih Type Asset')
                            ->maxLength(255),

                    ])->columns(2),
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\DatePicker::make('tgl_perolehan')
                            ->label('Tgl Perolehan'),
                        Forms\Components\TextInput::make('harga')
                            ->label('Harga')
                            ->numeric()
                            ->default(0),
                        Forms\Components\TextInput::make('nbv')
                            ->label('Netbook Value')
                            ->numeric()
                            ->default(0),
                        Forms\Components\TextInput::make('serial_number')
                            ->label('Serial Number')
                            ->placeholder('Isikan Serial Number')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('qty_sap')
                            ->label('Qty SAP')
                            ->numeric()
                            ->default(0),
                        Forms\Components\TextInput::make('qty_aktual')
                            ->label('Qty Aktual')
                            ->numeric()
                            ->default(0),

                    ])->columns(3),

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\TextInput::make('kondisi')
                            ->label('Kondisi Fisik')
                            ->placeholder('Pilih Kondisi')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('status')
                            ->label('Status Asset')
                            ->placeholder('Jelaskan Status Asset')
                            ->maxLength(255),
                        Forms\Components\Textarea::make('lokasi')
                            ->placeholder('Isikan lokasi asset')
                            ->autosize(),
                        Forms\Components\Textarea::make('keterangan')
                            ->placeholder('Isikan keterangan detail asset')
                            ->autosize(),
                    ])->columns(2),

                Forms\Components\Group::make()
                    ->schema([

                        // Forms\Components\TextInput::make('karyawan_id')
                        //     ->label('User Pengguna')
                        //     ->placeholder('Pilih user pengguna')
                        //     ->numeric(),
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
                            }),
                        Forms\Components\Select::make('plant_id')
                            // ->relationship('plant', 'nama')
                            ->label('Plant')
                            ->placeholder('Cari kode atau nama plant')
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
                        Forms\Components\FileUpload::make('foto')
                            ->image(),
                        Forms\Components\Toggle::make('is_aktif')
                            ->required(),
                        // Forms\Components\TextInput::make('user_id')
                        //     ->label('User Input')
                        //     ->numeric(),
                    ])->columns(2),
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
                Tables\Columns\TextColumn::make('nomor')
                    ->label('No. Asset')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('tipe')
                    ->label('Type')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('nama')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('serial_number')
                    ->label('Serial Number')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('karyawan.nama')
                    ->label('Pengguna')
                    ->sortable()
                    ->searchable(),
                // Tables\Columns\TextColumn::make('qty_sap')
                //     ->label('Qty-SAP')
                //     ->numeric()
                //     ->sortable(),
                Tables\Columns\TextColumn::make('qty_aktual')
                    ->label('Qty-Aktual')
                    ->numeric()
                    ->sortable(),
                // Tables\Columns\IconColumn::make('is_aktif')
                //     ->label('Aktif')
                //     ->sortable()
                //     ->boolean(),
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
                    ->relationship('plant', 'kode')
                    ->label('Filter by Plant')
                    ->options(Plant::all()->pluck('kode', 'id')->toArray()),

                TernaryFilter::make('is_aktif')
                    ->label('Filter by Aktif')
                    ->trueLabel('Aktif')
                    ->falseLabel('Non Aktif')
                    ->placeholder('Semua')
                    ->queries(
                        true: fn (Builder $query): Builder => $query->where('is_aktif', true),
                        false: fn (Builder $query): Builder => $query->where('is_aktif', false),
                        blank: fn (Builder $query): Builder => $query
                    ),
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
                        $fileName = "asset-{$date}.xlsx"; // Menambahkan tanggal pada nama file
                        return Excel::download(new AssetExport($recordIds), $fileName);
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
            'index' => Pages\ListAssets::route('/'),
            'create' => Pages\CreateAsset::route('/create'),
            'edit' => Pages\EditAsset::route('/{record}/edit'),
        ];
    }
}
