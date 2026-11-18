<?php

namespace App\Exports;

use App\Models\Claim;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ClaimExport implements FromCollection, WithMapping, WithHeadings, ShouldAutoSize
{
    protected array $recordIds;
    protected int $rowNumber = 0;

    public function __construct(array $recordIds)
    {
        $this->recordIds = $recordIds;
    }

    public function collection()
    {
        return Claim::whereIn('id', $this->recordIds)
            ->with(['plant.company', 'asset', 'creator', 'approvals.user', 'attachments'])
            ->get();
    }

    public function map($claim): array
    {
        $this->rowNumber++;

        $approvalSummary = $claim->approvals->map(function ($a) {
            $nama = $a->user->name ?? 'User';
            $status = ucfirst($a->status);
            $tgl = $a->action_at ? Carbon::parse($a->action_at)->format('d/m/Y H:i') : '-';
            $catatan = $a->catatan ? " ({$a->catatan})" : '';
            return "Tahap {$a->step_order} - {$nama}: {$status} [{$tgl}]{$catatan}";
        })->implode("\n");

        return [
            $this->rowNumber,
            $claim->ticket_number,
            $claim->plant ? ($claim->plant->kode . ' - ' . $claim->plant->nama) : '-',
            $claim->asset->nomor ?? '-',
            $claim->asset->nama ?? '-',
            $claim->asset->serial_number ?? '-',
            ucfirst($claim->incident_type),
            $claim->tgl_kejadian ? Carbon::parse($claim->tgl_kejadian)->format('d/m/Y') : '-',
            (float) $claim->estimasi_kerugian,
            $claim->latar_belakang,
            $claim->kronologi,
            ucfirst($claim->status),
            $approvalSummary ?: 'Belum ada approval',
            $claim->creator->name ?? '-',
            $claim->created_at ? Carbon::parse($claim->created_at)->format('d/m/Y H:i') : '-',
        ];
    }

    public function headings(): array
    {
        return [
            'No.',
            'Nomor Tiket',
            'Plant',
            'Nomor Aset',
            'Nama Aset',
            'Serial Number',
            'Jenis Insiden',
            'Tanggal Kejadian',
            'Estimasi Kerugian (Rp)',
            'Latar Belakang',
            'Kronologi',
            'Status Klaim',
            'Riwayat Approval',
            'Dibuat Oleh',
            'Tanggal Dibuat',
        ];
    }
}
