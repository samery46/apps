<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CostCenterResource\Pages;
use App\Filament\Resources\CostCenterResource\RelationManagers;
use App\Models\CostCenter;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\Plant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;

class CostCenterResource extends Resource
{
    protected static ?string $model = CostCenter::class;
    protected static ?string $pluralModelLabel = 'Cost Center';

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $navigationGroup = 'Asset Management';

    protected static ?int $navigationSort = 311;
    public static function form(Form $form): Form
    {
        return $form
        ->schema([

            Forms\Components\Section::make('Cost Center')
            ->description('Informasi Detail')
            ->schema([
                Forms\Components\Select::make('plant_id')
                    // ->relationship('plant', 'nama')
                    ->label('Plant')
                    ->placeholder('Cari kode atau nama plant')
                    ->required()
                    ->columnSpan(3)
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
                                return [$plant->id => $plant->kode . ' - TSP ' . $plant->nama]; // Format opsi dengan kode - nama
                            })
                            ->toArray();
                    })
                    ->getOptionLabelUsing(function ($value) {
                        $plant = Plant::find($value);
                        return $plant ? $plant->kode . ' - TSP ' . $plant->nama : null; // Format label dengan kode - nama
                    }),
                Forms\Components\TextInput::make('cost_center')
                    ->required()
                    ->label('Cost Center')
                    ->placeholder('Isikan kode Cost Center')
                    ->columnSpan(3)
                    ->maxLength(255),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->columnSpan(3)
                    ->placeholder('Isikan nama Cost Center')
                    ->maxLength(255),
                Forms\Components\TextInput::make('short_text')
                    ->maxLength(255)
                    ->label('Short Text')
                    ->placeholder('Isikan Short Text')
                    ->columnSpan(3),
                Forms\Components\Toggle::make('is_aktif')
                    ->required()
                    ->columnSpan(2),
                Forms\Components\Hidden::make('user_id')
                    ->default(Auth::id())
                    ->required(),

            ])->columns(8),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('plant.kode')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('cost_center')
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('short_text')
                    ->label('Short Text')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_aktif')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Created By')
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
            ->defaultSort('cost_center', 'asc')
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
            'index' => Pages\ListCostCenters::route('/'),
            'create' => Pages\CreateCostCenter::route('/create'),
            'edit' => Pages\EditCostCenter::route('/{record}/edit'),
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
