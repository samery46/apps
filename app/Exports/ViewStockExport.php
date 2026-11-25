<?php

namespace App\Exports;

use App\Models\ViewStock;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ViewStockExport implements FromCollection, WithMapping, WithHeadings, ShouldAutoSize
{
    protected ?array $recordsOrIds;
    protected int $rowNumber = 0;

    /**
     * @param array|null $recordsOrIds Jika null, akan mengekspor semua data stok
     */
    public function __construct(?array $recordsOrIds = null)
    {
        $this->recordsOrIds = $recordsOrIds;
    }

    public function collection()
    {
        $query = ViewStock::with(['barang', 'plant']);

        if (! empty($this->recordsOrIds)) {
            $first = reset($this->recordsOrIds);
            if (is_array($first) && isset($first['barang_id'])) {
                $query->where(function ($q) {
                    foreach ($this->recordsOrIds as $pair) {
                        $q->orWhere(function ($sub) use ($pair) {
                            $sub->where('barang_id', $pair['barang_id'])
                                ->where('plant_id', $pair['plant_id']);
                        });
                    }
                });
            } else {
                $query->whereIn('id', $this->recordsOrIds);
            }
        }

        return $query->get();
    }

    public function map($row): array
    {
        $this->rowNumber++;

        $totalMasuk = (int) ($row->total_masuk ?? 0);
        $totalKeluar = (int) ($row->total_keluar ?? 0);
        $sisaStok = $totalMasuk - $totalKeluar;

        return [
            $this->rowNumber,
            $row->plant?->kode ?? '-',
            $row->plant?->nama ?? '-',
            $row->barang?->kode ?? '-',
            $row->barang?->nama ?? '-',
            $totalMasuk,
            $totalKeluar,
            $sisaStok,
        ];
    }

    public function headings(): array
    {
        return [
            'No.',
            'Kode Plant',
            'Nama Plant',
            'Kode Barang',
            'Nama Barang',
            'Total Masuk',
            'Total Keluar',
            'Sisa Stok',
        ];
    }
}
