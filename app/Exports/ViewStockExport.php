<?php

namespace App\Exports;

use App\Models\ViewStock;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ViewStockExport implements FromCollection, WithMapping, WithHeadings
{
    protected array $recordPairs;
    protected int $rowNumber = 0;

    public function __construct(array $recordPairs)
    {
        $this->recordPairs = $recordPairs;
    }

    public function collection()
    {
        return collect($this->recordPairs)->map(function ($pair) {
            return DB::table('view_stocks')
                ->join('barangs', 'view_stocks.barang_id', '=', 'barangs.id')
                ->join('plants', 'view_stocks.plant_id', '=', 'plants.id')
                ->select(
                    'barangs.nama as barang_nama',
                    'plants.nama as plant_nama',
                    'view_stocks.total_masuk',
                    'view_stocks.total_keluar',
                    DB::raw('(view_stocks.total_masuk - view_stocks.total_keluar) as stok_akhir')
                )
                ->where('view_stocks.barang_id', $pair['barang_id'])
                ->where('view_stocks.plant_id', $pair['plant_id'])
                ->first();
        })->filter(); // Hindari null jika data tidak ditemukan
    }

    public function map($row): array
    {
        $this->rowNumber++;

        return [
            $this->rowNumber,
            $row->barang_nama,
            $row->plant_nama,
            $row->total_masuk,
            $row->total_keluar,
            $row->stok_akhir,
        ];
    }

    public function headings(): array
    {
        return [
            'No.',
            'Nama Barang',
            'Nama Plant',
            'Total Masuk',
            'Total Keluar',
            'Stok Akhir',
        ];
    }
}

