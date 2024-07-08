<?php

namespace App\Filament\Resources;

use App\Exports\UidsapExport;
use App\Filament\Resources\UidsapResource\Pages;
use App\Filament\Resources\UidsapResource\RelationManagers;
use App\Models\Uidsap;
use App\Models\Company;
use App\Models\Departemen;
use App\Models\Karyawan;
use App\Models\Plant;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Maatwebsite\Excel\Facades\Excel;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Filters\TernaryFilter;

class UidsapResource extends Resource
{
    protected static ?string $model = Uidsap::class;

    protected static ?string $pluralModelLabel = 'UID SAP';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Master';

    protected static ?int $navigationSort = 114;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi')
                    ->description('Informasi Use ID SAP Detail')
                    ->schema([
                        Forms\Components\TextInput::make('username')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\DatePicker::make('valid_from'),
                        Forms\Components\TextInput::make('cost_center')
                            ->maxLength(255),
                        Forms\Components\Select::make('karyawan_id')
                            ->label('Nama')
                            ->relationship('karyawan', 'nama')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->getSearchResultsUsing(function (string $search) {
                                return Karyawan::where('nama', 'like', "%{$search}%")
                                    ->limit(5)
                                    ->pluck('nama', 'id')
                                    ->toArray();
                            })
                            ->getOptionLabelUsing(function ($value) {
                                $karyawan = Karyawan::find($value);
                                return $karyawan ? $karyawan->nama : null;
                            }),

                        Forms\Components\DatePicker::make('valid_end'),
                        Forms\Components\TextInput::make('keterangan')
                            ->maxLength(255),
                        Forms\Components\Toggle::make('is_aktif')
                            ->required(),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('plant.kode')
                    ->label('Plant')
                    ->getStateUsing(function ($record) {
                        $karyawanId = $record->karyawan_id;
                        $plantId = optional(Karyawan::find($karyawanId))->plant_id;
                        $plant = Plant::find($plantId);
                        if ($plant) {
                            return $plant->kode . ' - ' . $plant->nama;
                        }
                        return '-';
                    }),

                Tables\Columns\TextColumn::make('departemen.kode')
                    ->label('Dept')
                    ->getStateUsing(function ($record) {
                        $karyawanId = $record->karyawan_id;
                        $departemenId = optional(Karyawan::find($karyawanId))->departemen_id;
                        $departemen = Departemen::find($departemenId);
                        if ($departemen) {
                            return $departemen->kode;
                        }
                        return '-';
                    }),
                Tables\Columns\TextColumn::make('username')
                    ->label('UID SAP')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('karyawan.nama')
                    ->label('Nama')
                    ->numeric()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('karyawan.job_title')
                    ->label('Job Title')
                    ->numeric()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('valid_from')
                    ->label('Valid From')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('cost_center')
                    ->label('Cost Center')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_aktif')
                    ->label('Aktif')
                    ->boolean()
                    ->sortable(),
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
                TernaryFilter::make('is_aktif')
                    ->label('Filter by Aktif')
                    ->trueLabel('Aktif')
                    ->falseLabel('Non Aktif')
                    ->placeholder('All')
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
                        $fileName = "uidsap-{$date}.xlsx"; // Menambahkan tanggal pada nama file
                        return Excel::download(new UidsapExport($recordIds), $fileName);
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
            'index' => Pages\ListUidsaps::route('/'),
            'create' => Pages\CreateUidsap::route('/create'),
            'edit' => Pages\EditUidsap::route('/{record}/edit'),
        ];
    }
}
