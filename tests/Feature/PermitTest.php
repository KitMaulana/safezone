<?php

use App\Enums\PermitStatus;
use App\Enums\Role;
use App\Models\VehiclePermit;
use App\Services\PermitService;
use Tests\TestHelpers;

beforeEach(function () {
    TestHelpers::academicYear();
    $this->admin = TestHelpers::user(Role::Admin);
    $this->permits = app(PermitService::class);
});

it('menerbitkan nomor stiker berurutan per tahun ajaran', function () {
    $first = $this->permits->issue(TestHelpers::vehicle(TestHelpers::student()), $this->admin);
    $second = $this->permits->issue(TestHelpers::vehicle(TestHelpers::student()), $this->admin);

    expect($first->permit_number)->toBe('SSZ-2627-0001')
        ->and($second->permit_number)->toBe('SSZ-2627-0002');
});

it('membuat token QR acak sepanjang 32 karakter', function () {
    $permit = $this->permits->issue(TestHelpers::vehicle(TestHelpers::student()), $this->admin);

    expect(strlen($permit->qr_token))->toBe(32);
});

it('menolak penerbitan stiker kedua untuk kendaraan yang sama', function () {
    $vehicle = TestHelpers::vehicle(TestHelpers::student());

    $this->permits->issue($vehicle, $this->admin);

    expect(fn () => $this->permits->issue($vehicle, $this->admin))
        ->toThrow(RuntimeException::class);
});

it('mengembalikan PDF untuk cetak satuan', function () {
    $permit = TestHelpers::activePermit(TestHelpers::student());

    $this->actingAs($this->admin)
        ->get('/admin/stiker/'.$permit->id.'/pdf')
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf');
});

it('mengembalikan PDF untuk cetak massal', function () {
    $a = TestHelpers::activePermit(TestHelpers::student());
    $b = TestHelpers::activePermit(TestHelpers::student());

    $this->actingAs($this->admin)
        ->post('/admin/stiker/pdf-massal', ['permits' => [$a->id, $b->id]])
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf');
});

it('menambah penghitung cetak setelah stiker dicetak', function () {
    $permit = TestHelpers::activePermit(TestHelpers::student(), ['status' => PermitStatus::Draft]);

    $this->actingAs($this->admin)->get('/admin/stiker/'.$permit->id.'/pdf')->assertOk();

    $permit->refresh();

    expect($permit->print_count)->toBe(1)
        ->and($permit->printed_at)->not->toBeNull()
        // auto_activate_on_print bernilai true secara bawaan.
        ->and($permit->status)->toBe(PermitStatus::Active);
});

it('melarang petugas mencetak stiker', function () {
    $permit = TestHelpers::activePermit(TestHelpers::student());
    $petugas = TestHelpers::user(Role::Petugas);

    $this->actingAs($petugas)->get('/admin/stiker/'.$permit->id.'/pdf')->assertForbidden();
});

it('menandai stiker kedaluwarsa lewat perintah artisan', function () {
    $permit = TestHelpers::activePermit(TestHelpers::student(), [
        'expires_at' => now()->subDay()->toDateString(),
    ]);

    $this->artisan('ssz:expire-permits')->assertSuccessful();

    expect($permit->fresh()->status)->toBe(PermitStatus::Expired);
});

it('memperpanjang stiker dengan menerbitkan nomor baru', function () {
    $permit = $this->permits->issue(TestHelpers::vehicle(TestHelpers::student()), $this->admin);

    $new = $this->permits->renew($permit, $this->admin);

    expect($permit->fresh()->status)->toBe(PermitStatus::Expired)
        ->and($new->permit_number)->not->toBe($permit->permit_number)
        ->and(VehiclePermit::count())->toBe(2);
});
