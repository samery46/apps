<?php

namespace App\Filament\Resources;

use App\Filament\Clusters\Plants;
use App\Filament\Resources\CompanyResource\Pages;
use App\Filament\Resources\CompanyResource\RelationManagers;
use App\Models\Company;
use App\Models\Plant;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;

class CompanyResource extends Resource
{
    protected static ?string $model = Company::class;

    protected static ?string $pluralModelLabel = 'company';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    // protected static ?string $navigationGroup = 'Plant';
    protected static ?string $cluster = Plants::class;

    protected static ?int $navigationSort = 100;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Company')
                            ->description('Company Detail')
                            ->schema([
                                Forms\Components\TextInput::make('kode')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('nama')
                                    ->maxLength(255),
                            ]),
                    ]),
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Alamat')
                            ->description('Alamat Detail')
                            ->schema([
                                Forms\Components\Textarea::make('alamat')
                                    ->autosize(),
                                Forms\Components\TextInput::make('kota')
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('pos')
                                    ->label('Kode POS')
                                    ->length(5),
                                Forms\Components\Toggle::make('is_aktif')
                                    ->required()
                                    ->hiddenOn('create'),
                            ])->columns(2),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('kode')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nama')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('kota')
                    ->searchable()
                    ->sortable(),
                // Tables\Columns\TextColumn::make('alamat')
                //     ->searchable(),
                // Tables\Columns\TextColumn::make('pos')
                //     ->label('Kode POS')
                //     ->searchable(),
                Tables\Columns\IconColumn::make('is_aktif')
                    ->boolean()
                    ->sortable(),
                // Tables\Columns\TextColumn::make('user.name')
                //     ->label('Create by')
                //     ->sortable()
                //     ->searchable(),
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
            'index' => Pages\ListCompanies::route('/'),
            'create' => Pages\CreateCompany::route('/create'),
            'edit' => Pages\EditCompany::route('/{record}/edit'),
        ];
    }
}
