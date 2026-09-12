<?php

use App\Enums\Role;
use App\Livewire\Auth\Login;
use Livewire\Livewire;
use Tests\TestHelpers;

it('mengarahkan tiap role ke halaman yang benar setelah masuk', function (string $role, string $expected) {
    $user = TestHelpers::user(Role::from($role));

    Livewire::test(Login::class)
        ->set('username', $user->username)
        ->set('password', 'password')
        ->call('login')
        ->assertRedirect($expected);

    expect(auth()->id())->toBe($user->id);
})->with([
    ['admin', '/admin'],
    ['petugas', '/petugas/scan'],
    ['siswa', '/siswa'],
    ['orangtua', '/ortu'],
]);

it('menolak akun yang dinonaktifkan', function () {
    $user = TestHelpers::user(Role::Admin, ['is_active' => false]);

    Livewire::test(Login::class)
        ->set('username', $user->username)
        ->set('password', 'password')
        ->call('login')
        ->assertHasErrors('username');

    expect(auth()->check())->toBeFalse();
});

it('menolak kata sandi yang salah', function () {
    $user = TestHelpers::user(Role::Admin);

    Livewire::test(Login::class)
        ->set('username', $user->username)
        ->set('password', 'salah-sekali')
        ->call('login')
        ->assertHasErrors('username');
});

it('memaksa ganti kata sandi pada login pertama', function () {
    $user = TestHelpers::user(Role::Admin, ['must_change_password' => true]);

    Livewire::test(Login::class)
        ->set('username', $user->username)
        ->set('password', 'password')
        ->call('login')
        ->assertRedirect('/ganti-password');

    // Middleware mengalihkan akses halaman lain selama kata sandi belum diganti.
    $this->actingAs($user)->get('/admin')->assertRedirect('/ganti-password');
});

it('melarang siswa membuka panel admin', function () {
    $siswa = TestHelpers::user(Role::Siswa);

    $this->actingAs($siswa)->get('/admin')->assertForbidden();
});

it('melarang petugas membuka panel admin', function () {
    $petugas = TestHelpers::user(Role::Petugas);

    $this->actingAs($petugas)->get('/admin')->assertForbidden();
    $this->actingAs($petugas)->get('/petugas/scan')->assertOk();
});

it('mengarahkan tamu ke halaman masuk', function () {
    $this->get('/admin')->assertRedirect('/login');
});

it('menerima nomor HP orang tua dalam berbagai format', function () {
    $student = TestHelpers::student();
    $parent = TestHelpers::parentFor($student, ['phone' => '081234567890']);

    // Akun ortu: username = nomor HP, kata sandi awal = 6 digit terakhir.
    Livewire::test(Login::class)
        ->set('username', '+62 812-3456-7890')
        ->set('password', '567890')
        ->call('login')
        ->assertRedirect('/ganti-password');

    expect(auth()->user()->username)->toBe('081234567890');
});
