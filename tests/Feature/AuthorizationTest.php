<?php

use App\Enums\Role;
use App\Enums\StudentStatus;
use App\Models\AttendanceLog;
use App\Notifications\StudentBlocked;
use App\Notifications\StudentCheckedIn;
use App\Notifications\StudentNotArrived;
use App\Services\ScanService;
use App\Services\StudentBlockService;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestHelpers;

beforeEach(function () {
    $this->student = TestHelpers::student(['photo_path' => 'students/uji.jpg']);
    $this->parent = TestHelpers::parentFor($this->student);
});

it('melarang tamu membuka foto siswa', function () {
    $this->get('/media/students/uji.jpg')->assertRedirect('/login');
});

it('melarang siswa lain membuka foto siswa', function () {
    Storage::fake('public');
    Storage::disk('public')->put('students/uji.jpg', 'konten');

    $lain = TestHelpers::student(['name' => 'Siswa Lain']);

    $this->actingAs($lain->user)->get('/media/students/uji.jpg')->assertForbidden();
});

it('mengizinkan petugas membuka foto siswa', function () {
    Storage::fake('public');
    Storage::disk('public')->put('students/uji.jpg', 'konten');

    $petugas = TestHelpers::user(Role::Petugas);

    $this->actingAs($petugas)->get('/media/students/uji.jpg')->assertOk();
});

it('mengizinkan orang tua membuka foto anaknya', function () {
    Storage::fake('public');
    Storage::disk('public')->put('students/uji.jpg', 'konten');

    $this->actingAs($this->parent->user)->get('/media/students/uji.jpg')->assertOk();
});

it('melarang siswa membuka detail siswa lain di panel admin', function () {
    $lain = TestHelpers::student();
    $lain->user->update(['must_change_password' => false]);

    $this->actingAs($lain->user->fresh())
        ->get('/admin/siswa/'.$this->student->id)
        ->assertForbidden();
});

it('mengirim notifikasi cek in hanya kepada orang tua siswa terkait', function () {
    Notification::fake();

    $lainStudent = TestHelpers::student();
    $lainParent = TestHelpers::parentFor($lainStudent);

    $permit = TestHelpers::activePermit($this->student);
    $petugas = TestHelpers::user(Role::Petugas);

    $this->travelTo(now('Asia/Jakarta')->setTime(7, 0)->utc());

    app(ScanService::class)->handle($permit->qr_token, $petugas, TestHelpers::gate());

    Notification::assertSentTo($this->parent->user, StudentCheckedIn::class);
    Notification::assertNotSentTo($lainParent->user, StudentCheckedIn::class);
});

it('menangguhkan stiker saat siswa diblokir dan memulihkannya saat blokir dibuka', function () {
    Notification::fake();

    $permit = TestHelpers::activePermit($this->student);
    $admin = TestHelpers::user(Role::Admin);
    $blocks = app(StudentBlockService::class);

    $blocks->block($this->student, 'Melanggar tata tertib berkendara.', 'pelanggaran', null, $admin);

    expect($this->student->fresh()->status)->toBe(StudentStatus::Blocked)
        ->and($permit->fresh()->status->value)->toBe('suspended');

    Notification::assertSentTo($this->parent->user, StudentBlocked::class);

    $blocks->unblock($this->student->fresh(), $admin);

    expect($this->student->fresh()->status)->toBe(StudentStatus::Active)
        ->and($permit->fresh()->status->value)->toBe('active');
});

it('membuka blokir berjangka lewat perintah artisan', function () {
    Notification::fake();

    $admin = TestHelpers::user(Role::Admin);
    TestHelpers::activePermit($this->student);

    app(StudentBlockService::class)->block(
        $this->student,
        'Blokir sementara.',
        'pelanggaran',
        now()->subDay()->toDateString(),
        $admin
    );

    $this->artisan('ssz:auto-unblock')->assertSuccessful();

    expect($this->student->fresh()->status)->toBe(StudentStatus::Active);
});

it('mengirim pengingat belum cek in kepada orang tua', function () {
    Notification::fake();

    // Pengingat hanya berjalan pada hari sekolah.
    $this->travelTo(now('Asia/Jakarta')->next('Monday')->setTime(7, 30)->utc());

    TestHelpers::activePermit($this->student);

    $this->artisan('ssz:not-arrived-alerts --force')->assertSuccessful();

    Notification::assertSentTo($this->parent->user, StudentNotArrived::class);
});

it('tidak mengirim pengingat bila anak sudah cek in', function () {
    Notification::fake();

    $this->travelTo(now('Asia/Jakarta')->next('Monday')->setTime(7, 30)->utc());

    $permit = TestHelpers::activePermit($this->student);

    AttendanceLog::create([
        'student_id' => $this->student->id,
        'vehicle_id' => $permit->vehicle_id,
        'permit_id' => $permit->id,
        'type' => 'in',
        'kind' => 'normal',
        'scanned_at' => now(),
    ]);

    $this->artisan('ssz:not-arrived-alerts --force')->assertSuccessful();

    Notification::assertNothingSent();
});
