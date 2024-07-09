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
            $copack->plant_id ? $copack->plant->kode . ' - ' . $copack->plant->nama : 'N/A',
            $copack->tgl,
            $copack->material_id ? $copack->material->kategori_deskripsi : 'N/A',
            $copack->material_id ? $copack->material->kode : 'N/A',
            $copack->material_id ? $copack->material->nama : 'N/A',
            $copack->qty,
            $copack->material_id ? $copack->material->uom : 'N/A',
            $copack->keterangan,
            $copack->user_id ? $copack->user->name : 'N/A',
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
            'Keterangan',
            'User Created ',
        ];
    }
}
