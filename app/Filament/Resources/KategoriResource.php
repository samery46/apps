<?php

namespace App\Filament\Resources;

use App\Exports\KategoriExport;
use App\Filament\Resources\KategoriResource\Pages;
use App\Filament\Resources\KategoriResource\RelationManagers;
use App\Models\Kategori;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Maatwebsite\Excel\Facades\Excel;

class KategoriResource extends Resource
{
    protected static ?string $model = Kategori::class;

    protected static ?string $pluralModelLabel = 'Kategori Barang';

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?string $navigationGroup = 'Stock';

    protected static ?int $navigationSort = 411;

    // protected static ?string $navigationIcon = null;
    // protected static ?string $navigationGroup = null;
    // protected static ?int $navigationSort = null;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Kategori')
                    ->description('Kategori Detail')
                    ->schema([
                        Forms\Components\Select::make('kategori_id')
                            ->label('Kategori')
                            ->relationship(
                                name: 'parent',
                                titleAttribute: 'nama',
                                modifyQueryUsing: fn ($query) => $query->where('is_barang', false)
                            )
                            ->searchable()
                            ->preload(),
                        Forms\Components\TextInput::make('kode')
                            ->required()
                            ->minLength(2)
                            ->maxLength(12),
                        Forms\Components\TextInput::make('nama')
                            ->label('Jenis / Nama Barang')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('keterangan')
                            ->maxLength(255),
                        Forms\Components\Select::make('satuan')
                            ->label('Satuan')
                            ->options([
                                'pcs' => 'Pcs',
                                'liter' => 'Liter',
                                'kg' => 'Kilogram',
                                'gram' => 'Gram',
                                'botol' => 'Botol',
                                'dus' => 'Dus',
                                'pak' => 'Pak',
                                'roll' => 'Roll',
                                'meter' => 'Meter',
                                'box' => 'Box',
                            ])
                            ->required()
                            ->searchable()
                            ->visible(fn ($state, $get) => $get('is_barang') === true)
                            ->nullable(),
                        Forms\Components\TextInput::make('stok_minimum')
                            ->numeric()
                            ->visible(fn ($state, $get) => $get('is_barang') === true)
                            ->nullable(),
                        Forms\Components\Toggle::make('is_barang')
                            ->label('Tandai sebagai Barang')
                            ->default(true),
                        Forms\Components\Toggle::make('is_aktif')
                            ->required()
                            ->hiddenOn('create'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('kategori.kode')
                    ->label('Kategori')
                    ->sortable()
                    ->searchable()
                    ->formatStateUsing(function ($state) {
                        // Cek jika kode diawali dengan 'CS'
                        if (substr($state, 0, 2) === 'CS') {
                            return 'Consumable';
                        }
                        // Cek jika kode diawali dengan 'HR'
                        elseif (substr($state, 0, 2) === 'HR') {
                            return 'Hardware Replacement';
                        }
                        // Jika tidak memenuhi kriteria di atas, kembalikan kode aslinya
                        return $state;
                    }),
                Tables\Columns\TextColumn::make('kategori.nama')
                    ->label('Jenis')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('kode')
                    ->label('Kode')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('nama')
                    ->label('Nama Barang')
                    ->wrap()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('keterangan')
                    ->sortable()
                    ->wrap()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('satuan')
                    ->sortable()
                    ->wrap()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('stok_minimum')
                    ->label('Stok Minimum')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('is_barang')
                    ->label('Is Barang')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_aktif')
                    ->label('Is Aktif')
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
            ])                    // Menambahkan order by 'kode'
            ->defaultSort('kode')
            ->filters([
                // Menggunakan SelectFilter untuk memfilter kolom `kategori.kode`
                SelectFilter::make('kategori_id')
                    ->label('Filter Kategori')
                    ->options([
                        'jenis' => 'Jenis',
                        'barang' => 'Barang',
                    ])
                    ->default('barang') // Set default ke 'Barang'
                    ->query(function (Builder $query, array $data) {
                        if ($data['value'] === 'barang') {
                            // Filter jika kategori_id ada (tidak null)
                            return $query->whereNotNull('kategori_id');
                        }

                        if ($data['value'] === 'jenis') {
                            // Filter jika kategori_id kosong (null)
                            return $query->whereNull('kategori_id');
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
                BulkAction::make('export')
                    ->label('Export')
                    ->color('info')
                    ->action(function ($records) {
                        $recordIds = $records->pluck('id')->toArray();
                        $date = date('Y-m-d'); // Mendapatkan tanggal saat ini dalam format YYYY-MM-DD
                        $fileName = "Kategori-{$date}.xlsx"; // Menambahkan tanggal pada nama file
                        return Excel::download(new KategoriExport($recordIds), $fileName);
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
            'index' => Pages\ListKategoris::route('/'),
            'create' => Pages\CreateKategori::route('/create'),
            'edit' => Pages\EditKategori::route('/{record}/edit'),
        ];
    }
}
