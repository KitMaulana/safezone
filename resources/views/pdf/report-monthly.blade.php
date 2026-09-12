<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Rekap Bulanan</title>
    <style>
        * { font-family: "DejaVu Sans", sans-serif; }
        body { font-size: 10pt; color: #1e293b; }
        h1 { font-size: 14pt; margin: 0 0 2mm 0; }
        .meta { font-size: 9pt; color: #64748b; margin-bottom: 5mm; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #1D4ED8; color: #fff; font-size: 9pt; padding: 2mm; text-align: left; }
        td { border-bottom: 0.2mm solid #e2e8f0; padding: 2mm; font-size: 9pt; }
        tr:nth-child(even) td { background: #f8fafc; }
        .foot { margin-top: 6mm; font-size: 8pt; color: #94a3b8; text-align: right; }
    </style>
</head>
<body>
    @php
        $days = $logs->groupBy(fn ($log) => $log->scanned_at->timezone('Asia/Jakarta')->toDateString());
    @endphp

    <h1>Rekap Kehadiran Bulanan</h1>
    <div class="meta">
        {{ $schoolName }} &bull; School Safe Zone<br>
        <strong>{{ $student->name }}</strong> &bull; NISN {{ $student->nisn }} &bull; Kelas {{ $student->class_room }}<br>
        Periode {{ $monthLabel }} &bull; {{ $days->count() }} hari tercatat
    </div>

    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Masuk</th>
                <th>Keluar</th>
                <th>Durasi</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($days as $date => $dayLogs)
                @php
                    $in = $dayLogs->where('type', 'in')->sortBy('scanned_at')->first();
                    $out = $dayLogs->where('type', 'out')->sortBy('scanned_at')->last();
                    $duration = $in && $out ? $in->scanned_at->diff($out->scanned_at)->format('%hj %im') : '-';
                @endphp
                <tr>
                    <td>{{ \Illuminate\Support\Carbon::parse($date)->translatedFormat('l, d M Y') }}</td>
                    <td>{{ $in?->scanned_at->timezone('Asia/Jakarta')->format('H.i') ?? '-' }}</td>
                    <td>{{ $out?->scanned_at->timezone('Asia/Jakarta')->format('H.i') ?? '-' }}</td>
                    <td>{{ $duration }}</td>
                    <td>{{ $out?->is_early_leave ? 'Keluar lebih awal' : '' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="foot">Dicetak {{ now('Asia/Jakarta')->format('d M Y H.i') }} WIB</div>
</body>
</html>
