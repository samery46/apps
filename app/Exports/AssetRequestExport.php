<?php

namespace App\Exports;

use App\Models\AssetRequest;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class AssetRequestExport implements FromCollection, WithMapping, WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */

    protected $recordIds;
    protected $rowNumber;

    public function __construct(array $recordIds)
    {
        $this->recordIds = $recordIds;
        $this->rowNumber = 0; // Inisialisasi nomor urut
    }

    public function collection()
    {
        // return AssetRequest::all();
        return AssetRequest::whereIn('id', $this->recordIds)->with('plant', 'assetGroup','costCenter', 'user')->get();
    }

    public function map($assetRequest): array
    {

        $this->rowNumber++;

        return [
            $this->rowNumber,
            $assetRequest->document_number,
            $assetRequest->plant_id ? $assetRequest->plant->kode . ' - ' . $assetRequest->plant->nama : '',
            $assetRequest->asset_group_id ? $assetRequest->assetGroup->asset_group . ' - ' . $assetRequest->assetGroup->name : '',
            $assetRequest->type,
            $assetRequest->fixed_asset_number,
            $assetRequest->sub_asset_number,
            $assetRequest->cea_number,
            $assetRequest->usage_period,
            $assetRequest->quantity,
            $assetRequest->cost_center_id ? $assetRequest->costCenter->cost_center . ' - ' . $assetRequest->costCenter->name : '',
            $assetRequest->condition,
            $assetRequest->item_name,
            $assetRequest->country_of_origin,
            $assetRequest->supplier,
            $assetRequest->year_of_manufacture,
            $assetRequest->expected_arrival,
            $assetRequest->expected_usage,
            $assetRequest->location,
            $assetRequest->description,
            $assetRequest->status,
            $assetRequest->user_id ? $assetRequest->user->name : '',
            $assetRequest->created_at,
        ];
    }

    public function headings(): array
    {
        return [
            'No.',
            'Nomor Transaksi',
            'Plant',
            'Kelompok Aktiva Tetap',
            'Jenis',
            'Nomor Aktiva Tetap',
            'Nomor Sub Asset',
            'Nomor CEA',
            'Penggunaan (Th)',
            'Qty',
            'Cost Center',
            'Status Barang',
            'Nama Barang',
            'Asal Negara',
            'Supplier',
            'Pembuatan',
            'Estimasi Kedatangan',
            'Estimasi Penggunaan',
            'Lokasi',
            'Catatan',
            'Status',
            'User Created ',
            'Date Created',
        ];
    }

}
