<?php

namespace App\Filament\Resources;

use App\Exports\MutasiExport;
use App\Filament\Resources\MutasiResource\Pages;
use App\Filament\Resources\MutasiResource\RelationManagers;
use App\Models\Mutasi;
use App\Models\Plant;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Maatwebsite\Excel\Facades\Excel;

class MutasiResource extends Resource
{
    protected static ?string $model = Mutasi::class;

    protected static ?string $pluralModelLabel = 'Mutasi Bank';

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $navigationGroup = 'Transaksi';

    protected static ?int $navigationSort = 137;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\Section::make('Periode')
                    ->description('Periode Transaksi')
                    ->schema([
                        Forms\Components\Select::make('plant_id')
                            ->label('Plant')
                            ->placeholder('Ketik kode atau nama plant')
                            ->required()
                            ->searchable()
                            ->columnSpan(2)
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
                            ->columnSpan(1)
                            ->required(),

                        Forms\Components\Select::make('periode')
                            ->label('Periode')
                            ->required()
                            ->columnSpan(1)
                            ->options(function () {
                                $currentMonth = now()->month; // Bulan saat ini
                                $currentYear = now()->year;  // Tahun saat ini
                                $periods = [];
                                // Loop untuk 1 tahun ke belakang
                                for ($i = 12; $i >= 1; $i--) {
                                    $date = now()->subMonths($i);
                                    $periods[$date->format('m-Y')] = $date->format('m-Y');
                                }
                                // Tambahkan bulan saat ini
                                $periods[sprintf('%02d-%04d', $currentMonth, $currentYear)] = sprintf('%02d-%04d', $currentMonth, $currentYear);
                                // Loop untuk 1 tahun ke depan
                                for ($i = 1; $i <= 12; $i++) {
                                    $date = now()->addMonths($i);
                                    $periods[$date->format('m-Y')] = $date->format('m-Y');
                                }
                                return $periods;
                            })
                            ->default(now()->format('m-Y')) // Set default value ke periode saat ini
                            ->native(false),
                    ])->columns(5),
                Forms\Components\Section::make('Transaksi')
                    ->description('Detail Transaksi')
                    ->schema([
                        Forms\Components\Section::make('IAP')
                            ->description('Transaksi IAP')
                            ->schema([
                                Forms\Components\TextInput::make('iap')
                                    ->label('IAP')
                                    // ->default(0)
                                    ->numeric(), // Memastikan hanya angka
                                // ->mask(function (\Filament\Forms\Components\TextInput\Mask $mask) {
                                //     return $mask->money(prefix: 'Rp ', thousandsSeparator: '.', decimalSeparator: ',', precision: 0);
                                // }),
                                // ->reactive(), // Reactive untuk Livewire
                                Forms\Components\TextInput::make('adm')
                                    ->label('Adm')
                                    ->numeric(),
                                Forms\Components\TextInput::make('potongan')
                                    ->label('Potongan IAP')
                                    ->numeric(),
                                Forms\Components\TextInput::make('subtotal1')
                                    ->label('Subtotal IAP')
                                    ->disabled()
                                    ->numeric(),
                            ])
                            ->columns(4),
                        Forms\Components\Section::make('Non IAP')
                            ->description('Transaksi Non IAP')
                            ->schema([
                                Forms\Components\TextInput::make('ar_mars')
                                    ->label('AR Mars')
                                    ->numeric(),
                                Forms\Components\TextInput::make('direct_selling')
                                    ->label('Direct Selling')
                                    ->numeric(),
                                Forms\Components\TextInput::make('rumah_club')
                                    ->label('Rumah Club')
                                    ->numeric(),
                                Forms\Components\TextInput::make('subtotal2')
                                    ->label('Subtotal Non IAP')
                                    ->numeric()
                                    ->disabled()
                            ])->columns(4), // Atur kolom dalam section IAP
                        Forms\Components\Section::make('Non IAP Others')
                            ->description('Transaksi Non IAP Others')
                            ->schema([
                                Forms\Components\TextInput::make('sewa_dispenser')
                                    ->label('Sewa Dispenser')
                                    ->numeric(),
                                Forms\Components\TextInput::make('avalan')
                                    ->label('Avalan')
                                    ->numeric(),
                                Forms\Components\TextInput::make('fada')
                                    ->label('FADA')
                                    ->numeric(),
                                Forms\Components\TextInput::make('jaminan')
                                    ->label('Jaminan')
                                    ->numeric(),
                                Forms\Components\TextInput::make('packaging')
                                    ->label('Packaging')
                                    ->numeric(),
                                Forms\Components\TextInput::make('galon_afkir')
                                    ->label('Galon Afkir')
                                    ->numeric(),
                                Forms\Components\TextInput::make('sewa_depo')
                                    ->label('Sewa Depo')
                                    ->numeric(),
                                Forms\Components\TextInput::make('raw_material')
                                    ->label('Raw Material')
                                    ->numeric(),
                                Forms\Components\TextInput::make('pem_listrik')
                                    ->label('Pem Listrik')
                                    ->numeric(),
                                Forms\Components\TextInput::make('klaim_sopir')
                                    ->label('Klaim Sopir')
                                    ->numeric(),
                                Forms\Components\TextInput::make('admin_bank')
                                    ->label('Admin Bank')
                                    ->numeric(),
                                Forms\Components\TextInput::make('others')
                                    ->label('Others')
                                    ->numeric(),
                                Forms\Components\TextInput::make('subtotal3')
                                    ->label('Subtotal Non IAP Others')
                                    ->numeric()
                                    ->disabled()
                            ])->columns(4), // Atur kolom dalam section IAP
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Total')
                    ->description('')
                    ->schema([
                        Forms\Components\TextInput::make('subtotal1')
                            ->label('Subtotal IAP')
                            ->numeric()
                            ->disabled(),
                        Forms\Components\TextInput::make('subtotal2')
                            ->label('Subtotal Non IAP')
                            ->numeric()
                            ->disabled(),
                        Forms\Components\TextInput::make('subtotal3')
                            ->label('Subtotal Non IAP Others')
                            ->numeric()
                            ->disabled(),
                        Forms\Components\TextInput::make('grandtotal')
                            ->label('Grand Total')
                            ->numeric()
                            ->disabled()
                    ])
                    ->columns(4),

                Forms\Components\Section::make('Keterangan')
                    ->description('Keterangan dan Upload File')
                    ->schema([
                        Forms\Components\TextInput::make('keterangan')
                            ->maxLength(255)
                            ->columnSpan(2),
                        Forms\Components\FileUpload::make('foto')
                            ->label('Foto')
                            ->image() // Membatasi hanya file gambar
                            ->maxSize(1024) // Maksimal ukuran file dalam kilobyte (1 MB)
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/jpg']) // Format file yang diterima
                            ->directory('uploads/lampiran') // Lokasi penyimpanan
                            ->preserveFilenames() // Memastikan nama file asli digunakan
                            ->enableDownload() // Tambahkan opsi unduh untuk debugging
                            ->enableOpen()
                            ->columnSpan(1),
                    ])
                    ->columns(4),
                Forms\Components\Hidden::make('user_id')
                    ->default(fn() => Auth::id())
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('periode')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tgl')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('plant.kode')
                    ->label('Plant')
                    ->searchable()
                    ->getStateUsing(function ($record) {
                        return $record->plant->kode . ' - ' . $record->plant->nama;
                    }),
                Tables\Columns\ImageColumn::make('foto')
                    ->label('Foto')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('iap')
                    ->label('IAP')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('adm')
                    ->label('Adm')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('potongan')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('ar_mars')
                    ->label('AR Mars')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('direct_selling')
                    ->label('Direct Selling')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('rumah_club')
                    ->label('Rumah Club')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sewa_dispenser')
                    ->label('Sewa Dispenser')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('avalan')
                    ->label('Avalan')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('fada')
                    ->label('FADA')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('jaminan')
                    ->label('Jaminan')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('packaging')
                    ->label('Packaging')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('galon_afkir')
                    ->label('Galon Afkir')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sewa_depo')
                    ->label('Sewa Depo')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('raw_material')
                    ->label('RM')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('pem_listrik')
                    ->label('Listrik')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('klaim_sopir')
                    ->label('Klaim Sopir')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('admin_bank')
                    ->label('Admin Bank')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('others')
                    ->label('Others')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('grandtotal')
                    ->label('Total')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('keterangan')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Created By')
                    ->sortable()
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
                            return Mutasi::with('plant')
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

                    SelectFilter::make('periode')
                        ->label('Periode')
                        ->options(function () {
                            $periods = [];

                            for ($i = 11; $i >= 0; $i--) {
                                $date = now()->subMonths($i);
                                $periods[$date->format('m-Y')] = $date->format('m-Y');
                            }

                            return $periods;
                        })
                        ->query(function ($query, array $data) {
                            $value = $data['value'] ?? null;

                            if (!$value) {
                                return $query;
                            }

                            [$month, $year] = explode('-', $value);
                            return $query->whereMonth('tgl', $month)->whereYear('tgl', $year);
                        })
                        ->default(now()->format('m-Y'))
                        ->native(false),
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

            ])
            ->actions([
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
                        $fileName = "Mutasi-{$date}.xlsx"; // Menambahkan tanggal pada nama file
                        return Excel::download(new MutasiExport($recordIds), $fileName);
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
            'index' => Pages\ListMutasis::route('/'),
            'create' => Pages\CreateMutasi::route('/create'),
            'edit' => Pages\EditMutasi::route('/{record}/edit'),
        ];
    }

    protected function getTableColumns(): array
    {
        return [
            ImageColumn::make('foto')
                ->label('Lampiran')
                ->size(100) // Ukuran thumbnail
                ->getUrlUsing(
                    fn($record) => $record->foto
                        ? asset('storage/' . $record->foto)
                        : asset('images/default-thumbnail.png')
                )
                ->openUrlInNewTab()
                ->circular() // Opsional: membuat gambar berbentuk lingkaran
                ->toggleable(isToggledHiddenByDefault: true),
        ];
    }
}
