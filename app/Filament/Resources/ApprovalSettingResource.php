<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ApprovalSettingResource\Pages;
use App\Filament\Resources\ApprovalSettingResource\RelationManagers;
use App\Models\ApprovalSetting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ApprovalSettingResource extends Resource
{
    protected static ?string $model = ApprovalSetting::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('plant_id')
                    ->relationship('plant', 'nama')
                    ->label('Plant')
                    ->searchable()
                    ->required()
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->kode} - TSP {$record->nama}"),
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->required()
                    ->label('Approver User'),
                Forms\Components\Select::make('level')
                    ->options([
                        1 => 'Level 1 (FAM/FAS Plant)',
                        2 => 'Level 2 (FA HO)',
                        3 => 'Level 3 (FAM HO)',
                    ])
                    ->required()
                    ->label('Approval Level'),
                Forms\Components\TextInput::make('position')
                    ->maxLength(255)
                    ->default(null),
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
                        return $record->plant->kode . ' - TSP ' . $record->plant->nama;
                    }),
                Tables\Columns\TextColumn::make('level')
                    ->label('Approval Level')
                    ->sortable()
                    ->formatStateUsing(function ($state) {
                        return match ($state) {
                            1 => 'Level 1 (FAM/FAS Plant)',
                            2 => 'Level 2 (FA HO)',
                            3 => 'Level 3 (FAM HO)',
                            default => 'Unknown',
                        };
                    }),
                Tables\Columns\TextColumn::make('user.name')->label('Approver')
                    ->sortable(),
                Tables\Columns\TextColumn::make('position')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_aktif')
                    ->boolean(),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
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
            'index' => Pages\ListApprovalSettings::route('/'),
            'create' => Pages\CreateApprovalSetting::route('/create'),
            'edit' => Pages\EditApprovalSetting::route('/{record}/edit'),
        ];
    }
}
