<?php

namespace App\Filament\Resources;

use App\Exports\MaterialExport;
use App\Filament\Resources\MaterialResource\Pages;
use App\Filament\Resources\MaterialResource\RelationManagers;
use App\Models\Material;
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
use Maatwebsite\Excel\Facades\Excel;

class MaterialResource extends Resource
{
    protected static ?string $model = Material::class;

    protected static ?string $pluralModelLabel = 'material';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Master';

    protected static ?int $navigationSort = 115;

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
                            ->maxLength(255),
                        Forms\Components\Select::make('kategori')
                            ->options([
                                '1' => 'Finish Goods',
                                '2' => 'Raw Material',
                            ])
                            ->native(false),
                        Forms\Components\TextInput::make('nama')
                            ->label('Nama Material')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('group')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('uom')
                            ->label('UoM')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('keterangan')
                            ->maxLength(255),
                        Forms\Components\Toggle::make('is_aktif')
                            ->required()
                            ->hiddenOn('create'),
                        Forms\Components\Hidden::make('user_id')
                            ->default(fn () => Auth::id())
                            ->required(),
                    ])
                    ->columns(2)
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // Tables\Columns\TextColumn::make('user_id')
                //     ->numeric()
                //     ->sortable(),
                Tables\Columns\TextColumn::make('kode')
                    ->label('Kode')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nama')
                    ->label('Nama Material')
                    ->searchable(),
                Tables\Columns\TextColumn::make('kategori')
                    ->formatStateUsing(function ($state) {
                        return $state == '1' ? 'Finish Good' : ($state == '2' ? 'Raw Material' : $state);
                    })
                    ->searchable(),
                Tables\Columns\TextColumn::make('uom')
                    ->label('UoM')
                    ->searchable(),
                Tables\Columns\TextColumn::make('group')
                    ->searchable(),
                // Tables\Columns\TextColumn::make('keterangan')
                //     ->searchable(),
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
                //
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
}
