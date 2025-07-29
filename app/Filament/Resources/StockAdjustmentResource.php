<?php

namespace App\Filament\Resources;

use App\Exports\StockAdjustmentExport;
use App\Filament\Resources\StockAdjustmentResource\Pages;
use App\Filament\Resources\StockAdjustmentResource\RelationManagers;
use App\Models\Plant;
use App\Models\StockAdjustment;
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

class StockAdjustmentResource extends Resource
{
    protected static ?string $model = StockAdjustment::class;

    protected static ?string $navigationIcon = 'heroicon-o-pencil-square';

    protected static ?string $navigationGroup = 'Stock';

    protected static ?int $navigationSort = 412;

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
                        Forms\Components\DatePicker::make('tanggal')
                            ->default(now())
                            ->required()
                            ->columnSpan(4),
                    ])->columns(8),

                Forms\Components\Section::make('Item')
                    ->description('Informasi Detail ')
                    ->schema([
                    Forms\Components\Select::make('barang_id')
                        ->label('Barang')
                        ->relationship('barang', 'nama', fn ($query) => $query->where('is_barang', true))
                        ->searchable()
                        ->required()
                        ->columnSpan(4),
                    Forms\Components\TextInput::make('jumlah_penyesuaian')
                        ->label('Jumlah Penyesuaian')
                        ->required()
                        ->numeric()
                        ->columnSpan(2),
                    Forms\Components\Select::make('tipe')
                        ->options(['penambahan' => 'Penambahan', 'pengurangan' => 'Pengurangan'])
                        ->required()
                        ->columnSpan(2),
                    Forms\Components\Textarea::make('alasan')
                        ->label('Alasan')
                        ->placeholder('Silahkan isikan alasannya')
                        ->columnSpan(8),
                    Forms\Components\Hidden::make('user_id')
                        ->default(Auth::id())
                        ->required(),
                ])->columns(8),


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
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('plant_info')
                    ->label('Nama Plant')
                    ->getStateUsing(fn ($record) =>
                        $record->plant?->kode . ' - ' . $record->plant?->nama
                    )
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('barang.kode')
                    ->label('Kode')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('barang.nama')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('tanggal')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('jumlah_penyesuaian')
                    ->label('Jumlah')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tipe')
                    ->label('Tipe')
                    ->badge()
                    ->colors([
                        'success' => 'penambahan',
                        'danger' => 'pengurangan',
                    ])
                    ->formatStateUsing(fn ($state) => [
                        'penambahan' => 'Penambahan',
                        'pengurangan' => 'Pengurangan',
                    ][$state] ?? $state)
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('alasan')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Created By')
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
            ])
            ->filters([
                SelectFilter::make('plant_id')
                    ->label('Filter by Plant')
                    ->options(function () {
                        if (auth()->check() && auth()->user()->id === 1) {
                            // Jika user memiliki ID 1, dianggap sebagai admin
                            return StockAdjustment::with('plant')
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
                    SelectFilter::make('tipe')
                        ->label('Tipe')
                        ->options([
                            'penambahan' => 'Penambahan',
                            'pengurangan' => 'Pengurangan',
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
                                $query->whereBetween('tanggal', [$data['start_date'], $data['end_date']]);
                            } elseif (!empty($data['start_date'])) {
                                $query->whereDate('tanggal', '>=', $data['start_date']);
                            } elseif (!empty($data['end_date'])) {
                                $query->whereDate('tanggal', '<=', $data['end_date']);
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
                        $fileName = "StockAdjustment-{$date}.xlsx"; // Menambahkan tanggal pada nama file
                        return Excel::download(new StockAdjustmentExport($recordIds), $fileName);
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
            'index' => Pages\ListStockAdjustments::route('/'),
            'create' => Pages\CreateStockAdjustment::route('/create'),
            'edit' => Pages\EditStockAdjustment::route('/{record}/edit'),
        ];
    }
}
