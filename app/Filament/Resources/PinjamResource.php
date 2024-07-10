<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PinjamResource\Pages;
use App\Filament\Resources\PinjamResource\RelationManagers;
use App\Models\Karyawan;
use App\Models\Perangkat;
use App\Models\Pinjam;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Filters\TernaryFilter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PinjamResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Pinjam::class;

    protected static ?string $pluralModelLabel = 'pinjam';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Transaksi';

    protected static ?int $navigationSort = 131;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Identitas')
                    ->description('Informasi Karyawan')
                    ->schema([
                        Forms\Components\Select::make('karyawan_id')
                            ->label('Nama')
                            ->relationship('karyawan', 'nama')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->getSearchResultsUsing(function (string $search) {
                                return Karyawan::where('nama', 'like', "%{$search}%")
                                    ->limit(5)
                                    ->pluck('nama', 'id')
                                    ->toArray();
                            })
                            ->getOptionLabelUsing(function ($value) {
                                return Karyawan::find($value)->nama;
                            }),
                        Forms\Components\TextInput::make('keterangan')
                            ->maxLength(255),
                    ])->columns(2),
                Forms\Components\Section::make('Tanggal Peminjaman')
                    ->schema([
                        Forms\Components\DatePicker::make('tgl_pinjam')
                            ->label('Tanggal Pinjam')
                            ->default(Carbon::today()->format('Y-m-d'))
                            ->required(),
                        Forms\Components\DatePicker::make('tgl_kembali')
                            ->label('Tanggal Kembali')
                            ->default(Carbon::today()->format('Y-m-d'))
                            ->hiddenOn('create'),
                    ])->columns(2),
                Forms\Components\Section::make('Penyerah')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('User Penyerah')
                            ->relationship('user', 'name', function ($query) {
                                $query->whereIn('id', [2, 3, 4, 7]);
                            })
                            ->required()
                            ->default(Auth::id()),
                        Forms\Components\Toggle::make('is_complete')
                            ->default(false)
                            ->required(),
                    ])->columns(2),
                Forms\Components\Section::make('Perangkat yang dipinjam')->schema([
                    self::getItemsRepeater(),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('karyawan.nama')
                    ->label('Nama')
                    ->numeric()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('tgl_pinjam')
                    ->label('Pinjam')
                    ->date()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('tgl_kembali')
                    ->label('Kembali')
                    ->date()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Penyerah')
                    ->numeric()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_complete')
                    ->label('Completed')
                    ->sortable()
                    ->boolean(),
                Tables\Columns\TextColumn::make('keterangan')
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
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])->defaultSort('created_at', 'desc')
            ->filters([
                TernaryFilter::make('is_complete')
                    ->label('Filter by Complete')
                    ->trueLabel('Complete')
                    ->falseLabel('Non Complete')
                    ->placeholder('All')
                    ->queries(
                        true: fn (Builder $query): Builder => $query->where('is_complete', true),
                        false: fn (Builder $query): Builder => $query->where('is_complete', false),
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
            'index' => Pages\ListPinjams::route('/'),
            'create' => Pages\CreatePinjam::route('/create'),
            'edit' => Pages\EditPinjam::route('/{record}/edit'),
        ];
    }

    public static function getItemsRepeater(): Repeater
    {
        return Repeater::make('items')
            ->relationship()
            ->schema([

                Forms\Components\Select::make('perangkat_id')
                    ->label('Perangkat')
                    // ->options(Perangkat::query()->pluck('nama', 'id'))
                    ->options(function () {
                        return Perangkat::where('is_aktif', true)
                            ->get()
                            ->mapWithKeys(function ($perangkat) {
                                return [$perangkat->id => $perangkat->nama . ' ' . $perangkat->serial_number];
                            })->toArray();
                    })
                    ->required()
                    ->reactive()
                    ->afterStateHydrated(function (Forms\Set $set, Forms\Get $get, $state) {
                        $perangkat = Perangkat::find($state);
                        $set('serial_number', $perangkat?->serial_number ?? 0);
                    })
                    ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                        $perangkat = Perangkat::find($state);
                        $set('serial_number', $perangkat?->serial_number ?? 0);
                    })

                    ->distinct()
                    ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                    ->columnSpan([
                        'md' => 5,
                    ])
                    ->searchable(),

                // Forms\Components\TextInput::make('serial_number')
                //     ->label('Serial Number')
                //     ->disabled()
                //     ->dehydrated()
                //     // ->numeric()
                //     ->required()
                //     ->columnSpan([
                //         'md' => 3,
                //     ]),
            ])
            ->extraItemActions([
                Action::make('openPerangkat')
                    ->tooltip('Open perangkat')
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->url(function (array $arguments, Repeater $component): ?string {
                        $itemData = $component->getRawItemState($arguments['item']);
                        $perangkat = Perangkat::find($itemData['perangkat_id']);
                        if (!$perangkat) {
                            return null;
                        }
                        return PerangkatResource::getUrl('edit', ['record' => $perangkat]);
                    }, shouldOpenInNewTab: true)
                    ->hidden(fn (array $arguments, Repeater $component): bool => blank($component->getRawItemState($arguments['item'])['perangkat_id'])),
            ])
            ->defaultItems(1)
            ->hiddenLabel()
            ->columns([
                'md' => 10,
            ])
            ->live();
        // ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set) {
        //     self::updateTotalPrice($get, $set);
        // });
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




// Forms\Components\Select::make('perangkat_id')
//                     ->label('Perangkat')
//                     ->options(Perangkat::query()->pluck('nama', 'id'))
//                     // ->options(function () {
//                     //     return Perangkat::where('is_aktif', true)
//                     //         ->get()
//                     //         ->mapWithKeys(function ($perangkat) {
//                     //             return [$perangkat->id => $perangkat->nama . ' ' . $perangkat->serial_number];
//                     //         })->toArray();
//                     // })
//                     ->required()
//                     ->reactive()

//                     // sampai sini
//                     ->afterStateHydrated(function (Forms\Set $set, Forms\Get $get, $state) {
//                         $perangkat = Perangkat::find($state);
//                         $set('serial_number', $perangkat?->serial_number ?? 0);
//                     })
//                     ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
//                         $perangkat = Perangkat::find($state);
//                         $set('serial_number', $perangkat?->serial_number ?? 0);
//                         // $serial_number = $get('serial_number');
//                     })

//                     //ini dipakai
//                     ->distinct()
//                     ->disableOptionsWhenSelectedInSiblingRepeaterItems()
//                     ->columnSpan([
//                         'md' => 5,
//                     ])
//                     ->searchable(),
