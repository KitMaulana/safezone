<?php

use App\Enums\Role;
use App\Enums\ViolationCategory;
use App\Livewire\Admin\Parents\Index;
use App\Livewire\Admin\Settings;
use App\Livewire\Admin\Students\Form;
use App\Models\AcademicYear;
use App\Models\Gate;
use App\Models\ParentGuardian;
use App\Models\User;
use App\Models\Violation;
use App\Services\SettingService;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestHelpers;

/**
 * Menutup regresi pola updateOrCreate(['id' => null]) yang membuat seluruh
 * formulir "tambah baru" di panel admin gagal dengan MassAssignmentException.
 */
beforeEach(function () {
    $this->admin = TestHelpers::user(Role::Admin);
    $this->actingAs($this->admin);
});

it('menambah orang tua baru beserta akunnya', function () {
    $anak = TestHelpers::student();

    Livewire::test(Index::class)
        ->call('create')
        ->set('name', 'Siti Rahmawati')
        ->set('phone', '0812-3456-7899')
        ->set('relationship', 'ibu')
        ->set('studentIds', [$anak->id])
        ->set('primaryStudentId', $anak->id)
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('close-modal');

    $parent = ParentGuardian::where('name', 'Siti Rahmawati')->firstOrFail();

    // Nomor HP dinormalisasi dan dipakai sebagai username akun.
    expect($parent->phone)->toBe('081234567899')
        ->and($parent->user)->not->toBeNull()
        ->and($parent->user->username)->toBe('081234567899')
        ->and($parent->students->pluck('id')->all())->toBe([$anak->id])
        ->and((bool) $parent->students->first()->pivot->is_primary)->toBeTrue();
});

it('mengubah data orang tua yang sudah ada tanpa membuat data baru', function () {
    $anak = TestHelpers::student();
    $parent = TestHelpers::parentFor($anak, ['name' => 'Nama Lama']);

    Livewire::test(Index::class)
        ->call('edit', $parent->id)
        ->set('name', 'Nama Baru')
        ->call('save')
        ->assertHasNoErrors();

    expect(ParentGuardian::count())->toBe(1)
        ->and($parent->fresh()->name)->toBe('Nama Baru');
});

it('mencatat pelanggaran baru', function () {
    $siswa = TestHelpers::student();

    Livewire::test(App\Livewire\Admin\Violations\Index::class)
        ->call('create')
        ->set('studentId', $siswa->id)
        ->set('form_category', ViolationCategory::TidakPakaiHelm->value)
        ->set('description', 'Tidak memakai helm saat masuk gerbang.')
        ->set('occurred_at', now('Asia/Jakarta')->format('Y-m-d\TH:i'))
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('close-modal');

    $violation = Violation::firstOrFail();

    expect($violation->student_id)->toBe($siswa->id)
        ->and($violation->points)->toBe(ViolationCategory::TidakPakaiHelm->defaultPoints())
        ->and($violation->reported_by)->toBe($this->admin->id);
});

it('mengisi poin otomatis saat kategori pelanggaran diganti', function () {
    Livewire::test(App\Livewire\Admin\Violations\Index::class)
        ->call('create')
        ->set('form_category', ViolationCategory::TidakPunyaSim->value)
        ->assertSet('points', ViolationCategory::TidakPunyaSim->defaultPoints());
});

it('menambah tahun ajaran baru lalu mengaktifkannya', function () {
    TestHelpers::academicYear();

    Livewire::test(Settings::class)
        ->call('newYear')
        ->set('yearName', '2027/2028')
        ->set('yearCode', '2728')
        ->set('yearStart', '2027-07-13')
        ->set('yearEnd', '2028-06-30')
        ->call('saveYear')
        ->assertHasNoErrors()
        ->assertDispatched('close-modal');

    $baru = AcademicYear::where('code', '2728')->firstOrFail();

    Livewire::test(Settings::class)->call('activateYear', $baru->id);

    expect($baru->fresh()->is_active)->toBeTrue()
        // Hanya boleh ada satu tahun ajaran aktif.
        ->and(AcademicYear::where('is_active', true)->count())->toBe(1);
});

it('menambah dan mengubah gerbang', function () {
    Livewire::test(Settings::class)
        ->call('newGate')
        ->set('gateName', 'Gerbang Timur')
        ->call('saveGate')
        ->assertHasNoErrors();

    $gate = Gate::where('name', 'Gerbang Timur')->firstOrFail();

    Livewire::test(Settings::class)
        ->call('editGate', $gate->id)
        ->set('gateName', 'Gerbang Timur 2')
        ->call('saveGate');

    expect(Gate::count())->toBe(1)
        ->and($gate->fresh()->name)->toBe('Gerbang Timur 2');
});

it('menyimpan pengaturan sekolah dan jam operasional', function () {
    $component = Livewire::test(Settings::class)
        ->set('form.school_name', 'SMA Negeri 1 Ciruas')
        ->set('form.checkin_open_time', '06:00')
        ->set('form.checkout_min_time', '13:00')
        ->set('form.violation_block_threshold', '12')
        ->call('save')
        ->assertHasNoErrors();

    $settings = app(SettingService::class);
    $settings->flush();

    expect($settings->get('checkin_open_time'))->toBe('06:00')
        ->and($settings->int('violation_block_threshold'))->toBe(12);
});

it('menambah siswa baru beserta akun otomatisnya', function () {
    TestHelpers::academicYear();

    Livewire::test(Form::class)
        ->set('nisn', '0099887766')
        ->set('name', 'Siswa Baru')
        ->set('gender', 'P')
        ->set('birth_date', '2009-05-17')
        ->set('class_room', 'XI IPA 2')
        ->call('save')
        ->assertHasNoErrors();

    $akun = User::where('username', '0099887766')->firstOrFail();

    // Kata sandi awal = tanggal lahir ddmmyyyy.
    expect(Hash::check('17052009', $akun->password))->toBeTrue()
        ->and($akun->must_change_password)->toBeTrue()
        ->and($akun->role)->toBe(Role::Siswa);
});

it('menambah kendaraan baru dengan plat ternormalisasi', function () {
    $siswa = TestHelpers::student();

    Livewire::test(App\Livewire\Admin\Vehicles\Form::class)
        ->set('student_id', $siswa->id)
        ->set('plate_number', 'b 1234 xyz')
        ->set('brand', 'Honda')
        ->set('model', 'Beat')
        ->set('color', 'Hitam')
        ->call('save')
        ->assertHasNoErrors();

    expect($siswa->vehicles()->first()->plate_number)->toBe('B1234XYZ');
});

it('menolak nomor polisi yang formatnya salah', function () {
    $siswa = TestHelpers::student();

    Livewire::test(App\Livewire\Admin\Vehicles\Form::class)
        ->set('student_id', $siswa->id)
        ->set('plate_number', '1234ABCD')
        ->call('save')
        ->assertHasErrors('plate_number');
});
