<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembaruan Status Klaim Aset</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f1f5f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: #1e293b;">

    <table width="100%" border="0" cellpadding="0" cellspacing="0" style="background-color: #f1f5f9; padding: 30px 15px;">
        <tr>
            <td align="center">
                <!-- Wrapper Card -->
                <table width="100%" border="0" cellpadding="0" cellspacing="0" style="max-width: 600px; background-color: #ffffff; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.06); overflow: hidden; border: 1px solid #e2e8f0;">
                    
                    <!-- Header -->
                    <tr>
                        <td style="background-color: {{ $finalStatus === 'approved' ? '#15803d' : '#b91c1c' }}; padding: 24px 20px; text-align: center;">
                            <h2 style="margin: 0; color: #ffffff; font-size: 20px; font-weight: 700;">
                                {{ $finalStatus === 'approved' ? '✅ KLAIM ASET DISETUJUI' : '❌ KLAIM ASET DITOLAK' }}
                            </h2>
                            <p style="margin: 5px 0 0 0; color: #f0fdf4; font-size: 13px;">Tiket: {{ $claim->ticket_number }}</p>
                        </td>
                    </tr>

                    <!-- Body Content -->
                    <tr>
                        <td style="padding: 26px 30px;">
                            <p style="margin: 0 0 16px 0; font-size: 15px; color: #334155;">
                                Halo <strong>{{ $claim->creator->name ?? 'Pemohon' }}</strong>,
                            </p>
                            <p style="margin: 0 0 20px 0; font-size: 14px; line-height: 1.6; color: #475569;">
                                Pengajuan klaim asuransi aset Anda dengan rincian berikut telah diperbarui statusnya:
                            </p>

                            <!-- Detail Singkat -->
                            <table width="100%" border="0" cellpadding="0" cellspacing="0" style="margin-bottom: 20px; background-color: #f8fafc; border-radius: 6px; padding: 14px; border: 1px solid #e2e8f0;">
                                <tr>
                                    <td width="35%" style="padding: 5px 0; font-size: 13px; font-weight: 600; color: #64748b;">No. Tiket</td>
                                    <td style="padding: 5px 0; font-size: 13px; color: #0f172a; font-weight: 700;">: {{ $claim->ticket_number }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 5px 0; font-size: 13px; font-weight: 600; color: #64748b;">Aset</td>
                                    <td style="padding: 5px 0; font-size: 13px; color: #1e293b;">: {{ $claim->asset->nama ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 5px 0; font-size: 13px; font-weight: 600; color: #64748b;">Status Akhir</td>
                                    <td style="padding: 5px 0; font-size: 13px; font-weight: 700; color: {{ $finalStatus === 'approved' ? '#15803d' : '#b91c1c' }};">
                                        : {{ strtoupper($finalStatus) }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 5px 0; font-size: 13px; font-weight: 600; color: #64748b;">Keterangan</td>
                                    <td style="padding: 5px 0; font-size: 13px; color: #334155;">: {{ $notes }}</td>
                                </tr>
                            </table>

                            <div style="text-align: center; margin-top: 25px;">
                                <a href="{{ $viewUrl }}" style="display: inline-block; background-color: #1e3a8a; color: #ffffff; text-decoration: none; padding: 10px 24px; border-radius: 6px; font-weight: 600; font-size: 13px;">
                                    Buka Dokumen di Web Aplikasi
                                </a>
                            </div>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8fafc; padding: 16px 30px; text-align: center; border-top: 1px solid #e2e8f0;">
                            <p style="margin: 0; font-size: 12px; color: #94a3b8;">
                                AppsClub - Notifikasi Otomatis
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

</body>
</html>
