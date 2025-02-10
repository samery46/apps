<?php

namespace App\Filament\Resources;

use App\Exports\SoftwareExport;
use App\Filament\Resources\SoftwareResource\Pages;
use App\Filament\Resources\SoftwareResource\RelationManagers;
use App\Models\Karyawan;
use App\Models\Plant;
use App\Models\Software;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class SoftwareResource extends Resource
{
    protected static ?string $model = Software::class;

    protected static ?string $pluralModelLabel = 'Software';
    protected static ?string $navigationIcon = 'heroicon-o-cube';
    protected static ?string $navigationGroup = 'Master';
    protected static ?int $navigationSort = 116;

    // protected static ?string $navigationIcon = null;
    // protected static ?string $navigationGroup = null;
    // protected static ?int $navigationSort = null;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Software')
                    ->description('Info Detail')
                    ->schema([
                        Forms\Components\Select::make('nama')
                            ->searchable()
                            ->options([
                                'AutoCAD' => [
                                    'autocadlt' => 'AutoCAD LT',
                                ],
                                'Ms. Office' => [
                                    'Office Pro Plus 2016' => 'Office Pro Plus 2016',
                                    'Office Pro Plus 2019' => 'Office Pro Plus 2019',
                                    'Office Std 2007' => 'Office Std 2007',
                                    'Office Std 2013' => 'Office Std 2013',
                                    'Office Std 2016' => 'Office Std 2016',
                                    'Office Std 2019' => 'Office Std 2019',
                                ],
                                'Project' => [
                                    'Project 2007' => 'Project 2007',
                                    'Project 2016' => 'Project 2016',
                                ],
                                'Teamviewer' => [
                                    'Teamviewer 10' => 'Teamviewer 10',
                                ],
                                'Visio' => [
                                    'Visio Std 2013' => 'Visio Std 2013',
                                    'Visio Std 2016' => 'Visio Std 2016',
                                    'Visio Std 2019' => 'Visio Std 2019',
                                ],
                                'Windows' => [
                                    'Windows 8.1 Pro' => 'Windows 8.1 Pro',
                                ],
                            ]),
                        Forms\Components\DatePicker::make('tgl')
                            ->label('Tanggal'),
                        Forms\Components\TextInput::make('srf')
                            ->label('Nomor SRF')
                            ->maxLength(255),

                    ])->columns(3),
                Forms\Components\Section::make('User')
                    ->description('Info Detail')
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
                            }),

                        Forms\Components\Select::make('karyawan_id')
                            ->label('Username')
                            ->placeholder('Cari NIK atau Nama User')
                            ->searchable()
                            ->preload()
                            ->getSearchResultsUsing(function (string $search) {
                                return Karyawan::where(function ($query) use ($search) {
                                    $query->where('nama', 'like', "%{$search}%")
                                        ->orWhere('nik', 'like', "%{$search}%");
                                })
                                    ->get(['nik', 'nama', 'id'])
                                    ->mapWithKeys(function ($karyawan) {
                                        return [$karyawan->id => $karyawan->nik . ' ' . $karyawan->nama];
                                    })
                                    ->toArray();
                            })
                            ->getOptionLabelUsing(function ($value) {
                                $karyawan = Karyawan::find($value);
                                return $karyawan ? $karyawan->nik . ' ' . $karyawan->nama : null;
                            }),
                        Forms\Components\Textarea::make('keterangan')
                            ->autosize(),
                    ])->columns(3),
                Forms\Components\Toggle::make('is_aktif')
                    ->required(),
                Forms\Components\Hidden::make('user_id')
                    ->default(Auth::id())
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
                    ->label('Nama Software')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tgl')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('srf')
                    ->label('Nomor SRF')
                    ->searchable(),
                Tables\Columns\TextColumn::make('karyawan.nik')
                    ->label('NIK')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('karyawan.nama')
                    ->label('Nama')
                    ->numeric()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('karyawan.job_title')
                    ->label('Job Title')
                    ->numeric()
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('keterangan')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('is_aktif')
                    ->label('Aktif')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                SelectFilter::make('plant_id')
                    ->label('Filter by Plant')
                    ->options(function () {
                        // if (auth()->check() && auth()->user()->id === 1) {
                        // Jika user memiliki ID 1, dianggap sebagai admin
                        return Software::with('plant')
                            ->get()
                            ->mapWithKeys(function ($item) {
                                return [$item->plant_id => "{$item->plant->kode} - {$item->plant->nama}"];
                            })
                            ->toArray();
                        // } else {
                        //     // Jika bukan user dengan ID 1, ambil plant yang dimiliki oleh user
                        //     return auth()->user()->plants->pluck('nama', 'id')->toArray();
                        // }
                    }),
                TernaryFilter::make('is_aktif')
                    ->label('Filter by Aktif')
                    ->trueLabel('Aktif')
                    ->falseLabel('Non Aktif')
                    ->placeholder('All')
                    ->queries(
                        true: fn(Builder $query): Builder => $query->where('is_aktif', true),
                        false: fn(Builder $query): Builder => $query->where('is_aktif', false),
                        blank: fn(Builder $query): Builder => $query
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
                        $fileName = "software-{$date}.xlsx"; // Menambahkan tanggal pada nama file
                        return Excel::download(new SoftwareExport($recordIds), $fileName);
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
            'index' => Pages\ListSoftware::route('/'),
            'create' => Pages\CreateSoftware::route('/create'),
            'edit' => Pages\EditSoftware::route('/{record}/edit'),
        ];
    }
}
