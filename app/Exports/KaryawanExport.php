<?php

namespace App\Exports;

use App\Models\Karyawan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;

class KaryawanExport implements FromCollection, WithMapping, WithHeadings
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
        return Karyawan::whereIn('id', $this->recordIds)->with('plant', 'departemen')->get();
    }

    public function map($karyawan): array
    {

        $this->rowNumber++; // Increment nomor urut setiap baris

        return [
            $this->rowNumber, // Menambahkan nomor urut
            $karyawan->nama,
            $karyawan->nik,
            $karyawan->job_title,
            $karyawan->email,
            $karyawan->uid_sap,
            $karyawan->user_ad,
            $karyawan->computer_name,
            $karyawan->status,
            $karyawan->plant_id ? $karyawan->plant->kode . ' - ' . $karyawan->plant->nama : 'N/A',
            $karyawan->departemen_id ? $karyawan->departemen->kode . ' - ' .  $karyawan->departemen->nama : 'N/A',
            $karyawan->is_aktif ? 'Aktif' : 'Non Aktif',
        ];
    }

    public function headings(): array
    {
        return [
            'No.',
            'NIK',
            'Nama',
            'Job Title',
            'Email',
            'UID SAP',
            'User AD',
            'Computer Name',
            'Ext',
            'Plant',
            'Departemen',
            'Status',
        ];
    }
}


// namespace App\Exports;

// use App\Models\Karyawan;
// use Maatwebsite\Excel\Concerns\Exportable;
// use Maatwebsite\Excel\Concerns\FromQuery;

// class KaryawanExport implements FromQuery
// {
//     use Exportable;

//     public function query()
//     {
//         return Karyawan::query()->where('is_aktif', '!=', 0);
//     }
// }
