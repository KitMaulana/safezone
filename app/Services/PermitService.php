<?php

namespace App\Services;

use App\Enums\PermitStatus;
use App\Models\AcademicYear;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehiclePermit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class PermitService
{
    public function __construct(
        private readonly AuditService $audit,
    ) {}

    /**
     * Terbitkan stiker baru untuk satu kendaraan.
     * Satu kendaraan hanya boleh punya satu permit berstatus draft/printed/active/suspended.
     */
    public function issue(Vehicle $vehicle, User $issuer, ?AcademicYear $year = null): VehiclePermit
    {
        $year ??= AcademicYear::current();

        if (! $year) {
            throw new RuntimeException('Tahun ajaran aktif belum diatur. Buka Pengaturan → Tahun Ajaran.');
        }

        if ($this->hasOpenPermit($vehicle)) {
            throw new RuntimeException('Kendaraan ini masih memiliki stiker aktif. Cabut atau perpanjang stiker lama terlebih dahulu.');
        }

        return DB::transaction(function () use ($vehicle, $issuer, $year) {
            $permit = VehiclePermit::create([
                'vehicle_id' => $vehicle->id,
                'permit_number' => $this->nextPermitNumber($year->code),
                'qr_token' => $this->generateToken(),
                'status' => PermitStatus::Draft,
                'issued_at' => now(),
                'expires_at' => $year->end_date,
                'issued_by' => $issuer->id,
            ]);

            $this->audit->log('permit.issue', $permit, null, [
                'permit_number' => $permit->permit_number,
                'plate_number' => $vehicle->plate_number,
            ]);

            return $permit;
        });
    }

    public function hasOpenPermit(Vehicle $vehicle): bool
    {
        return $vehicle->permits()
            ->whereIn('status', $this->openStatusValues())
            ->exists();
    }

    /** Nomor stiker berurutan per tahun ajaran: SSZ-2627-0001. */
    public function nextPermitNumber(string $yearCode): string
    {
        $prefix = "SSZ-{$yearCode}-";

        $last = VehiclePermit::where('permit_number', 'like', $prefix.'%')
            ->orderByDesc('permit_number')
            ->value('permit_number');

        $next = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;

        return $prefix.str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    public function generateToken(): string
    {
        do {
            $token = Str::random(32);
        } while (VehiclePermit::where('qr_token', $token)->exists());

        return $token;
    }

    public function markPrinted(VehiclePermit $permit, bool $autoActivate): VehiclePermit
    {
        $before = ['status' => $permit->status->value, 'print_count' => $permit->print_count];

        $permit->print_count++;
        $permit->printed_at = now();

        if ($permit->status === PermitStatus::Draft) {
            $permit->status = PermitStatus::Printed;
        }

        if ($autoActivate && in_array($permit->status, [PermitStatus::Draft, PermitStatus::Printed], true)) {
            $permit->status = PermitStatus::Active;
            $permit->activated_at = now();
        }

        $permit->save();

        $this->audit->log('permit.print', $permit, $before, [
            'status' => $permit->status->value,
            'print_count' => $permit->print_count,
        ]);

        return $permit;
    }

    public function activate(VehiclePermit $permit): VehiclePermit
    {
        $before = ['status' => $permit->status->value];

        $permit->update([
            'status' => PermitStatus::Active,
            'activated_at' => $permit->activated_at ?? now(),
        ]);

        $this->audit->log('permit.activate', $permit, $before, ['status' => PermitStatus::Active->value]);

        return $permit;
    }

    public function suspend(VehiclePermit $permit): VehiclePermit
    {
        $before = ['status' => $permit->status->value];

        $permit->update(['status' => PermitStatus::Suspended]);

        $this->audit->log('permit.suspend', $permit, $before, ['status' => PermitStatus::Suspended->value]);

        return $permit;
    }

    public function unsuspend(VehiclePermit $permit): VehiclePermit
    {
        if ($permit->status !== PermitStatus::Suspended) {
            return $permit;
        }

        $status = $permit->isExpired() ? PermitStatus::Expired : PermitStatus::Active;
        $before = ['status' => $permit->status->value];

        $permit->update(['status' => $status]);

        $this->audit->log('permit.unsuspend', $permit, $before, ['status' => $status->value]);

        return $permit;
    }

    public function revoke(VehiclePermit $permit, string $reason): VehiclePermit
    {
        $before = ['status' => $permit->status->value];

        $permit->update([
            'status' => PermitStatus::Revoked,
            'revoked_at' => now(),
            'revoked_reason' => $reason,
        ]);

        $this->audit->log('permit.revoke', $permit, $before, [
            'status' => PermitStatus::Revoked->value,
            'reason' => $reason,
        ]);

        return $permit;
    }

    /** Perpanjang: stiker lama menjadi expired, terbit stiker baru untuk tahun ajaran aktif. */
    public function renew(VehiclePermit $permit, User $issuer): VehiclePermit
    {
        return DB::transaction(function () use ($permit, $issuer) {
            $permit->update(['status' => PermitStatus::Expired]);

            $this->audit->log('permit.renew', $permit, ['status' => $permit->status->value], null);

            return $this->issue($permit->vehicle, $issuer);
        });
    }

    private function openStatusValues(): array
    {
        return array_map(fn (PermitStatus $status) => $status->value, PermitStatus::openStatuses());
    }
}
