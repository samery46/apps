<?php

namespace App\Filament\Resources;

use App\Filament\Clusters\Plants;
use App\Filament\Resources\DepartemenResource\Pages;
use App\Filament\Resources\DepartemenResource\RelationManagers;
use App\Models\Departemen;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Filters\TernaryFilter;

class DepartemenResource extends Resource
{
    protected static ?string $model = Departemen::class;

    protected static ?string $pluralModelLabel = 'departemen';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    // protected static ?string $navigationGroup = 'Plant';
    protected static ?string $cluster = Plants::class;

    protected static ?int $navigationSort = 103;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Departemen')
                    ->description('Departemen Detail')
                    ->schema([
                        Forms\Components\Select::make('departemen_id')
                            ->label('Parent')
                            // ->relationship('departemen', 'nama')
                            ->relationship('departemen', 'nama', function ($query) {
                                $query->whereNotIn('id', [1, 6, 15, 18]); // query tdk menampilkan Dept GA, MFG, MKT
                            })

                            ->searchable()
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
                        Forms\Components\TextInput::make('kode')
                            ->required()
                            ->minLength(2)
                            ->maxLength(12),
                        Forms\Components\TextInput::make('nama')
                            ->label('Nama Dept/Sub-Dept')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('keterangan')
                            ->maxLength(255),
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
                Tables\Columns\TextColumn::make('departemen.kode')
                    ->label('Dept')
                    ->numeric()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('kode')
                    ->label('Sub-Dept')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nama')
                    ->searchable()
                    ->sortable(),
                // Tables\Columns\TextColumn::make('keterangan')
                //     ->label('Ket')
                //     ->searchable(),
                // Tables\Columns\TextColumn::make('user.name')->label('User'),
                // Tables\Columns\IconColumn::make('is_aktif')
                //     ->label('Is Aktif')
                //     ->boolean()
                //     ->sortable(),
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
                SelectFilter::make('departemen_id')
                    ->relationship('departemen', 'kode')
                    ->label('Filter by Parent')
                    ->options(Departemen::all()->pluck('kode', 'id')->toArray()),

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
                // Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListDepartemens::route('/'),
            'create' => Pages\CreateDepartemen::route('/create'),
            'edit' => Pages\EditDepartemen::route('/{record}/edit'),
        ];
    }
}
