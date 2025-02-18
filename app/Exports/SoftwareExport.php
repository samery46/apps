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
        // $deptId = $software->karyawan_id ? $software->karyawan->departemen : null;
        $deptId = $software->karyawan?->departemen; // Menggunakan optional chaining

        return [
            $this->rowNumber, // Menambahkan nomor urut
            $companyId ? $companyId->kode : '',
            $software->plant_id ? $software->plant->kode . ' - ' . $software->plant->nama  : '',
            $software->nama,
            $software->tgl,
            $software->srf,
            $software->karyawan?->nik ?? '',
            $software->karyawan?->nama ?? '',
            $software->karyawan?->job_title ?? '',
            $software->karyawan?->email ?? '',
            $deptId?->nama ?? '', // Menghindari error jika departemen null
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
