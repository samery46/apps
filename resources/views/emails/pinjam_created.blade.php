<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Notifikasi Peminjaman Perangkat</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f4f6f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: #333333;">

    <table width="100%" border="0" cellpadding="0" cellspacing="0" style="background-color: #f4f6f9; padding: 30px 15px;">
        <tr>
            <td align="center">
                <!-- Wrapper Card -->
                <table width="100%" max-width="600px" border="0" cellpadding="0" cellspacing="0" style="max-width: 600px; background-color: #ffffff; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); overflow: hidden;">
                    
                    <!-- Header -->
                    <tr>
                        <td style="background-color: #2563eb; padding: 30px 20px; text-align: center;">
                            <h2 style="margin: 0; color: #ffffff; font-size: 24px; font-weight: 600; letter-spacing: 0.5px;">
                                AppsClub 
                            </h2>
                            <p style="margin: 5px 0 0 0; color: #bfdbfe; font-size: 14px;">Notifikasi Peminjaman Perangkat</p>
                        </td>
                    </tr>

                    <!-- Body Content -->
                    <tr>
                        <td style="padding: 30px 30px 20px 30px;">
                            <p style="margin: 0 0 20px 0; font-size: 16px; line-height: 1.5; color: #4b5563;">
                                Hai, berikut adalah rincian data peminjaman perangkat yang telah berhasil dicatat ke dalam sistem:
                            </p>

                            <!-- Data Identitas Utama -->
                            <table width="100%" border="0" cellpadding="0" cellspacing="0" style="margin-bottom: 25px; background-color: #f8fafc; border-radius: 6px; padding: 15px;">
                                <tr>
                                    <td width="35%" style="padding: 6px 0; font-size: 14px; font-weight: 600; color: #64748b;">Nama Karyawan</td>
                                    <td style="padding: 6px 0; font-size: 14px; color: #1e293b; font-weight: 500;">: {{ $pinjam->karyawan->nama }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 6px 0; font-size: 14px; font-weight: 600; color: #64748b;">Tanggal Pinjam</td>
                                    <td style="padding: 6px 0; font-size: 14px; color: #1e293b;">: {{ \Carbon\Carbon::parse($pinjam->tgl_pinjam)->translatedFormat('d F Y') }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 6px 0; font-size: 14px; font-weight: 600; color: #64748b;">User Penyerah</td>
                                    <td style="padding: 6px 0; font-size: 14px; color: #1e293b;">: {{ $pinjam->user->name }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 6px 0; font-size: 14px; font-weight: 600; color: #64748b;">Status Transaksi</td>
                                    <td style="padding: 6px 0; font-size: 14px;">: 
                                        @if($pinjam->is_complete)
                                            <span style="background-color: #dcfce7; color: #15803d; padding: 2px 8px; border-radius: 12px; font-size: 12px; font-weight: 600;">Sudah dikembalikan</span>
                                        @else
                                            <span style="background-color: #fef9c3; color: #a16207; padding: 2px 8px; border-radius: 12px; font-size: 12px; font-weight: 600;">Belum dikembalikan</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 6px 0; font-size: 14px; font-weight: 600; color: #64748b;">Keterangan</td>
                                    <td style="padding: 6px 0; font-size: 14px; color: #64748b; font-style: italic;">: {{ $pinjam->keterangan ?? '' }}</td>
                                </tr>
                            </table>

                            <!-- Data Tabel Perangkat -->
                            <h3 style="margin: 0 0 10px 0; font-size: 16px; font-weight: 600; color: #0f172a;">Daftar Perangkat Yang Dipinjam</h3>
                            <table width="100%" border="0" cellpadding="10" cellspacing="0" style="border: 1px solid #e2e8f0; border-radius: 6px; overflow: hidden; margin-bottom: 20px;">
                                <tr style="background-color: #f1f5f9;">
                                    <th align="left" style="font-size: 13px; font-weight: 600; color: #475569; border-bottom: 1px solid #e2e8f0;">No</th>
                                    <th align="left" style="font-size: 13px; font-weight: 600; color: #475569; border-bottom: 1px solid #e2e8f0;">Nama Perangkat / Item</th>
                                    <th align="left" style="font-size: 13px; font-weight: 600; color: #475569; border-bottom: 1px solid #e2e8f0;">Serial Number (SN)</th>
                                </tr>
                                @foreach($pinjam->items as $index => $item)
                                <tr>
                                    <td style="font-size: 14px; color: #334155; border-bottom: 1px solid #f1f5f9;">{{ $index + 1 }}</td>
                                    <td style="font-size: 14px; color: #1e293b; font-weight: 500; border-bottom: 1px solid #f1f5f9;">{{ $item->perangkat->nama }}</td>
                                    <td style="font-size: 14px; color: #475569; font-family: monospace; border-bottom: 1px solid #f1f5f9;">{{ $item->perangkat->serial_number }}</td>
                                </tr>
                                @endforeach
                            </table>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8fafc; padding: 20px 30px; text-align: center; border-top: 1px solid #e2e8f0;">
                            <p style="margin: 0; font-size: 12px; color: #94a3b8;">
                                Email ini dikirim secara otomatis oleh sistem. Mohon tidak membalas langsung ke alamat ini jika status *Reply-To* tidak aktif.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

</body>
</html>