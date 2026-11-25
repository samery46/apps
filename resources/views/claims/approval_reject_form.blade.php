<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Konfirmasi Penolakan Klaim Asuransi Aset</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f8fafc;
            color: #0f172a;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }
        .card {
            background: #ffffff;
            max-width: 520px;
            width: 100%;
            border-radius: 12px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
            border: 1px solid #e2e8f0;
            padding: 32px 28px;
        }
        .header {
            text-align: center;
            margin-bottom: 24px;
        }
        .icon {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            background-color: #fee2e2;
            color: #dc2626;
            margin-bottom: 12px;
        }
        h1 { font-size: 19px; font-weight: 700; color: #991b1b; margin: 0 0 6px 0; }
        p.subtitle { font-size: 13px; color: #64748b; margin: 0; }
        .ticket-info {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 12px 14px;
            font-size: 13px;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #334155;
            margin-bottom: 6px;
        }
        textarea {
            width: 100%;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 10px 12px;
            font-family: inherit;
            font-size: 14px;
            resize: vertical;
            min-height: 100px;
        }
        textarea:focus {
            outline: none;
            border-color: #dc2626;
            box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
        }
        .actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }
        .btn {
            padding: 10px 20px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            border: none;
        }
        .btn-cancel { background: #f1f5f9; color: #475569; }
        .btn-danger { background: #dc2626; color: #ffffff; }
        .btn-danger:hover { background: #b91c1c; }
    </style>
</head>
<body>
    <div class="card">
        <div class="header">
            <div class="icon">✕</div>
            <h1>Konfirmasi Penolakan Klaim Asuransi Aset</h1>
            <p class="subtitle">Silakan berikan alasan atau catatan penolakan untuk pembuat tiket.</p>
        </div>

        <div class="ticket-info">
            <div><strong>No. Tiket:</strong> {{ $claim->ticket_number }}</div>
            <div><strong>Aset:</strong> {{ $claim->asset->nama ?? '-' }}</div>
            <div><strong>Approver:</strong> {{ $approval->user->name }} (Tahap {{ $approval->step_order }})</div>
        </div>

        <form action="{{ $submitUrl }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="catatan">Alasan Penolakan <span style="color: #dc2626;">*</span></label>
                <textarea name="catatan" id="catatan" required placeholder="Contoh: Dokumen Berita Acara belum ditandatangani oleh saksi di lapangan..."></textarea>
            </div>

            <div class="actions">
                <a href="{{ url('/admin/claims') }}" class="btn btn-cancel">Batal</a>
                <button type="submit" class="btn btn-danger">Tolak Klaim Ini</button>
            </div>
        </form>
    </div>
</body>
</html>
