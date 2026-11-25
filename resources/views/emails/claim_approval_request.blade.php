<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Permintaan Persetujuan Klaim Aset</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f1f5f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: #1e293b;">

    <table width="100%" border="0" cellpadding="0" cellspacing="0" style="background-color: #f1f5f9; padding: 30px 15px;">
        <tr>
            <td align="center">
                <!-- Wrapper Card -->
                <table width="100%" border="0" cellpadding="0" cellspacing="0" style="max-width: 620px; background-color: #ffffff; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.06); overflow: hidden; border: 1px solid #e2e8f0;">
                    
                    <!-- Header -->
                    <tr>
                        <td style="background-color: #1e3a8a; padding: 26px 20px; text-align: center;">
                            <h2 style="margin: 0; color: #ffffff; font-size: 22px; font-weight: 700; letter-spacing: 0.5px;">
                                AppsClub
                            </h2>
                            <p style="margin: 6px 0 0 0; color: #93c5fd; font-size: 14px;">Permintaan Persetujuan (Approval Request) Klaim Asuransi Aset</p>
                        </td>
                    </tr>

                    <!-- Body Content -->
                    <tr>
                        <td style="padding: 28px 30px 20px 30px;">
                            <p style="margin: 0 0 16px 0; font-size: 15px; line-height: 1.5; color: #334155;">
                                Yth. <strong>{{ $approval->user->name }}</strong>,
                            </p>
                            <p style="margin: 0 0 20px 0; font-size: 14px; line-height: 1.6; color: #475569;">
                                Terdapat pengajuan klaim asuransi aset yang membutuhkan persetujuan Anda pada <strong>Tahap {{ $approval->step_order }}</strong>.
                                Berikut adalah ringkasan data klaim asuransi:
                            </p>

                            <!-- Data Identitas Klaim -->
                            <table width="100%" border="0" cellpadding="0" cellspacing="0" style="margin-bottom: 24px; background-color: #f8fafc; border-radius: 6px; padding: 16px; border: 1px solid #e2e8f0;">
                                <tr>
                                    <td width="38%" style="padding: 6px 0; font-size: 13px; font-weight: 600; color: #64748b;">No. Tiket </td>
                                    <td style="padding: 6px 0; font-size: 14px; color: #0f172a; font-weight: 700; font-family: monospace;">: {{ $claim->ticket_number }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 6px 0; font-size: 13px; font-weight: 600; color: #64748b;">Plant</td>
                                    <td style="padding: 6px 0; font-size: 13px; color: #1e293b; font-weight: 600;">: {{ $claim->plant->kode ?? '-' }} - {{ $claim->plant->nama ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 6px 0; font-size: 13px; font-weight: 600; color: #64748b;">Unit Aset Fisik</td>
                                    <td style="padding: 6px 0; font-size: 13px; color: #1e293b;">: {{ $claim->asset->nomor ?? '-' }} - {{ $claim->asset->nama ?? 'Aset' }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 6px 0; font-size: 13px; font-weight: 600; color: #64748b;">Tanggal Kejadian</td>
                                    <td style="padding: 6px 0; font-size: 13px; color: #1e293b;">: {{ \Carbon\Carbon::parse($claim->incident_date)->translatedFormat('d F Y H:i') }} WIB</td>
                                </tr>
                                <tr>
                                    <td style="padding: 6px 0; font-size: 13px; font-weight: 600; color: #64748b;">Estimasi Kerugian</td>
                                    <td style="padding: 6px 0; font-size: 14px; color: #b91c1c; font-weight: 700;">: Rp {{ number_format($claim->estimasi_kerugian, 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 6px 0; font-size: 13px; font-weight: 600; color: #64748b;">Diajukan Oleh</td>
                                    <td style="padding: 6px 0; font-size: 13px; color: #1e293b;">: {{ $claim->creator->name ?? 'Staf' }} ({{ $claim->creator->email ?? '-' }})</td>
                                </tr>
                            </table>

                            <!-- Latar Belakang -->
                            <div style="margin-bottom: 25px; padding: 14px 16px; background-color: #fffbeb; border-left: 4px solid #f59e0b; border-radius: 4px;">
                                <strong style="font-size: 13px; color: #92400e;">latar Belakang :</strong>
                                <p style="margin: 6px 0 0 0; font-size: 13px; color: #78350f; line-height: 1.5; white-space: pre-line;">{{ \Illuminate\Support\Str::limit($claim->latar_belakang, 500) }}</p>
                            </div>

                            <!-- Kronologi / Uraian -->
                            <div style="margin-bottom: 25px; padding: 14px 16px; background-color: #fffbeb; border-left: 4px solid #f59e0b; border-radius: 4px;">
                                <strong style="font-size: 13px; color: #92400e;">Kronologi Kejadian:</strong>
                                <p style="margin: 6px 0 0 0; font-size: 13px; color: #78350f; line-height: 1.5; white-space: pre-line;">{{ \Illuminate\Support\Str::limit($claim->kronologi, 500) }}</p>
                            </div>                            

                            <!-- Tombol Aksi Approval -->
                            <div style="text-align: center; margin: 30px 0 15px 0;">
                                <table width="100%" border="0" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td align="center" style="padding-bottom: 12px;">
                                            <!-- Tombol Approve (Hijau) -->
                                            <a href="{{ $approveUrl }}" style="display: inline-block; background-color: #16a34a; color: #ffffff; text-decoration: none; padding: 12px 28px; border-radius: 6px; font-weight: 700; font-size: 14px; margin-right: 8px; box-shadow: 0 2px 4px rgba(22, 163, 74, 0.2);">
                                                ✅ SETUJUI KLAIM
                                            </a>
                                            <!-- Tombol Reject (Merah) -->
                                            <a href="{{ $rejectUrl }}" style="display: inline-block; background-color: #dc2626; color: #ffffff; text-decoration: none; padding: 12px 28px; border-radius: 6px; font-weight: 700; font-size: 14px; box-shadow: 0 2px 4px rgba(220, 38, 38, 0.2);">
                                                ❌ TOLAK KLAIM
                                            </a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td align="center" style="padding-top: 8px;">
                                            <a href="{{ $viewUrl }}" style="font-size: 12px; color: #2563eb; text-decoration: underline;">
                                                🔗 Link web aplikasi untuk melihat detail dokumen dan lampiran / berita acara lengkapnya.
                                            </a>
                                        </td>
                                    </tr>
                                </table>
                            </div>

                            <p style="margin: 25px 0 0 0; font-size: 12px; color: #94a3b8; text-align: center; line-height: 1.5;">
                                * Tautan persetujuan di atas dilengkapi tanda tangan digital (Signed URL) dan berlaku aman hingga 30 hari ke depan.
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8fafc; padding: 18px 30px; text-align: center; border-top: 1px solid #e2e8f0;">
                            <p style="margin: 0; font-size: 12px; color: #94a3b8;">
                                Email ini dikirim secara otomatis oleh AppsClub. Anda menerima email ini karena terdaftar sebagai approver resmi untuk Plant terkait.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

</body>
</html>
