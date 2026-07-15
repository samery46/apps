<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TypeResource\Pages;
use App\Filament\Resources\TypeResource\RelationManagers;
use App\Models\Type;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TypeResource extends Resource
{
    protected static ?string $model = Type::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $navigationGroup = 'Master';
    protected static ?int $navigationSort = 119;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Type')
                    ->description('Type Transaksi Detail')
                    ->schema([

                        Forms\Components\TextInput::make('nama')
                            ->label('Type Transaksi')
                            ->required()
                            ->maxLength(50),
                        // Forms\Components\TextInput::make('keterangan')
                        //     ->maxLength(100),
                        Forms\Components\Select::make('keterangan')
                            ->label('Kategori')
                            ->options([
                                '1' => 'Finish Goods',
                                '2' => 'Raw Material',
                            ])
                            ->native(false),
                        Forms\Components\Toggle::make('is_aktif')
                            ->required(),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama')
                    ->label('Type Transaksi')
                    ->searchable(),
                Tables\Columns\TextColumn::make('keterangan')
                    ->label('Kategori')
                    ->formatStateUsing(function ($state) {
                        return $state == '1' ? 'Finish Goods' : ($state == '2' ? 'Raw Material' : $state);
                    })
                    ->sortable()
                    ->searchable(),
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
            ->defaultSort('nama', 'asc') // Urutkan secara default berdasarkan kolom 'nama' secara ascending
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
            'index' => Pages\ListTypes::route('/'),
            'create' => Pages\CreateType::route('/create'),
            'edit' => Pages\EditType::route('/{record}/edit'),
        ];
    }
}
