<?php

namespace App\Exports;

use App\Models\Material;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;

class MaterialExport implements FromCollection, WithMapping, WithHeadings
{

    protected $recordIds;
    protected $rowNumber;
    public function __construct(array $recordIds)
    {
        $this->recordIds = $recordIds;
        $this->rowNumber = 0; // Inisialisasi nomor urut
    }
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Material::whereIn('id', $this->recordIds)->with('user')->get();
    }

    public function map($material): array
    {

        $this->rowNumber++; // Increment nomor urut setiap baris

        return [
            $this->rowNumber, // Menambahkan nomor urut
            $material->kode,
            $material->nama,
            $material->kategori == 1 ? 'Finish Goods' : ($material->kategori == 2 ? 'Raw Material' : 'Unknown'),
            $material->uom,
            $material->group,
            $material->keterangan,
            $material->is_aktif ? 'Aktif' : 'Non Aktif',
        ];
    }
    public function headings(): array
    {
        return [
            'No.',
            'Kode',
            'Nama Material',
            'Kategori',
            'UoM',
            'Group',
            'Keterangan',
            'Status',
        ];
    }
}
