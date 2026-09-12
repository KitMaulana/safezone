# School Safe Zone — SMA Negeri 1 Ciruas

PWA Laravel 11 untuk pendataan siswa pembawa sepeda motor, penerbitan stiker QR **"KARTU MASUK SMAN 1 Ciruas"**, cek in/cek out oleh petugas gerbang, portal siswa & orang tua, serta notifikasi Web Push.

> *Aman berangkat, aman pulang, orang tua tenang.*
> Program kolaborasi SMA Negeri 1 Ciruas bersama Satlantas Polres Serang.

---

## 1. Fitur

### Admin
- Dashboard: kendaraan terdaftar, stiker aktif, cek in hari ini, belum cek out, ditolak, siswa diblokir, grafik 7 hari, 10 scan terakhir (auto refresh).
- CRUD siswa (+ foto, akun otomatis), impor CSV/XLSX dengan pratinjau & validasi per baris, unduh template.
- CRUD orang tua/wali, penautan banyak anak, penandaan kontak utama.
- CRUD kendaraan: validasi nomor polisi, kelengkapan syarat (JSON), foto STNK.
- Stiker: terbitkan, aktifkan, tangguhkan, cabut, perpanjang; cetak PDF satuan (90×55 mm & A4) dan massal (8 stiker/lembar A4 dengan garis potong).
- Blokir/buka blokir siswa (stiker aktif otomatis ditangguhkan), pelanggaran berpoin dengan saran blokir otomatis.
- Pengajuan perubahan data dari siswa: setujui (langsung diterapkan) / tolak dengan catatan.
- Laporan: kehadiran, kendaraan & stiker, pelanggaran, scan ditolak. Ekspor XLSX & PDF.
- Pengaturan: identitas sekolah, jam & aturan, privasi, stiker, tahun ajaran, gerbang, cadangan data.
- Manajemen pengguna: buat akun, reset kata sandi (ditampilkan sekali), aktif/nonaktif, paksa ganti sandi.
- Audit log seluruh aksi sensitif.

### Petugas (Penegak Kedisiplinan Siswa)
- Scan QR via kamera HP (`html5-qrcode`), pilihan gerbang & mode (Auto/In/Out), senter.
- Layar hasil penuh layar: **HIJAU** berhasil, **MERAH** diblokir, **KUNING** kedaluwarsa/dicabut/tidak dikenal, **ABU** duplikat — dengan getar & bunyi berbeda.
- **Antrean offline**: bila tidak ada sinyal, scan disimpan di IndexedDB dan dikirim ulang otomatis saat online (idempoten per `offline_id`).
- Daftar "Hari Ini" (jam masuk/keluar/status), pencatatan manual dengan alasan wajib, pencatatan pelanggaran ringan.

### Siswa
- Status stiker, status hari ini, ringkasan 7 hari, kartu digital ber-QR (cadangan bila stiker rusak).
- Riwayat 60 hari + ekspor PDF, ubah foto & nomor HP, ajukan perubahan alamat/data orang tua, ganti kata sandi.

### Orang Tua
- Pemilih anak, status hari ini (`wire:poll.15s`), status stiker.
- Aktifkan notifikasi Web Push, daftar notifikasi, riwayat per bulan + ringkasan PDF bulanan, kelola perangkat push.

### Publik
- `/q/{token}` — satu URL, dua tampilan: tamu hanya melihat **plat nomor + status + tombol kontak darurat**; admin/petugas melihat data lengkap + tombol cek in/out.
- Landing page, halaman privasi, halaman offline, halaman error bertema (403/404/419/500/503).

---

## 2. Stack

| Komponen | Pilihan |
|---|---|
| Framework | Laravel 11 (PHP 8.2+) |
| UI | Blade + Livewire 3 + Tailwind CSS 3 + Alpine.js (semua layout custom, tanpa starter kit) |
| Basis data | MySQL 8 / MariaDB 10.6+ |
| Queue & scheduler | driver `database`, dijalankan cron |
| QR | `simplesoftwareio/simple-qrcode` (SVG, error-correction H) |
| PDF | `barryvdh/laravel-dompdf` |
| Gambar | `intervention/image` v3 (GD) |
| Web Push | `laravel-notification-channels/webpush` (VAPID, tanpa Firebase) |
| Scanner | `html5-qrcode` (bundel npm, bukan CDN) |
| Ekspor | `maatwebsite/excel` |
| Uji | Pest 3 |

---

## 3. Instalasi lokal

Prasyarat: PHP 8.2+ dengan ekstensi `gd, zip, mbstring, exif, pdo_mysql, openssl, fileinfo, bcmath`; Composer; Node.js 18+; MySQL/MariaDB.

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
# buat basis data lebih dulu, mis. CREATE DATABASE ssz_ciruas;
php artisan migrate --seed
npm run build
php artisan serve
```

Pengembangan: `php artisan serve` + `npm run dev` (dua terminal), plus `php artisan queue:work` bila ingin menguji antrean.

### Catatan khusus XAMPP di Windows
- Aktifkan `extension=gd`, `extension=zip`, dan `extension=exif` di `C:\xampp\php\php.ini`.
- Untuk `php artisan webpush:vapid`, set variabel lingkungan `OPENSSL_CONF` ke `C:\xampp\php\extras\ssl\openssl.cnf` lebih dulu, jika tidak pembuatan kunci EC akan gagal.
- Laravel 11 sudah lewat masa dukungan keamanan, sehingga Composer versi baru memblokir pemasangannya. Berkas `composer.json` menyetel `policy.advisories.block: false` agar stack tetap sesuai ketentuan proyek. **Lihat bagian [Catatan keamanan](#8-catatan-keamanan).**

---

## 4. Akun demo

Tersedia setelah `php artisan migrate --seed` (DemoSeeder).

| Role | Username | Kata sandi |
|---|---|---|
| Admin | `admin` | `password` |
| Petugas | `petugas1` | `password` |
| Siswa | `0012345678` (NISN) | `01012009` (tanggal lahir ddmmyyyy) |
| Orang tua | `081234567890` (no. HP) | `567890` (6 digit terakhir) |

Akun siswa dan orang tua wajib mengganti kata sandi pada login pertama.

**Jangan jalankan DemoSeeder di produksi.** Di produksi cukup:

```bash
php artisan db:seed --class=SettingSeeder
php artisan db:seed --class=AcademicYearSeeder
php artisan db:seed --class=RoleUserSeeder
```

---

## 5. Pengujian

```bash
php artisan test
```

64 pengujian mencakup: login per role & pengalihan, akun nonaktif, paksa ganti kata sandi, otorisasi lintas role, alur scan lengkap (cek in → duplikat → cek out → masuk kembali → keluar lebih awal), penolakan blokir/kedaluwarsa/dicabut/token tak dikenal, batas jam gerbang, idempotensi antrean offline, penomoran stiker berurutan, PDF satuan & massal, tampilan publik vs aman `/q/{token}`, rate limit, otorisasi berkas media, notifikasi ke orang tua yang benar, serta perintah terjadwal.

---

## 6. Deploy ke cPanel (shared hosting)

1. **Bangun aset lokal**: `npm run build` — sertakan folder `public/build` saat unggah.
2. **Struktur folder**: unggah seluruh proyek ke `~/ssz-app/` (di luar `public_html`). Salin isi `public/` ke doc-root, lalu di `index.php` doc-root ubah:
   ```php
   require __DIR__.'/../ssz-app/vendor/autoload.php';
   $app = require_once __DIR__.'/../ssz-app/bootstrap/app.php';
   ```
   Alternatif yang lebih rapi: arahkan doc-root subdomain langsung ke `~/ssz-app/public`.
3. **PHP**: pilih 8.2/8.3 di *MultiPHP Manager*; aktifkan `gd, mbstring, intl, zip, pdo_mysql, fileinfo, bcmath, openssl, exif`. Naikkan `upload_max_filesize`/`post_max_size` ke 8M dan `memory_limit` ke 256M (untuk cetak massal DomPDF).
4. **Composer**: `composer install --no-dev --optimize-autoloader`. Bila Terminal tidak tersedia, unggah folder `vendor` hasil lokal (versi PHP harus sama).
5. **Basis data**: buat DB & user, isi `.env`, lalu `php artisan migrate --force` dan seeder produksi di atas.
6. **Cron (tiap menit, dua baris)**:
   ```
   * * * * * cd /home/USER/ssz-app && /usr/local/bin/php artisan schedule:run >> /dev/null 2>&1
   * * * * * cd /home/USER/ssz-app && /usr/local/bin/php artisan queue:work --stop-when-empty --max-time=50 --tries=3 >> /dev/null 2>&1
   ```
7. **Optimasi**: `php artisan config:cache route:cache view:cache event:cache` — ulangi setiap kali `.env` berubah.
8. **HTTPS wajib** — kamera dan Web Push hanya jalan di HTTPS. Aktifkan AutoSSL dan paksa redirect di `.htaccess`.
9. **VAPID**: `php artisan webpush:vapid --show`, salin ke `.env` produksi.
10. **Uji pasca-deploy**: login admin → ganti kata sandi → isi Pengaturan → cetak 1 stiker uji → scan dari HP petugas → aktifkan push di HP orang tua.

### Membuat kunci VAPID

```bash
php artisan webpush:vapid          # menulis langsung ke .env
php artisan webpush:vapid --show   # hanya menampilkan, untuk disalin manual
```

### Perintah terjadwal

| Perintah | Jadwal | Fungsi |
|---|---|---|
| `ssz:not-arrived-alerts` | tiap menit (hari sekolah) | mengirim pengingat pada jam `late_alert_time` |
| `ssz:expire-permits` | 00.10 harian | menandai stiker kedaluwarsa |
| `ssz:auto-unblock` | 00.20 harian | membuka blokir berjangka yang sudah lewat |
| `ssz:permit-expiring` | 07.00 harian | mengingatkan stiker yang akan habis (14 hari) |
| `ssz:daily-summary` | 15.30 hari sekolah | ringkasan harian ke admin |
| `ssz:backup` | Minggu 01.00 | cadangan basis data ke `storage/app/backups` |

---

## 7. Struktur penting

```
app/
  Enums/            Role, StudentStatus, PermitStatus, ScanResultType, ViolationCategory
  Services/         ScanService, PermitService, StickerPdfService, StudentImportService,
                    SettingService, AuditService, AccountService, PhotoService,
                    StudentBlockService, BackupService, ScanResult
  Livewire/         Auth, Admin, Petugas, Siswa, Ortu, Shared
  Notifications/    9 kelas notifikasi (database + Web Push)
  Policies/         StudentPolicy, VehiclePolicy, VehiclePermitPolicy
resources/views/
  layouts/          app (sidebar admin), mobile (petugas/siswa/ortu), public
  components/ui/    button, card, badge, input, select, textarea, modal, table,
                    stat, alert, empty-state, avatar, toast
  components/icon/  39 ikon Heroicons inline
  pdf/              _sticker-card, _sticker-styles, sticker-single, sticker-sheet,
                    sticker-preview, report-attendance, report-monthly
public/             manifest.webmanifest, sw.js, icons/, images/
```

---

## 8. Catatan keamanan

- **Laravel 11 sudah melewati masa dukungan keamanan.** Composer memblokir pemasangannya karena beberapa advisory yang tidak akan di-backport ke 11.x. Proyek ini menonaktifkan blokir tersebut (`policy.advisories.block: false` di `composer.json`) karena `CLAUDE.md` mengunci versi framework. Jalankan `composer audit` untuk melihat daftarnya, dan pertimbangkan naik ke Laravel 12 LTS sebelum aplikasi dipakai menangani data siswa sungguhan.
- QR hanya memuat token acak 32 karakter, **tanpa data pribadi**. Data bisa dicabut tanpa mencetak ulang stiker.
- Nama siswa **tidak** dicetak di stiker; kelas dapat dimatikan lewat Pengaturan.
- Foto & berkas siswa disajikan lewat `/media/{path}` dengan pemeriksaan policy — tidak pernah lewat `asset('storage/...')`.
- Endpoint `/q/*` dibatasi 60 permintaan/menit/IP; login dibatasi 5 percobaan/menit.
- Header keamanan (X-Frame-Options, Referrer-Policy, CSP dasar, Permissions-Policy) dipasang lewat middleware `SecurityHeaders`.
- Seluruh aksi sensitif tercatat di `audit_logs`; seluruh pemindaian (termasuk yang gagal) tercatat di `scan_attempts`.

---

## 9. Yang perlu dicoba manual di HP

Beberapa hal tidak bisa diuji otomatis dan sebaiknya dicoba langsung:

1. **Scan QR fisik** — cetak satu stiker, pastikan QR terbaca kamera HP pada ukuran cetak 28 mm dan membuka `/q/{token}`.
2. **Izin kamera** — halaman `/petugas/scan` memerlukan HTTPS (atau `localhost`).
3. **Antrean offline** — aktifkan mode pesawat, scan beberapa kali, lalu matikan mode pesawat; badge "N menunggu" harus kosong kembali.
4. **Web Push** — aktifkan notifikasi di HP orang tua, lalu scan anaknya; notifikasi harus muncul.
5. **Pasang ke layar utama** — banner "Pasang aplikasi" dan ikon PWA.
6. **Cetak massal** — pastikan potongan 90×55 mm pas saat dicetak di kertas A4 tanpa penskalaan ("Actual size", bukan "Fit to page").

---

## 10. FAQ

**Kenapa kartu masuk tidak memuat nama siswa?**
Stiker menempel pada kendaraan yang diparkir di ruang publik. Nama hanya muncul saat dipindai oleh petugas yang sudah masuk ke aplikasi.

**Orang tua tidak menerima notifikasi.**
Pastikan HTTPS aktif, kunci VAPID terisi di `.env`, orang tua sudah menekan "Aktifkan Notifikasi", dan cron `queue:work` berjalan.

**QR rusak/robek, bagaimana mencatat kehadiran?**
Gunakan menu **Manual** pada akun petugas (alasan wajib diisi dan tercatat), atau minta siswa membuka **Kartu Digital** di akunnya.

**Bisakah satu siswa punya lebih dari satu kendaraan?**
Bisa. Setiap kendaraan memiliki stikernya sendiri, tetapi hanya boleh ada satu stiker berstatus hidup (draf/dicetak/aktif/ditangguhkan) per kendaraan.

**Bagaimana cara ganti tahun ajaran?**
Pengaturan → Tahun ajaran → Tambah → Aktifkan. Nomor stiker baru mengikuti kode tahun ajaran aktif (mis. `SSZ-2728-0001`).
