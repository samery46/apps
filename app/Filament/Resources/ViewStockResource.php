<?php

namespace App\Filament\Resources;

use App\Exports\ViewStockExport;
use App\Filament\Resources\ViewStockResource\Pages;
use App\Filament\Resources\ViewStockResource\RelationManagers;
use App\Models\ViewStock;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Facades\Excel;

class ViewStockResource extends Resource
{
    protected static ?string $model = ViewStock::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?string $navigationGroup = 'Stock';

    protected static ?int $navigationSort = 414;


    // ✅ Di sinilah getTableQuery diletakkan
    public static function getTableQuery(): Builder
    {
        return ViewStock::query()->select([
            'id',
            'barang_id',
            'plant_id',
            'total_masuk',
            'total_keluar',
        ]);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('plant.kode')
                    ->label('Kode')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('plant_info')
                    ->label('Plant')
                    ->getStateUsing(fn ($record) =>
                        $record->plant?->kode . ' - ' . $record->plant?->nama
                    )
                    ->sortable(),
                Tables\Columns\TextColumn::make('barang.kode')
                    ->label('Kode')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('barang.nama')
                    ->label('Barang')
                    ->limit(60) // membatasi tampilan maksimal 60 karakter
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_masuk')
                    ->label('Masuk')
                    ->numeric(),
                    // ->sortable(),

                Tables\Columns\TextColumn::make('total_keluar')
                    ->label('Keluar')
                    ->numeric(),
                    // ->sortable(),
                Tables\Columns\TextColumn::make('sisa_stok')
                    ->label('Sisa')
                    ->numeric()
                    ->color(fn ($state) => $state < 10 ? 'danger' : ($state < 30 ? 'warning' : 'success'))
                    ->sortable(),
            ])
            ->defaultSort('plant.kode')
            ->filters([
                SelectFilter::make('plant_id')
                    ->label('Filter by Plant')
                    ->options(function () {
                        $query = ViewStock::with('plant')
                            ->select('plant_id') // ambil hanya kolom plant_id
                            ->whereNotNull('plant_id') // pastikan valid
                            ->distinct() // ambil unik
                            ->get();

                        return $query
                            ->mapWithKeys(function ($item) {
                                return [$item->plant_id => "{$item->plant->kode} - {$item->plant->nama}"];
                            })
                            ->sortKeys()
                            ->toArray();
                    })
                    ->searchable(),
                SelectFilter::make('barang_id')
                    ->label('Barang')
                    ->options(function () {
                        return ViewStock::with('barang')
                            ->select('barang_id')       // hanya ambil ID
                            ->whereNotNull('barang_id') // pastikan valid
                            ->distinct()
                            ->get()
                            ->mapWithKeys(fn ($item) => [
                                $item->barang_id => "{$item->barang->nama}"
                            ])
                            ->toArray();
                    })
                    ->searchable(),
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
            ])
            ->headerActions([
                Tables\Actions\Action::make('export_all')
                    ->label('Export Excel (Semua)')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(function () {
                        $date = date('Y-m-d_His');
                        $fileName = "ViewStock-Semua-{$date}.xlsx";
                        return Excel::download(new ViewStockExport(), $fileName);
                    }),
            ])
            ->bulkActions([
                BulkAction::make('export_selected')
                    ->label('Export Excel (Terpilih)')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->action(function ($records) {
                        $recordIds = $records->pluck('id')->toArray();
                        $date = date('Y-m-d_His');
                        $fileName = "ViewStock-Terpilih-{$date}.xlsx";
                        return Excel::download(new ViewStockExport($recordIds), $fileName);
                    }),
            ]);
    }

    // ⛔ Nonaktifkan aksi edit, buat, hapus
    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $model): bool
    {
        return false;
    }

    public static function canDelete(Model $model): bool
    {
        return false;
    }

    public static function isReadOnly(): bool
{
    return true;
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
            'index' => Pages\ListViewStocks::route('/'),
            // 'create' => Pages\CreateViewStock::route('/create'),
            // 'edit' => Pages\EditViewStock::route('/{record}/edit'),
        ];
    }
}
