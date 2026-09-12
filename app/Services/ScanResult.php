<?php

namespace App\Services;

use App\Enums\ScanResultType;
use App\Models\AttendanceLog;
use App\Models\Vehicle;
use App\Models\VehiclePermit;
use Illuminate\Contracts\Support\Arrayable;

/** Hasil satu pemindaian, dikirim sebagai JSON ke layar petugas. */
class ScanResult implements Arrayable
{
    public function __construct(
        public readonly ScanResultType $status,
        public readonly string $message,
        public readonly ?AttendanceLog $log = null,
        public readonly ?VehiclePermit $permit = null,
        public readonly ?string $reason = null,
    ) {}

    public static function ok(AttendanceLog $log, string $message): self
    {
        return new self(ScanResultType::Ok, $message, $log, $log->permit);
    }

    public static function duplicate(AttendanceLog $last, string $message): self
    {
        return new self(ScanResultType::Duplicate, $message, $last, $last->permit);
    }

    public static function deny(ScanResultType $status, string $message, ?VehiclePermit $permit = null, ?string $reason = null): self
    {
        return new self($status, $message, null, $permit, $reason);
    }

    public function isOk(): bool
    {
        return $this->status === ScanResultType::Ok;
    }

    public function toArray(): array
    {
        $student = $this->permit?->vehicle?->student;
        $vehicle = $this->permit?->vehicle;

        return [
            'status' => $this->status->value,
            'color' => $this->status->color(),
            'label' => $this->status->label(),
            'message' => $this->message,
            'reason' => $this->reason,
            'type' => $this->log?->type,
            'type_label' => $this->log?->typeLabel(),
            'kind' => $this->log?->kind,
            'time' => $this->log?->scanned_at?->timezone('Asia/Jakarta')->format('H.i'),
            'is_early_leave' => (bool) $this->log?->is_early_leave,
            'student' => $student ? [
                'name' => $student->name,
                'class_room' => $student->class_room,
                'photo_url' => $student->photo_path ? route('media', ['path' => $student->photo_path]) : null,
            ] : null,
            'vehicle' => $vehicle ? [
                'plate' => Vehicle::formatPlate($vehicle->plate_number),
                'brand' => trim($vehicle->brand.' '.$vehicle->model),
            ] : null,
            'permit_number' => $this->permit?->permit_number,
        ];
    }
}
