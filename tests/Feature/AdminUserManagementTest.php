<?php

use App\Enums\Role;
use App\Livewire\Admin\Users\Index;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestHelpers;

beforeEach(function () {
    $this->admin = TestHelpers::user(Role::Admin, ['username' => 'admin']);
    $this->actingAs($this->admin);
});

it('menampilkan kata sandi acak setelah reset dan membuka modalnya', function () {
    $target = TestHelpers::user(Role::Petugas);
    $lama = $target->password;

    $component = Livewire::test(Index::class)
        ->call('resetPassword', $target->id)
        ->assertDispatched('open-modal');

    $sandi = $component->get('generatedPassword');

    // Kata sandi ditampilkan sekali: 10 karakter huruf + angka, tanpa simbol.
    expect($sandi)->toBeString()
        ->and(strlen($sandi))->toBe(10)
        ->and($sandi)->toMatch('/^[A-Za-z0-9]{10}$/');

    // Benar-benar tersimpan sebagai kata sandi baru akun tersebut.
    $target->refresh();

    expect(Hash::check($sandi, $target->password))->toBeTrue()
        ->and($target->password)->not->toBe($lama)
        ->and($target->must_change_password)->toBeTrue();

    // Dan tampil di layar.
    $component->assertSee($sandi);

    expect(AuditLog::where('action', 'user.reset_password')->count())->toBe(1);
});

it('mengisi formulir dan membuka modal saat tombol ubah ditekan', function () {
    $target = TestHelpers::user(Role::Petugas, ['name' => 'Petugas Gerbang 2']);

    Livewire::test(Index::class)
        ->call('edit', $target->id)
        ->assertDispatched('open-modal')
        ->assertSet('editingId', $target->id)
        ->assertSet('name', 'Petugas Gerbang 2')
        ->assertSet('username', $target->username)
        ->assertSet('form_role', 'petugas');
});

it('mengosongkan formulir saat tombol tambah akun ditekan', function () {
    Livewire::test(Index::class)
        ->call('edit', TestHelpers::user(Role::Petugas)->id)
        ->call('create')
        ->assertDispatched('open-modal')
        ->assertSet('editingId', null)
        ->assertSet('name', '')
        ->assertSet('form_role', 'petugas');
});

it('membuat akun baru dengan kata sandi awal yang ditampilkan sekali', function () {
    $component = Livewire::test(Index::class)
        ->call('create')
        ->set('name', 'Wakasek Kesiswaan')
        ->set('username', 'wakasek')
        ->set('form_role', 'admin')
        ->call('save');

    $sandi = $component->get('generatedPassword');
    $baru = User::where('username', 'wakasek')->firstOrFail();

    expect($sandi)->toMatch('/^[A-Za-z0-9]{10}$/')
        ->and(Hash::check($sandi, $baru->password))->toBeTrue()
        ->and($baru->must_change_password)->toBeTrue()
        ->and($baru->role)->toBe(Role::Admin);
});

it('tidak menampilkan kata sandi baru saat hanya mengubah data akun', function () {
    $target = TestHelpers::user(Role::Petugas);

    Livewire::test(Index::class)
        ->call('edit', $target->id)
        ->set('name', 'Nama Diperbarui')
        ->call('save')
        ->assertSet('generatedPassword', null)
        ->assertDispatched('close-modal');

    expect($target->fresh()->name)->toBe('Nama Diperbarui');
});

it('melarang admin menonaktifkan akunnya sendiri', function () {
    Livewire::test(Index::class)
        ->call('toggleActive', $this->admin->id)
        ->assertDispatched('toast');

    expect($this->admin->fresh()->is_active)->toBeTrue();
});

it('menonaktifkan dan mengaktifkan kembali akun lain', function () {
    $target = TestHelpers::user(Role::Petugas);

    Livewire::test(Index::class)->call('toggleActive', $target->id);
    expect($target->fresh()->is_active)->toBeFalse();

    Livewire::test(Index::class)->call('toggleActive', $target->id);
    expect($target->fresh()->is_active)->toBeTrue();
});
