<?php

namespace App\Exports;

use App\Models\TransaksiInOut;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class TransaksiInOutExport implements FromCollection, WithMapping, WithHeadings
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
        return TransaksiInOut::whereIn('id', $this->recordIds)->with('barang','karyawan','plant', 'pemberi')->get();
    }


    public function map($transaksiInOut): array
    {

        $this->rowNumber++;

        return [
            $this->rowNumber,
            $transaksiInOut->plant_id ? $transaksiInOut->plant->kode . ' - ' . $transaksiInOut->plant->nama : '',
            $transaksiInOut->tgl_pengajuan,
            $transaksiInOut->status_pengajuan,
            $transaksiInOut->jenis_transaksi,
            $transaksiInOut->barang_id ? $transaksiInOut->barang->nama : '',
            $transaksiInOut->qty,
            $transaksiInOut->tgl_diterima,
            $transaksiInOut->karyawan_id ? $transaksiInOut->karyawan->nama : '',
            $transaksiInOut->catatan,
            $transaksiInOut->user_id ? $transaksiInOut->pemberi->name : '',

        ];
    }

    public function headings(): array
    {
        return [
            'No.',
            'Kode Plant',
            'Tanggal Pengajuan',
            'Status',
            'Jenis',
            'Nama Barang',
            'Qty',
            'Tanggal Diterima',
            'User Pengguna',
            'Catatan',
            'User Created',
        ];
    }

    // public function collection()
    // {
    //     return TransaksiInOut::all();
    // }
}
