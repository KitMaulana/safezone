<?php

namespace App\Services;

use App\Enums\PermitStatus;
use App\Enums\ScanResultType;
use App\Enums\StudentStatus;
use App\Events\VehicleScanned;
use App\Models\AttendanceLog;
use App\Models\Gate;
use App\Models\ScanAttempt;
use App\Models\User;
use App\Models\VehiclePermit;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Logika cek in / cek out (SPEC §8).
 * Mode: auto | in | out | manual.
 */
class ScanService
{
    /** Jeda minimum antar-scan untuk siswa yang sama (detik). */
    public const DUPLICATE_WINDOW = 120;

    /** Batas mundur waktu klien untuk scan dari antrean offline (jam). */
    public const OFFLINE_MAX_BACKDATE_HOURS = 12;

    public function __construct(
        private readonly SettingService $settings,
    ) {}

    public function handle(
        string $token,
        User $officer,
        ?Gate $gate = null,
        string $mode = 'auto',
        ?string $note = null,
        ?Carbon $clientScannedAt = null,
        ?string $offlineId = null,
        ?string $deviceInfo = null,
    ): ScanResult {
        $permit = VehiclePermit::with(['vehicle.student.parents.user', 'vehicle.student.user'])
            ->where('qr_token', $token)
            ->first();

        if (! $permit) {
            $this->record(null, ScanResultType::NotFound, $officer, $token);

            return ScanResult::deny(ScanResultType::NotFound, 'Stiker tidak dikenal. Periksa kembali QR atau gunakan mode manual.');
        }

        $student = $permit->vehicle->student;

        if ($student->status === StudentStatus::Blocked) {
            $this->record($permit, ScanResultType::DeniedBlocked, $officer, $token);

            return ScanResult::deny(
                ScanResultType::DeniedBlocked,
                'DIBLOKIR — tidak dicatat sebagai cek in.',
                $permit,
                $student->blocked_reason
            );
        }

        if ($permit->status === PermitStatus::Revoked) {
            $this->record($permit, ScanResultType::DeniedRevoked, $officer, $token);

            return ScanResult::deny(
                ScanResultType::DeniedRevoked,
                'Stiker sudah dicabut.',
                $permit,
                $permit->revoked_reason
            );
        }

        if ($permit->status !== PermitStatus::Active || $permit->isExpired()) {
            $this->record($permit, ScanResultType::DeniedExpired, $officer, $token);

            $reason = $permit->isExpired()
                ? 'Masa berlaku habis pada '.$permit->expires_at->format('d M Y').'.'
                : 'Status stiker: '.$permit->status->label().'.';

            return ScanResult::deny(ScanResultType::DeniedExpired, 'Stiker tidak berlaku.', $permit, $reason);
        }

        // Idempoten untuk kiriman antrean offline.
        if ($offlineId) {
            $existing = AttendanceLog::where('offline_id', $offlineId)->first();

            if ($existing) {
                return ScanResult::duplicate($existing, 'Scan ini sudah tersinkron sebelumnya.');
            }
        }

        $scannedAt = $this->resolveScanTime($clientScannedAt);
        $local = $scannedAt->copy()->timezone('Asia/Jakarta');

        // Scan sebelum jam gerbang dibuka ditolak lembut.
        $openTime = $this->settings->get('checkin_open_time', '05:30');

        if ($mode !== 'manual' && $local->format('H:i') < $openTime) {
            $this->record($permit, ScanResultType::TooEarly, $officer, $token);

            return ScanResult::deny(
                ScanResultType::TooEarly,
                'Gerbang belum dibuka.',
                $permit,
                'Cek in baru dilayani mulai pukul '.str_replace(':', '.', $openTime).'.'
            );
        }

        $lastLog = AttendanceLog::where('student_id', $student->id)
            ->whereDate('scanned_at', $local->toDateString())
            ->latest('scanned_at')
            ->first();

        // Anti double-scan: scan ulang < 2 menit dianggap duplikat dan tidak dicatat.
        if ($lastLog && abs($lastLog->scanned_at->diffInSeconds($scannedAt)) < self::DUPLICATE_WINDOW) {
            $this->record($permit, ScanResultType::Duplicate, $officer, $token);

            return ScanResult::duplicate(
                $lastLog,
                'Sudah tercatat '.$lastLog->typeLabel().' pukul '.$lastLog->scanned_at->timezone('Asia/Jakarta')->format('H.i').'.'
            );
        }

        $type = match (true) {
            $mode === 'in' => 'in',
            $mode === 'out' => 'out',
            ! $lastLog || $lastLog->type === 'out' => 'in',
            default => 'out',
        };

        $kind = match (true) {
            $mode === 'manual' => 'manual',
            $type === 'in' && $lastLog?->type === 'out' => 're_entry',
            default => 'normal',
        };

        // Cek out sebelum jam minimum ditandai "keluar lebih awal".
        $isEarlyLeave = $type === 'out' && $local->format('H:i') < $this->settings->get('checkout_min_time', '12:00');

        $log = DB::transaction(function () use ($permit, $student, $type, $kind, $scannedAt, $officer, $gate, $note, $lastLog, $isEarlyLeave, $clientScannedAt, $offlineId, $deviceInfo) {
            return AttendanceLog::create([
                'student_id' => $student->id,
                'vehicle_id' => $permit->vehicle_id,
                'permit_id' => $permit->id,
                'type' => $type,
                'kind' => $kind,
                'scanned_at' => $scannedAt,
                'scanned_by' => $officer->id,
                'gate_id' => $gate?->id,
                'device_info' => $deviceInfo,
                'note' => $note,
                'pair_id' => $type === 'out' && $lastLog?->type === 'in' ? $lastLog->id : null,
                'is_early_leave' => $isEarlyLeave,
                'is_offline_sync' => (bool) $clientScannedAt,
                'offline_id' => $offlineId,
            ]);
        });

        $this->record($permit, ScanResultType::Ok, $officer, $token);

        $log->setRelation('student', $student);
        $log->setRelation('permit', $permit);
        $log->setRelation('gate', $gate);

        event(new VehicleScanned($log));

        $message = $type === 'in'
            ? ($kind === 're_entry' ? 'MASUK KEMBALI' : 'CEK IN').' '.$local->format('H.i')
            : ($isEarlyLeave ? 'CEK OUT (lebih awal) ' : 'CEK OUT ').$local->format('H.i');

        return ScanResult::ok($log, $message);
    }

    /** Waktu klien hanya diterima bila masuk akal (maks 12 jam mundur, tidak di masa depan). */
    private function resolveScanTime(?Carbon $clientScannedAt): Carbon
    {
        if (! $clientScannedAt) {
            return now();
        }

        if ($clientScannedAt->isFuture() || $clientScannedAt->lt(now()->subHours(self::OFFLINE_MAX_BACKDATE_HOURS))) {
            return now();
        }

        return $clientScannedAt;
    }

    private function record(?VehiclePermit $permit, ScanResultType $result, ?User $officer, string $token): void
    {
        ScanAttempt::create([
            'qr_token_raw' => substr($token, 0, 255),
            'permit_id' => $permit?->id,
            'result' => $result,
            'scanned_by' => $officer?->id,
            'ip' => request()->ip(),
            'user_agent' => substr((string) request()->userAgent(), 0, 512),
            'created_at' => now(),
        ]);
    }
}
