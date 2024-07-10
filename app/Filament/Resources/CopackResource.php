<?php

namespace App\Filament\Resources;

use App\Exports\CopackExport;
use App\Filament\Resources\CopackResource\Pages;
use App\Filament\Resources\CopackResource\RelationManagers;
use App\Models\Copack;
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
use Maatwebsite\Excel\Facades\Excel;


class CopackResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Copack::class;

    protected static ?string $pluralModelLabel = 'stock copack';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Transaksi';

    protected static ?int $navigationSort = 132;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Copacker')
                    ->description('Nama Copacker')
                    ->schema([
                        Forms\Components\Select::make('plant_id')
                            ->label('Copacker')
                            ->placeholder('Cari kode atau nama Copack')
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
                        Forms\Components\DatePicker::make('tgl')
                            ->label('Tanggal')
                            ->default(Carbon::today()->format('Y-m-d'))
                            ->required(),
                    ])
                    ->columns(3)
                    ->collapsible(),
                Forms\Components\Section::make('Material')
                    ->description('Detail Material')
                    ->schema([
                        Forms\Components\Select::make('material_id')
                            ->label('Kode Material')
                            ->placeholder('Cari kode atau nama material')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->getSearchResultsUsing(function (string $search) {
                                // return Plant::where('nama', 'like', "%{$search}%")
                                return Material::where(function ($query) use ($search) {
                                    $query->where('nama', 'like', "%{$search}%")
                                        ->orWhere('kode', 'like', "%{$search}%"); // Tambahkan pencarian juga berdasarkan 'kode'
                                })
                                    // ->limit(5)
                                    ->get(['kode', 'nama', 'id']) // Ambil kolom kode, nama, dan id
                                    ->mapWithKeys(function ($material) {
                                        return [$material->id => $material->kode . ' - ' . $material->nama]; // Format opsi dengan kode - nama
                                    })
                                    ->toArray();
                            })
                            ->getOptionLabelUsing(function ($value) {
                                $material = Material::find($value);
                                return $material ? $material->kode . ' - ' . $material->nama : null; // Format label dengan kode - nama
                            }),
                        Forms\Components\TextInput::make('qty')
                            ->label('Quantity')
                            ->numeric(),
                        Forms\Components\TextInput::make('keterangan')
                            ->maxLength(255),
                    ])
                    ->columns(3)
                    ->collapsible(),
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
                    ->label('Copacker')
                    ->searchable()
                    ->getStateUsing(function ($record) {
                        return $record->plant->kode . ' - ' . $record->plant->nama;
                    }),
                Tables\Columns\TextColumn::make('tgl')
                    ->label('Tanggal')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('material.kategori')
                    ->label('Kategori')
                    ->formatStateUsing(function ($state) {
                        return $state == '1' ? 'Finish Good' : ($state == '2' ? 'Raw Material' : $state);
                    })
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('material.kode')
                    ->label('Kode')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('material.nama')
                    ->label('Nama Material')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('qty')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('material.uom')
                    ->label('UoM')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('keterangan')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User Create')
                    ->numeric()
                    ->sortable()
                    ->searchable()
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
            ])->defaultSort('tgl', 'desc')
            ->filters([
                SelectFilter::make('plant_id')
                    ->label('Filter by Plant')
                    ->options(function () {
                        return Copack::with('plant')
                            ->get()
                            ->mapWithKeys(function ($item) {
                                return [$item->plant_id => $item->plant->kode . ' - ' . $item->plant->nama];
                            })
                            ->toArray();
                    }),
                SelectFilter::make('tgl')
                    ->label('Filter by Tgl')
                    ->options(function () {
                        return Copack::orderBy('tgl', 'desc')
                            ->distinct()
                            ->pluck('tgl', 'tgl')
                            ->toArray();
                    }),
                SelectFilter::make('material_id')
                    ->label('Filter by Material')
                    ->options(function () {
                        return Copack::with('material')
                            ->get()
                            ->mapWithKeys(function ($item) {
                                return [$item->material_id => $item->material->kode . ' - ' . $item->material->nama];
                            })
                            ->toArray();
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
                        $fileName = "stock-copack-{$date}.xlsx"; // Menambahkan tanggal pada nama file
                        return Excel::download(new CopackExport($recordIds), $fileName);
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
            'index' => Pages\ListCopacks::route('/'),
            'create' => Pages\CreateCopack::route('/create'),
            'edit' => Pages\EditCopack::route('/{record}/edit'),
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
