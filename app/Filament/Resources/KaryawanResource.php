<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KaryawanResource\Pages;
use App\Models\Departemen;
use App\Models\Karyawan;
use App\Models\Plant;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Actions\Action;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\KaryawanExport;
use Filament\Tables\Actions\BulkAction;

class KaryawanResource extends Resource
{
    protected static ?string $model = Karyawan::class;

    protected static ?string $pluralModelLabel = 'karyawan';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Master';

    protected static ?int $navigationSort = 103;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Identitas')
                            ->description('Informasi Karyawan')
                            ->schema([
                                Forms\Components\TextInput::make('nik')
                                    ->label('NIK')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('nama')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\DatePicker::make('tgl_lahir')
                                    ->label('Tgl Lahir'),
                            ])->collapsible(),
                    ]),
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Plant / Dept')
                            ->description('Plant - Departemen - Sub Departemen')
                            ->schema([
                                Forms\Components\Select::make('plant_id')
                                    // ->relationship('plant', 'nama')
                                    ->label('Plant')
                                    ->placeholder('Cari kode atau nama plant')
                                    ->required()
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
                                                return [$plant->id => $plant->kode . ' - ' . $plant->nama]; // Format opsi dengan kode - nama
                                            })
                                            ->toArray();
                                    })
                                    ->getOptionLabelUsing(function ($value) {
                                        $plant = Plant::find($value);
                                        return $plant ? $plant->kode . ' - ' . $plant->nama : null; // Format label dengan kode - nama
                                    }),
                                Forms\Components\Select::make('departemen_id')
                                    ->relationship('departemen', 'nama')
                                    ->placeholder('Cari nama departemen')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->getSearchResultsUsing(function (string $search) {
                                        return Departemen::where('nama', 'like', "%{$search}%")
                                            ->limit(5)
                                            ->pluck('nama', 'id')
                                            ->toArray();
                                    })
                                    ->getOptionLabelUsing(function ($value) {
                                        return Departemen::find($value)->kode;
                                    }),
                                Forms\Components\TextInput::make('job_title')
                                    ->label('Job Title')
                                    ->maxLength(255),
                            ])->collapsible(),
                    ]),
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Akses')
                            ->description('Detail akses')
                            ->schema([
                                Forms\Components\TextInput::make('email')
                                    ->email()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('uid_sap')
                                    ->label('User ID SAP')
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('user_ad')
                                    ->label('User AD')
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('computer_name')
                                    ->label('Computer Name')
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('status')
                                    ->label('Extension')
                                    ->maxLength(255),
                            ])
                            ->columns(2),
                    ]),

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Foto')
                            // ->description('Status Aktif')
                            ->schema([
                                Forms\Components\FileUpload::make('foto')
                                    ->image(),

                                Forms\Components\Toggle::make('is_aktif')
                                    ->label('Aktif')
                                    ->required()
                                    ->hiddenOn('create'),
                            ])
                            ->columns(1),
                    ]),
            ]);
    }

    // public static function form(Form $form): Form
    // {
    //     return $form
    //         ->schema([
    //             Forms\Components\Select::make('plant_id')
    //                 ->relationship('plant', 'kode')
    //                 ->required(),
    //             Forms\Components\Select::make('departemen_id')
    //                 ->relationship('departemen', 'kode')
    //                 ->required(),
    //             Forms\Components\TextInput::make('nama')
    //                 ->required()
    //                 ->maxLength(255),
    //             Forms\Components\TextInput::make('nik')
    //                 ->label('NIK')
    //                 ->required()
    //                 ->maxLength(255),
    //             Forms\Components\TextInput::make('job_title')
    //                 ->label('Job Title')
    //                 ->maxLength(255),
    //             Forms\Components\TextInput::make('email')
    //                 ->email()
    //                 ->maxLength(255),
    //             Forms\Components\TextInput::make('uid_sap')
    //                 ->label('User ID SAP')
    //                 ->maxLength(255),
    //             Forms\Components\TextInput::make('user_ad')
    //                 ->label('User AD')
    //                 ->maxLength(255),
    //             Forms\Components\TextInput::make('computer_name')
    //                 ->label('Computer Name')
    //                 ->maxLength(255),
    //             Forms\Components\DatePicker::make('tgl_lahir')
    //                 ->label('Tgl Lahir'),
    //             Forms\Components\TextInput::make('status')
    //                 ->maxLength(255),
    //             Forms\Components\TextInput::make('foto')
    //                 ->maxLength(255),
    //             Forms\Components\Toggle::make('is_aktif')
    //                 ->required(),
    //         ]);
    // }

    public static function table(Table $table): Table
    {
        return $table
            // ->query(function (Builder $query) {
            //     return $query->where('is_aktif', true);
            // })
            ->columns([

                // Tables\Columns\TextColumn::make('plant.kode')
                //     ->label('Plant')
                //     ->numeric()
                //     ->sortable()
                //     ->searchable(),
                Tables\Columns\TextColumn::make('plant.kode')
                    ->label('Plant')
                    ->searchable()
                    ->getStateUsing(function ($record) {
                        return $record->plant->kode . ' - ' . $record->plant->nama;
                    }),

                Tables\Columns\TextColumn::make('departemen.kode')
                    ->label('Dept')
                    ->numeric()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('nik')
                    ->label('NIK')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nama')
                    ->searchable(),
                Tables\Columns\TextColumn::make('job_title')
                    ->label('Job Title')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user_ad')
                    ->label('User AD')
                    ->searchable(),
                Tables\Columns\TextColumn::make('uid_sap')
                    ->label('UID SAP')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Ext')
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
                SelectFilter::make('plant_id')
                    ->relationship('plant', 'kode')
                    ->label('Filter by Plant')
                    ->options(Plant::all()->pluck('kode', 'id')->toArray()),

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
                    ->action(function ($records) {
                        $recordIds = $records->pluck('id')->toArray();
                        return Excel::download(new KaryawanExport($recordIds), 'karyawan.xlsx');
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
            'index' => Pages\ListKaryawans::route('/'),
            'create' => Pages\CreateKaryawan::route('/create'),
            'edit' => Pages\EditKaryawan::route('/{record}/edit'),
        ];
    }
}
