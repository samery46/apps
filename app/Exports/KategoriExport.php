<?php

namespace App\Exports;

use App\Models\Kategori;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class KategoriExport implements FromCollection, WithMapping, WithHeadings
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
        return Kategori::whereIn('id', $this->recordIds)->with('parent')->get();
    }

    public function map($kategori): array
    {

        $this->rowNumber++;

        // Mapping berdasarkan prefix kode
        $kategoriLabel = match (substr($kategori->kode, 0, 2)) {
        'CS' => 'Consumable',
        'HR' => 'Hardware Replacement',
        default => $kategori->kode,
    };

        return [
            $this->rowNumber,
            $kategoriLabel,
            $kategori->kategori_id ? $kategori->parent->nama : '',
            $kategori->kode,
            $kategori->nama,
            $kategori->keterangan,
            $kategori->is_barang ? 'Ya' : 'Tidak',
            $kategori->satuan,
            $kategori->is_aktif ? 'Aktif' : 'Nonaktif',

        ];
    }

    public function headings(): array
    {
        return [
            'No.',
            'Kategori',
            'Jenis',
            'Kode',
            'Nama',
            'Keterangan',
            'Is_Barang',
            'Satuan',
            'Is_aktif',
            ];
    }

    // public function collection()
    // {
    //     return Kategori::all();
    // }
}
