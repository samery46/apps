<?php

namespace App\Exports;

use App\Models\Plant;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PlantExport implements FromCollection, WithMapping, WithHeadings
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
        return Plant::whereIn('id', $this->recordIds)->with('company')->get();
    }

    public function map($plant): array
    {

        $this->rowNumber++; // Increment nomor urut setiap baris

        return [
            $this->rowNumber, // Menambahkan nomor urut
            $plant->company_id ? $plant->company->kode . ' - ' . $plant->company->nama : 'N/A',
            $plant->kode . ' - ' . $plant->nama,
            // $plant->nama,
            $plant->kota,
            $plant->alamat,
            $plant->pos,
            $plant->telp,
            $plant->is_aktif ? 'Aktif' : 'Non Aktif',
        ];
    }
    public function headings(): array
    {
        return [
            'No.',
            'Company',
            'Plant',
            'Kota',
            'Alamat',
            'Kode POS',
            'Telp',
            'Status',
        ];
    }
}
