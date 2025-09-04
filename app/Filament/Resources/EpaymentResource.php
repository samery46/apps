<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EpaymentResource\Pages;
use App\Filament\Resources\EpaymentResource\RelationManagers;
use App\Models\Epayment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EpaymentResource extends Resource
{
    protected static ?string $model = Epayment::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('bill_to')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('bill_to_name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('vendor')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('vendor_name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('ticket_no')
                    ->required()
                    ->maxLength(255),
                Forms\Components\DatePicker::make('ticket_date')
                    ->required(),
                Forms\Components\TextInput::make('miro_no')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\DatePicker::make('miro_date'),
                Forms\Components\DatePicker::make('paid_date'),
                Forms\Components\TextInput::make('po_no')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('sj_no')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('gr_no')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('amount')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('status')
                    ->required()
                    ->maxLength(255),
                Forms\Components\DatePicker::make('created_date')
                    ->required(),
                Forms\Components\TextInput::make('created_time')
                    ->required(),
                Forms\Components\TextInput::make('confirm_by')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('composite_key')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('bill_to')
                    ->searchable(),
                Tables\Columns\TextColumn::make('bill_to_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('vendor')
                    ->searchable(),
                Tables\Columns\TextColumn::make('vendor_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('ticket_no')
                    ->searchable(),
                Tables\Columns\TextColumn::make('ticket_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('miro_no')
                    ->searchable(),
                Tables\Columns\TextColumn::make('miro_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('paid_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('po_no')
                    ->searchable(),
                Tables\Columns\TextColumn::make('sj_no')
                    ->searchable(),
                Tables\Columns\TextColumn::make('gr_no')
                    ->searchable(),
                Tables\Columns\TextColumn::make('amount')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_time'),
                Tables\Columns\TextColumn::make('confirm_by')
                    ->searchable(),
                Tables\Columns\TextColumn::make('composite_key')
                    ->searchable(),
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
            'index' => Pages\ListEpayments::route('/'),
            'create' => Pages\CreateEpayment::route('/create'),
            'edit' => Pages\EditEpayment::route('/{record}/edit'),
        ];
    }
}
