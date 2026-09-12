<?php

namespace App\Support;

use App\Enums\Role;
use Illuminate\Support\Facades\Route;

/**
 * Peta menu per role (SPEC §11.3).
 * Item yang route-nya belum terdaftar otomatis disembunyikan.
 */
class Navigation
{
    /** Menu lengkap untuk sidebar admin. */
    public static function admin(): array
    {
        return self::filter([
            ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'icon' => 'home'],
            ['label' => 'Siswa', 'route' => 'admin.students.index', 'icon' => 'users'],
            ['label' => 'Kendaraan & Stiker', 'route' => 'admin.permits.index', 'icon' => 'qr-code'],
            ['label' => 'Kendaraan', 'route' => 'admin.vehicles.index', 'icon' => 'truck'],
            ['label' => 'Orang Tua', 'route' => 'admin.parents.index', 'icon' => 'user'],
            ['label' => 'Scan', 'route' => 'petugas.scan', 'icon' => 'camera'],
            ['label' => 'Kehadiran', 'route' => 'admin.attendance.index', 'icon' => 'clock'],
            ['label' => 'Pelanggaran', 'route' => 'admin.violations.index', 'icon' => 'flag'],
            ['label' => 'Blokir', 'route' => 'admin.blocks.index', 'icon' => 'ban'],
            ['label' => 'Pengajuan Data', 'route' => 'admin.data-requests.index', 'icon' => 'inbox'],
            ['label' => 'Laporan', 'route' => 'admin.reports.attendance', 'icon' => 'chart'],
            ['label' => 'Pengguna', 'route' => 'admin.users.index', 'icon' => 'key'],
            ['label' => 'Pengaturan', 'route' => 'admin.settings', 'icon' => 'cog'],
            ['label' => 'Audit Log', 'route' => 'admin.audit-logs.index', 'icon' => 'shield'],
        ]);
    }

    /** Menu ringkas admin untuk bottom nav di HP. */
    public static function adminMobile(): array
    {
        return self::filter([
            ['label' => 'Beranda', 'route' => 'admin.dashboard', 'icon' => 'home'],
            ['label' => 'Siswa', 'route' => 'admin.students.index', 'icon' => 'users'],
            ['label' => 'Scan', 'route' => 'petugas.scan', 'icon' => 'camera'],
            ['label' => 'Stiker', 'route' => 'admin.permits.index', 'icon' => 'qr-code'],
            ['label' => 'Lainnya', 'route' => 'admin.settings', 'icon' => 'menu'],
        ]);
    }

    public static function petugas(): array
    {
        return self::filter([
            ['label' => 'Scan', 'route' => 'petugas.scan', 'icon' => 'camera'],
            ['label' => 'Hari Ini', 'route' => 'petugas.today', 'icon' => 'clock'],
            ['label' => 'Manual', 'route' => 'petugas.manual', 'icon' => 'search'],
            ['label' => 'Profil', 'route' => 'petugas.profile', 'icon' => 'user'],
        ]);
    }

    public static function siswa(): array
    {
        return self::filter([
            ['label' => 'Beranda', 'route' => 'siswa.home', 'icon' => 'home'],
            ['label' => 'Kartu', 'route' => 'siswa.card', 'icon' => 'id-card'],
            ['label' => 'Riwayat', 'route' => 'siswa.history', 'icon' => 'clock'],
            ['label' => 'Profil', 'route' => 'siswa.profile', 'icon' => 'user'],
        ]);
    }

    public static function ortu(): array
    {
        return self::filter([
            ['label' => 'Beranda', 'route' => 'ortu.home', 'icon' => 'home'],
            ['label' => 'Riwayat', 'route' => 'ortu.history', 'icon' => 'clock'],
            ['label' => 'Notifikasi', 'route' => 'ortu.notifications', 'icon' => 'bell'],
            ['label' => 'Profil', 'route' => 'ortu.profile', 'icon' => 'user'],
        ]);
    }

    /** Menu bottom nav sesuai role pengguna yang sedang masuk. */
    public static function forRole(?Role $role): array
    {
        return match ($role) {
            Role::Admin => self::adminMobile(),
            Role::Petugas => self::petugas(),
            Role::Siswa => self::siswa(),
            Role::OrangTua => self::ortu(),
            default => [],
        };
    }

    private static function filter(array $items): array
    {
        return array_values(array_filter($items, fn ($item) => Route::has($item['route'])));
    }
}
