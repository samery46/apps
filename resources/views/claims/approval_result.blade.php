<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Status Persetujuan Klaim Asuransi Aset</title>
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
            max-width: 500px;
            width: 100%;
            border-radius: 12px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.01);
            border: 1px solid #e2e8f0;
            padding: 36px 30px;
            text-align: center;
        }
        .icon {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            margin-bottom: 20px;
        }
        .icon-success { background-color: #dcfce7; color: #16a34a; }
        .icon-danger { background-color: #fee2e2; color: #dc2626; }
        .icon-info { background-color: #e0f2fe; color: #0284c7; }
        h1 { font-size: 20px; font-weight: 700; margin: 0 0 12px 0; }
        p { font-size: 14px; line-height: 1.6; color: #475569; margin: 0 0 24px 0; }
        .ticket-box {
            background: #f1f5f9;
            border-radius: 8px;
            padding: 12px;
            font-size: 13px;
            color: #334155;
            margin-bottom: 24px;
            text-align: left;
        }
        .ticket-box div { margin-bottom: 4px; }
        .ticket-box div:last-child { margin-bottom: 0; }
        .btn {
            display: inline-block;
            background-color: #1e3a8a;
            color: #ffffff;
            text-decoration: none;
            padding: 10px 24px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            transition: background-color 0.2s;
        }
        .btn:hover { background-color: #1e40af; }
    </style>
</head>
<body>
    <div class="card">
        @if($success)
            <div class="icon icon-success">✓</div>
            <h1 style="color: #16a34a;">Persetujuan Berhasil</h1>
        @else
            <div class="icon icon-info">ℹ</div>
            <h1 style="color: #0369a1;">Pemberitahuan</h1>
        @endif

        <p>{{ $message }}</p>

        @if(isset($claim))
        <div class="ticket-box">
            <div><strong>No. Tiket:</strong> {{ $claim->ticket_number }}</div>
            <div><strong>Plant:</strong> {{ $claim->plant->kode ?? '-' }} - {{ $claim->plant->nama ?? '-' }}</div>
            <div><strong>Aset:</strong> {{ $claim->asset->nama ?? '-' }}</div>
            <div><strong>Status Klaim:</strong> <span style="text-transform: uppercase; font-weight: 600;">{{ $claim->status }}</span></div>
        </div>
        @endif

        <div>
            <a href="{{ url('/admin/claims') }}" class="btn">Masuk ke Web Aplikasi</a>
        </div>
    </div>
</body>
</html>
