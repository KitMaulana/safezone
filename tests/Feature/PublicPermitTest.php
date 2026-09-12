<?php

use App\Enums\Role;
use App\Models\ScanAttempt;
use Illuminate\Support\Str;
use Tests\TestHelpers;

beforeEach(function () {
    $this->student = TestHelpers::student(['name' => 'Ken Arya Pratama', 'nisn' => '0012345678']);
    $this->parent = TestHelpers::parentFor($this->student, ['phone' => '081234567890', 'name' => 'Siti Rahmawati']);
    $this->permit = TestHelpers::activePermit($this->student);
});

it('hanya menampilkan plat nomor dan tombol darurat kepada tamu', function () {
    $response = $this->get('/q/'.$this->permit->qr_token);

    $response->assertOk()
        ->assertSee('Kendaraan ini terdaftar')
        ->assertSee($this->permit->vehicle->formatted_plate)
        ->assertSee('Hubungi Kontak Darurat')
        ->assertDontSee('Ken Arya Pratama')
        ->assertDontSee('0012345678')
        ->assertDontSee('XII IPA 1');
});

it('menampilkan data lengkap kepada petugas yang sudah masuk', function () {
    $petugas = TestHelpers::user(Role::Petugas);

    $this->actingAs($petugas)
        ->get('/q/'.$this->permit->qr_token)
        ->assertOk()
        ->assertSee('Ken Arya Pratama')
        ->assertSee('0012345678')
        ->assertSee('XII IPA 1')
        ->assertSee('Siti Rahmawati');
});

it('menampilkan data lengkap kepada admin', function () {
    $admin = TestHelpers::user(Role::Admin);

    $this->actingAs($admin)
        ->get('/q/'.$this->permit->qr_token)
        ->assertOk()
        ->assertSee('Ken Arya Pratama');
});

it('tidak menampilkan data lengkap kepada siswa lain', function () {
    $lain = TestHelpers::student(['name' => 'Siswa Lain']);

    $this->actingAs($lain->user)
        ->get('/q/'.$this->permit->qr_token)
        ->assertOk()
        ->assertDontSee('Ken Arya Pratama')
        ->assertDontSee('0012345678');
});

it('menandai kepemilikan bagi siswa pemilik tanpa membuka data lengkap', function () {
    $this->actingAs($this->student->user)
        ->get('/q/'.$this->permit->qr_token)
        ->assertOk()
        ->assertSee('Ini kendaraan Anda')
        ->assertDontSee('0012345678');
});

it('menampilkan halaman 404 bertema untuk token tak dikenal dan mencatatnya', function () {
    $this->get('/q/'.Str::random(32))
        ->assertNotFound()
        ->assertSee('Stiker tidak dikenal');

    expect(ScanAttempt::where('result', 'not_found')->count())->toBe(1);
});

it('mencatat setiap akses halaman publik ke scan_attempts', function () {
    $this->get('/q/'.$this->permit->qr_token)->assertOk();

    expect(ScanAttempt::where('result', 'ok')->where('permit_id', $this->permit->id)->count())->toBe(1);
});

it('membatasi laju akses halaman publik', function () {
    for ($i = 0; $i < 60; $i++) {
        $this->get('/q/'.$this->permit->qr_token);
    }

    $this->get('/q/'.$this->permit->qr_token)->assertStatus(429);
});
