<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Rekap Nilai</title>
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
        }
        th {
            background: #ecfeff;
        }
    </style>
</head>
<body>
    <h2>Rekap Nilai Magang</h2>
    <div class="meta">
        <p><strong>Nama:</strong> {{ $user->name }}</p>
        <p><strong>Role:</strong> {{ ucfirst($user->role) }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Nama Siswa</th>
                <th>Semester</th>
                <th>Nilai</th>
                <th>Catatan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($grades as $grade)
                <tr>
                    <td>{{ $grade->user->name ?? '-' }}</td>
                    <td>{{ $grade->semester }}</td>
                    <td>{{ $grade->nilai }}</td>
                    <td>{{ $grade->catatan ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4">Belum ada data nilai.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
