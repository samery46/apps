<?php

namespace App\Exports;

use App\Models\Mutasi;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class MutasiExport implements FromCollection, WithMapping, WithHeadings
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
        return Mutasi::whereIn('id', $this->recordIds)->with('plant', 'user')->get();
    }

    public function map($mutasi): array
    {

        $this->rowNumber++;

        return [
            $this->rowNumber,
            $mutasi->periode,
            $mutasi->tgl,
            $mutasi->plant_id ? $mutasi->plant->kode . ' - ' . $mutasi->plant->nama : '',
            $mutasi->iap,
            $mutasi->adm,
            $mutasi->potongan,
            $mutasi->subtotal1,
            $mutasi->ar_mars,
            $mutasi->direct_selling,
            $mutasi->rumah_club,
            $mutasi->subtotal2,
            $mutasi->sewa_dispenser,
            $mutasi->avalan,
            $mutasi->fada,
            $mutasi->jaminan,
            $mutasi->packaging,
            $mutasi->galon_afkir,
            $mutasi->sewa_depo,
            $mutasi->raw_material,
            $mutasi->pem_listrik,
            $mutasi->klaim_sopir,
            $mutasi->admin_bank,
            $mutasi->others,
            $mutasi->subtotal3,
            $mutasi->grandtotal,
            $mutasi->keterangan,
            $mutasi->user_id ? $mutasi->user->name : '',
        ];
    }

    public function headings(): array
    {
        return [
            'No.',
            'Periode',
            'Tanggal',
            'Plant',
            'IAP',
            'Adm',
            'Potongan',
            'Total IAP',
            'AR Mars',
            'Direct Selling',
            'Rumah Club',
            'Total Non IAP',
            'Sewa Dispenser',
            'Avalan',
            'FADA',
            'Jaminan',
            'Packaging',
            'Galon Afkir',
            'Sewa Depo',
            'Raw Material',
            'Pem Listrik',
            'Klaim Sopir',
            'Admin Bank',
            'Others',
            'Total Others',
            'Total Collection',
            'Keterangan',
            'User Created ',
        ];
    }
}
