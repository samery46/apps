<?php

namespace App\Exports;

use App\Models\Software;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SoftwareExport implements FromCollection, WithMapping, WithHeadings
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
        return Software::whereIn('id', $this->recordIds)->with('plant', 'karyawan', 'user')->get();
    }

    public function map($software): array
    {

        $this->rowNumber++; // Increment nomor urut setiap baris

        $companyId = $software->plant_id ? $software->plant->company : null;
        $deptId = $software->karyawan_id ? $software->karyawan->departemen : null;

        return [
            $this->rowNumber, // Menambahkan nomor urut
            $companyId ? $companyId->kode : 'N/A',
            $software->plant_id ? $software->plant->kode . ' - ' . $software->plant->nama  : 'N/A',
            $software->nama,
            $software->tgl,
            $software->srf,
            $software->karyawan_id ? $software->karyawan->nik : 'N/A',
            $software->karyawan_id ? $software->karyawan->nama : 'N/A',
            $software->karyawan_id ? $software->karyawan->job_title : 'N/A',
            $software->karyawan_id ? $software->karyawan->email : 'N/A',
            $deptId ? $deptId->nama : 'N/A',
            $software->keterangan,
            $software->is_aktif ? 'Aktif' : 'Non Aktif',
        ];
    }

    public function headings(): array
    {
        return [
            'No.',
            'Company',
            'Plant',
            'Nama Software',
            'Tanggal',
            'Nomor SRF',
            'NIK',
            'Nama User',
            'Job Title',
            'Email',
            'Departemen',
            'Keterangan',
            'Status',
        ];
    }
}
