<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AttendanceExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping
{
    public function __construct(private readonly Collection $logs) {}

    public function collection(): Collection
    {
        return $this->logs;
    }

    public function headings(): array
    {
        return [
            'Tanggal', 'Jam', 'NISN', 'Nama', 'Kelas', 'Nomor Polisi',
            'Jenis', 'Sifat', 'Keluar Lebih Awal', 'Gerbang', 'Petugas', 'Catatan',
        ];
    }

    public function map($log): array
    {
        $local = $log->scanned_at->timezone('Asia/Jakarta');

        return [
            $local->format('d/m/Y'),
            $local->format('H.i'),
            $log->student->nisn,
            $log->student->name,
            $log->student->class_room,
            $log->vehicle?->formatted_plate ?? '-',
            $log->typeLabel(),
            $log->kindLabel(),
            $log->is_early_leave ? 'Ya' : 'Tidak',
            $log->gate?->name ?? '-',
            $log->scannedBy?->name ?? '-',
            $log->note ?? '-',
        ];
    }
}
