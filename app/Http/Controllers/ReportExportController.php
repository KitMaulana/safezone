<?php

namespace App\Http\Controllers;

use App\Exports\AttendanceExport;
use App\Models\AttendanceLog;
use App\Models\Student;
use App\Services\SettingService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class ReportExportController extends Controller
{
    /** Ekspor log kehadiran ke XLSX atau PDF. */
    public function attendance(Request $request)
    {
        $data = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
            'type' => ['nullable', 'in:in,out'],
            'classRoom' => ['nullable', 'string'],
            'gateId' => ['nullable', 'integer'],
            'format' => ['nullable', 'in:xlsx,pdf'],
        ]);

        $from = $data['from'] ?? now('Asia/Jakarta')->startOfMonth()->toDateString();
        $to = $data['to'] ?? now('Asia/Jakarta')->toDateString();

        $logs = AttendanceLog::query()
            ->with(['student:id,name,class_room,nisn', 'vehicle:id,plate_number', 'gate:id,name', 'scannedBy:id,name'])
            ->whereDate('scanned_at', '>=', $from)
            ->whereDate('scanned_at', '<=', $to)
            ->when($data['type'] ?? null, fn ($q, $type) => $q->where('type', $type))
            ->when($data['gateId'] ?? null, fn ($q, $gate) => $q->where('gate_id', $gate))
            ->when($data['classRoom'] ?? null, fn ($q, $room) => $q->whereHas('student', fn ($s) => $s->where('class_room', $room)))
            ->orderBy('scanned_at')
            ->get();

        $filename = 'laporan-kehadiran-'.$from.'-sd-'.$to;

        if (($data['format'] ?? 'xlsx') === 'pdf') {
            $pdf = Pdf::loadView('pdf.report-attendance', [
                'logs' => $logs,
                'from' => $from,
                'to' => $to,
                'classRoom' => $data['classRoom'] ?? null,
                'schoolName' => app(SettingService::class)->get('school_name'),
            ]);

            $pdf->setPaper('a4', 'landscape');

            return $pdf->download($filename.'.pdf');
        }

        return Excel::download(new AttendanceExport($logs), $filename.'.xlsx');
    }

    /** Ringkasan bulanan untuk orang tua / siswa. */
    public function monthly(Request $request)
    {
        $data = $request->validate([
            'student' => ['nullable', 'integer'],
            'month' => ['nullable', 'date_format:Y-m'],
        ]);

        $user = $request->user();
        $month = $data['month'] ?? now('Asia/Jakarta')->format('Y-m');
        [$year, $monthNumber] = explode('-', $month);

        $student = isset($data['student'])
            ? Student::findOrFail($data['student'])
            : Student::where('user_id', $user->id)->firstOrFail();

        abort_unless($user->can('view', $student), 403);

        $logs = AttendanceLog::with('gate:id,name')
            ->where('student_id', $student->id)
            ->whereYear('scanned_at', $year)
            ->whereMonth('scanned_at', $monthNumber)
            ->orderBy('scanned_at')
            ->get();

        $pdf = Pdf::loadView('pdf.report-monthly', [
            'student' => $student,
            'logs' => $logs,
            'monthLabel' => Carbon::parse($month.'-01')->translatedFormat('F Y'),
            'schoolName' => app(SettingService::class)->get('school_name'),
        ]);

        return $pdf->download('rekap-'.$student->nisn.'-'.$month.'.pdf');
    }
}
