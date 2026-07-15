<?php

namespace App\Filament\Resources;

use App\Exports\MaterialExport;
use App\Filament\Resources\MaterialResource\Pages;
use App\Filament\Resources\MaterialResource\RelationManagers;
use App\Models\Material;
use App\Models\Plant;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Hidden;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Maatwebsite\Excel\Facades\Excel;

class MaterialResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Material::class;

    protected static ?string $pluralModelLabel = 'material';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Master';

    protected static ?int $navigationSort = 113;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Material')
                    ->description('Detail Material')
                    ->schema([
                        Forms\Components\TextInput::make('kode')
                            ->label('Kode Material')
                            ->required()
                            ->columnSpan(2)
                            ->maxLength(255),
                        Forms\Components\TextInput::make('nama')
                            ->label('Nama Material')
                            ->columnSpan(4)
                            ->maxLength(255),
                        Forms\Components\TextInput::make('uom')
                            ->label('UoM')
                            ->columnSpan(2)
                            ->maxLength(255),
                        Forms\Components\Select::make('kategori')
                            ->options([
                                '1' => 'Finish Goods',
                                '2' => 'Raw Material',
                            ])
                            ->native(false)
                            ->columnSpan(2),
                        Forms\Components\TextInput::make('group')
                            ->maxLength(255)
                            ->columnSpan(2),
                        Forms\Components\TextInput::make('keterangan')
                            ->maxLength(255)
                            ->columnSpan(4),
                        Forms\Components\Toggle::make('is_aktif')
                            ->required()
                            ->hiddenOn('create')
                            ->columnSpan(2),
                        Forms\Components\Hidden::make('user_id')
                            ->default(fn() => Auth::id())
                            ->required(),


                    ])
                    ->columns(8)
                    ->collapsible(),

                Forms\Components\Section::make('Plant')
                    ->description('Akses Plant')
                    ->schema([
                        Forms\Components\Checkbox::make('select_all_plants')
                            ->label('Akses All Plant')
                            ->reactive()
                            ->afterStateUpdated(function (callable $set, $state) {
                                if ($state) {
                                    // Centang semua checkbox jika "Akses All" dicentang
                                    $set('plants', \App\Models\Plant::all()->pluck('id')->toArray());
                                } else {
                                    // Kosongkan semua checkbox jika "Akses All" tidak dicentang
                                    $set('plants', []);
                                }
                            }),

                        Forms\Components\CheckboxList::make('plants')
                            ->relationship('plants', 'nama')
                            ->options(function () {
                                return \App\Models\Plant::all()
                                    ->sortBy('kode')
                                    ->mapWithKeys(function ($plant) {
                                        return [$plant->id => "{$plant->kode} - {$plant->nama}"];
                                    })->toArray();
                            })
                            ->label('Plants')
                            ->columns(4), // Untuk menampilkan checkbox dalam dua kolom,
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {

        // $plantOptions = Plant::all()->pluck('kode', 'id');

        // $plantOptions = Plant::whereIn('id', [39, 40, 41])->pluck('kode', 'id');
        // $selectedDate = request()->get('tgl', Carbon::now()->toDateString());

        // Filter plantOptions untuk hanya menampilkan plant dengan stok lebih dari 0
        // $filteredPlantOptions = array_filter($plantOptions->toArray(), function ($plantKode, $plantId) use ($selectedDate) {
        //     // Ambil objek Material pertama untuk contoh, atau sesuaikan sesuai kebutuhan
        //     $material = Material::first(); // Pastikan ada data Material
        //     return $material->getStokPerPlantDanTanggal($plantId, $selectedDate) > 0;
        // }, ARRAY_FILTER_USE_BOTH);


        return $table
            ->columns([
                Tables\Columns\TextColumn::make('kode')
                    ->label('Kode')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('nama')
                    ->label('Nama Material')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('kategori')
                    ->formatStateUsing(function ($state) {
                        return $state == '1' ? 'Finish Goods' : ($state == '2' ? 'Raw Material' : $state);
                    })
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('uom')
                    ->label('UoM')
                    ->sortable()
                    ->searchable(),
                // Tables\Columns\TextColumn::make('group')
                //     ->sortable()
                //     ->searchable(),

                // Tables\Columns\TextColumn::make('stok')
                //     ->label('Stok')
                //     ->sortable()
                //     ->getStateUsing(fn (Material $record) => $record->stok),

                // ...array_map(function ($plantId, $plantKode) {
                //     return Tables\Columns\TextColumn::make("stok_plant_{$plantId}")
                //         ->label("Stok $plantKode")
                //         ->getStateUsing(fn (Material $record) => $record->getStokPerPlant($plantId));
                // }, array_keys($plantOptions->toArray()), $plantOptions->toArray()),


                // ini yang terakhir bisa
                // ...array_map(function ($plantId, $plantKode) use ($selectedDate) {
                //     return Tables\Columns\TextColumn::make("stok_plant_{$plantId}")
                //         ->label("$plantKode")
                //         ->getStateUsing(fn (Material $record) => $record->getStokPerPlantDanTanggal($plantId, $selectedDate));
                // }, array_keys($plantOptions->toArray()), $plantOptions->toArray()),
                // Sampai sini


                // ...array_map(function ($plantId, $plantKode) use ($selectedDate) {
                //     return TextColumn::make("stok_plant_{$plantId}")
                //         ->label("$plantKode")
                //         ->getStateUsing(fn (Material $record) => $record->getStokPerPlantDanTanggal($plantId, $selectedDate));
                // }, array_keys($filteredPlantOptions), $filteredPlantOptions),

                Tables\Columns\TextColumn::make('group')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('plants.kode')
                    ->label('Plant')
                    ->searchable()
                    ->formatStateUsing(fn($state) => is_array($state) ? implode(', ', $state) : ($state ?? '-')),
                Tables\Columns\IconColumn::make('is_aktif')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('keterangan')
                    ->searchable()
                    ->sortable()
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
            ])->defaultSort('kode', 'asc')
            ->filters([

                Tables\Filters\Filter::make('tgl')
                    ->form([
                        Forms\Components\DatePicker::make('tgl')
                            ->label('Tanggal')
                            ->default(Carbon::now()->toDateString())
                            ->required(),
                    ])
                    ->query(function ($query, array $data) {
                        return $query;
                    }),

                SelectFilter::make('kategori')
                    ->label('Filter by Kategori')
                    ->options([
                        '1' => 'Finish Goods',
                        '2' => 'Raw Material',
                    ]),
                SelectFilter::make('uom')
                    ->label('Filter by UoM')
                    ->options(function () {
                        return Material::distinct()
                            ->pluck('uom', 'uom')
                            ->toArray();
                    }),
                TernaryFilter::make('is_aktif')
                    ->label('Filter by Aktif')
                    ->trueLabel('Aktif')
                    ->falseLabel('Non Aktif')
                    ->placeholder('Semua')
                    ->queries(
                        true: fn(Builder $query): Builder => $query->where('is_aktif', true),
                        false: fn(Builder $query): Builder => $query->where('is_aktif', false),
                        blank: fn(Builder $query): Builder => $query
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
                        $fileName = "material-{$date}.xlsx"; // Menambahkan tanggal pada nama file
                        return Excel::download(new MaterialExport($recordIds), $fileName);
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
            'index' => Pages\ListMaterials::route('/'),
            'create' => Pages\CreateMaterial::route('/create'),
            'edit' => Pages\EditMaterial::route('/{record}/edit'),
        ];
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
