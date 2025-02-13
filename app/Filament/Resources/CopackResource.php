<?php

namespace App\Filament\Resources;

use App\Exports\CopackExport;
use App\Filament\Resources\CopackResource\Pages;
use App\Filament\Resources\CopackResource\RelationManagers;
use App\Models\Copack;
use App\Models\Material;
use App\Models\Plant;
use App\Models\Type;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Hidden;
use Filament\Navigation\NavigationGroup;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Filters\SelectFilter;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Redirect;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DateRangePicker;
use Filament\Forms\Components\DatePicker;


class CopackResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Copack::class;

    protected static ?string $pluralModelLabel = 'stock copack';

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationGroup = 'Transaksi';

    // protected static ?string $navigationGroup = 'Copack';

    protected static ?int $navigationSort = 138;

    // protected static ?int $navigationSort = 101;

    public static function form(Form $form): Form
    {

        return $form
            ->schema([
                Forms\Components\Section::make('Copacker')
                    ->description('Nama Copacker')
                    ->schema([
                        Forms\Components\Select::make('plant_id')
                            ->label('Plant')
                            ->placeholder('Ketik kode atau nama plant')
                            ->required()
                            ->searchable()
                            ->columnSpan(3)
                            ->preload()
                            ->getSearchResultsUsing(function (string $search) {
                                return Plant::where(function ($query) use ($search) {
                                    $query->where('nama', 'like', "%{$search}%")
                                        ->orWhere('kode', 'like', "%{$search}%");
                                })
                                    ->whereIn('id', auth()->user()->plants->pluck('id')) // Filter berdasarkan hak akses user
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

                        Forms\Components\DatePicker::make('tgl')
                            ->label('Tanggal')
                            ->default(Carbon::today()->format('Y-m-d'))
                            ->columnSpan(2)
                            ->required()
                            ->extraAttributes([
                                'min' => Carbon::today()->subDays(7)->format('Y-m-d'), // Tanggal minimal 7 hari terakhir
                                'max' => Carbon::today()->format('Y-m-d'), // Tanggal maksimal hari ini
                            ])
                            ->rules([
                                'after_or_equal:' . Carbon::today()->subDays(7)->format('Y-m-d'), // Validasi backend minimal 7 hari terakhir
                                'before_or_equal:' . Carbon::today()->format('Y-m-d'), // Validasi backend maksimal hari ini
                            ]),
                    ])
                    ->columns(8)
                    ->collapsible(),
                Forms\Components\Section::make('Material')
                    ->description('Detail Material')
                    ->schema([

                        Forms\Components\Select::make('material_id')
                            ->label('Kode Material')
                            ->placeholder('Cari kode atau nama material')
                            ->required()
                            ->searchable()
                            ->columnSpan(3)
                            ->preload()
                            ->getSearchResultsUsing(function (string $search) {
                                // Ambil plant yang bisa diakses oleh user
                                $userPlantIds = auth()->user()->plants->pluck('id');

                                // Ambil material yang terkait dengan plant yang bisa diakses oleh user
                                return Material::whereHas('plants', function ($query) use ($userPlantIds) {
                                    $query->whereIn('plants.id', $userPlantIds); // Gunakan alias untuk 'plants.id'
                                })
                                    ->where(function ($query) use ($search) {
                                        $query->where('nama', 'like', "%{$search}%")
                                            ->orWhere('kode', 'like', "%{$search}%"); // Pencarian berdasarkan nama atau kode
                                    })
                                    ->get(['kode', 'nama', 'id']) // Ambil kolom kode, nama, dan id
                                    ->mapWithKeys(function ($material) {
                                        return [$material->id => $material->kode . ' - ' . $material->nama]; // Format opsi dengan kode - nama
                                    })
                                    ->toArray();
                            })
                            ->getOptionLabelUsing(function ($value) {
                                $material = Material::find($value);
                                return $material ? $material->kode . ' - ' . $material->nama : null; // Format label dengan kode - nama
                            })
                            ->reactive() // Memicu reactivity ketika material dipilih
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $material = Material::find($state);
                                    if ($material) {
                                        $set('kategori', $material->kategori); // Mengisi Kategori
                                        $set('uom', $material->uom); // Mengisi UoM
                                    }
                                }
                            }),
                            // ->live(), // Menjaga state agar modal tidak tertutup


                        // ini untuk menambahkan material_id
                        // Forms\Components\Select::make('material_id')
                        //     ->label('Kode Material')
                        //     ->placeholder('Cari kode atau nama material')
                        //     ->required()
                        //     ->searchable()
                        //     ->columnSpan(3)
                        //     ->preload()
                        //     ->getSearchResultsUsing(function (string $search) {
                        //         // return Plant::where('nama', 'like', "%{$search}%")
                        //         return Material::where(function ($query) use ($search) {
                        //             $query->where('nama', 'like', "%{$search}%")
                        //                 ->orWhere('kode', 'like', "%{$search}%"); // Tambahkan pencarian juga berdasarkan 'kode'
                        //         })
                        //             // ->limit(5)
                        //             ->get(['kode', 'nama', 'id']) // Ambil kolom kode, nama, dan id
                        //             ->mapWithKeys(function ($material) {
                        //                 return [$material->id => $material->kode . ' - ' . $material->nama]; // Format opsi dengan kode - nama
                        //             })
                        //             ->toArray();
                        //     })
                        //     ->getOptionLabelUsing(function ($value) {
                        //         $material = Material::find($value);
                        //         return $material ? $material->kode . ' - ' . $material->nama : null; // Format label dengan kode - nama
                        //     })

                        //     ->reactive() // Memicu reactivity ketika asset dipilih
                        //     ->afterStateUpdated(function ($state, callable $set) {
                        //         if ($state) {
                        //             $material = Material::find($state);
                        //             if ($material) {
                        //                 $set('kategori', $material->kategori); // Mengisi Kategori
                        //                 $set('uom', $material->uom); // Mengisi UoM
                        //             }
                        //         }
                        //     }),

                        // sampai disini



                        // Forms\Components\TextInput::make('kategori')
                        //     ->label('Kategori')
                        //     ->disabled()
                        //     ->afterStateHydrated(function ($state, callable $set, $record) {
                        //         if ($record && $record->material) {
                        //             $kategori = $record->material->kategori;
                        //             $set('kategori', $kategori === 1 ? 'FG' : ($kategori === 2 ? 'RM' : 'Lainnya'));
                        //         }
                        //     })
                        //     ->afterStateUpdated(function ($state, callable $set) {
                        //         if ($state) {
                        //             $set('kategori', $state === 1 ? 'FG' : ($state === 2 ? 'RM' : 'Lainnya'));
                        //         }
                        //     }),

                        Forms\Components\TextInput::make('kategori')
                            ->label('Kategori')
                            ->disabled()
                            ->hidden() // Menyembunyikan kolom dari tampilan
                            ->afterStateHydrated(function ($state, callable $set, $record) {
                                if ($record && $record->material) {
                                    $set('kategori', $record->material->kategori);
                                }
                            }),
                        Forms\Components\TextInput::make('uom')
                            ->label('UoM')
                            ->disabled()
                            ->columnSpan(1)
                            ->afterStateHydrated(function ($state, callable $set, $record) {
                                if ($record && $record->material) {
                                    $set('uom', $record->material->uom);
                                }
                            }),

                        Forms\Components\TextInput::make('qty')
                            ->label('Quantity')
                            ->columnSpan(2)
                            // ->numeric(),
                            ->type('number')
                            ->step(0.01),

                        // Forms\Components\Select::make('type_id')
                        //     ->label('Type')
                        //     ->relationship('type', 'nama', function ($query) {
                        //         // Menambahkan kondisi hanya mengambil tipe yang aktif
                        //         $query->where('is_aktif', true);
                        //     }) // Relasi ke tabel type
                        //     ->searchable()
                        //     ->required(),

                        Forms\Components\Select::make('type_id')
                            ->label('Type')
                            ->columnSpan(2)
                            ->options(function (callable $get) {
                                // Ambil nilai kategori
                                $kategori = $get('kategori');

                                if ($kategori == 1) { // Jika kategori adalah 1 (FG)
                                    // return Type::whereIn('id', [3, 4, 5, 10])
                                    return Type::where('keterangan', 1) // Sesuaikan nilai keterangan
                                        ->where('is_aktif', true) // Tambahkan kondisi is_aktif = true
                                        ->orderBy('nama', 'asc') // Urutkan berdasarkan nama secara ascending
                                        ->pluck('nama', 'id')
                                        ->toArray();
                                } elseif ($kategori == 2) { // Jika kategori adalah 2 (RM)
                                    // return Type::whereNotIn('id', [3, 4, 5, 10])
                                    return Type::where('keterangan', 2) // Sesuaikan nilai keterangan
                                        ->where('is_aktif', true) // Tambahkan kondisi is_aktif = true
                                        ->orderBy('nama', 'asc') // Urutkan berdasarkan nama secara ascending
                                        ->pluck('nama', 'id')
                                        ->toArray();
                                }

                                // Default: semua tipe yang aktif
                                return Type::where('is_aktif', true)
                                    ->orderBy('nama', 'asc') // Urutkan berdasarkan nama secara ascending
                                    ->pluck('nama', 'id')
                                    ->toArray();
                            })
                            ->reactive() // Membuat elemen ini responsif terhadap perubahan kategori
                            ->required(),

                        Forms\Components\TextInput::make('vendor')
                            ->label('Vendor / Supplier')
                            ->columnSpan(3)
                            ->maxLength(255),
                        Forms\Components\TextInput::make('keterangan')
                            ->label('Keterangan')
                            ->columnSpan(3)
                            ->maxLength(255),
                        Forms\Components\TextInput::make('reason')
                            ->label('Alasan diubah')
                            ->columnSpan(2)
                            ->maxLength(255)
                            ->required()
                            ->hiddenOn('create'),
                    ])
                    ->columns(8)
                    ->collapsible(),
                Forms\Components\Hidden::make('user_id')
                    ->default(fn() => Auth::id())
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('plant.kode')
                    ->label('Copacker')
                    ->searchable()
                    ->getStateUsing(function ($record) {
                        return $record->plant->kode . ' - ' . $record->plant->nama;
                    }),
                Tables\Columns\TextColumn::make('tgl')
                    ->label('Tanggal')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('material.kategori')
                    ->label('Kategori')
                    ->formatStateUsing(function ($state) {
                        return $state == '1' ? 'Finish Good' : ($state == '2' ? 'Raw Material' : $state);
                    })
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('material.kode')
                    ->label('Kode')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('material.nama')
                    ->label('Nama Material')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('qty')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('material.uom')
                    ->label('UoM')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('type.nama')
                    ->label('Type')
                    ->searchable()
                    ->getStateUsing(function ($record) {
                        return $record->type->nama;
                    })
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('vendor')
                    ->label('Vendor / Supplier')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('keterangan')
                    ->label('Keterangan')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('reason')
                    ->label('Alasan dirubah')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Create By')
                    ->numeric()
                    ->sortable()
                    ->searchable()
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
            ])->defaultSort('tgl', 'desc')
            ->filters([

                SelectFilter::make('plant_id')
                    ->label('Filter by Plant')
                    ->options(function () {
                        if (auth()->check() && auth()->user()->id === 1) {
                            // Jika user memiliki ID 1, dianggap sebagai admin
                            return Copack::with('plant')
                                ->get()
                                ->mapWithKeys(function ($item) {
                                    return [$item->plant_id => "{$item->plant->kode} - {$item->plant->nama}"];
                                })
                                ->toArray();
                        } else {
                            // Jika bukan user dengan ID 1, ambil plant yang dimiliki oleh user
                            return auth()->user()->plants->pluck('nama', 'id')->toArray();
                        }
                    }),

                // SelectFilter::make('tgl')
                //     ->label('Filter by Tgl')
                //     ->options(function () {
                //         return Copack::orderBy('tgl', 'desc')
                //             ->distinct()
                //             ->pluck('tgl', 'tgl')
                //             ->toArray();
                //     }),

                Filter::make('tgl_range')
                // ->label('Filter Tanggal')
                ->form([
                    DatePicker::make('start_date')
                        ->label('Dari Tanggal')
                        ->default(Carbon::today()->toDateString()), // Set default ke hari ini
                    DatePicker::make('end_date')
                        ->label('Sampai Tanggal'),
                ])
                ->query(function ($query, $data) {
                    if (!empty($data['start_date']) && !empty($data['end_date'])) {
                        $query->whereBetween('tgl', [$data['start_date'], $data['end_date']]);
                    } elseif (!empty($data['start_date'])) {
                        $query->whereDate('tgl', '>=', $data['start_date']);
                    } elseif (!empty($data['end_date'])) {
                        $query->whereDate('tgl', '<=', $data['end_date']);
                    }
                }),
                SelectFilter::make('material_id')
                    ->label('Filter by Material')
                    ->options(function () {
                        return Copack::with('material')
                            ->get()
                            ->mapWithKeys(function ($item) {
                                return [$item->material_id => $item->material->kode . ' - ' . $item->material->nama];
                            })
                            ->toArray();
                    }),

                SelectFilter::make('type_id')
                    ->label('Filter by Type')
                    ->options(function () {
                        return Copack::with('type')
                            ->get()
                            ->mapWithKeys(function ($item) {
                                return [$item->type_id => $item->type->nama];
                            })
                            ->toArray();
                    }),

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
                        $fileName = "stock-copack-{$date}.xlsx"; // Menambahkan tanggal pada nama file
                        return Excel::download(new CopackExport($recordIds), $fileName);
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
            'index' => Pages\ListCopacks::route('/'),
            'create' => Pages\CreateCopack::route('/create'),
            'edit' => Pages\EditCopack::route('/{record}/edit'),
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
