<?php

use App\Enums\PermitStatus;
use App\Enums\Role;
use App\Enums\ScanResultType;
use App\Enums\StudentStatus;
use App\Models\AttendanceLog;
use App\Models\ScanAttempt;
use App\Services\ScanService;
use Illuminate\Support\Str;
use Tests\TestHelpers;

beforeEach(function () {
    $this->officer = TestHelpers::user(Role::Petugas);
    $this->gate = TestHelpers::gate();
    $this->student = TestHelpers::student();
    $this->permit = TestHelpers::activePermit($this->student);
    $this->scans = app(ScanService::class);

    // Jam scan dibuat wajar agar tidak tertolak aturan checkin_open_time.
    $this->travelTo(now('Asia/Jakarta')->setTime(7, 0)->utc());
});

it('mencatat cek in pada scan pertama', function () {
    $result = $this->scans->handle($this->permit->qr_token, $this->officer, $this->gate);

    expect($result->status)->toBe(ScanResultType::Ok)
        ->and($result->log->type)->toBe('in')
        ->and($result->log->kind)->toBe('normal');

    $this->assertDatabaseCount('attendance_logs', 1);
});

it('menganggap scan ulang di bawah dua menit sebagai duplikat', function () {
    $this->scans->handle($this->permit->qr_token, $this->officer, $this->gate);

    $this->travel(30)->seconds();

    $result = $this->scans->handle($this->permit->qr_token, $this->officer, $this->gate);

    expect($result->status)->toBe(ScanResultType::Duplicate);
    $this->assertDatabaseCount('attendance_logs', 1);
});

it('mencatat cek out setelah lewat dua menit', function () {
    $this->scans->handle($this->permit->qr_token, $this->officer, $this->gate);

    $this->travelTo(now('Asia/Jakarta')->setTime(15, 10)->utc());

    $result = $this->scans->handle($this->permit->qr_token, $this->officer, $this->gate);

    expect($result->status)->toBe(ScanResultType::Ok)
        ->and($result->log->type)->toBe('out')
        ->and($result->log->is_early_leave)->toBeFalse()
        ->and($result->log->pair_id)->not->toBeNull();
});

it('menandai cek out sebelum jam minimum sebagai keluar lebih awal', function () {
    $this->scans->handle($this->permit->qr_token, $this->officer, $this->gate);

    $this->travelTo(now('Asia/Jakarta')->setTime(9, 40)->utc());

    $result = $this->scans->handle($this->permit->qr_token, $this->officer, $this->gate);

    expect($result->log->type)->toBe('out')
        ->and($result->log->is_early_leave)->toBeTrue();
});

it('mencatat masuk kembali setelah cek out', function () {
    $this->scans->handle($this->permit->qr_token, $this->officer, $this->gate);

    $this->travelTo(now('Asia/Jakarta')->setTime(9, 40)->utc());
    $this->scans->handle($this->permit->qr_token, $this->officer, $this->gate);

    $this->travelTo(now('Asia/Jakarta')->setTime(10, 30)->utc());
    $result = $this->scans->handle($this->permit->qr_token, $this->officer, $this->gate);

    expect($result->log->type)->toBe('in')
        ->and($result->log->kind)->toBe('re_entry');
});

it('menolak siswa yang diblokir tanpa mencatat kehadiran', function () {
    $this->student->update([
        'status' => StudentStatus::Blocked,
        'blocked_reason' => 'Tidak memakai helm berulang kali.',
    ]);

    $result = $this->scans->handle($this->permit->qr_token, $this->officer, $this->gate);

    expect($result->status)->toBe(ScanResultType::DeniedBlocked)
        ->and($result->reason)->toBe('Tidak memakai helm berulang kali.');

    $this->assertDatabaseCount('attendance_logs', 0);
    expect(ScanAttempt::where('result', ScanResultType::DeniedBlocked->value)->count())->toBe(1);
});

it('menolak stiker yang kedaluwarsa', function () {
    $this->permit->update(['expires_at' => now()->subDay()->toDateString()]);

    $result = $this->scans->handle($this->permit->qr_token, $this->officer, $this->gate);

    expect($result->status)->toBe(ScanResultType::DeniedExpired);
    $this->assertDatabaseCount('attendance_logs', 0);
});

it('menolak stiker yang dicabut', function () {
    $this->permit->update(['status' => PermitStatus::Revoked, 'revoked_reason' => 'Kendaraan dijual.']);

    $result = $this->scans->handle($this->permit->qr_token, $this->officer, $this->gate);

    expect($result->status)->toBe(ScanResultType::DeniedRevoked);
});

it('menolak token yang tidak dikenal dan mencatatnya', function () {
    $result = $this->scans->handle(Str::random(32), $this->officer, $this->gate);

    expect($result->status)->toBe(ScanResultType::NotFound);
    expect(ScanAttempt::where('result', ScanResultType::NotFound->value)->count())->toBe(1);
});

it('menolak scan sebelum gerbang dibuka', function () {
    $this->travelTo(now('Asia/Jakarta')->setTime(4, 30)->utc());

    $result = $this->scans->handle($this->permit->qr_token, $this->officer, $this->gate);

    expect($result->status)->toBe(ScanResultType::TooEarly);
    $this->assertDatabaseCount('attendance_logs', 0);
});

it('bersifat idempoten untuk kiriman antrean offline', function () {
    $offlineId = (string) Str::uuid();
    $clientTime = now()->subHour();

    $first = $this->scans->handle(
        token: $this->permit->qr_token,
        officer: $this->officer,
        gate: $this->gate,
        clientScannedAt: $clientTime,
        offlineId: $offlineId,
    );

    $second = $this->scans->handle(
        token: $this->permit->qr_token,
        officer: $this->officer,
        gate: $this->gate,
        clientScannedAt: $clientTime,
        offlineId: $offlineId,
    );

    expect($first->status)->toBe(ScanResultType::Ok)
        ->and($first->log->is_offline_sync)->toBeTrue()
        ->and($second->status)->toBe(ScanResultType::Duplicate);

    $this->assertDatabaseCount('attendance_logs', 1);
});

it('mengabaikan waktu klien yang mundur lebih dari 12 jam', function () {
    $result = $this->scans->handle(
        token: $this->permit->qr_token,
        officer: $this->officer,
        gate: $this->gate,
        clientScannedAt: now()->subDays(2),
        offlineId: (string) Str::uuid(),
    );

    expect($result->log->scanned_at->diffInMinutes(now()))->toBeLessThan(2);
});

it('mewajibkan alasan pada pencatatan manual lewat endpoint scan', function () {
    $this->actingAs($this->officer)
        ->postJson('/api/scan', [
            'token' => $this->permit->qr_token,
            'mode' => 'manual',
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors('note');
});

it('menerima pencatatan manual dengan alasan', function () {
    $this->actingAs($this->officer)
        ->postJson('/api/scan', [
            'token' => $this->permit->qr_token,
            'mode' => 'manual',
            'note' => 'QR pada stiker sobek.',
            'gate_id' => $this->gate->id,
        ])
        ->assertOk()
        ->assertJsonPath('status', 'ok');

    expect(AttendanceLog::first()->kind)->toBe('manual');
});

it('melarang siswa memanggil endpoint scan', function () {
    $siswa = TestHelpers::user(Role::Siswa);

    $this->actingAs($siswa)
        ->postJson('/api/scan', ['token' => $this->permit->qr_token])
        ->assertForbidden();
});
