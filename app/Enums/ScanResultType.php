<?php

namespace App\Enums;

enum ScanResultType: string
{
    case Ok = 'ok';
    case Duplicate = 'duplicate';
    case DeniedBlocked = 'denied_blocked';
    case DeniedExpired = 'denied_expired';
    case DeniedRevoked = 'denied_revoked';
    case NotFound = 'not_found';
    case TooEarly = 'too_early';

    /** Warna layar hasil scan di HP petugas. */
    public function color(): string
    {
        return match ($this) {
            self::Ok => 'green',
            self::DeniedBlocked => 'red',
            self::DeniedExpired, self::DeniedRevoked, self::NotFound, self::TooEarly => 'yellow',
            self::Duplicate => 'gray',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Ok => 'Berhasil',
            self::Duplicate => 'Duplikat',
            self::DeniedBlocked => 'Diblokir',
            self::DeniedExpired => 'Kedaluwarsa',
            self::DeniedRevoked => 'Dicabut',
            self::NotFound => 'Tidak Dikenal',
            self::TooEarly => 'Belum Waktunya',
        };
    }
}
