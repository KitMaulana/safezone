# CLAUDE.md — School Safe Zone SMAN 1 Ciruas

## Tentang proyek
PWA Laravel 11 untuk pendataan siswa pembawa sepeda motor, stiker QR "KARTU MASUK SMAN 1 Ciruas",
cek in/cek out oleh petugas, portal siswa, dan notifikasi orang tua. Spesifikasi lengkap ada di `SPEC.md`
— SELALU baca bagian yang relevan di SPEC.md sebelum mengerjakan fitur. Jika ada konflik antara
instruksi pengguna dan SPEC.md, ikuti instruksi pengguna lalu perbarui SPEC.md.

## Stack (jangan diganti)
- Laravel 11, PHP 8.2, MySQL 8. Blade + Livewire 3 + Tailwind 3 + Alpine (bawaan Livewire).
- DILARANG memasang Filament, Breeze, Jetstream, Fortify, AdminLTE, atau template admin apa pun.
  Semua layout, komponen UI, dan auth dibuat manual (lihat SPEC §11).
- Paket yang boleh: livewire/livewire, simplesoftwareio/simple-qrcode, barryvdh/laravel-dompdf,
  intervention/image, laravel-notification-channels/webpush, maatwebsite/excel, html5-qrcode (npm),
  @fontsource/inter (npm).
- Target deploy: shared hosting cPanel → tidak ada Redis/Supervisor/WebSocket. Queue = database, dijalankan cron.

## Konvensi kode
- Bahasa UI, pesan validasi, komentar yang menghadap pengguna: Bahasa Indonesia. Nama variabel/kelas: Inggris.
- Zona waktu tampilan: Asia/Jakarta. Format tanggal `d M Y`, jam `H.i` (contoh 06.52).
- Enum PHP 8.1 untuk role/status (`app/Enums`). Logika bisnis di `app/Services`, bukan di komponen Livewire.
- Setiap komponen Livewire full-page memakai `#[Layout('layouts.app')]` / `layouts.mobile` sesuai role.
- Komponen UI reusable di `resources/views/components/ui/*` — gunakan ulang, jangan duplikasi markup.
- Semua aksi sensitif (terbit/cabut stiker, blokir, ubah data siswa, reset password) dicatat via `AuditService`.
- Foto & berkas disajikan lewat `MediaController` dengan otorisasi; jangan pernah memakai `asset('storage/...')` untuk data siswa.
- Validasi plat nomor: normalisasi uppercase tanpa spasi, regex `^[A-Z]{1,2}[0-9]{1,4}[A-Z]{0,3}$`.
- Tulis Feature Test (Pest) minimal untuk: login per role, alur scan (in/out/blocked/expired/duplicate),
  otorisasi akses data siswa, tampilan publik vs aman untuk `/q/{token}`.

## Perintah
- Setup: `composer install && npm install && cp .env.example .env && php artisan key:generate && php artisan migrate --seed && npm run build`
- Dev: `php artisan serve` + `npm run dev`
- Test: `php artisan test`
- Worker lokal: `php artisan queue:work`

## Akun demo (dari DemoSeeder)
admin / password · petugas1 / password · siswa: NISN 0012345678 / 01012009 · ortu: 081234567890 / 567890

## Cara kerja tiap sesi
1. Baca SPEC.md bagian yang diminta. 2. Buat/ubah migrasi & model dulu, lalu service, lalu Livewire/Blade.
3. Jalankan `php artisan migrate:fresh --seed` & `php artisan test` sebelum menyatakan selesai.
4. Ringkas apa yang dibuat + cara mencobanya di browser di akhir jawaban.
