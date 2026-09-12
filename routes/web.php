<?php

use App\Http\Controllers\MediaController;
use App\Http\Controllers\PermitPublicController;
use App\Http\Controllers\PushSubscriptionController;
use App\Http\Controllers\ReportExportController;
use App\Http\Controllers\ScanApiController;
use App\Http\Controllers\StickerController;
use App\Livewire\Admin;
use App\Livewire\Auth;
use App\Livewire\Ortu;
use App\Livewire\Petugas;
use App\Livewire\Siswa;
use App\Services\BackupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

/*
|--------------------------------------------------------------------------
| Halaman publik
|--------------------------------------------------------------------------
*/
Route::view('/', 'public.landing')->name('landing');
Route::view('/privasi', 'public.privacy')->name('privasi');
Route::view('/offline', 'public.offline')->name('offline');

// Satu URL, dua tampilan (tamu vs petugas) — lihat PermitPublicController.
Route::get('/q/{token}', [PermitPublicController::class, 'show'])
    ->middleware('throttle:60,1')
    ->name('permit.public');

/*
|--------------------------------------------------------------------------
| Autentikasi
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', Auth\Login::class)->name('login');
    Route::get('/lupa-password', Auth\ForgotPassword::class)->name('password.request');
    Route::get('/reset-password/{token}', Auth\ResetPassword::class)->name('password.reset');
});

Route::post('/logout', function (Request $request) {
    Illuminate\Support\Facades\Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('landing');
})->middleware('auth')->name('logout');

Route::get('/ganti-password', Auth\ChangePassword::class)->middleware('auth')->name('password.change');

/*
|--------------------------------------------------------------------------
| Berkas terlindungi & langganan push
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/media/{path}', [MediaController::class, 'show'])->where('path', '.*')->name('media');

    Route::post('/push/subscribe', [PushSubscriptionController::class, 'store'])->name('push.subscribe');
    Route::delete('/push/unsubscribe', [PushSubscriptionController::class, 'destroy'])->name('push.unsubscribe');

    // Endpoint scan dipakai scanner petugas dan tombol pada halaman /q/{token}.
    Route::post('/api/scan', [ScanApiController::class, 'store'])
        ->middleware('role:admin,petugas')
        ->name('api.scan');
});

/*
|--------------------------------------------------------------------------
| Admin
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'password.changed', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', Admin\Dashboard::class)->name('dashboard');

        // Siswa
        Route::get('/siswa', Admin\Students\Index::class)->name('students.index');
        Route::get('/siswa/tambah', Admin\Students\Form::class)->name('students.create');
        Route::get('/siswa/impor', Admin\Students\Import::class)->name('students.import');
        Route::get('/siswa/{student}', Admin\Students\Show::class)->name('students.show');
        Route::get('/siswa/{student}/ubah', Admin\Students\Form::class)->name('students.edit');

        // Orang tua
        Route::get('/orang-tua', Admin\Parents\Index::class)->name('parents.index');

        // Kendaraan
        Route::get('/kendaraan', Admin\Vehicles\Index::class)->name('vehicles.index');
        Route::get('/kendaraan/tambah', Admin\Vehicles\Form::class)->name('vehicles.create');
        Route::get('/kendaraan/{vehicle}/ubah', Admin\Vehicles\Form::class)->name('vehicles.edit');

        // Stiker
        Route::get('/stiker', Admin\Permits\Index::class)->name('permits.index');
        Route::get('/stiker/{permit}/pratinjau', [StickerController::class, 'preview'])->name('stiker.preview');
        Route::get('/stiker/{permit}/pdf', [StickerController::class, 'single'])->name('stiker.single');
        Route::post('/stiker/pdf-massal', [StickerController::class, 'batch'])->name('stiker.batch');

        // Kehadiran, pelanggaran, blokir, pengajuan data
        Route::get('/kehadiran', Admin\Attendance\Index::class)->name('attendance.index');
        Route::get('/pelanggaran', Admin\Violations\Index::class)->name('violations.index');
        Route::get('/pelanggaran/tambah', Admin\Violations\Index::class)->name('violations.create');
        Route::get('/blokir', Admin\Blocks\Index::class)->name('blocks.index');
        Route::get('/pengajuan-data', Admin\DataRequests\Index::class)->name('data-requests.index');

        // Laporan & ekspor
        Route::get('/laporan', Admin\Reports\Attendance::class)->name('reports.attendance');
        Route::get('/laporan/kehadiran/export', [ReportExportController::class, 'attendance'])->name('reports.attendance.export');

        // Pengguna, pengaturan, audit
        Route::get('/pengguna', Admin\Users\Index::class)->name('users.index');
        Route::get('/pengaturan', Admin\Settings::class)->name('settings');
        Route::get('/audit-log', Admin\AuditLogs\Index::class)->name('audit-logs.index');

        // Cadangan basis data
        Route::get('/cadangan', function () {
            $path = app(BackupService::class)->create();

            return response()->download(Storage::disk('local')->path($path));
        })->name('backup.download');
    });

/*
|--------------------------------------------------------------------------
| Petugas (admin juga boleh)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'password.changed', 'role:admin,petugas'])
    ->prefix('petugas')
    ->name('petugas.')
    ->group(function () {
        Route::get('/scan', Petugas\Scanner::class)->name('scan');
        Route::get('/hari-ini', Petugas\Today::class)->name('today');
        Route::get('/manual', Petugas\ManualScan::class)->name('manual');
        Route::get('/profil', Petugas\Profile::class)->name('profile');
    });

/*
|--------------------------------------------------------------------------
| Siswa
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'password.changed', 'role:siswa'])
    ->prefix('siswa')
    ->name('siswa.')
    ->group(function () {
        Route::get('/', Siswa\Home::class)->name('home');
        Route::get('/kartu', Siswa\DigitalCard::class)->name('card');
        Route::get('/riwayat', Siswa\History::class)->name('history');
        Route::get('/riwayat/pdf', [ReportExportController::class, 'monthly'])->name('history.pdf');
        Route::get('/profil', Siswa\Profile::class)->name('profile');
    });

/*
|--------------------------------------------------------------------------
| Orang tua
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'password.changed', 'role:orangtua'])
    ->prefix('ortu')
    ->name('ortu.')
    ->group(function () {
        Route::get('/', Ortu\Home::class)->name('home');
        Route::get('/anak/{student}', Ortu\Home::class)->name('child');
        Route::get('/riwayat', Ortu\History::class)->name('history');
        Route::get('/riwayat/pdf', [ReportExportController::class, 'monthly'])->name('history.pdf');
        Route::get('/notifikasi', Ortu\Notifications::class)->name('notifications');
        Route::get('/profil', Ortu\Profile::class)->name('profile');
    });
