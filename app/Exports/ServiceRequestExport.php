<?php

namespace App\Exports;

use App\Models\ServiceRequest;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ServiceRequestExport implements FromCollection, WithMapping, WithHeadings
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
        return ServiceRequest::whereIn('id', $this->recordIds)->with('asset', 'user')->get();
    }

    public function map($serviceRequest): array
    {

        $this->rowNumber++; // Increment nomor urut setiap baris

        // Ubah spare_parts ke bentuk string yang bisa dibaca
        $sparePartsArray = $serviceRequest->spare_parts; // Asumsikan ini sudah dalam bentuk array
        $sparePartsString = '';

        if (is_array($sparePartsArray)) {
            // Ubah setiap spare part ke bentuk string deskriptif
            $sparePartsString = implode(', ', array_map(function ($part) {
                return $part['name'] . ' (Qty : ' . $part['quantity'] . ', Harga : ' . $part['price'] . ')';
            }, $sparePartsArray));
        }

        return [
            $this->rowNumber, // Menambahkan nomor urut
            $serviceRequest->asset_id ? $serviceRequest->asset->plant->kode . ' - ' . $serviceRequest->asset->plant->nama : '',
            $serviceRequest->asset_id ? $serviceRequest->asset->nomor . ' - ' . $serviceRequest->asset->sub : '',
            $serviceRequest->asset_id ? $serviceRequest->asset->nama : '',
            $serviceRequest->asset_id ? $serviceRequest->asset->serial_number : '',
            strip_tags($serviceRequest->problem), // Menghilangkan tag HTML
            $serviceRequest->start_date,
            $serviceRequest->end_date ? $serviceRequest->end_date : 'Belum di Selesai',
            $serviceRequest->vendor,
            // $serviceRequest->spare_parts,
            // is_array($serviceRequest->spare_parts) ? implode(', ', $serviceRequest->spare_parts) : $serviceRequest->spare_parts,
            $sparePartsString, // Memasukkan string spare parts yang sudah diformat
            strip_tags($serviceRequest->notes), // Menghilangkan tag HTML pada notes
            $serviceRequest->status,
            $serviceRequest->user_id ? $serviceRequest->user->name : '',
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
            'Kerusakan',
            'Tanggal Service',
            'Tanggal Selesai',
            'Vendor',
            'Spare Parts',
            'Keterangan',
            'Status',
            'Create By',
        ];
    }
}
