<?php

namespace App\Exports;

use App\Models\Uidsap;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;

class UidsapExport implements FromCollection, WithMapping, WithHeadings
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
        return Uidsap::whereIn('id', $this->recordIds)->with('karyawan', 'user')->get();
    }

    public function map($uidsap): array
    {

        $this->rowNumber++; // Increment nomor urut setiap baris

        $plantId = $uidsap->karyawan_id ? $uidsap->karyawan->plant : null;
        $deptId = $uidsap->karyawan_id ? $uidsap->karyawan->departemen : null;

        return [
            $this->rowNumber, // Menambahkan nomor urut
            $plantId ? $plantId->kode . ' - ' . $plantId->nama : 'N/A',
            $deptId ? $deptId->kode : 'N/A',
            $uidsap->username,
            $uidsap->karyawan_id ? $uidsap->karyawan->nik : 'N/A',
            $uidsap->karyawan_id ? $uidsap->karyawan->nama : 'N/A',
            $uidsap->karyawan_id ? $uidsap->karyawan->job_title : 'N/A',
            $uidsap->karyawan_id ? $uidsap->karyawan->email : 'N/A',
            $uidsap->valid_from,
            $uidsap->valid_end,
            $uidsap->keterangan,
            $uidsap->is_aktif ? 'Aktif' : 'Non Aktif',
        ];
    }

    public function headings(): array
    {
        return [
            'No.',
            'Plant',
            'Departemen',
            'UID SAP',
            'NIK',
            'Nama User',
            'Job Title',
            'Email',
            'Valid From',
            'Valid End',
            'Keterangan',
            'Aktif',
        ];
    }
}
