<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Kehadiran</title>
    <style>
        * { font-family: "DejaVu Sans", sans-serif; }
        body { font-size: 9pt; color: #1e293b; }
        h1 { font-size: 13pt; margin: 0 0 2mm 0; }
        .meta { font-size: 8pt; color: #64748b; margin-bottom: 4mm; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #1D4ED8; color: #fff; font-size: 8pt; padding: 2mm 1.5mm; text-align: left; }
        td { border-bottom: 0.2mm solid #e2e8f0; padding: 1.6mm 1.5mm; font-size: 8pt; }
        tr:nth-child(even) td { background: #f8fafc; }
        .foot { margin-top: 5mm; font-size: 7.5pt; color: #94a3b8; text-align: right; }
    </style>
</head>
<body>
    <h1>Laporan Kehadiran Kendaraan Siswa</h1>
    <div class="meta">
        {{ $schoolName }} &bull; School Safe Zone<br>
        Periode {{ \Illuminate\Support\Carbon::parse($from)->format('d M Y') }} – {{ \Illuminate\Support\Carbon::parse($to)->format('d M Y') }}
        @if ($classRoom) &bull; Kelas {{ $classRoom }} @endif
        &bull; {{ $logs->count() }} catatan
    </div>

    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Jam</th>
                <th>NISN</th>
                <th>Nama</th>
                <th>Kelas</th>
                <th>No. Polisi</th>
                <th>Jenis</th>
                <th>Gerbang</th>
                <th>Petugas</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($logs as $log)
                @php $local = $log->scanned_at->timezone('Asia/Jakarta'); @endphp
                <tr>
                    <td>{{ $local->format('d/m/Y') }}</td>
                    <td>{{ $local->format('H.i') }}</td>
                    <td>{{ $log->student->nisn }}</td>
                    <td>{{ $log->student->name }}</td>
                    <td>{{ $log->student->class_room }}</td>
                    <td>{{ $log->vehicle?->formatted_plate ?? '-' }}</td>
                    <td>{{ $log->typeLabel() }}{{ $log->is_early_leave ? ' (lebih awal)' : '' }}</td>
                    <td>{{ $log->gate?->name ?? '-' }}</td>
                    <td>{{ $log->scannedBy?->name ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="foot">Dicetak {{ now('Asia/Jakarta')->format('d M Y H.i') }} WIB</div>
</body>
</html>
