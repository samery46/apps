<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WorkflowConfigResource\Pages;
use App\Filament\Resources\WorkflowConfigResource\RelationManagers;
use App\Models\WorkflowConfig;
use App\Models\Plant;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class WorkflowConfigResource extends Resource
{
    protected static ?string $model = WorkflowConfig::class;

    protected static ?string $pluralModelLabel = 'Workflow Asuransi';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    
    protected static ?string $navigationGroup = 'Asset Management';

    protected static ?int $navigationSort = 314;


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('plant_id')
                    ->label('Plant')
                    ->placeholder('Cari kode atau nama Plant')
                    ->searchable()
                    ->preload()
                    ->getSearchResultsUsing(function (string $search) {
                        return Plant::where(function ($query) use ($search) {
                            $query->where('nama', 'like', "%{$search}%")
                                ->orWhere('kode', 'like', "%{$search}%");
                        })
                            ->get(['kode', 'nama', 'id'])
                            ->mapWithKeys(function ($plant) {
                                return [$plant->id => $plant->kode . ' - ' . $plant->nama];
                            })
                            ->toArray();
                    })
                    ->getOptionLabelUsing(function ($value) {
                        $plant = Plant::find($value);
                        return $plant ? $plant->kode . ' - ' . $plant->nama : null;
                    })                    
                    ->required(),
                

                Forms\Components\Select::make('user_id') // Gunakan user_id, bukan user.name
                    ->label('User Approval')
                    ->placeholder('Cari nama atau email User')
                    ->searchable()
                    ->preload()
                    ->getSearchResultsUsing(function (string $search) {
                        return User::where(function ($query) use ($search) {
                            $query->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        })
                            ->limit(50)
                            ->get(['id', 'name', 'email'])
                            ->mapWithKeys(function ($user) {
                                return [$user->id => $user->name . ' (' . $user->email . ')'];
                            })
                            ->toArray();
                    })
                    ->getOptionLabelUsing(function ($value) {
                        $user = User::find($value);
                        return $user ? $user->name . ' (' . $user->email . ')' : null;
                    })
                    ->required(),                    
                Forms\Components\TextInput::make('step_order')
                    ->required()
                    ->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('plant.kode')
                    ->label('Plant')
                    ->searchable()
                    ->sortable()
                    ->getStateUsing(function ($record) {
                        return $record->plant->kode . ' - TSP ' . $record->plant->nama;
                    }),
                Tables\Columns\TextColumn::make('step_order')
                    ->numeric()
                    ->sortable(),                    
                Tables\Columns\TextColumn::make('user.name')->label('Approver')
                    ->sortable(),
                
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
            'index' => Pages\ListWorkflowConfigs::route('/'),
            'create' => Pages\CreateWorkflowConfig::route('/create'),
            'edit' => Pages\EditWorkflowConfig::route('/{record}/edit'),
        ];
    }
}
