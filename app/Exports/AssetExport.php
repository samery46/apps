<?php

namespace App\Exports;

use App\Models\Asset;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;

class AssetExport implements FromCollection, WithMapping, WithHeadings
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
        // return Asset::whereIn('id', $this->recordIds)
        //     ->with('plant', 'user', 'karyawan')
        //     ->get();

        return Asset::whereIn('id', $this->recordIds)
            ->with(['plant.company', 'user', 'karyawan']) // Tambahkan relasi 'company'
            ->get();
    }

    public function map($asset): array
    {

        $this->rowNumber++; // Increment nomor urut setiap baris

        return [
            $this->rowNumber, // Menambahkan nomor urut
            $asset->plant && $asset->plant->company ? $asset->plant->company->kode : '', // Tambahkan kolom company
            $asset->plant_id ? $asset->plant->kode . ' - ' . $asset->plant->nama : '',
            $asset->nomor,
            $asset->sub,
            $asset->nama,
            $asset->tipe,
            $asset->serial_number,
            $asset->qty_sap,
            $asset->qty_aktual,
            $asset->tgl_perolehan,
            $asset->harga,
            $asset->nbv,
            $asset->karyawan_id ? $asset->karyawan->nama : '',
            $asset->status,
            $asset->kondisi,
            $asset->lokasi,
            $asset->keterangan,
            $asset->user_id ? $asset->user->name : '',
            $asset->is_aktif ? 'Aktif' : 'Non Aktif',
        ];
    }

    public function headings(): array
    {
        return [
            'No.',
            'Company',
            'Plant',
            'No. Asset',
            'Sub Asset',
            'Nama',
            'Type',
            'Serial Number',
            'QTY SAP',
            'QTY Aktual',
            'Tgl Perolehan',
            'Harga',
            'NBV',
            'Pengguna',
            'Status',
            'Kondisi',
            'Lokasi',
            'Keterangan',
            'Create By',
            'Aktif',
        ];
    }
}
