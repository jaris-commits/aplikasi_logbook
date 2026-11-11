<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Rekap Logbook</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #1f2937;
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        .meta {
            margin-bottom: 15px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #d1d5db;
            padding: 8px;
            text-align: left;
            vertical-align: top;
        }
        th {
            background: #eef2ff;
        }
        .status {
            font-weight: bold;
            text-transform: capitalize;
        }
    </style>
</head>
<body>
    <h2>Rekap Logbook Magang</h2>
    <div class="meta">
        <p><strong>Nama:</strong> {{ $user->name }}</p>
        <p><strong>Role:</strong> {{ ucfirst($user->role) }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Aktivitas</th>
                <th>Status</th>
                <th>Catatan Guru</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logbooks as $log)
                <tr>
                    <td>{{ $log->tanggal }}</td>
                    <td>{{ $log->aktivitas }}</td>
                    <td class="status">{{ $log->status }}</td>
                    <td>{{ $log->catatan_guru ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4">Belum ada data logbook.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if($signatureSvg)
        <div style="margin-top: 40px; width: 250px; margin-left: auto; text-align: center;">
            <p>Tanda tangan visual</p>
            {!! $signatureSvg !!}
            <p><strong>{{ $signature->user->name }}</strong><br>{{ $signature->signed_at->format('d/m/Y H:i') }}</p>
            <p style="font-size: 8px; word-break: break-all;">Hash data: {{ $signature->document_hash }}</p>
            <p style="font-size: 8px;">Gambar tanda tangan untuk keperluan internal, bukan tanda tangan elektronik tersertifikasi.</p>
        </div>
    @endif
</body>
</html>
