<?php

namespace App\Exports;

use App\Models\Mutasi;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;


class MutasiExport implements FromCollection, WithMapping, WithHeadings, WithEvents
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

    public function registerEvents(): array
{
    return [
        AfterSheet::class => function(AfterSheet $event) {
            $totals = [
                'iap' => 0,
                'adm' => 0,
                'potongan' => 0,
                'subtotal1' => 0,
                'ar_mars' => 0,
                'direct_selling' => 0,
                'rumah_club' => 0,
                'subtotal2' => 0,
                'sewa_dispenser' => 0,
                'avalan' => 0,
                'fada' => 0,
                'jaminan' => 0,
                'packaging' => 0,
                'galon_afkir' => 0,
                'sewa_depo' => 0,
                'raw_material' => 0,
                'pem_listrik' => 0,
                'klaim_sopir' => 0,
                'admin_bank' => 0,
                'others' => 0,
                'subtotal3' => 0,
                'grandtotal' => 0,
            ];

            $records = Mutasi::whereIn('id', $this->recordIds)->get();

            foreach ($records as $record) {
                foreach ($totals as $key => $value) {
                    $totals[$key] += $record->{$key} ?? 0;
                }
            }

            $rowCount = $records->count() + 2; // 1 for header, 1 for start from 1

            // Mulai menulis ke kolom E dst (karena kolom A-D adalah No, Periode, Tanggal, Plant)
            $columnStartIndex = 5; // Kolom E = index ke-5 (1-based)
            $fields = array_keys($totals);
            $sheet = $event->sheet->getDelegate();

            // Tulis label "Subtotal" di kolom D
            $sheet->setCellValue('D' . $rowCount, 'Subtotal');

            // Tulis nilai subtotal satu-satu
            foreach ($fields as $i => $field) {
                $excelColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columnStartIndex + $i);
                $sheet->setCellValue($excelColumn . $rowCount, $totals[$field]);
            }

            // Tambahkan format bold
            $sheet->getStyle('D' . $rowCount . ':' . $excelColumn . $rowCount)->getFont()->setBold(true);
        }
    ];
}



}
