<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;

use Filament\Widgets\TableWidget;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use App\Models\Copack;
use App\Models\Plant;
use Carbon\Carbon;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;

class SummaryReport extends BaseWidget
{
    protected function getStats(): array
    {
        return [

        ];
    }
}



// class SummaryReport extends TableWidget
// {
    // protected int | string | array $columnSpan = 'full'; // Agar widget memenuhi lebar dashboard

    // public function table(Table $table): Table
    // {


        // return $table
            // ->query(Copack::whereNull('deleted_at')) // Hanya menampilkan data yang belum dihapus

            // ->columns([
            //     // TextColumn::make('tgl')->label('Tanggal'),
            //     TextColumn::make('plant.nama')->label('Nama Plant')
            //         ->sortable(query: fn (Builder $query, string $direction) =>
            //             $query->orderByRaw("(SELECT kode FROM plants WHERE plants.id = copacks.plant_id) {$direction}")
            //         ),
            //     TextColumn::make('material.nama')->label('Material ID'),
            //     TextColumn::make('qty')->label('Quantity'),
            //     TextColumn::make('type_id')->label('Type ID'),
            // ])
            // ->filters([
            //     Filter::make('tgl')
            //         ->form([
            //             DatePicker::make('tgl_from')
            //                 ->label('Dari Tanggal')
            //                 ->default(Carbon::now()->subDays(7)), // Default ke 7 hari lalu

            //             DatePicker::make('tgl_until')
            //                 ->label('Sampai Tanggal')
            //                 ->default(Carbon::now()), // Default ke hari ini
            //         ])
            //         ->query(function (Builder $query, array $data): Builder {
            //             return $query
            //                 ->when($data['tgl_from'], fn(Builder $query, $date) => $query->whereDate('tgl', '>=', $date))
            //                 ->when($data['tgl_until'], fn(Builder $query, $date) => $query->whereDate('tgl', '<=', $date));
            //         }),
            // ]);
    // }


// }
