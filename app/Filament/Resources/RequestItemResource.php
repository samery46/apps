<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RequestItemResource\Pages;
use App\Filament\Resources\RequestItemResource\RelationManagers;
use App\Models\Asset;
use App\Models\Karyawan;
use App\Models\Kategori;
use App\Models\Plant;
use App\Models\RequestItem;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RequestItemResource extends Resource
{
    protected static ?string $model = RequestItem::class;

    protected static ?string $pluralModelLabel = 'Request IT';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Transaksi';

    protected static ?int $navigationSort = 136;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Plant')
                    ->description('Informasi Plant')
                    ->schema([
                        Forms\Components\Select::make('plant_id')
                            // ->relationship('plant', 'nama')
                            ->label('Kode Plant')
                            ->placeholder('Cari kode / nama plant')
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
                            })
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $plant = Plant::find($state);
                                    if ($plant) {
                                        $set('kota', $plant->kota ?? null);
                                        $set('alamat', $plant->alamat);
                                        $set('pos', $plant->pos);
                                        $set('telp', $plant->telp);
                                    }
                                }
                            }),
                        Forms\Components\TextInput::make('kota')
                            ->label('Kota')
                            ->disabled()
                            ->afterStateHydrated(function ($state, callable $set, $record) {
                                if ($record && $record->plant) {
                                    $set('kota', $record->plant->kota);
                                }
                            }),

                        Forms\Components\TextInput::make('telp')
                            ->label('Telepon')
                            ->disabled()
                            ->afterStateHydrated(function ($state, callable $set, $record) {
                                if ($record && $record->plant) {
                                    $set('pos', $record->plant->telp);
                                }
                            }),
                        Forms\Components\TextInput::make('pos')
                            ->label('Kode POS')
                            ->disabled()
                            ->afterStateHydrated(function ($state, callable $set, $record) {
                                if ($record && $record->plant) {
                                    $set('pos', $record->plant->pos);
                                }
                            }),
                        Forms\Components\Textarea::make('alamat')
                            ->label('Alamat')
                            ->disabled()
                            ->afterStateHydrated(function ($state, callable $set, $record) {
                                if ($record && $record->plant) {
                                    $set('alamat', $record->plant->alamat);
                                }
                            })->columnSpanFull(),
                    ])->columns(4),
                Forms\Components\Section::make('Periode')
                    ->description('Informasi Periode')
                    ->schema([
                        Forms\Components\Select::make('period')
                            ->label('Periode Bulan')
                            ->options(function () {
                                $options = [];
                                // Tambahkan opsi "Januari 2024"
                                // $options['01-2024'] = 'Januari 2024';
                                // Tambahkan opsi bulan untuk tahun berjalan
                                $currentYear = Carbon::now()->year;
                                foreach (range(1, 12) as $month) {
                                    $monthName = Carbon::createFromDate(null, $month)->translatedFormat('F');
                                    $options[str_pad($month, 2, '0', STR_PAD_LEFT) . "-{$currentYear}"] = "{$monthName}{$currentYear}";
                                }

                                // Tambahkan opsi bulan untuk tahun berikutnya
                                $nextYear = $currentYear + 1;
                                foreach (range(1, 12) as $month) {
                                    $monthName = Carbon::createFromDate(null, $month)->translatedFormat('F');
                                    $options[str_pad($month, 2, '0', STR_PAD_LEFT) . "-{$nextYear}"] = "{$monthName}{$nextYear}";
                                }
                                return $options;
                            })
                            ->required(),
                        Forms\Components\Select::make('process')
                            ->label('Proses Pembelian')
                            ->options([
                                'HO' => 'HO',
                                'Unit' => 'Unit',
                            ])->required(),
                    ])
                    ->columns(4),
                Forms\Components\Section::make('Kategori')
                    ->description('Informasi Kategori dan Nama Item')
                    ->schema([
                        Forms\Components\Select::make('kategori_id')
                            ->label('Nama Item')
                            ->placeholder('Cari kode / nama barang')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->getSearchResultsUsing(function (string $search) {
                                // return Plant::where('nama', 'like', "%{$search}%")
                                return Kategori::where(function ($query) use ($search) {
                                    $query->where('nama', 'like', "%{$search}%")
                                        ->orWhere('kode', 'like', "%{$search}%"); // Tambahkan pencarian juga berdasarkan 'kode'
                                })
                                    // ->limit(5)
                                    ->get(['kode', 'nama', 'id']) // Ambil kolom kode, nama, dan id
                                    ->mapWithKeys(function ($kategori) {
                                        return [$kategori->id => $kategori->kode . ' - ' . $kategori->nama]; // Format opsi dengan kode - nama
                                    })
                                    ->toArray();
                            })
                            ->getOptionLabelUsing(function ($value) {
                                $kategori = Kategori::find($value);
                                return $kategori ? $kategori->kode . ' - ' . $kategori->nama : null; // Format label dengan kode - nama
                            })->columnSpan(2),
                        Forms\Components\Select::make('item_type')
                            ->label('Type Item')
                            ->options([
                                'Original' => 'Original',
                                'OEM' => 'OEM',
                                'Refill' => 'Refill',
                                'Remanufacture' => 'Remanufacture',
                                'Lainnya' => 'Lainnya',
                            ])->required(),
                        Forms\Components\Textarea::make('description')
                            ->required()
                            ->maxLength(65535)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('qty')
                            ->label('Quantity')
                            ->required()
                            ->numeric()
                            ->reactive() // Untuk mereaktifkan input qty
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                // Update nilai total setelah qty berubah
                                $set('total', $state * $get('price'));
                            }),

                        Forms\Components\TextInput::make('price')
                            ->label('Price')
                            ->required()
                            ->numeric()
                            ->prefix('Rp.')
                            ->reactive() // Untuk mereaktifkan input price
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                // Update nilai total setelah price berubah
                                $set('total', $state * $get('qty'));
                            }),

                        Forms\Components\TextInput::make('total')
                            ->label('Total')
                            ->numeric()
                            ->disabled() // Agar kolom ini hanya dapat dibaca
                            ->prefix('Rp.')
                            ->columnSpan(2)
                            ->default(fn(callable $get) => $get('qty') * $get('price')), // Default menghitung dari qty * price
                    ])
                    ->columns(4),
                Forms\Components\Section::make('Asset')
                    ->description('Informasi Asset yang digunakan')
                    ->schema([

                        Forms\Components\Select::make('asset_id')
                            ->label('Nomor Asset')
                            ->placeholder('Cari kode / nomor Asset')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->getSearchResultsUsing(function (string $search) {
                                return Asset::where(function ($query) use ($search) {
                                    $query->where('nomor', 'like', "%{$search}%")
                                        ->orWhere('nama', 'like', "%{$search}%");
                                })
                                    ->get(['nomor', 'serial_number', 'nama', 'id'])
                                    ->mapWithKeys(function ($asset) {
                                        return [$asset->id => $asset->nomor . '-' . $asset->serial_number . '-' . $asset->nama];
                                    })
                                    ->toArray();
                            })
                            ->getOptionLabelUsing(function ($value) {
                                $asset = Asset::find($value);
                                return $asset ? $asset->nomor . '-' . $asset->serial_number . '-' . $asset->nama : null;
                            })
                            ->columnSpan(3),
                        // ->visible(fn(callable $get) => $get('type_asset') === 'SAP'), // Tampilkan jika type_asset adalah 'SAP'

                        // Forms\Components\TextInput::make('manual_asset_id')
                        //     ->label('Input Manual Nomor Asset')
                        //     ->placeholder('Silahkan masukkan nomor asset')
                        //     ->required(fn(callable $get) => $get('type_asset') === 'Non SAP') // Wajib diisi jika Non SAP
                        //     ->visible(fn(callable $get) => $get('type_asset') === 'Non SAP') // Tampilkan jika type_asset adalah 'Non SAP'
                        //     ->columnSpan(3),



                        // Forms\Components\Repeater::make('asset_id')
                        //     ->label('Daftar Nomor Asset')
                        //     ->schema([
                        //         Forms\Components\Select::make('asset_id')
                        //             ->label('Nomor Asset')
                        //             ->placeholder('Cari kode / nomor Asset')
                        //             ->searchable()
                        //             ->preload()
                        //             ->getSearchResultsUsing(function (string $search) {
                        //                 return Asset::where(function ($query) use ($search) {
                        //                     $query->where('nomor', 'like', "%{$search}%")
                        //                         ->orWhere('nama', 'like', "%{$search}%");
                        //                 })
                        //                     ->get(['nomor', 'serial_number', 'nama', 'id'])
                        //                     ->mapWithKeys(function ($asset) {
                        //                         return [$asset->id => $asset->nomor . '-' . $asset->serial_number . '-' . $asset->nama];
                        //                     })
                        //                     ->toArray();
                        //             })
                        //             ->getOptionLabelUsing(function ($value) {
                        //                 $asset = Asset::find($value);
                        //                 return $asset ? $asset->nomor : null;
                        //             }),
                        //     ])
                        //     ->columns(1) // Atur jumlah kolom yang ingin ditampilkan
                        //     ->columnSpan(2)
                        //     ->createItemButtonLabel('Tambah Asset') // Label untuk tombol tambah
                        //     ->disableItemDeletion(false), // Izinkan penghapusan item
                        Forms\Components\TextInput::make('number_user')
                            ->label('Jumlah User')
                            ->required()
                            ->maxLength(50),
                        // Forms\Components\Select::make('karyawan_id')
                        //     ->label('User Pengguna')
                        //     ->placeholder('Cari nama karyawan')
                        //     ->required()
                        //     ->searchable()
                        //     ->preload()
                        //     ->multiple() // Mengizinkan pemilihan banyak karyawan
                        //     ->getSearchResultsUsing(function (string $search) {
                        //         return Karyawan::where('nama', 'like', "%{$search}%")
                        //             ->get(['nama', 'id'])
                        //             ->mapWithKeys(function ($karyawan) {
                        //                 return [$karyawan->id => $karyawan->nama]; // Nama karyawan sebagai label
                        //             })->toArray();
                        //     })
                        //     ->getOptionLabelUsing(function ($value) {
                        //         $karyawan = Karyawan::find($value); // Mengambil nama berdasarkan ID
                        //         return $karyawan ? $karyawan->nama : null;
                        //     })
                        //     ->columnSpan(3),



                        Forms\Components\Select::make('karyawan_id')
                            ->label('User Pengguna')
                            ->placeholder('Cari nama karyawan')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->getSearchResultsUsing(function (string $search) {
                                return Karyawan::where(function ($query) use ($search) {
                                    $query->where('nama', 'like', "%{$search}%");
                                })
                                    ->get(['nama', 'id'])
                                    ->mapWithKeys(function ($karyawan) {
                                        return [$karyawan->id => $karyawan->nama];
                                    })
                                    ->toArray();
                            })
                            ->multiple()
                            ->getOptionLabelUsing(function ($value) {
                                $karyawan = Karyawan::find($value);
                                return $karyawan ? $karyawan->nama : null;
                            })->columnSpan(3),

                        // Forms\Components\Select::make('karyawan_id')
                        //     ->relationship('karyawans', 'nama')
                        //     ->options(function () {
                        //         return \App\Models\Karyawan::all()
                        //             ->sortBy('nama')
                        //             ->mapWithKeys(function ($karyawan) {
                        //                 return [$karyawan->id => "{$karyawan->nama}"];
                        //             })->toArray();
                        //     })
                        //     ->multiple() // Menambahkan opsi multiple
                        //     ->label('Karyawan')
                        //     ->columns(4),

                        Forms\Components\Textarea::make('job_description')
                            ->label('Job description user')
                            ->maxLength(65535)
                            ->columnSpan(2),
                        Forms\Components\Textarea::make('justification')
                            ->label('Request Justification')
                            ->required()
                            ->maxLength(65535)
                            ->columnSpan(2),
                    ])
                    ->columns(4),
                Forms\Components\Section::make('Keterangan')
                    ->description('Catatan, estimasi, dan riwayat pembelian')
                    ->schema([

                        Forms\Components\Textarea::make('notes')
                            ->label('Catatan')
                            ->maxLength(65535)
                            ->columnSpan(2),
                        Forms\Components\Textarea::make('estimation')
                            ->label('Estimasi Pembelian')
                            ->required()
                            ->maxLength(65535)
                            ->columnSpan(2),
                        Forms\Components\Textarea::make('purchase_history')
                            ->label('Riwayat Pembelian')
                            ->required()
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ])
                    ->columns(4),

                Forms\Components\Section::make('Mesin Fotocopy')
                    ->description('Untuk pengajuan printer, Apakah ada mesin fotocopy')
                    ->schema([
                        Forms\Components\Toggle::make('is_photocopier')
                            ->label('Ada mesin fotocopy ?'),

                        Forms\Components\Textarea::make('reason')
                            ->label('Alasan')
                            ->placeholder('Jika ada fotocopy, silahkan ketikkan alasaanya ...')
                            ->maxLength(65535)
                            ->columnSpan(3)
                            ->required(function (callable $get) {
                                // Jika is_photocopier bernilai true, maka reason harus diisi
                                return $get('is_photocopier') === true;
                            }),
                        Forms\Components\Toggle::make('is_aktif')
                            ->required(),
                        // ->hiddenOn('create'),

                    ])
                    ->columns(4),
            ]);
    }

    // Fungsi untuk mengubah label Create
    protected static function getCreateButtonLabel(): string
    {
        return 'Create Request IT'; // Sesuaikan dengan teks yang kamu inginkan
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('plant.kode')
                    ->label('Plant')
                    ->sortable()
                    ->searchable()
                    ->getStateUsing(function ($record) {
                        return $record->plant->kode . ' - ' . $record->plant->nama;
                    }),
                Tables\Columns\TextColumn::make('process')
                    ->searchable(),

                Tables\Columns\TextColumn::make('period')
                    ->label('Periode')
                    ->searchable()
                    ->wrap()
                    ->sortable()
                    ->getStateUsing(function ($record) {
                        // Mengubah format dari '11-2024' menjadi 'November 2024'
                        return Carbon::createFromFormat('m-Y', $record->period)->translatedFormat('F Y');
                    }),
                Tables\Columns\TextColumn::make('kategori.kode')
                    ->label('Kategori')
                    ->sortable()
                    ->searchable()
                    ->formatStateUsing(function ($state) {
                        // Cek jika kode diawali dengan 'CS'
                        if (substr($state, 0, 2) === 'CS') {
                            return 'Consumable';
                        }
                        // Cek jika kode diawali dengan 'HR'
                        elseif (substr($state, 0, 2) === 'HR') {
                            return 'Hardware Replacement';
                        }
                        // Jika tidak memenuhi kriteria di atas, kembalikan kode aslinya
                        return $state;
                    }),
                Tables\Columns\TextColumn::make('kategori.kategori.nama')
                    ->label('Jenis|Merek')
                    ->sortable()
                    ->wrap()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('kategori.nama')
                    ->label('Barang')
                    ->sortable()
                    ->wrap()
                    ->searchable(),
                Tables\Columns\TextColumn::make('qty')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('price')
                    ->money('Rp. ')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total')
                    ->label('Total')
                    ->money('Rp. ')
                    ->getStateUsing(function ($record) {
                        return $record->qty * $record->price; // Menghitung total qty x price
                    }),
                Tables\Columns\TextColumn::make('description')
                    ->label('Deskripsi')
                    ->sortable()
                    ->wrap()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('asset.nomor')
                    ->label('Nomor Asset')
                    ->sortable()
                    ->searchable()
                    ->wrap()
                    ->getStateUsing(function ($record) {
                        return $record->asset->nomor . ' - ' . $record->asset->serial_number . ' - ' . $record->asset->nama;
                    }),
                Tables\Columns\TextColumn::make('number_user')
                    ->label('Jumlah User')
                    ->searchable(),
                Tables\Columns\TextColumn::make('karyawan.nama')
                    ->label('Nama User ')
                    ->sortable()
                    ->wrap()
                    ->searchable(),
                Tables\Columns\TextColumn::make('justification')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('notes')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('estimation')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('purchase_history')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('item_type')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('is_photocopier')
                    ->label('Fotocopy')
                    ->getStateUsing(function ($record) {
                        return $record->is_photocopier ? 'Ada' : 'Tidak Ada';
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('reason')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('is_aktif')
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
            'index' => Pages\ListRequestItems::route('/'),
            'create' => Pages\CreateRequestItem::route('/create'),
            'edit' => Pages\EditRequestItem::route('/{record}/edit'),
        ];
    }
}
