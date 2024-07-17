<?php

namespace App\Filament\Resources;

use App\Exports\PlantExport;
use App\Filament\Clusters\Plants;
use App\Filament\Resources\PlantResource\Pages;
use App\Filament\Resources\PlantResource\RelationManagers;
use App\Models\Company;
use App\Models\Plant;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Filters\TernaryFilter;
use App\Helpers\EmailHelper; // Pastikan Anda mengimpor EmailHelper
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Tables\Actions\BulkAction;
use Maatwebsite\Excel\Facades\Excel;

class PlantResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Plant::class;

    protected static ?string $pluralModelLabel = 'plant';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $cluster = Plants::class;

    protected static ?int $navigationSort = 102;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Plant')
                            ->description('Plant Detail')
                            ->schema([
                                Forms\Components\Select::make('company_id')
                                    ->relationship('company', 'nama')
                                    ->options(function () {
                                        return Company::all()->mapWithKeys(function ($company) {
                                            return [$company->id => $company->kode . ' - ' . $company->nama];
                                        })->toArray();
                                    })
                                    ->required(),
                                Forms\Components\TextInput::make('kode')
                                    ->required()
                                    ->length(4),
                                Forms\Components\TextInput::make('nama')
                                    ->maxLength(255),
                            ])->columns(2),
                    ]),

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Alamat')
                            ->description('Plant Detail')
                            ->schema([

                                Forms\Components\Textarea::make('alamat')
                                    ->autosize(),
                                Forms\Components\TextInput::make('kota')
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('pos')
                                    ->label('Kode POS')
                                    ->length(5),
                                Forms\Components\TextInput::make('telp')
                                    ->tel()
                                    ->telRegex('/^[+]*[(]{0,1}[0-9]{1,4}[)]{0,1}[-\s\.\/0-9]*$/'),
                                Forms\Components\Toggle::make('is_aktif')
                                    ->required()
                                    ->hiddenOn('create')
                            ])->columns(2),

                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('company.kode')
                    ->label('Company')
                    ->numeric()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('kode_and_nama')
                    ->label('Kode Plant')
                    ->getStateUsing(function ($record) {
                        return $record->kode . ' - ' . $record->nama;
                    })
                    ->toggleable(isToggledHiddenByDefault: false),

                Tables\Columns\TextColumn::make('kode')
                    ->label('Kode')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('nama')
                    ->label('Plant')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('kota')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('alamat')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('pos')
                    ->label('Kode POS')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('telp')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('is_aktif')
                    ->sortable()
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
            ])->defaultSort('kode', 'asc')
            ->filters([
                SelectFilter::make('company_id')
                    ->relationship('company', 'kode')
                    ->label('Filter by Company')
                    ->options(Company::all()->pluck('kode', 'id')->toArray()),

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
                        $fileName = "plant-{$date}.xlsx"; // Menambahkan tanggal pada nama file
                        return Excel::download(new PlantExport($recordIds), $fileName);
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
            'index' => Pages\ListPlants::route('/'),
            'create' => Pages\CreatePlant::route('/create'),
            'edit' => Pages\EditPlant::route('/{record}/edit'),
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




    // public static function afterSave($record): void
    // {
    //     $to = 'sam@ketik-kan.com'; // Ganti dengan email penerima
    //     $subject = 'Plant Data Updated';
    //     $body = "Plant with kode: {$record->kode} and nama: {$record->nama} has been updated.";

    //     EmailHelper::sendEmail($to, $subject, $body);
    // }

    // public static function afterCreate($record): void
    // {
    //     $to = 'sam@ketik-kan.com'; // Ganti dengan email penerima
    //     $subject = 'New Plant Created';
    //     $body = "New Company with kode: {$record->kode} and nama: {$record->nama} has been created.";

    //     EmailHelper::sendEmail($to, $subject, $body);
    // }
}
