<?php

namespace App\Filament\Resources;

use App\Filament\Clusters\Transaksi;
use App\Filament\Resources\PerangkatResource\Pages;
use App\Filament\Resources\PerangkatResource\RelationManagers;
use App\Imports\PerangkatsImport;
use App\Models\Perangkat;
use App\Models\Plant;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Pages\Actions\CreateAction;
use Illuminate\Support\Facades\Storage;

class PerangkatResource extends Resource
{
    protected static ?string $model = Perangkat::class;

    protected static ?string $pluralModelLabel = 'perangkat';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $cluster = Transaksi::class;

    protected static ?int $navigationSort = 105;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nama')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('serial_number')
                    ->maxLength(255),
                Forms\Components\TextInput::make('keterangan')
                    ->maxLength(255),
                Forms\Components\TextInput::make('qty')
                    ->numeric(),
                Forms\Components\Select::make('plant_id')
                    ->relationship('plant', 'kode')
                    ->required(),
                Forms\Components\Toggle::make('is_aktif')
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
                Tables\Columns\TextColumn::make('nama')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('serial_number')
                    ->searchable()
                    ->sortable(),
                // Tables\Columns\TextColumn::make('keterangan')
                //     ->searchable()
                //     ->sortable(),
                // Tables\Columns\TextColumn::make('qty')
                //     ->numeric()
                //     ->sortable(),
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

                SelectFilter::make('plant_id')
                    ->relationship('plant', 'kode')
                    ->label('Filter by Plant')
                    ->options(Plant::all()->pluck('kode', 'id')->toArray()),

            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListPerangkats::route('/'),
            'create' => Pages\CreatePerangkat::route('/create'),
            'edit' => Pages\EditPerangkat::route('/{record}/edit'),
        ];
    }
}
