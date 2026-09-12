<?php

namespace App\Enums;

enum Role: string
{
    case Admin = 'admin';
    case Petugas = 'petugas';
    case Siswa = 'siswa';
    case OrangTua = 'orangtua';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Admin',
            self::Petugas => 'Penegak Kedisiplinan Siswa',
            self::Siswa => 'Siswa',
            self::OrangTua => 'Orang Tua',
        };
    }

    /** Halaman tujuan setelah login. */
    public function homeRoute(): string
    {
        return match ($this) {
            self::Admin => 'admin.dashboard',
            self::Petugas => 'petugas.scan',
            self::Siswa => 'siswa.home',
            self::OrangTua => 'ortu.home',
        };
    }

    public function isStaff(): bool
    {
        return in_array($this, [self::Admin, self::Petugas], true);
    }
}
