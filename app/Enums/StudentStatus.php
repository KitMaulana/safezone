<?php

namespace App\Enums;

enum StudentStatus: string
{
    case Active = 'active';
    case Blocked = 'blocked';
    case Graduated = 'graduated';
    case Inactive = 'inactive';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Aktif',
            self::Blocked => 'Diblokir',
            self::Graduated => 'Lulus',
            self::Inactive => 'Nonaktif',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Active => 'success',
            self::Blocked => 'danger',
            self::Graduated => 'brand',
            self::Inactive => 'slate',
        };
    }
}
