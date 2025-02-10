<?php

namespace App\Exports;

use App\Models\AssetUsage;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;

class AssetUsageExport implements FromCollection, WithMapping, WithHeadings
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
        return AssetUsage::whereIn('id', $this->recordIds)->with('asset', 'karyawan')->get();
    }

    public function map($assetUsage): array
    {

        $this->rowNumber++; // Increment nomor urut setiap baris

        return [
            $this->rowNumber, // Menambahkan nomor urut
            $assetUsage->asset_id ? $assetUsage->asset->plant->kode . ' - ' . $assetUsage->asset->plant->nama : '',
            $assetUsage->asset_id ? $assetUsage->asset->nomor . ' - ' . $assetUsage->asset->sub : '',
            $assetUsage->asset_id ? $assetUsage->asset->nama : '',
            $assetUsage->asset_id ? $assetUsage->asset->serial_number : '',
            $assetUsage->karyawan_id ? $assetUsage->karyawan->plant->kode . ' - ' . $assetUsage->karyawan->plant->nama : '',
            $assetUsage->karyawan_id ? $assetUsage->karyawan->nik : '',
            $assetUsage->karyawan_id ? $assetUsage->karyawan->nama : '',
            $assetUsage->karyawan_id ? $assetUsage->karyawan->job_title : '',
            $assetUsage->karyawan_id ? $assetUsage->karyawan->email : '',
            $assetUsage->start_date,
            $assetUsage->end_date ? $assetUsage->end_date : 'Belum di Kembalikan',
            strip_tags($assetUsage->notes), // Menghilangkan tag HTML pada notes
        ];
    }

    public function headings(): array
    {
        return [
            'No.',
            'Kode Plant',
            'Nomor Asset - sub',
            'Nama',
            'Serial Number',
            'Kode Plant',
            'NIK',
            'Nama',
            'Job Title',
            'Email',
            'Tanggal Mulai Pakai',
            'Tanggal Selesai',
            'Keterangan',
        ];
    }
}
