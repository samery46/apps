<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        @page {
            margin: 10px;
        }

        body {
            font-family: sans-serif;
            font-size: 11px;
            margin: 10px;
        }

        .title {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
        }

        .subtitle {
            text-align: center;
            font-size: 14px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }

        td,
        th {
            padding: 4px;
            vertical-align: top;
        }

        .box {
            border: 1px solid black;
            padding: 5px;
            margin-top: 10px;
        }

        .checkbox {
            border: 1px solid black;
            width: 10px;
            height: 10px;
            display: inline-block;
            text-align: center;
            line-height: 10px;
            font-weight: bold;
            margin-right: 5px;
        }

        .signatures {
            margin-top: 40px;
            text-align: center;
        }

        .signatures td {
            width: 33%;
        }

        .page-break {
            page-break-after: always;
        }

        .section-title {
            font-weight: bold;
            background: #eee;
            padding: 3px;
            margin-bottom: 4px;
        }
    </style>
</head>

<body>
    @foreach ($assetRequests as $assetRequest)
        <div class="section">
            <div class="title" style="text-decoration: underline;">FIXED ASSET NUMBER REQUEST FORM</div>
            <div class="subtitle">PT. TIRTA SUKSES PERKASA</div>

            <table style="margin-top: 20px;">
                <tr>
                    <td>Plant : {{ $assetRequest->plant->kode ?? '' }} - TSP {{ $assetRequest->plant->nama ?? '' }}</td>
                    <td>Tanggal : {{ \Carbon\Carbon::parse($assetRequest->created_at)->format('d-M-y') }}</td>
                    <td>No. Transaksi : {{ $assetRequest->document_number }}</td>
                    <td>Dibuat Oleh : {{ $assetRequest->user->name ?? '-' }}</td>
                </tr>
            </table>

            <div class="box">
                <div class="section-title">DIISI OLEH ACCOUNTING & ASSETS MANAGEMENT</div>
                <table>
                    <tr>
                        <td>Kelompok Aktiva Tetap</td>
                        <td>: {{ $assetRequest->assetGroup->asset_group ?? '' }} -
                            {{ $assetRequest->assetGroup->name ?? '' }}</td>
                        <td>Jenis</td>
                        <td>: {{ $assetRequest->type }}</td>
                    </tr>
                    <tr>
                        <td>Nomor Aktiva Tetap</td>
                        <td>: {{ $assetRequest->fixed_asset_number ?? '-' }}</td>
                        <td>Sub Asset No</td>
                        <td>: {{ $assetRequest->sub_asset_number ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>Nomor CEA</td>
                        <td>: {{ $assetRequest->cea_number ?? '-' }}</td>
                        <td>Masa Penggunaan</td>
                        <td>: {{ $assetRequest->usage_period ?? '-' }} Tahun</td>
                    </tr>
                    <tr>
                        <td>Cost Center</td>
                        <td>: {{ $assetRequest->costCenter->cost_center ?? '' }} -
                            {{ $assetRequest->costCenter->name ?? '' }}</td>
                        <td>Jumlah</td>
                        <td>: {{ $assetRequest->quantity }} Unit</td>
                    </tr>
                </table>
            </div>

            <div class="box">
                <div class="section-title">Status Barang</div>
                <table>
                    <tr>
                        <td>
                            <div class="checkbox">
                                @if ($assetRequest->condition === 'Baru')
                                    v
                                @endif
                            </div> Baru
                        </td>
                        <td>
                            <div class="checkbox">
                                @if ($assetRequest->condition === 'Bekas')
                                    v
                                @endif
                            </div> Bekas
                        </td>
                    </tr>
                </table>
            </div>

            <div class="box">
                <div class="section-title">Detail</div>

                <table>
                    <tr>
                        <td>Jenis / Nama Barang</td>
                        <td>: {{ $assetRequest->item_name }}</td>
                    </tr>
                    <tr>
                        <td>Asal Negara</td>
                        <td>: {{ $assetRequest->country_of_origin ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>Pembuat & Tahun Pembuatan</td>
                        <td>: {{ $assetRequest->manufacturer ?? '-' }} / {{ $assetRequest->year_of_manufacture }}</td>
                    </tr>
                    <tr>
                        <td>Pemasok</td>
                        <td>: {{ $assetRequest->supplier ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>Rencana Kedatangan</td>
                        <td>: {{ \Carbon\Carbon::parse($assetRequest->expected_arrival)->translatedFormat('F Y') }}
                        </td>
                    </tr>
                    <tr>
                        <td>Rencana Pemakaian</td>
                        <td>: {{ \Carbon\Carbon::parse($assetRequest->expected_usage)->translatedFormat('F Y') }}</td>
                    </tr>
                    <tr>
                        <td>Lokasi Penempatan</td>
                        <td>: {{ $assetRequest->plant->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>Lokasi (Ruang/Dept)</td>
                        <td>: {{ $assetRequest->location ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>Catatan</td>
                        <td>: {{ $assetRequest->description ?? '-' }}</td>
                    </tr>
                </table>
            </div>
            <p style="padding-top: 30px;"><strong>Approval History:</strong></p>

            <table style="margin-top: 10px; border-collapse: collapse; width: 100%;">
                <tr align="left">
                    <th style="border: 1px solid black;">No.</th>
                    <th style="border: 1px solid black;">User Approval</th>
                    <th style="border: 1px solid black;">Status</th>
                    <th style="border: 1px solid black;">Tanggal</th>
                    <th style="border: 1px solid black;">Catatan</th>
                </tr>
                @forelse ($assetRequest->approvals as $approval)
                    <tr align="left">
                        <td style="border: 1px solid black;">{{ $loop->iteration }}.</td>
                        <td style="border: 1px solid black;">{{ $approval->user->name }}</td>
                        <td style="border: 1px solid black;">{{ ucfirst($approval->status) }}</td>
                        <td style="border: 1px solid black;">
                            @if ($approval->approved_at)
                                {{ \Carbon\Carbon::parse($approval->approved_at)->format('d/m/Y H:i') }}
                            @endif
                        </td>
                        <td style="border: 1px solid black;">{{ ucfirst($approval->note) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" style="border: 1px solid black;">-</td>
                    </tr>
                @endforelse
            </table>
            <p style="padding-top: 60px;font-style: italic;">Dicetak pada :
                {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</p>
        </div>

        @if (!$loop->last)
            <div class="page-break"></div>
        @endif
    @endforeach
</body>

</html>
