<?php

namespace App\Exports;

use App\Models\Network;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;

class NetworkExport implements FromCollection, WithMapping, WithHeadings
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
        return Network::whereIn('id', $this->recordIds)->with('plant', 'karyawan', 'user')->get();
    }

    public function map($network): array
    {

        $this->rowNumber++; // Increment nomor urut setiap baris

        $plantId = $network->karyawan_id ? $network->karyawan->plant : null;
        $deptId = $network->karyawan_id ? $network->karyawan->departemen : null;

        return [
            $this->rowNumber, // Menambahkan nomor urut
            $plantId ? $plantId->kode . ' - ' . $plantId->nama : '',
            $network->segmen . '.' . $network->ip,
            $network->mac,
            $network->karyawan_id ? $network->karyawan->nik : '',
            $network->karyawan_id ? $network->karyawan->nama : '',
            $network->karyawan_id ? $network->karyawan->job_title : '',
            $network->karyawan_id ? $network->karyawan->email : '',
            $deptId ? $deptId->kode . ' - ' . $deptId->nama : '',
            $network->keterangan,
            $network->is_aktif ? 'Aktif' : 'Non Aktif',
        ];
    }

    public function headings(): array
    {
        return [
            'No.',
            'Plant',
            'IP Address',
            'Mac Address',
            'NIK',
            'Nama User',
            'Job Title',
            'Email',
            'Departemen',
            'Keterangan',
            'Aktif',
        ];
    }
}
