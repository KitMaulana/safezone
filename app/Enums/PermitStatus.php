<?php

namespace App\Enums;

enum PermitStatus: string
{
    case Draft = 'draft';
    case Printed = 'printed';
    case Active = 'active';
    case Suspended = 'suspended';
    case Revoked = 'revoked';
    case Expired = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draf',
            self::Printed => 'Sudah Dicetak',
            self::Active => 'Aktif',
            self::Suspended => 'Ditangguhkan',
            self::Revoked => 'Dicabut',
            self::Expired => 'Kedaluwarsa',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Active => 'success',
            self::Draft, self::Printed => 'warning',
            self::Suspended => 'accent',
            self::Revoked, self::Expired => 'danger',
        };
    }

    /** Status yang masih "hidup": satu kendaraan hanya boleh punya satu. */
    public static function openStatuses(): array
    {
        return [self::Draft, self::Printed, self::Active, self::Suspended];
    }
}
