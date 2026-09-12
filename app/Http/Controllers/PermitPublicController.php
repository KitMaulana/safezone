<?php

namespace App\Http\Controllers;

use App\Enums\PermitStatus;
use App\Enums\ScanResultType;
use App\Models\Gate;
use App\Models\ScanAttempt;
use App\Models\VehiclePermit;
use App\Services\SettingService;
use Illuminate\Http\Request;

/**
 * Satu URL, dua tampilan (SPEC §6.2):
 * - tamu / siswa / ortu  -> hanya plat nomor, status, dan tombol kontak darurat
 * - admin / petugas      -> data lengkap + tombol cek in/out
 */
class PermitPublicController extends Controller
{
    public function __construct(
        private readonly SettingService $settings,
    ) {}

    public function show(Request $request, string $token)
    {
        $permit = VehiclePermit::with(['vehicle.student.parents'])
            ->where('qr_token', $token)
            ->first();

        if (! $permit) {
            $this->record(null, ScanResultType::NotFound, $request, $token);

            return response()->view('public.permit-not-found', [], 404);
        }

        $this->record($permit, ScanResultType::Ok, $request, $token);

        $user = $request->user();

        if ($user?->isStaff()) {
            return view('public.permit-secure', [
                'permit' => $permit,
                'student' => $permit->vehicle->student,
                'vehicle' => $permit->vehicle,
                'primaryParent' => $permit->vehicle->student->primaryParent(),
                'recentLogs' => $permit->vehicle->student->attendanceLogs()
                    ->with('gate:id,name')
                    ->latest('scanned_at')
                    ->limit(5)
                    ->get(),
                'gates' => Gate::where('is_active', true)->get(['id', 'name']),
                'policePhone' => $this->settings->get('police_phone'),
            ]);
        }

        return view('public.permit', [
            'permit' => $permit,
            'plate' => $permit->vehicle->formatted_plate,
            'statusLabel' => $this->publicStatusLabel($permit),
            'statusColor' => $permit->isUsable() ? 'success' : 'slate',
            'emergencyPhone' => $permit->vehicle->student->primaryParent()?->phone,
            'showEmergencyPhone' => $this->settings->bool('public_show_emergency_phone', true),
            'policePhone' => $this->settings->get('police_phone'),
            'schoolPhone' => $this->settings->get('school_phone'),
            'schoolName' => $this->settings->get('school_name'),
            'isOwner' => $this->isOwner($request, $permit),
        ]);
    }

    /** Publik hanya melihat Aktif / Tidak Aktif / Ditangguhkan — tanpa alasan. */
    private function publicStatusLabel(VehiclePermit $permit): string
    {
        if ($permit->isUsable()) {
            return 'Aktif';
        }

        return $permit->status === PermitStatus::Suspended ? 'Ditangguhkan' : 'Tidak Aktif';
    }

    /** Siswa pemilik atau orang tuanya melihat kartu tambahan "Ini kendaraan Anda". */
    private function isOwner(Request $request, VehiclePermit $permit): bool
    {
        $user = $request->user();

        if (! $user) {
            return false;
        }

        return $user->can('view', $permit->vehicle->student);
    }

    private function record(?VehiclePermit $permit, ScanResultType $result, Request $request, string $token): void
    {
        ScanAttempt::create([
            'qr_token_raw' => substr($token, 0, 255),
            'permit_id' => $permit?->id,
            'result' => $result,
            'scanned_by' => $request->user()?->id,
            'ip' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 512),
            'created_at' => now(),
        ]);
    }
}
