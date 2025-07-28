<?php

namespace App\Exports;

use App\Models\StockAdjustment;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class StockAdjustmentExport implements FromCollection, WithMapping, WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */

    protected $recordIds;
    protected $rowNumber;

    public function __construct(array $recordIds)
    {
        $this->recordIds = $recordIds;
        $this->rowNumber = 0; // Inisialisasi nomor urut
    }

    public function collection()
    {
        return StockAdjustment::whereIn('id', $this->recordIds)->with('barang','plant', 'user')->get();
    }

    public function map($stockAdjustment): array
    {

        $this->rowNumber++;

        return [
            $this->rowNumber,
            $stockAdjustment->plant_id ? $stockAdjustment->plant->kode . ' - ' . $stockAdjustment->plant->nama : '',
            $stockAdjustment->tanggal,
            $stockAdjustment->tipe,
            $stockAdjustment->barang_id ? $stockAdjustment->barang->nama : '',
            $stockAdjustment->jumlah_penyesuaian,
            $stockAdjustment->alasan,
            $stockAdjustment->user_id ? $stockAdjustment->user->name : '',

        ];
    }


    public function headings(): array
    {
        return [
            'No.',
            'Kode Plant',
            'Tanggal Transaksi',
            'Tipe',
            'Nama Barang',
            'Jumlah',
            'Alasan',
            'User Created',
            ];
    }

    // public function collection()
    // {
    //     return StockAdjustment::all();
    // }
}
