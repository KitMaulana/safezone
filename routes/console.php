<?php

use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Penjadwalan
|--------------------------------------------------------------------------
| Di shared hosting, cron cPanel menjalankan `php artisan schedule:run`
| setiap menit (lihat README bagian Deploy).
*/

// Pengingat "belum cek in" — command memeriksa sendiri jam dari Pengaturan.
Schedule::command('ssz:not-arrived-alerts')->everyMinute()->weekdays();

// Stiker kedaluwarsa & blokir berjangka yang sudah lewat.
Schedule::command('ssz:expire-permits')->dailyAt('00:10');
Schedule::command('ssz:auto-unblock')->dailyAt('00:20');

// Pemberitahuan stiker akan kedaluwarsa (14 hari sebelumnya).
Schedule::command('ssz:permit-expiring')->dailyAt('07:00');

// Ringkasan harian untuk admin.
Schedule::command('ssz:daily-summary')->weekdays()->at('15:30');

// Cadangan basis data mingguan.
Schedule::command('ssz:backup')->weeklyOn(0, '01:00');
