<?php

use App\Enums\PermitStatus;
use App\Enums\StudentStatus;
use App\Models\Student;
use App\Models\Vehicle;
use App\Models\VehiclePermit;

it('menormalisasi plat nomor menjadi huruf besar tanpa spasi', function () {
    $vehicle = new Vehicle(['plate_number' => 'b 1234 xyz']);

    expect($vehicle->plate_number)->toBe('B1234XYZ');
});

it('menampilkan plat nomor dengan spasi', function () {
    $vehicle = new Vehicle(['plate_number' => 'a1234xy']);

    expect($vehicle->formatted_plate)->toBe('A 1234 XY');
});

it('menerima format plat nomor yang valid', function (string $plate) {
    expect(preg_match(Vehicle::PLATE_REGEX, Vehicle::normalizePlate($plate)))->toBe(1);
})->with(['A1234XY', 'B 1234 XYZ', 'a 12 b', 'AB1234CDE']);

it('menolak format plat nomor yang tidak valid', function (string $plate) {
    expect(preg_match(Vehicle::PLATE_REGEX, Vehicle::normalizePlate($plate)))->toBe(0);
})->with(['1234XY', 'ABCXYZ', 'A12345XY', 'A1234XYZW']);

it('menganggap stiker dapat dipakai hanya bila aktif, belum kedaluwarsa, dan siswa tidak diblokir', function () {
    $student = new Student(['status' => StudentStatus::Active]);
    $vehicle = new Vehicle;
    $vehicle->setRelation('student', $student);

    $permit = new VehiclePermit([
        'status' => PermitStatus::Active,
        'expires_at' => now()->addMonth(),
    ]);
    $permit->setRelation('vehicle', $vehicle);

    expect($permit->isUsable())->toBeTrue();

    // Kedaluwarsa
    $permit->expires_at = now()->subDay();
    expect($permit->isUsable())->toBeFalse();

    // Aktif lagi, tetapi siswa diblokir
    $permit->expires_at = now()->addMonth();
    $student->status = StudentStatus::Blocked;
    expect($permit->isUsable())->toBeFalse();

    // Siswa aktif, tetapi stiker ditangguhkan
    $student->status = StudentStatus::Active;
    $permit->status = PermitStatus::Suspended;
    expect($permit->isUsable())->toBeFalse();
});
