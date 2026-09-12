<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingService
{
    private const CACHE_KEY = 'ssz.settings';

    private const CACHE_TTL = 600; // 10 menit

    /** Nilai bawaan bila kunci belum ada di database. */
    public const DEFAULTS = [
        'school_name' => 'SMA Negeri 1 Ciruas',
        'school_address' => 'Jl. Raya Serang - Jakarta Km. 9, Ciruas, Kabupaten Serang, Banten',
        'school_phone' => '(0254) 281150',
        'police_phone' => '110',
        'headmaster_name' => '',
        'whatsapp_admin' => '',
        'late_alert_time' => '07:30',
        'checkin_open_time' => '05:30',
        'checkout_min_time' => '12:00',
        'auto_activate_on_print' => '1',
        'public_show_emergency_phone' => '1',
        'sticker_show_class' => '1',
        'sticker_print_back' => '0',
        'sticker_footer_text' => 'Satlantas Polres Serang',
        'violation_block_threshold' => '10',
    ];

    public function all(): array
    {
        $stored = Cache::remember(self::CACHE_KEY, self::CACHE_TTL, fn () => Setting::pluck('value', 'key')->all());

        return array_merge(self::DEFAULTS, $stored);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->all()[$key] ?? $default ?? self::DEFAULTS[$key] ?? null;
    }

    public function bool(string $key, bool $default = false): bool
    {
        $value = $this->get($key);

        return $value === null ? $default : filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    public function int(string $key, int $default = 0): int
    {
        $value = $this->get($key);

        return $value === null ? $default : (int) $value;
    }

    public function set(string $key, mixed $value): void
    {
        Setting::updateOrCreate(
            ['key' => $key],
            ['value' => is_bool($value) ? ($value ? '1' : '0') : (string) $value]
        );

        $this->flush();
    }

    public function setMany(array $values): void
    {
        foreach ($values as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => is_bool($value) ? ($value ? '1' : '0') : (string) $value]
            );
        }

        $this->flush();
    }

    public function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
