<?php

namespace App\Exports;

use App\Models\Copack;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CopackExport implements FromCollection, WithMapping, WithHeadings
{

    protected $recordIds;
    protected $rowNumber;

    public function __construct(array $recordIds)
    {
        $this->recordIds = $recordIds;
        $this->rowNumber = 0; // Inisialisasi nomor urut
    }

    public function collection()
    {
        return Copack::whereIn('id', $this->recordIds)->with('plant', 'user', 'material')->get();
    }
    /**
     * @return \Illuminate\Support\Collection
     */
    public function map($copack): array
    {

        $this->rowNumber++;

        return [
            $this->rowNumber,
            $copack->plant_id ? $copack->plant->kode . ' - ' . $copack->plant->nama : '',
            $copack->tgl,
            $copack->material_id ? $copack->material->kategori_deskripsi : '',
            $copack->material_id ? $copack->material->kode : '',
            $copack->material_id ? $copack->material->nama : '',
            $copack->qty,
            $copack->material_id ? $copack->material->uom : '',
            $copack->type_id ? $copack->type->nama : '',
            $copack->vendor,
            $copack->keterangan,
            $copack->user_id ? $copack->user->name : '',
        ];
    }

    public function headings(): array
    {
        return [
            'No.',
            'Copacker',
            'Tanggal',
            'Kategori',
            'Kode',
            'Nama Material',
            'Quantity',
            'UoM',
            'Type Transaksi',
            'Vendor / Supplier',
            'Keterangan',
            'User Created ',
        ];
    }
}
