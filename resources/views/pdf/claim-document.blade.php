<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Berita Acara Klaim Aset</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 12mm 15mm;
        }

        *, *::before, *::after {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9pt;
            line-height: 1.35;
            color: #222;
            margin: 0;
            padding: 0;
            background: #fff;
        }

        .header-table {
            width: 100%;
            border-bottom: 2px solid #1a365d;
            padding-bottom: 6px;
            margin-bottom: 12px;
        }

        .company-name {
            font-size: 13pt;
            font-weight: bold;
            color: #1a365d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .doc-title {
            font-size: 12pt;
            font-weight: bold;
            color: #111;
            text-align: center;
            text-transform: uppercase;
            margin: 10px 0 2px 0;
            text-decoration: underline;
        }

        .doc-subtitle {
            font-size: 8.5pt;
            color: #555;
            text-align: center;
            margin-bottom: 14px;
        }

        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            font-size: 8.5pt;
        }

        .meta-table td {
            padding: 3px 5px;
            vertical-align: top;
        }

        .section-box {
            border: 1px solid #1a365d;
            margin-bottom: 12px;
            page-break-inside: avoid;
        }

        .section-header {
            background-color: #1a365d;
            color: #ffffff;
            font-weight: bold;
            font-size: 8.5pt;
            padding: 4px 8px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .section-body {
            padding: 6px 8px;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5pt;
        }

        .data-table td {
            padding: 3px 5px;
            vertical-align: top;
        }

        .data-table td.label {
            width: 25%;
            color: #444;
            font-weight: 500;
        }

        .data-table td.separator {
            width: 2%;
            text-align: center;
        }

        .data-table td.value {
            width: 73%;
            font-weight: bold;
            color: #111;
        }

        .text-block {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 3px;
            padding: 6px 8px;
            font-size: 8.5pt;
            white-space: pre-line;
            color: #2d3748;
            margin-top: 3px;
        }

        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8pt;
            font-weight: bold;
            text-transform: uppercase;
        }

        .badge-danger {
            background-color: #fee2e2;
            color: #991b1b;
            border: 1px solid #f87171;
        }

        .badge-success {
            background-color: #dcfce7;
            color: #166534;
            border: 1px solid #86efac;
        }

        /* Tabel Persetujuan / Approval Signatures */
        .approval-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
            font-size: 8pt;
            text-align: center;
            page-break-inside: avoid;
        }

        .approval-table th, .approval-table td {
            border: 1px solid #94a3b8;
            padding: 5px;
            vertical-align: middle;
        }

        .approval-table th {
            background-color: #f1f5f9;
            color: #1e293b;
            font-weight: bold;
        }

        .signature-box {
            height: 48px;
            vertical-align: bottom;
            font-size: 7.5pt;
            color: #166534;
            font-weight: bold;
        }

        .stamp-approved {
            border: 1.5px solid #16a34a;
            color: #16a34a;
            padding: 3px 6px;
            border-radius: 3px;
            display: inline-block;
            text-transform: uppercase;
            font-size: 7.5pt;
            letter-spacing: 0.5px;
        }

        .stamp-pending {
            border: 1.5px dashed #ca8a04;
            color: #ca8a04;
            padding: 3px 6px;
            border-radius: 3px;
            display: inline-block;
            text-transform: uppercase;
            font-size: 7.5pt;
        }

        .stamp-rejected {
            border: 1.5px solid #dc2626;
            color: #dc2626;
            padding: 3px 6px;
            border-radius: 3px;
            display: inline-block;
            text-transform: uppercase;
            font-size: 7.5pt;
        }

        .footer {
            margin-top: 15px;
            border-top: 1px dashed #cbd5e1;
            padding-top: 4px;
            font-size: 7.5pt;
            color: #64748b;
            display: flex;
            justify-content: space-between;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>

@foreach ($claims as $claim)
    <div class="claim-document">
        <!-- HEADER PERUSAHAAN -->
        <table class="header-table">
            <tr>
                <td style="vertical-align: middle;">
                    <div class="company-name">{{ $claim->plant->company->nama ?? 'PT. TIRTA SUKSES PERKASA' }}</div>
                    <div style="font-size: 8pt; color: #555;">Plant : {{ $claim->plant->kode ?? '-' }} - {{ $claim->plant->nama ?? '-' }}</div>
                </td>
                <td style="text-align: right; vertical-align: middle;">
                    <div style="font-size: 8.5pt; font-weight: bold;">ARSIP FISIK RESMI</div>
                    <div style="font-size: 8pt; color: #555;">Status: <span class="badge {{ $claim->status === 'approved' ? 'badge-success' : 'badge-danger' }}">{{ strtoupper($claim->status) }}</span></div>
                </td>
            </tr>
        </table>

        <!-- JUDUL DOKUMEN -->
        <div class="doc-title">KLAIM ASET</div>
        <div class="doc-subtitle">Nomor Dokumen : <strong>{{ $claim->ticket_number }}</strong></div>

        <!-- META DATA TRANSAKSI -->
        <table class="meta-table">
            <tr>
                <td style="width: 18%; font-weight: bold; color: #444;">Tanggal Laporan</td>
                <td style="width: 2%;">:</td>
                <td style="width: 30%;">{{ \Carbon\Carbon::parse($claim->created_at)->translatedFormat('d F Y') }}</td>

                <td style="width: 18%; font-weight: bold; color: #444;">Dibuat Oleh</td>
                <td style="width: 2%;">:</td>
                <td style="width: 30%;">{{ $claim->creator->name ?? 'Staff' }}</td>
            </tr>
            <tr>
                <td style="font-weight: bold; color: #444;">Plant</td>
                <td>:</td>
                <td>{{ $claim->plant->kode ?? '-' }} ({{ $claim->plant->nama ?? '-' }})</td>

                <td style="font-weight: bold; color: #444;">Status Persetujuan</td>
                <td>:</td>
                <td>{{ strtoupper($claim->status) }}</td>
            </tr>
        </table>

        <!-- 1. INFORMASI ASET -->
        <div class="section-box">
            <div class="section-header">1. Informasi Aset</div>
            <div class="section-body">
                <table class="data-table">
                    <tr>
                        <td class="label">Nomor Aset SAP</td>
                        <td class="separator">:</td>
                        <td class="value">{{ $claim->asset->nomor ?? '-' }} {{ !empty($claim->asset->sub) ? '('.$claim->asset->sub.')' : '' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Nama / Deskripsi Aset</td>
                        <td class="separator">:</td>
                        <td class="value">{{ $claim->asset->nama ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Tipe / Kategori</td>
                        <td class="separator">:</td>
                        <td class="value">{{ $claim->asset->tipe ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Nomor Seri (Serial No)</td>
                        <td class="separator">:</td>
                        <td class="value">{{ $claim->asset->serial_number ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Kondisi Fisik Saat Klaim</td>
                        <td class="separator">:</td>
                        <td class="value">{{ $claim->asset->kondisi ?? 'Mengalami Kendala/Insiden' }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- 2. DETAIL INSIDEN & KERUGIAN -->
        <div class="section-box">
            <div class="section-header">2. Rincian Insiden & Kerugian Finansial</div>
            <div class="section-body">
                <table class="data-table">
                    <tr>
                        <td class="label">Jenis Insiden</td>
                        <td class="separator">:</td>
                        <td class="value">
                            @if ($claim->incident_type === 'rusak')
                                <span class="badge badge-danger">Rusak / Kerusakan Fisik</span>
                            @else
                                <span class="badge badge-danger">Hilang / Pencurian</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="label">Tanggal Kejadian</td>
                        <td class="separator">:</td>
                        <td class="value">{{ \Carbon\Carbon::parse($claim->tgl_kejadian)->translatedFormat('d F Y') }}</td>
                    </tr>
                    <tr>
                        <td class="label">Estimasi Kerugian</td>
                        <td class="separator">:</td>
                        <td class="value" style="font-size: 10pt; color: #b91c1c;">
                            Rp {{ number_format((float) $claim->estimasi_kerugian, 0, ',', '.') }}
                        </td>
                    </tr>
                </table>

                <div style="margin-top: 8px;">
                    <div style="font-weight: bold; color: #333; margin-bottom: 2px;">Latar Belakang Insiden:</div>
                    <div class="text-block">{{ $claim->latar_belakang }}</div>
                </div>

                <div style="margin-top: 8px;">
                    <div style="font-weight: bold; color: #333; margin-bottom: 2px;">Kronologi Kejadian Terperinci:</div>
                    <div class="text-block">{{ $claim->kronologi }}</div>
                </div>
            </div>
        </div>

        <!-- 3. BERKAS BUKTI / LAMPIRAN -->
        <div class="section-box">
            <div class="section-header">3. Foto, Lampiran & Dokumen Pendukung</div>
            <div class="section-body">
                @if ($claim->attachments && $claim->attachments->count() > 0)
                    <table style="width: 100%; border-collapse: collapse; font-size: 8pt;">
                        <thead>
                            <tr style="background: #f1f5f9; text-align: left;">
                                <th style="border: 1px solid #cbd5e1; padding: 4px; width: 5%;">No</th>
                                <th style="border: 1px solid #cbd5e1; padding: 4px; width: 50%;">Nama Berkas / File</th>
                                <th style="border: 1px solid #cbd5e1; padding: 4px; width: 45%;">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($claim->attachments as $idx => $att)
                                <tr>
                                    <td style="border: 1px solid #cbd5e1; padding: 4px; text-align: center;">{{ $idx + 1 }}</td>
                                    <td style="border: 1px solid #cbd5e1; padding: 4px;">{{ $att->nama_file_asli ?: basename($att->file_path) }}</td>
                                    <td style="border: 1px solid #cbd5e1; padding: 4px;">{{ $att->keterangan ?: '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div style="font-size: 8pt; color: #666; font-style: italic;">Tidak ada berkas lampiran pendukung yang disertakan.</div>
                @endif
            </div>
        </div>

        <!-- 4. LEMBAR PERSETUJUAN PEJABAT BERWENANG -->
        <div class="section-box">
            <div class="section-header">4. Riwayat Persetujuan</div>
            <div class="section-body">
                <table class="approval-table">
                    <thead>
                        <tr>
                            <th style="width: 25%;">Dibuat</th>
                            @foreach ($claim->approvals as $approval)
                                <th style="width: {{ 75 / max(1, $claim->approvals->count()) }}%;">
                                    Approver Tahap #{{ $approval->step_order }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <!-- Kolom Pembuat -->
                            <td>
                                <div style="font-weight: bold; margin-bottom: 2px;">{{ $claim->creator->name ?? 'Staff Pelapor' }}</div>
                                <div style="color: #64748b; font-size: 7.5pt;"></div>
                                <div class="signature-box">
                                    <span class="stamp-approved">SUBMITTED</span>
                                    <div style="color: #64748b; font-size: 7pt; font-weight: normal; margin-top: 2px;">
                                        {{ \Carbon\Carbon::parse($claim->created_at)->format('d/m/Y H:i') }}
                                    </div>
                                </div>
                            </td>

                            <!-- Kolom-kolom Approver -->
                            @foreach ($claim->approvals as $approval)
                                <td>
                                    <div style="font-weight: bold; margin-bottom: 2px;">{{ $approval->user->name ?? 'Approver' }}</div>
                                    <div style="color: #64748b; font-size: 7.5pt;">{{ $approval->user->email ?? '-' }}</div>
                                    <div class="signature-box">
                                        @if ($approval->status === 'approved')
                                            <span class="stamp-approved">APPROVED</span>
                                            <div style="color: #64748b; font-size: 7pt; font-weight: normal; margin-top: 2px;">
                                                {{ \Carbon\Carbon::parse($approval->action_at)->format('d/m/Y H:i') }}
                                            </div>
                                        @elseif ($approval->status === 'rejected')
                                            <span class="stamp-rejected">REJECTED</span>
                                            <div style="color: #64748b; font-size: 7pt; font-weight: normal; margin-top: 2px;">
                                                {{ \Carbon\Carbon::parse($approval->action_at)->format('d/m/Y H:i') }}
                                            </div>
                                        @else
                                            <span class="stamp-pending">PENDING</span>
                                            <div style="color: #94a3b8; font-size: 7pt; font-weight: normal; margin-top: 2px;">
                                                Menunggu Tindakan
                                            </div>
                                        @endif
                                    </div>
                                    @if (!empty($approval->catatan))
                                        <div style="font-size: 7pt; font-style: italic; color: #475569; margin-top: 3px; border-top: 1px dotted #cbd5e1; padding-top: 2px;">
                                            "{{ $approval->catatan }}"
                                        </div>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- FOOTER DOKUMEN ARSIP -->
        <div class="footer">
            <span>Dokumen Resmi &bull; No. Dokumen #{{ $claim->ticket_number }}</span>
            <span style="text-align: right;">Dicetak pada : {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}</span>
        </div>
    </div>

    @if (! $loop->last)
        <div class="page-break"></div>
    @endif
@endforeach

</body>
</html>
