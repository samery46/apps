<?php

namespace App\Filament\Resources;

use App\Exports\TransaksiInOutExport;
use App\Filament\Resources\TransaksiInOutResource\Pages;
use App\Filament\Resources\TransaksiInOutResource\RelationManagers;
use App\Models\Karyawan;
use App\Models\Plant;
use App\Models\TransaksiInOut;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class TransaksiInOutResource extends Resource
{
    protected static ?string $model = TransaksiInOut::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-up-tray';

    protected static ?string $navigationGroup = 'Stock';

    protected static ?int $navigationSort = 413;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

            Forms\Components\Section::make('Plant')
                ->description('Detail Plant')
                ->schema([

                    Forms\Components\Select::make('plant_id')
                        ->label('Plant')
                        ->placeholder('Ketik kode atau nama plant')
                        ->required()
                        ->searchable()
                        ->columnSpan(4)
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
                    Forms\Components\Select::make('jenis_transaksi')
                        ->label('Jenis')
                        ->options(['Masuk' => 'Masuk', 'Keluar' => 'Keluar'])
                        ->columnSpan(2)
                        ->required(),
                    Forms\Components\DatePicker::make('tgl_pengajuan')
                        ->label('Tgl Pengajuan')
                        ->default(now())
                        ->columnSpan(3),
                    Forms\Components\Select::make('status_pengajuan')
                        ->label('Status')
                        ->options([
                            'Diajukan' => 'Diajukan',
                            'Diproses' => 'Diproses',
                            'Selesai' => 'Selesai',
                            'Ditolak' => 'Ditolak',
                        ])
                        ->columnSpan(3)
                        ->default('Diajukan')
                        ->required(),
                ])->columns(12),

            Forms\Components\Section::make('Transaksi')
                ->description('Detail Transaksi')
                ->schema([

                Forms\Components\Select::make('barang_id')
                    ->label('Barang')
                    ->columnSpan(6)
                    ->options(function () {
                        return \App\Models\Kategori::where('is_barang', true)
                            ->pluck('nama', 'id')
                            ->toArray();
                    })
                    ->searchable()
                    ->required(),
                Forms\Components\TextInput::make('qty')
                    ->required()
                    ->placeholder('0')
                    ->columnSpan(3)
                    ->numeric(),

                // Forms\Components\Select::make('kondisi_barang')
                //     ->label('Kondisi')
                //     ->options(['Bagus' => 'Bagus', 'Rusak' => 'Rusak'])
                //     ->nullable()
                //     ->columnSpan(2),


                Forms\Components\DatePicker::make('tgl_diterima')
                    ->label('Tanggal Diterima')
                    // ->visible(fn ($get) => $get('status_pengajuan') === 'Selesai')
                    ->nullable()
                    ->columnSpan(3),
                Forms\Components\Select::make('karyawan_id')
                    ->label('Pengguna')
                    ->placeholder('Cari NIK atau Nama User')
                    ->searchable()
                    ->columnSpan(6)
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
                            })
                            // ->visible(fn ($get) => $get('jenis_transaksi') === 'Keluar')
                            ->required(fn ($get) => $get('jenis_transaksi') === 'Keluar'),

                Forms\Components\Textarea::make('catatan')
                    ->columnSpan(6),
                // Forms\Components\Toggle::make('pengaruh_stok')
                //     ->label('Memengaruhi Stok?')
                //     ->inline(false)
                //     ->columnSpan(2),
                Forms\Components\Hidden::make('user_id')
                    ->default(Auth::id())
                    ->required(),

                ])->columns(12),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('plant.kode')
                    ->label('Kode Plant')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('plant_info')
                    ->label('Plant')
                    ->getStateUsing(fn ($record) =>
                        $record->plant?->kode . ' - ' . $record->plant?->nama
                    )
                    // ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('barang.nama')
                ->label('Barang')
                    ->sortable()
                    ->limit(50) // membatasi tampilan maksimal 60 karakter
                    ->searchable(),
                Tables\Columns\TextColumn::make('jenis_transaksi')
                    ->label('Jenis')
                    ->badge()
                    ->colors([
                        'success' => 'Masuk',
                        'danger' => 'Keluar',
                    ]),
                Tables\Columns\TextColumn::make('qty')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tgl_pengajuan')
                    ->label('Tgl Pengajuan')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tgl_diterima')
                    ->label('Tgl Diterima')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status_pengajuan')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'gray' => 'Diajukan',
                        'warning' => 'Diproses',
                        'success' => 'Selesai',
                        'danger' => 'Ditolak',
                    ]),
                // Tables\Columns\TextColumn::make('kondisi_barang')
                //     ->label('Kondisi'),
                // Tables\Columns\IconColumn::make('pengaruh_stok')
                //     ->label('Pengaruh Stock')
                //     ->boolean()
                //     ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('karyawan.nama')
                    ->label('Pengguna')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('pemberi.name')
                    ->label('Created By')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                    ->label('Filter by Plant')
                    ->options(function () {
                        if (auth()->check() && auth()->user()->id === 1) {
                            // Jika user memiliki ID 1, dianggap sebagai admin
                            return TransaksiInOut::with('plant')
                                ->get()
                                ->sortBy(function ($item) {
                                    return $item->plant->kode;
                                })
                                ->mapWithKeys(function ($item) {
                                    return [$item->plant_id => "{$item->plant->kode} - {$item->plant->nama}"];
                                })
                                ->toArray();
                        } else {
                                return auth()->user()->plants
                                    ->sortBy('kode')
                                    ->mapWithKeys(fn ($plant) => [$plant->id => "{$plant->kode} - {$plant->nama}"])
                                    ->toArray();
                        }
                    }),
                    SelectFilter::make('jenis_transaksi')
                        ->label('Jenis')
                        ->options([
                            'Masuk' => 'Masuk',
                            'Keluar' => 'Keluar',
                    ]),
                    SelectFilter::make('status_pengajuan')
                        ->label('Status')
                        ->options([
                            'Diajukan' => 'Diajukan',
                            'Diproses' => 'Diproses',
                            'Selesai' => 'Selesai',
                            'Ditolak' => 'Ditolak',
                        ]),

                    Filter::make('tgl_range')
                        // ->label('Filter Tanggal')
                        ->form([
                            DatePicker::make('start_date')
                                ->label('Dari Tanggal')
                                ->default(Carbon::today()->subDays(6)->toDateString()), // 7 hari terakhir (termasuk hari ini)
                            DatePicker::make('end_date')
                                ->label('Sampai Tanggal')
                                ->default(Carbon::today()->toDateString()), // Set default ke hari ini
                        ])
                        ->query(function ($query, $data) {
                            if (!empty($data['start_date']) && !empty($data['end_date'])) {
                                $query->whereBetween('tgl_pengajuan', [$data['start_date'], $data['end_date']]);
                            } elseif (!empty($data['start_date'])) {
                                $query->whereDate('tgl_pengajuan', '>=', $data['start_date']);
                            } elseif (!empty($data['end_date'])) {
                                $query->whereDate('tgl_pengajuan', '<=', $data['end_date']);
                            }
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
                        $fileName = "TransaksiInOut-{$date}.xlsx"; // Menambahkan tanggal pada nama file
                        return Excel::download(new TransaksiInOutExport($recordIds), $fileName);
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
            'index' => Pages\ListTransaksiInOuts::route('/'),
            'create' => Pages\CreateTransaksiInOut::route('/create'),
            'edit' => Pages\EditTransaksiInOut::route('/{record}/edit'),
        ];
    }
}
