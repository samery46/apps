<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>List Data Assets</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 10mm;
        }

        *, *::before, *::after {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 8.5pt;
            color: #222;
            margin: 0;
            padding: 0;
            background-color: #ffffff;
        }

        .page-title {
            text-align: center;
            font-size: 12pt;
            font-weight: bold;
            color: #1a365d;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #1a365d;
            padding-bottom: 4px;
        }

        /* Container Card - Tinggi dikunci (~180px) agar hanya 4 card yang muat dalam 1 halaman A4 */
        .card {
            border: 1px solid #1a365d;
            border-radius: 4px;
            margin-bottom: 10px;
            padding: 8px;
            background: #fff;
            page-break-inside: avoid; /* Mencegah card terpotong di tengah halaman */
        }

        .card-header {
            background-color: #1a365d;
            color: #ffffff;
            padding: 5px 8px;
            font-weight: bold;
            font-size: 9pt;
            margin: -8px -8px 6px -8px;
            border-top-left-radius: 3px;
            border-top-right-radius: 3px;
        }

        /* Layout 2 Kolom Utama (Foto & Info) */
        .layout-table {
            width: 100%;
            border-collapse: collapse;
        }

        .layout-table td {
            vertical-align: top;
            padding: 1px;
        }

        .photo-column {
            width: 170px;
            text-align: center;
        }

        /* Ukuran Foto Disesuaikan Menjadi 160px x 160px agar Pas 4 Card */
        .asset-photo {
            width: 160px;
            height: 160px;
            border: 1px solid #ccc;
            border-radius: 4px;
            padding: 2px;
            background: #f9f9f9;
            object-fit: cover;
        }

        .no-photo {
            width: 160px;
            height: 160px;
            border: 1px dashed #ccc;
            line-height: 160px;
            text-align: center;
            color: #888;
            font-size: 8.5pt;
            background: #f8f9fa;
            border-radius: 4px;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-table td {
            padding: 2.5px 4px;
            font-size: 8.5pt;
        }

        .label {
            font-weight: bold;
            color: #4a5568;
            width: 25%;
        }

        .value {
            color: #1a202c;
            width: 25%;
        }

        .badge {
            display: inline-block;
            padding: 1px 5px;
            font-size: 7.5pt;
            font-weight: bold;
            border-radius: 3px;
        }
        .badge-success { background: #c6f6d5; color: #22543d; }
        .badge-danger { background: #fed7d7; color: #742a2a; }

        .footer-info {
            margin-top: 5px;
            font-size: 7pt;
            color: #718096;
            text-align: right;
            border-top: 1px dashed #e2e8f0;
            padding-top: 2px;
        }
    </style>
</head>
<body>

    <div class="page-title">List Data Asset</div>

    @forelse ($assets as $index => $asset)
        <div class="card">
            <div class="card-header">
                #{{ $index + 1 }} | {{ $asset->nama ?? 'Aset Tanpa Nama' }}
                <span style="float: right;">
                    {{ $asset->plant->kode ?? '' }} {{ isset($asset->plant->nama) ? '- ' . $asset->plant->nama : '' }}
                </span>
            </div>

            <table class="layout-table">
                <tr>
                    <!-- Kolom Foto (160px x 160px) -->
                    <td class="photo-column">
                        @php
                            $photoBase64 = null;
                            if (!empty($asset->foto)) {
                                try {
                                    $storagePath = storage_path('app/public/' . $asset->foto);
                                    $publicPath = public_path($asset->foto);

                                    if (file_exists($storagePath)) {
                                        $type = pathinfo($storagePath, PATHINFO_EXTENSION);
                                        $data = file_get_contents($storagePath);
                                        $photoBase64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                                    } elseif (file_exists($publicPath)) {
                                        $type = pathinfo($publicPath, PATHINFO_EXTENSION);
                                        $data = file_get_contents($publicPath);
                                        $photoBase64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                                    }
                                } catch (\Exception $e) {
                                    $photoBase64 = null;
                                }
                            }
                        @endphp

                        @if($photoBase64)
                            <img src="{{ $photoBase64 }}" class="asset-photo" alt="Foto Asset">
                        @else
                            <div class="no-photo">-</div>
                        @endif
                    </td>

                    <!-- Detail Isian Atribut Model Asset -->
                    <td>
                        <table class="info-table">
                            <tr>
                                <td class="label">Nomor Asset / Sub</td>
                                <td class="value">: {{ $asset->nomor ?? '-' }} / {{ $asset->sub ?? '0' }}</td>
                            </tr>
                            <tr>
                                <td class="label">Serial Number</td>
                                <td class="value">: {{ $asset->serial_number ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="label">Harga / NBV</td>
                                <td class="value" colspan="3">: Rp {{ number_format((float)($asset->harga ?? 0), 0, ',', '.') }} / Rp {{ number_format((float)($asset->nbv ?? 0), 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td class="label">Tgl Perolehan</td>
                                <td class="value">: {{ !empty($asset->tgl_perolehan) ? \Carbon\Carbon::parse($asset->tgl_perolehan)->format('d/m/Y') : '-' }}</td>
                            </tr>
                            <tr>
                                <td class="label">Kondisi</td>
                                <td class="value">: {{ $asset->kondisi ?? '-' }}</td>
                            </tr>                            
                            <tr>
                                <td class="label">Lokasi</td>
                                <td class="value" colspan="3">: {{ $asset->lokasi ?? '-' }}</td>
                            </tr>                            
                            <tr>
                                <td class="label">Keterangan</td>
                                <td class="value" colspan="3">: {{ $asset->keterangan ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="label">Tipe</td>
                                <td class="value">: {{ $asset->tipe ?? '-' }}</td>
                            </tr>    
                            <tr>    
                                <td class="label">Qty (SAP/Aktual)</td>
                                <td class="value">: {{ $asset->qty_sap ?? 0 }} / {{ $asset->qty_aktual ?? 0 }}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

            <div class="footer-info">
                | Dicetak: {{ date('d/m/Y H:i') }}
            </div>
        </div>
    @empty
        <p style="text-align: center; margin-top: 50px;">Tidak ada data aset yang dipilih.</p>
    @endforelse

</body>
</html>