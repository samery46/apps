<?php

namespace App\Filament\Resources;

use App\Exports\NetworkExport;
use App\Filament\Resources\NetworkResource\Pages;
use App\Filament\Resources\NetworkResource\RelationManagers;
use App\Models\Departemen;
use App\Models\Karyawan;
use App\Models\Network;
use App\Models\Plant;
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

class NetworkResource extends Resource
{
    protected static ?string $model = Network::class;

    protected static ?string $pluralModelLabel = 'Network';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Master';

    protected static ?int $navigationSort = 114;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('IP Address')
                    ->description('Info Detail')
                    ->schema([
                        Forms\Components\TextInput::make('segmen')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('ip')
                            ->label('IP Address')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('mac')
                            ->label('Mac Address')
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
                        Forms\Components\Toggle::make('is_aktif')
                            ->required(),
                    ])->columns(3),
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
                Tables\Columns\TextColumn::make('segmen')
                    ->label('Segmen')
                    ->numeric()
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('ip')
                    ->label('IP')
                    ->numeric()
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('segmen_and_ip')
                    ->label('IP Address')
                    ->getStateUsing(function ($record) {
                        return $record->segmen . '.' . $record->ip;
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('mac')
                    ->label('Mac Address')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('karyawan.nik')
                    ->label('NIK')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('karyawan.nama')
                    ->label('Nama User')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('departemen.kode')
                    ->label('Departemen')
                    ->getStateUsing(function ($record) {
                        $karyawanId = $record->karyawan_id;
                        $departemenId = optional(Karyawan::find($karyawanId))->departemen_id;
                        $departemen = Departemen::find($departemenId);
                        if ($departemen) {
                            return $departemen->nama;
                        }
                        return '';
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('keterangan')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\IconColumn::make('is_aktif')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: false),
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
                        return Network::with('plant')
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
                        $fileName = "network-{$date}.xlsx"; // Menambahkan tanggal pada nama file
                        return Excel::download(new NetworkExport($recordIds), $fileName);
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
            'index' => Pages\ListNetworks::route('/'),
            'create' => Pages\CreateNetwork::route('/create'),
            'edit' => Pages\EditNetwork::route('/{record}/edit'),
        ];
    }
}
