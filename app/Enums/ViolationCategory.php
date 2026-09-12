<?php

namespace App\Enums;

enum ViolationCategory: string
{
    case TidakPakaiHelm = 'tidak_pakai_helm';
    case BerboncenganTiga = 'berboncengan_tiga';
    case KnalpotBising = 'knalpot_bising';
    case ParkirSembarangan = 'parkir_sembarangan';
    case TidakPunyaSim = 'tidak_punya_sim';
    case Kebut = 'kebut';
    case Lainnya = 'lainnya';

    public function label(): string
    {
        return match ($this) {
            self::TidakPakaiHelm => 'Tidak memakai helm',
            self::BerboncenganTiga => 'Berboncengan tiga',
            self::KnalpotBising => 'Knalpot bising',
            self::ParkirSembarangan => 'Parkir sembarangan',
            self::TidakPunyaSim => 'Tidak punya SIM',
            self::Kebut => 'Mengebut di area sekolah',
            self::Lainnya => 'Lainnya',
        };
    }

    public function defaultPoints(): int
    {
        return match ($this) {
            self::TidakPakaiHelm => 3,
            self::BerboncenganTiga => 3,
            self::KnalpotBising => 2,
            self::ParkirSembarangan => 1,
            self::TidakPunyaSim => 5,
            self::Kebut => 4,
            self::Lainnya => 1,
        };
    }

    /** Kategori ringan yang boleh dicatat petugas. */
    public static function lightCategories(): array
    {
        return [self::TidakPakaiHelm, self::ParkirSembarangan, self::KnalpotBising];
    }
}
