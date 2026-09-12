# SCHOOL SAFE ZONE — SMAN 1 CIRUAS
## Dokumen Spesifikasi & Prompt Vibecoding (Laravel PWA)

> **Cara pakai dokumen ini**
> 1. Buat folder proyek kosong, mis. `school-safe-zone/`.
> 2. Simpan file ini sebagai `SPEC.md` di root proyek, dan salin isi bagian **§13 CLAUDE.md** ke file `CLAUDE.md` di root proyek.
> 3. Salin dua logo ke `public/images/logo-sman1ciruas.png` dan `public/images/logo-polres-serang.png` (ada di folder `assets/` yang disertakan).
> 4. Buka folder di VS Code → jalankan Claude Code → tempel **Prompt Fase 0** dari §14, tunggu selesai, lanjut Fase 1, dst. Setiap fase dirancang selesai dalam satu sesi dan meninggalkan aplikasi dalam kondisi bisa dijalankan.
> 5. Bagian §15 berisi checklist uji terima per fase; §16 berisi panduan deploy ke cPanel.

---

## 1. Ringkasan Produk

**Nama aplikasi:** School Safe Zone SMAN 1 Ciruas (singkatan internal: **SSZ**)
**Tagline:** *Aman berangkat, aman pulang, orang tua tenang.*
**Bentuk:** Progressive Web App (PWA) berbasis Laravel — bisa di-"install" ke layar utama HP tanpa Play Store, berjalan di browser HP/laptop, dengan dukungan offline terbatas dan Web Push Notification.
**Mitra:** Satlantas Polres Serang (Kabupaten) — logo tampil di stiker & halaman publik sebagai bentuk kolaborasi program keselamatan berkendara pelajar.

### 1.1 Masalah yang diselesaikan
- Sekolah perlu tahu siapa saja siswa yang membawa sepeda motor, apakah sudah diizinkan (kelengkapan surat/persetujuan orang tua), dan kendaraan mana milik siapa.
- Petugas gerbang perlu cara cepat memverifikasi kendaraan siswa saat masuk/keluar tanpa mencatat manual.
- Orang tua ingin tahu anaknya sudah sampai sekolah dan kapan pulang.
- Jika terjadi kecelakaan/keadaan darurat di jalan, masyarakat/polisi perlu cara cepat menghubungi orang tua tanpa membocorkan data pribadi siswa ke publik.
- Sekolah perlu alat pendisiplinan: siswa yang melanggar bisa diblokir sehingga kendaraannya tidak bisa cek in.

### 1.2 Peran pengguna (4 role)

| Role | Kode `role` | Siapa | Ringkas fitur |
|---|---|---|---|
| Admin | `admin` | Guru / Wakasek Kesiswaan / operator | Kelola semua data, terbitkan & cetak stiker, blokir/buka blokir, laporan, pengaturan, kelola akun |
| Penegak Kedisiplinan Siswa | `petugas` | Petugas gerbang / OSIS-PKS / satpam | Scan QR untuk cek in & cek out, lihat daftar kendaraan hari ini, catat pelanggaran ringan |
| Siswa | `siswa` | Pemilik kendaraan | Lihat/perbarui profil, upload foto, lihat status stiker, riwayat cek in/out |
| Orang Tua | `orangtua` | Wali siswa | Lihat profil anak, notifikasi real-time cek in/out, riwayat, kontak darurat |

> Satu orang tua bisa punya lebih dari satu anak di sekolah (relasi many-to-many `parent_student`). Satu siswa bisa punya lebih dari satu kendaraan terdaftar, tetapi hanya satu **stiker aktif** per kendaraan.

---

## 2. Stack Teknis & Batasan

| Komponen | Pilihan | Catatan |
|---|---|---|
| Framework | **Laravel 11** (PHP 8.2+) | Kompatibel PHP 8.2/8.3 di cPanel |
| UI | **Blade + Livewire 3 + Tailwind CSS 3 + Alpine.js** | **Semua layout admin, petugas, siswa, ortu, dan halaman publik dibangun sendiri (custom).** JANGAN memakai Filament, Breeze, Jetstream, AdminLTE, atau starter kit lain. Auth (login/logout/reset password) juga dibuat manual dengan Livewire. |
| Ikon | Heroicons (inline SVG via Blade component) | Tanpa CDN agar bisa offline |
| Database | **MySQL 8 / MariaDB 10.6+** | Shared hosting cPanel |
| Queue | `database` driver | Dijalankan oleh **cron cPanel** tiap menit: `php artisan queue:work --stop-when-empty --max-time=50` |
| Scheduler | `php artisan schedule:run` via cron tiap menit | Untuk pengingat & pembersihan |
| QR Code | `bacon/bacon-qr-code` (via `simplesoftwareio/simple-qrcode`) | Output SVG/PNG, tanpa ekstensi Imagick (pakai GD) |
| PDF stiker | `barryvdh/laravel-dompdf` | Mendukung CSS sederhana, embed gambar base64 |
| Gambar | `intervention/image` v3 (driver GD) | Resize/kompres foto siswa ke max 600px, WebP/JPEG |
| Web Push | `laravel-notification-channels/webpush` | VAPID keys, tanpa Firebase |
| Scanner QR | `html5-qrcode` (bundled via npm, bukan CDN) | Pakai kamera HP di browser |
| Excel export | `maatwebsite/excel` atau `openspout/openspout` | Laporan kehadiran kendaraan |
| Build assets | Vite | Hasil build (`public/build`) di-commit atau di-upload manual ke hosting |
| Bahasa UI | **Bahasa Indonesia** seluruhnya | Termasuk pesan validasi (`lang/id`) |
| Zona waktu | `Asia/Jakarta` | Set di `config/app.php` |

### 2.1 Batasan shared hosting yang harus dihormati
- Tidak ada Redis, tidak ada Supervisor, tidak ada WebSocket/Reverb. Real-time di sisi orang tua memakai **Web Push** + **polling ringan** (Livewire `wire:poll.15s`) di halaman yang terbuka.
- `public_html` biasanya menunjuk ke folder `public/` Laravel lewat symlink atau memindahkan isi `public/` — dokumen §16 menjelaskan cara aman.
- Batas upload PHP kecil (biasanya 2–8 MB) → kompres foto di sisi klien (canvas) sebelum upload, dan validasi maksimal 2 MB di server.
- Jangan bergantung pada `php artisan storage:link` (kadang symlink dinonaktifkan) → sediakan route fallback `GET /media/{path}` yang membaca dari `storage/app/public` dengan otorisasi.

---

## 3. Matriks Hak Akses

| Aksi | admin | petugas | siswa | orangtua | publik |
|---|:-:|:-:|:-:|:-:|:-:|
| Login | ✔ | ✔ | ✔ | ✔ | – |
| Dashboard statistik sekolah | ✔ | ringkas | – | – | – |
| CRUD siswa, kendaraan, orang tua | ✔ | – | – | – | – |
| Terbitkan / cabut / perpanjang stiker | ✔ | – | – | – | – |
| Cetak stiker PDF (satuan & massal) | ✔ | – | – | – | – |
| Scan QR → **data lengkap** | ✔ | ✔ | – | – | – |
| Scan QR → **cek in / cek out** | ✔ | ✔ | – | – | – |
| Scan QR publik → **hanya plat nomor** | – | – | – | – | ✔ |
| Blokir / buka blokir siswa | ✔ | – | – | – | – |
| Catat pelanggaran | ✔ | ✔ (ringan) | – | – | – |
| Lihat & edit profil sendiri, upload foto | ✔ | ✔ | ✔ | ✔ | – |
| Ajukan perubahan data (nomor HP ortu, alamat) | – | – | ✔ (perlu persetujuan admin) | – | – |
| Lihat riwayat cek in/out anak | ✔ | hari ini | milik sendiri | anak sendiri | – |
| Terima notifikasi cek in/out | – | – | – | ✔ | – |
| Laporan & ekspor | ✔ | – | – | – | – |
| Pengaturan sekolah, jam, gerbang, tahun ajaran | ✔ | – | – | – | – |
| Kelola akun & reset password | ✔ | – | – | – | – |

Implementasi: middleware `role:admin`, `role:admin,petugas`, dst. + Policy untuk objek (`StudentPolicy`, `VehiclePolicy`) agar siswa/ortu hanya bisa membuka data miliknya.

---

## 4. Alur Utama (User Flows)

### 4.1 Pendaftaran kendaraan & penerbitan stiker (Admin)
1. Admin menambah/ mengimpor data siswa (CSV: NISN, NIS, nama, kelas, JK, tgl lahir, alamat, HP siswa, nama ortu, HP ortu). Akun siswa & ortu otomatis dibuat (lihat §5.10 aturan akun).
2. Admin membuka siswa → **Tambah Kendaraan**: plat nomor (nomor polisi), merek, tipe, warna, tahun, foto STNK (opsional), no. SIM (opsional), centang kelengkapan syarat (surat izin ortu, fotokopi STNK, dsb).
3. Klik **Terbitkan Stiker** → sistem membuat `vehicle_permit` dengan `qr_token` acak, nomor stiker berurutan `SSZ-2627-0001`, masa berlaku sampai akhir tahun ajaran.
4. Klik **Cetak Stiker** → PDF (satuan) atau pilih banyak → **Cetak Massal** (A4, 8 stiker/lembar).
5. Stiker ditempel di motor. Status permit menjadi `printed` lalu `active` setelah admin klik **Aktifkan** (atau otomatis aktif saat cetak — dapat diatur di Pengaturan).

### 4.2 Cek in / cek out (Petugas)
1. Petugas login di HP → menu **Scan** terbuka kamera belakang.
2. Arahkan ke QR di stiker → aplikasi memanggil `POST /api/scan` dengan token.
3. Server menentukan otomatis: belum ada log hari ini → **CEK IN**; sudah cek in & belum cek out → **CEK OUT**; sudah cek out → tampilkan "Sudah cek out pukul HH:MM" dengan tombol *Cek in lagi* (kasus keluar-masuk, mis. izin) yang mencatat sebagai `re_entry`.
4. Layar hasil besar & berwarna: **HIJAU** (berhasil, foto siswa, nama, kelas, plat, jam), **MERAH** (DIBLOKIR – tampilkan alasan, tidak dicatat sebagai cek in tetapi dicatat sebagai `denied` di `scan_attempts`), **KUNING** (stiker kedaluwarsa/dicabut/tidak dikenal).
5. Getar + bunyi singkat (Web Audio) untuk umpan balik; layar otomatis kembali ke scanner setelah 3 detik.
6. Setiap scan yang berhasil memicu event `VehicleScanned` → job notifikasi ke orang tua.
7. Mode **Manual**: petugas bisa cari plat/nama jika QR rusak, dengan catatan wajib alasan.

### 4.3 Scan oleh publik (masyarakat / polisi)
1. Siapa pun memindai QR dengan kamera HP biasa → membuka `https://domain/q/{token}`.
2. Karena **tidak login**, halaman menampilkan: logo sekolah & Polres, teks "Kendaraan ini terdaftar di School Safe Zone SMAN 1 Ciruas", **plat nomor** besar, status stiker (Aktif/Tidak aktif), dan tombol **Hubungi Kontak Darurat** yang memanggil nomor ortu tanpa menampilkan nama/NISN/alamat/foto. (Nomor darurat memang sudah tercetak di stiker, jadi tidak membocorkan data baru; tampilkan/sembunyikan nomor ini dapat diatur admin di Pengaturan → `public_show_emergency_phone`.)
3. Jika yang membuka halaman ini **sudah login sebagai admin/petugas**, halaman yang sama otomatis menampilkan **data lengkap** (foto, nama, NISN, kelas, ortu, alamat, riwayat 5 scan terakhir) — satu URL, dua tampilan berdasarkan sesi.
4. Halaman publik tidak boleh mengekspos ID internal, dan token tidak boleh bisa ditebak (lihat §6).

### 4.4 Siswa
- Login → dashboard: kartu status stiker (aktif/kedaluwarsa/diblokir), tombol "Lihat kartu digital" (QR yang sama dengan stiker, berguna jika stiker rusak), log cek in/out hari ini & riwayat 30 hari.
- Profil: upload/ganti foto (crop persegi di klien), ubah nomor HP sendiri, ajukan perubahan alamat/HP ortu (masuk antrian persetujuan admin).
- Ganti password.

### 4.5 Orang Tua
- Login → pilih anak (jika >1) → kartu: foto anak, kelas, plat, status hari ini ("Sudah di sekolah sejak 06.52", "Sudah pulang 15.10", "Belum cek in").
- Aktifkan notifikasi (tombol memicu `Notification.requestPermission()` → simpan subscription).
- Riwayat cek in/out (filter tanggal), unduh PDF ringkas bulanan.
- Pengingat otomatis (opsional, diatur admin): jika anak belum cek in sampai jam `late_alert_time` (default 07:30) pada hari sekolah, ortu menerima push "Ananda belum tercatat masuk".

### 4.6 Blokir (Admin)
- Admin membuka siswa → **Blokir** → isi alasan, jenis (`pelanggaran`, `administrasi`, `lainnya`), tanggal berakhir (opsional, kosong = sampai dibuka manual).
- Status siswa `blocked` → semua permit aktif miliknya di-tandai `suspended`; scan petugas menghasilkan layar MERAH.
- Siswa & ortu menerima notifikasi in-app + push berisi alasan (bisa dinonaktifkan).
- **Buka Blokir** mengembalikan permit ke `active`, dicatat di `audit_logs`.

---

## 5. Skema Database

Konvensi: nama tabel jamak snake_case, primary key `id` (bigint), `timestamps()`, `softDeletes()` pada tabel master. Semua tanggal disimpan UTC di DB dan ditampilkan WIB.

### 5.1 `users`
| Kolom | Tipe | Ket |
|---|---|---|
| id | bigint PK | |
| name | string | |
| username | string unique | NISN untuk siswa; nomor HP untuk ortu; bebas untuk admin/petugas |
| email | string nullable unique | opsional |
| phone | string nullable | |
| password | string | hash |
| role | enum(`admin`,`petugas`,`siswa`,`orangtua`) | |
| is_active | boolean default true | akun nonaktif tidak bisa login |
| must_change_password | boolean default true | paksa ganti password saat login pertama |
| last_login_at | timestamp nullable | |
| remember_token, timestamps, soft deletes | | |

### 5.2 `students`
| Kolom | Tipe |
|---|---|
| id, user_id (FK users, unique, nullable) | |
| nisn string(10) unique, nis string nullable | |
| name string, gender enum(`L`,`P`), birth_date date nullable | |
| class_room string (mis. `XII IPA 1`), academic_year_id FK | |
| address text nullable, phone string nullable | |
| photo_path string nullable | |
| status enum(`active`,`blocked`,`graduated`,`inactive`) default active | |
| blocked_reason text nullable, blocked_at timestamp nullable, blocked_until date nullable, blocked_by FK users nullable | |
| timestamps, soft deletes | |

### 5.3 `parents` (wali)
`id, user_id FK unique nullable, name, phone (unique, dipakai sebagai username), phone_alt nullable, relationship enum('ayah','ibu','wali'), address nullable, timestamps, softDeletes`

### 5.4 `parent_student` (pivot)
`parent_id FK, student_id FK, is_primary boolean default false` — kontak darurat di stiker = ortu `is_primary`.

### 5.5 `vehicles`
`id, student_id FK, plate_number string unique (disimpan uppercase tanpa spasi, mis. A1234XY; ditampilkan berspasi), brand, model, color, year smallint nullable, stnk_owner_name nullable, stnk_photo_path nullable, sim_number nullable, sim_type enum('C','C1','tidak_ada') default tidak_ada, requirements json nullable (checklist: surat_izin_ortu, fotokopi_stnk, fotokopi_sim, pernyataan_tata_tertib), notes text nullable, is_active boolean default true, timestamps, softDeletes`

### 5.6 `vehicle_permits` (stiker / kartu masuk)
| Kolom | Ket |
|---|---|
| id, vehicle_id FK | |
| permit_number string unique | format `SSZ-{kodeTA}-{0001}` mis. `SSZ-2627-0001` |
| qr_token string(32) unique | acak, tidak bisa ditebak; ini yang ada di QR |
| status enum(`draft`,`printed`,`active`,`suspended`,`revoked`,`expired`) | |
| issued_at, printed_at nullable, activated_at nullable, expires_at date | |
| revoked_at nullable, revoked_reason nullable | |
| issued_by FK users | |
| print_count unsigned tinyint default 0 | |
| timestamps | |

Aturan: satu kendaraan hanya boleh punya **satu** permit dengan status `draft/printed/active/suspended` pada satu waktu (unique partial via validasi service).

### 5.7 `attendance_logs` (cek in / cek out)
| Kolom | Ket |
|---|---|
| id, student_id FK, vehicle_id FK, permit_id FK | |
| type enum(`in`,`out`) | |
| kind enum(`normal`,`re_entry`,`manual`) default normal | |
| scanned_at timestamp | |
| scanned_by FK users (petugas/admin) | |
| gate_id FK gates nullable | |
| device_info string nullable (user-agent ringkas) | |
| note text nullable (wajib bila manual) | |
| pair_id bigint nullable | id log `in` pasangan untuk log `out` |
| is_early_leave boolean default false | `out` sebelum `checkout_min_time` |
| is_offline_sync boolean default false | dikirim dari antrean offline petugas |
| timestamps | |

Index: `(student_id, scanned_at)`, `(scanned_at)`.

### 5.8 `scan_attempts`
Mencatat **setiap** scan termasuk yang gagal: `id, qr_token_raw string, permit_id nullable, result enum('ok','denied_blocked','denied_expired','denied_revoked','not_found','duplicate'), scanned_by nullable, ip, user_agent, created_at`. Berguna untuk audit & deteksi penyalahgunaan.

### 5.9 `violations`
`id, student_id FK, reported_by FK users, category enum('tidak_pakai_helm','berboncengan_tiga','knalpot_bising','parkir_sembarangan','tidak_punya_sim','kebut','lainnya'), description text, points smallint default 0, occurred_at, evidence_photo_path nullable, timestamps` — akumulasi poin dapat memicu saran blokir otomatis (threshold di pengaturan).

### 5.10 Tabel pendukung
- `academic_years`: `id, name ('2026/2027'), code ('2627'), start_date, end_date, is_active`
- `gates`: `id, name ('Gerbang Utama'), is_active`
- `settings`: key-value (`school_name, school_address, school_phone, headmaster_name, whatsapp_admin, late_alert_time, checkin_open_time, checkout_min_time, auto_activate_on_print, public_show_emergency_phone, sticker_show_class, sticker_print_back, sticker_footer_text, violation_block_threshold`)
- `notifications` (bawaan Laravel, morph)
- `push_subscriptions` (dari paket webpush)
- `audit_logs`: `id, user_id, action, subject_type, subject_id, before json, after json, ip, created_at`
- `data_change_requests`: `id, student_id, field, old_value, new_value, status enum('pending','approved','rejected'), reviewed_by, reviewed_at, note`
- `sessions`, `password_reset_tokens`, `jobs`, `failed_jobs`, `cache` (bawaan)

**Aturan akun otomatis:**
- Siswa: `username = NISN`, password awal = tanggal lahir `ddmmyyyy` (atau `sman1ciruas` jika kosong), `must_change_password = true`.
- Ortu: `username = nomor HP` (dinormalisasi `08xxxxxxxx`), password awal = 6 digit terakhir nomor HP, `must_change_password = true`.
- Admin membuat akun petugas manual.

---

## 6. Desain QR Code & Keamanan Data

### 6.1 Isi QR
QR **hanya** berisi URL: `https://{APP_URL}/q/{qr_token}`.
- Tidak ada data siswa di dalam QR → tidak bisa dibaca offline oleh pihak lain, dan data bisa diubah/dicabut tanpa mencetak ulang.
- `qr_token` = 32 karakter acak (`Str::random(32)` base62). Rate-limit endpoint `/q/*` (60 req/menit/IP) untuk mencegah enumerasi.
- QR dibuat dengan error-correction level **H** agar tetap terbaca meski stiker tergores; ukuran cetak minimal 3×3 cm.

### 6.2 Dua tampilan, satu URL
```
GET /q/{token}
  ├─ tamu (guest)              → view public.permit  (plat nomor, status, tombol darurat*)
  └─ auth + role admin/petugas → view secure.permit  (data lengkap + tombol Cek In/Out)
```
Logika ada di `PermitPublicController@show`, memakai `Auth::check() && in_array(role, ['admin','petugas'])`. Siswa/ortu yang memindai QR-nya sendiri melihat tampilan publik + tautan "Masuk untuk melihat data lengkap" (hanya jika pemilik).

### 6.3 Data yang tampil
| Field | Publik | Admin/Petugas |
|---|:-:|:-:|
| Plat nomor | ✔ | ✔ |
| Status stiker (Aktif/Tidak aktif) & nomor stiker | ✔ | ✔ |
| Tombol "Hubungi kontak darurat" (tel: link, nomor **tidak** ditulis di layar kecuali setting mengizinkan) | ✔* | ✔ |
| Foto siswa | – | ✔ |
| Nama, NISN, kelas | – | ✔ |
| Nama & HP orang tua | – | ✔ |
| Alamat | – | ✔ |
| Merek/warna motor, SIM | – | ✔ |
| Status blokir + alasan | – | ✔ |
| 5 log scan terakhir | – | ✔ |

### 6.4 Keamanan lain
- Semua route scan memakai `auth` + `role` middleware + CSRF (Livewire) / Sanctum token (jika API).
- Foto siswa disimpan di `storage/app/public/students/{id}.jpg` dan disajikan lewat route `media` yang memeriksa otorisasi — **tidak** bisa diakses publik dengan URL tebakan.
- Password di-hash bcrypt; login dibatasi 5 percobaan/menit (throttle).
- `audit_logs` mencatat: terbit/cabut stiker, blokir/buka blokir, ubah data siswa, reset password, cetak.
- Header keamanan: `X-Frame-Options: DENY`, `Referrer-Policy: same-origin`, CSP dasar (self + data: untuk gambar).
- Backup: perintah artisan `ssz:backup` mengekspor dump SQL ke `storage/app/backups` (dipicu cron mingguan).

---

## 7. Desain Stiker "KARTU MASUK SMAN 1 Ciruas"

### 7.1 Spesifikasi fisik
- Ukuran per stiker: **90 × 55 mm** (seukuran kartu nama, mudah dipotong & dilaminasi/di-print di stiker vinyl).
- Lembar massal: A4 portrait, **2 kolom × 4 baris = 8 stiker**, margin 10 mm, garis potong putus-putus tipis abu-abu.
- Cetak satuan: PDF berukuran 90×55 mm langsung (untuk printer stiker) **dan** versi A4 dengan 1 stiker di tengah.

### 7.2 Tata letak (landscape 90×55 mm)
```
┌──────────────────────────────────────────────────────┐
│ [Logo SMAN 1 Ciruas]   KARTU MASUK        [Logo Polres]│
│                        SMAN 1 CIRUAS                  │
│                School Safe Zone • TA 2026/2027        │
├────────────────────┬─────────────────────────────────┤
│                    │  NO. POLISI                      │
│    ┌──────────┐    │  A 1234 XY        (besar, tebal)  │
│    │          │    │                                  │
│    │  QR CODE │    │  No. Stiker : SSZ-2627-0001      │
│    │  (28mm)  │    │  Berlaku s.d.: 30 Jun 2027       │
│    │          │    │  Kelas      : XII IPA 1 *        │
│    └──────────┘    │                                  │
│  scan untuk verifikasi                                │
├────────────────────┴─────────────────────────────────┤
│ ⚠ Jika terjadi hal darurat pada pengemudi kendaraan   │
│   ini, hubungi: 0812-3456-7890 (Ibu Siti)            │
│   Sekolah: (0254) xxxxxx  •  Satlantas Polres Serang  │
└──────────────────────────────────────────────────────┘
```
\* Kelas opsional (bisa dimatikan di pengaturan karena bersifat identifikasi). Nama siswa **tidak** dicetak di stiker demi privasi — nama muncul hanya lewat scan oleh petugas.

### 7.3 Gaya visual
- Warna dasar: biru tua `#1D4ED8` (dari logo sekolah) untuk header; aksen kuning `#FACC15` (logo Polres) untuk strip "KARTU MASUK"; blok darurat latar merah muda `#FEE2E2` dengan teks merah `#B91C1C`.
- Font: DejaVu Sans (bawaan DomPDF, aman tanpa install font) — bold untuk plat nomor 20pt.
- Logo dimuat sebagai base64 dari `public/images/*.png` agar DomPDF tidak butuh akses HTTP.
- Bagian belakang (opsional, halaman 2): tata tertib berkendara singkat 5 poin + tanda tangan Kepala Sekolah & stempel.

### 7.4 Implementasi
- Service `StickerPdfService::single(VehiclePermit $permit): Response` dan `::batch(Collection $permits): Response`.
- Blade: `resources/views/pdf/sticker-single.blade.php`, `pdf/sticker-sheet.blade.php`, komponen `pdf/_sticker-card.blade.php` (dipakai keduanya).
- QR dirender sebagai SVG inline (`QrCode::format('svg')->errorCorrection('H')->size(300)`), disisipkan base64 `data:image/svg+xml`.
- Setelah generate, `print_count++`, `printed_at = now()`, status `draft → printed` (dan → `active` jika `auto_activate_on_print`).

---

## 8. Logika Cek In / Cek Out (ScanService)

```php
// app/Services/ScanService.php  (pseudo)
public function handle(string $token, User $officer, ?Gate $gate, string $mode = 'auto', ?string $note = null): ScanResult
{
    $permit = VehiclePermit::with('vehicle.student.primaryParent')->where('qr_token', $token)->first();
    if (!$permit)                                   return $this->deny('not_found');
    $student = $permit->vehicle->student;
    if ($student->status === 'blocked')             return $this->deny('denied_blocked', $permit, $student->blocked_reason);
    if ($permit->status === 'revoked')              return $this->deny('denied_revoked', $permit);
    if ($permit->status !== 'active' || $permit->expires_at->isPast())
                                                    return $this->deny('denied_expired', $permit);

    $today   = now('Asia/Jakarta')->toDateString();
    $lastLog = AttendanceLog::where('student_id', $student->id)->whereDate('scanned_at', $today)->latest('scanned_at')->first();

    // Anti double-scan: scan ulang < 2 menit dari log terakhir → duplicate (tidak dicatat)
    if ($lastLog && $lastLog->scanned_at->diffInSeconds(now()) < 120) return $this->duplicate($lastLog);

    $type = match(true) {
        $mode === 'in'  => 'in',
        $mode === 'out' => 'out',
        !$lastLog || $lastLog->type === 'out' => 'in',
        default => 'out',
    };
    $kind = ($type === 'in' && $lastLog?->type === 'out') ? 're_entry' : ($mode === 'manual' ? 'manual' : 'normal');

    $log = AttendanceLog::create([...]);           // pair_id diisi jika out
    ScanAttempt::create([... 'result' => 'ok']);
    event(new VehicleScanned($log));               // → NotifyParentOfScan (queued)
    return ScanResult::ok($log);
}
```

Aturan tambahan (dapat diatur di Pengaturan):
- `checkin_open_time` (default 05:30): scan sebelum jam ini ditolak lembut dengan pesan.
- `checkout_min_time` (default 12:00): scan `out` sebelum jam ini dianggap `re_entry`/izin keluar dan diberi label "Keluar lebih awal" — tetap dicatat, notifikasi ke ortu berbunyi berbeda.
- Hari libur (tabel `holidays` opsional, fase lanjutan) → scan tetap dicatat dengan label "Hari libur".

---

## 9. Notifikasi

### 9.1 Kanal
1. **In-app** (`database` channel) — tampil di lonceng notifikasi & halaman Notifikasi untuk ortu, siswa, admin.
2. **Web Push** (`WebPushChannel`) — ke semua `push_subscriptions` milik user ortu. Payload: judul, isi, ikon (logo sekolah), `url` tujuan (`/ortu/anak/{id}`), `tag` unik per event agar tidak menumpuk.

### 9.2 Jenis notifikasi
| Kelas | Penerima | Isi contoh |
|---|---|---|
| `StudentCheckedIn` | ortu | "✅ Ananda **Ken** sudah tiba di sekolah pukul 06.52 (Gerbang Utama)." |
| `StudentCheckedOut` | ortu | "🏠 Ananda **Ken** sudah cek out dari sekolah pukul 15.10." |
| `StudentEarlyLeave` | ortu | "⚠️ Ananda **Ken** keluar sekolah pukul 09.40 (lebih awal). Hubungi wali kelas bila tidak mengetahui." |
| `StudentNotArrived` | ortu | "⏰ Hingga pukul 07.30, kendaraan Ananda **Ken** belum tercatat masuk sekolah." (scheduler, hari sekolah saja, hanya bila siswa punya permit aktif dan tidak ada log `in` hari ini) |
| `StudentBlocked` / `StudentUnblocked` | siswa + ortu | "🚫 Kartu masuk kendaraan Anda ditangguhkan: {alasan}." |
| `PermitExpiring` | siswa + ortu | 14 hari sebelum kedaluwarsa |
| `DataChangeReviewed` | siswa | hasil pengajuan perubahan data |
| `DailySummaryAdmin` | admin | ringkasan 15.30: jumlah cek in, belum cek out, ditolak |

### 9.3 Implementasi Web Push di shared hosting
- Generate VAPID: `php artisan webpush:vapid` → simpan ke `.env`.
- Service worker `public/sw.js` menangani event `push` (tampilkan notifikasi) & `notificationclick` (buka `url`).
- Tombol "Aktifkan Notifikasi" di halaman ortu → `navigator.serviceWorker.ready` → `pushManager.subscribe({userVisibleOnly:true, applicationServerKey})` → `POST /push/subscribe`.
- Notifikasi dikirim lewat queue `database`; cron menjalankan worker tiap menit → keterlambatan maksimal ~60 detik. Bila ingin instan, `ShouldQueue` dapat dilepas untuk `StudentCheckedIn/Out` (kirim sinkron saat scan; pengiriman push ~200 ms).
- Fallback tampilan: halaman ortu memakai `wire:poll.15s` pada komponen status hari ini.

---

## 10. PWA

- `public/manifest.webmanifest`: `name` "School Safe Zone SMAN 1 Ciruas", `short_name` "SSZ Ciruas", `start_url` "/", `display` "standalone", `theme_color` "#1D4ED8", `background_color` "#ffffff", ikon 192/512 px (dibuat dari logo sekolah, plus `maskable`).
- `public/sw.js` (ditulis manual, bukan Workbox agar sederhana):
  - Precache: `/offline`, CSS/JS hasil build, logo, ikon.
  - Strategi: **network-first** untuk halaman HTML; **cache-first** untuk aset statis; **tidak** meng-cache request Livewire (`/livewire/*`) dan `/q/*`.
  - Halaman `/offline` menampilkan pesan "Tidak ada koneksi" + tombol coba lagi.
- Scanner petugas mendukung **antrean offline**: jika `POST /api/scan` gagal karena offline, simpan ke IndexedDB (`pendingScans`), tampilkan badge "N scan menunggu", kirim ulang otomatis saat online (`sync` event atau saat halaman dibuka). Server menerima `client_scanned_at` untuk scan antrean (dibatasi maksimal 12 jam mundur), menandai `is_offline_sync = true`, dan idempoten berdasarkan `offline_id` (UUID dari klien).
- Meta tag iOS: `apple-mobile-web-app-capable`, `apple-touch-icon`.
- Banner "Pasang aplikasi" kustom (menangkap `beforeinstallprompt`), muncul sekali per 7 hari.

---

## 11. Panduan UI/UX (custom, tanpa template)

### 11.1 Identitas visual
- Palet Tailwind (`tailwind.config.js` → `theme.extend.colors`):
  - `brand`: `50:#EFF6FF, 100:#DBEAFE, 500:#2563EB, 600:#1D4ED8, 700:#1E40AF, 900:#1E3A8A` (biru logo sekolah)
  - `accent`: `400:#FACC15, 500:#EAB308` (kuning Polres)
  - `danger`: `#DC2626`, `success`: `#16A34A`, `warning`: `#F59E0B`
- Font: `Inter` (self-hosted via `@fontsource/inter`, bukan Google Fonts CDN) fallback `system-ui`.
- Logo sekolah di kiri header; logo Polres tampil di halaman publik/stiker/login footer sebagai "Didukung oleh Satlantas Polres Serang".

### 11.2 Struktur layout (buat sendiri)
- `layouts/app.blade.php` — untuk admin: sidebar kiri (desktop) yang berubah jadi **bottom navigation** (mobile), topbar dengan lonceng notifikasi (Livewire `NotificationBell`), avatar, tombol logout.
- `layouts/mobile.blade.php` — untuk petugas/siswa/ortu: header sederhana + bottom nav 3–4 tab, semua tombol tinggi ≥ 44 px (ramah jempol).
- `layouts/public.blade.php` — halaman `/q/{token}`, login, offline: satu kolom tengah, tanpa navigasi.
- Komponen Blade reusable di `resources/views/components/`: `ui.button`, `ui.card`, `ui.badge`, `ui.input`, `ui.select`, `ui.modal`, `ui.table`, `ui.stat`, `ui.empty-state`, `ui.alert`, `ui.avatar`, `icon.*`.
- Dark mode: opsional (class strategy), default terang.

### 11.3 Halaman per role (peta menu)
**Admin** `/admin`: Dashboard · Siswa · Kendaraan & Stiker · Orang Tua · Scan · Kehadiran (log) · Pelanggaran & Blokir · Pengajuan Data · Laporan · Pengguna · Pengaturan · Audit Log
**Petugas** `/petugas`: Scan · Hari Ini (daftar sudah in/out, pencarian) · Manual · Profil
**Siswa** `/siswa`: Beranda · Kartu Digital · Riwayat · Profil
**Orang Tua** `/ortu`: Beranda (per anak) · Riwayat · Notifikasi · Profil
**Publik**: `/` (landing ringkas + tombol Masuk + info program), `/q/{token}`, `/login`, `/lupa-password`, `/offline`, `/privasi`

### 11.4 Dashboard admin (widget)
Stat cards: Kendaraan terdaftar · Stiker aktif · Cek in hari ini · Belum cek out · Ditolak (blokir) hari ini · Siswa diblokir. Grafik batang 7 hari (cek in per hari), tabel 10 scan terakhir (auto refresh 30 s), daftar stiker kedaluwarsa < 30 hari, pengajuan data menunggu.

---

## 12. Struktur Proyek & Routing

```
app/
  Enums/{Role, PermitStatus, StudentStatus, ScanResultType}.php
  Http/Controllers/{PermitPublicController, MediaController, StickerController, ScanApiController, PushSubscriptionController, ReportExportController}.php
  Http/Middleware/{EnsureRole, ForcePasswordChange}.php
  Livewire/
    Auth/{Login, ForgotPassword, ResetPassword, ChangePassword}.php
    Admin/{Dashboard, Students/*, Vehicles/*, Permits/*, Parents/*, Attendance/*, Violations/*, Blocks/*, DataRequests/*, Reports/*, Users/*, Settings/*, AuditLogs/*}.php
    Petugas/{Scanner, Today, ManualScan}.php
    Siswa/{Home, DigitalCard, History, Profile}.php
    Ortu/{Home, History, Notifications, Profile}.php
    Shared/{NotificationBell, PhotoUploader}.php
  Models/{User, Student, ParentGuardian, Vehicle, VehiclePermit, AttendanceLog, ScanAttempt, Violation, AcademicYear, Gate, Setting, AuditLog, DataChangeRequest}.php
  Services/{ScanService, PermitService, StickerPdfService, StudentImportService, SettingService, AuditService}.php
  Events/VehicleScanned.php  Listeners/NotifyParentOfScan.php
  Notifications/{StudentCheckedIn, StudentCheckedOut, StudentEarlyLeave, StudentNotArrived, StudentBlocked, StudentUnblocked, PermitExpiring, DataChangeReviewed, DailySummaryAdmin}.php
  Policies/{StudentPolicy, VehiclePolicy, VehiclePermitPolicy}.php
  Console/Commands/{SendNotArrivedAlerts, ExpirePermits, SszBackup}.php
database/migrations, seeders/{RoleUserSeeder, SettingSeeder, AcademicYearSeeder, DemoSeeder}
resources/views/{layouts, components, livewire, pdf, public, errors}
public/{manifest.webmanifest, sw.js, images/logo-sman1ciruas.png, images/logo-polres-serang.png, icons/*}
routes/web.php, routes/api.php, routes/console.php
lang/id/*.php
```

Rute inti:
```
GET  /                       landing
GET  /login  POST /login  POST /logout
GET  /q/{token}              PermitPublicController@show   (throttle:60,1)
GET  /media/{path}           MediaController@show          (auth)
GET  /offline

# admin (auth, role:admin, password.changed)
GET  /admin ... (Livewire full-page components)
GET  /admin/stiker/{permit}/pdf         StickerController@single
POST /admin/stiker/pdf-massal           StickerController@batch
GET  /admin/laporan/kehadiran/export    ReportExportController@attendance

# petugas (auth, role:admin,petugas)
GET  /petugas/scan, /petugas/hari-ini, /petugas/manual
POST /api/scan               ScanApiController@store (auth:web via session, CSRF)  → JSON ScanResult

# siswa / ortu
GET  /siswa/*, /ortu/*
POST /push/subscribe, DELETE /push/unsubscribe
```

---

## 13. Isi `CLAUDE.md` (salin ke root proyek)

````markdown
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
````

---

## 14. Prompt Bertahap untuk Claude Code (VS Code)

> Tempel satu prompt per sesi. Tunggu Claude Code selesai, coba di browser, baru lanjut. Jika ada error, tempel pesan errornya dan minta perbaikan sebelum pindah fase. Setiap prompt sengaja menyebut bagian SPEC yang relevan.

### Fase 0 — Inisialisasi proyek
```
Baca CLAUDE.md dan SPEC.md §1, §2, §12. Buat proyek Laravel 11 baru di folder ini (composer create-project, jika folder sudah berisi file, buat di subfolder sementara lalu pindahkan isinya tanpa menimpa CLAUDE.md, SPEC.md, dan public/images/*).
Lakukan:
1. Pasang paket: livewire/livewire ^3, simplesoftwareio/simple-qrcode, barryvdh/laravel-dompdf, intervention/image ^3, laravel-notification-channels/webpush, maatwebsite/excel, pestphp/pest (dev). npm: tailwindcss, postcss, autoprefixer, @tailwindcss/forms, html5-qrcode, @fontsource/inter.
2. Konfigurasi Tailwind sesuai SPEC §11.1 (palet brand/accent/danger/success/warning, font Inter self-hosted) dan Vite.
3. Set config/app.php: timezone Asia/Jakarta, locale id, faker_locale id_ID. Pasang lang/id (validation, auth, pagination, passwords) berbahasa Indonesia — tulis sendiri terjemahannya.
4. .env.example: DB mysql, QUEUE_CONNECTION=database, SESSION_DRIVER=database, CACHE_STORE=database, FILESYSTEM_DISK=local, APP_URL, VAPID_PUBLIC_KEY/VAPID_PRIVATE_KEY/VAPID_SUBJECT kosong.
5. Buat migrasi bawaan queue (jobs, failed_jobs), sessions, cache, notifications, push_subscriptions.
6. Buat Enums: Role, StudentStatus, PermitStatus, ScanResultType (app/Enums).
7. Buat komponen Blade UI dasar di resources/views/components/ui: button, card, badge, input, select, textarea, modal (Alpine), table, stat, alert, empty-state, avatar; dan komponen ikon Heroicons inline (minimal: home, users, qr-code, truck, bell, cog, document, logout, camera, check, x, exclamation, clock, search, plus, printer, shield).
8. Buat tiga layout custom: layouts/app (sidebar desktop + bottom nav mobile), layouts/mobile, layouts/public — SPEC §11.2. Jangan gunakan starter kit apa pun.
9. Landing page "/" sederhana: logo sekolah, nama aplikasi, tagline, tombol Masuk, footer "Didukung oleh Satlantas Polres Serang" dengan logo Polres (public/images/logo-polres-serang.png).
10. Pastikan `npm run build` dan `php artisan serve` berjalan tanpa error, halaman "/" tampil rapi di lebar 375px dan 1280px.
Akhiri dengan ringkasan struktur folder yang dibuat.
```

### Fase 1 — Skema database, model, seeder
```
Baca SPEC.md §5 dan §12. Buat semua migrasi, model Eloquent, relasi, cast, dan factory untuk:
users (dengan role, username, is_active, must_change_password), academic_years, students, parents (model ParentGuardian, tabel parents), parent_student, vehicles, vehicle_permits, attendance_logs, scan_attempts, violations, gates, settings, audit_logs, data_change_requests.
Ketentuan:
- Gunakan enum PHP dari app/Enums pada cast model.
- Student: relasi user, parents (belongsToMany + pivot is_primary), primaryParent() accessor, vehicles, activePermits, attendanceLogs; scope blocked()/active().
- Vehicle: mutator plate_number → uppercase tanpa spasi; accessor formatted_plate → "A 1234 XY".
- VehiclePermit: scope active(); method isUsable(): bool (status active & belum kedaluwarsa & siswa tidak blocked).
- Tambahkan kolom is_offline_sync boolean default false pada attendance_logs (SPEC §10).
- SettingService: get(key, default), set(key, value), cache 10 menit; SettingSeeder mengisi semua kunci di SPEC §5.10 dengan nilai default (school_name "SMA Negeri 1 Ciruas", late_alert_time 07:30, checkin_open_time 05:30, checkout_min_time 12:00, auto_activate_on_print true, public_show_emergency_phone true, violation_block_threshold 10, sticker_footer_text, whatsapp_admin).
- AcademicYearSeeder: 2026/2027 (kode 2627, 14 Jul 2026 – 30 Jun 2027, aktif). Gate seeder: "Gerbang Utama".
- RoleUserSeeder: admin/password, petugas1/password. DemoSeeder: 12 siswa kelas XII (nama Indonesia), 12 ortu (username = no HP), 8 kendaraan dengan permit aktif, 30 hari log kehadiran acak, 2 siswa diblokir, 3 pelanggaran. Sertakan siswa demo NISN 0012345678 password 01012009 dan ortu 081234567890 password 567890.
- AuditService::log(action, subject, before, after).
Jalankan php artisan migrate:fresh --seed dan pastikan sukses. Tulis Unit test untuk mutator plat & isUsable().
```

### Fase 2 — Autentikasi custom & middleware role
```
Baca SPEC.md §3, §5.10, §11. Buat autentikasi manual (tanpa Breeze/Fortify):
1. Livewire Auth\Login di /login: field "Username / NISN / No. HP" + password + ingat saya; throttle 5x/menit; setelah login arahkan sesuai role: admin→/admin, petugas→/petugas/scan, siswa→/siswa, orangtua→/ortu. Akun is_active=false ditolak dengan pesan.
2. Middleware EnsureRole (alias `role`) menerima daftar role; middleware ForcePasswordChange (alias `password.changed`) mengarahkan ke /ganti-password bila must_change_password=true.
3. Livewire Auth\ChangePassword (/ganti-password) dan Auth\ForgotPassword + ResetPassword (via email jika ada; jika tidak ada email tampilkan pesan "hubungi admin sekolah").
4. Logout POST.
5. Halaman placeholder untuk /admin, /petugas/scan, /siswa, /ortu memakai layout yang tepat, menampilkan nama user & tombol logout.
6. Policies: StudentPolicy (view: admin, petugas, siswa pemilik, ortu terkait), VehiclePolicy, VehiclePermitPolicy. Daftarkan di AuthServiceProvider/AppServiceProvider.
7. Halaman login menampilkan logo sekolah di atas dan logo Polres kecil di footer, responsif, dengan layout public.
8. Feature test Pest: login tiap role diarahkan ke halaman benar; user nonaktif ditolak; siswa tidak bisa akses /admin (403); ForcePasswordChange bekerja.
```

### Fase 3 — Modul Admin: Siswa, Orang Tua, Kendaraan (CRUD + import)
```
Baca SPEC.md §4.1, §5, §11.3, §11.4. Bangun panel admin custom (layouts/app dengan sidebar) berisi:
1. Admin\Dashboard: stat cards (kendaraan terdaftar, stiker aktif, cek in hari ini, belum cek out, ditolak hari ini, siswa diblokir), grafik batang 7 hari cek in (SVG sederhana atau Chart.js via npm), tabel 10 scan terakhir wire:poll.30s, daftar stiker kedaluwarsa <30 hari, jumlah pengajuan data menunggu.
2. Admin\Students\Index: tabel pencarian (nama/NISN/kelas), filter status & kelas, pagination, badge status; aksi lihat/edit/hapus (soft delete).
   Admin\Students\Form: create/edit lengkap termasuk upload foto (Livewire WithFileUploads, resize 600px via intervention, simpan storage/app/public/students). Saat create otomatis buat akun user siswa (username NISN, password ddmmyyyy dari tanggal lahir, must_change_password true).
   Admin\Students\Show: tab Profil, Orang Tua, Kendaraan & Stiker, Riwayat Kehadiran, Pelanggaran, Blokir.
3. Admin\Students\Import: unggah CSV/XLSX (kolom: nisn, nis, nama, jk, tgl_lahir(YYYY-MM-DD), kelas, alamat, hp_siswa, nama_ortu, hp_ortu, hubungan) → pratinjau 10 baris + validasi per baris → impor dengan StudentImportService (buat siswa, ortu (cari by hp), pivot is_primary, akun user keduanya). Sediakan tombol unduh template CSV.
4. Admin\Parents\Index/Form: kelola ortu, tautkan ke ≥1 siswa, tandai kontak utama; buat akun otomatis (username no HP, password 6 digit terakhir).
5. Admin\Vehicles\Form (di dalam Show siswa dan halaman terpisah Admin\Vehicles\Index): plat (validasi regex SPEC), merek, tipe, warna, tahun, nama pemilik STNK, foto STNK opsional, SIM, checklist kelengkapan (JSON: surat_izin_ortu, fotokopi_stnk, fotokopi_sim, pernyataan_tata_tertib).
6. MediaController + route /media/{path} dengan otorisasi (admin/petugas bebas; siswa/ortu hanya file miliknya).
7. Semua perubahan dicatat AuditService. Notifikasi flash sukses/gagal memakai komponen ui.alert (Alpine toast).
Uji manual: buat siswa baru, unggah foto, impor CSV 3 baris, tambah kendaraan.
```

### Fase 4 — Stiker: penerbitan, QR, PDF satuan & massal
```
Baca SPEC.md §6 dan §7 dengan teliti. Implementasikan:
1. PermitService: issue(Vehicle, User): VehiclePermit (generate qr_token Str::random(32) unik, permit_number berurutan per tahun ajaran format SSZ-{kode}-{0001}, expires_at = end_date tahun ajaran aktif, status draft); revoke(permit, reason); renew(permit) (buat permit baru, lama → expired); suspend/unsuspend(permit).
   Validasi: satu kendaraan hanya satu permit non-final (draft/printed/active/suspended).
2. StickerPdfService dengan DomPDF: single(permit) → PDF ukuran 90x55 mm + varian A4 tengah (parameter ?a4=1); batch(permits) → A4 8 stiker/lembar dengan garis potong. Tata letak persis SPEC §7.2 & §7.3: dua logo (base64 dari public/images), judul "KARTU MASUK" / "SMAN 1 CIRUAS", "School Safe Zone • TA {tahun}", QR SVG error-correction H berisi URL {APP_URL}/q/{qr_token}, plat nomor besar, No. Stiker, Berlaku s.d., kelas (jika setting sticker_show_class true), blok darurat merah muda: "Jika terjadi hal darurat pada pengemudi kendaraan ini, hubungi: {hp ortu utama} ({nama ortu})", baris sekolah & Satlantas Polres Serang. Halaman 2 opsional (setting sticker_print_back): tata tertib 5 poin + tanda tangan Kepala Sekolah.
   Setelah render: print_count++, printed_at, status draft→printed→(active jika auto_activate_on_print).
3. Admin\Permits\Index: daftar stiker dengan filter status/kelas, pencarian plat/nomor stiker, checkbox multi-pilih → tombol "Cetak Massal (PDF)" (POST /admin/stiker/pdf-massal), aksi per baris: Cetak, Aktifkan, Tangguhkan, Cabut (modal alasan), Perpanjang.
4. Di Admin\Students\Show tab Kendaraan & Stiker: tombol Terbitkan Stiker → langsung tampil tombol Cetak.
5. Pratinjau stiker HTML di layar (komponen Blade yang sama dengan PDF, dibungkus card) sebelum cetak.
6. Perintah artisan ssz:expire-permits (tandai expired yang lewat tanggal) dijadwalkan harian 00:10.
7. Test: penerbitan menghasilkan nomor berurutan; tidak bisa terbit dua permit aktif untuk satu kendaraan; PDF single & batch mengembalikan content-type application/pdf.
Tampilkan hasil PDF contoh dari DemoSeeder dan pastikan QR terbaca (uji dengan library decode atau instruksi manual).
```

### Fase 5 — Halaman publik /q/{token} (dua tampilan)
```
Baca SPEC.md §4.3, §6.2, §6.3. Buat PermitPublicController@show untuk GET /q/{token} (throttle 60/menit):
- Tamu → view public.permit (layouts/public): logo sekolah + logo Polres, teks "Kendaraan ini terdaftar di School Safe Zone SMAN 1 Ciruas", plat nomor besar (formatted), badge status (Aktif / Tidak Aktif / Ditangguhkan — jangan tampilkan alasan), nomor stiker, tombol besar "📞 Hubungi Kontak Darurat" (href tel:) — nomor ditampilkan sebagai teks hanya jika setting public_show_emergency_phone true; tombol "Hubungi Sekolah"; tautan kecil "Masuk sebagai petugas". TIDAK ADA nama, NISN, foto, alamat, kelas.
- Login role admin/petugas → view secure.permit (layouts/mobile): foto siswa (via /media), nama, NISN, kelas, status siswa & blokir + alasan, kendaraan (merek/warna/tahun/SIM), ortu utama & HP (tel:), alamat, status permit & masa berlaku, 5 log scan terakhir, dan tombol besar "Cek In / Cek Out sekarang" yang memanggil ScanService (Fase 6) — untuk sementara tampilkan tombol nonaktif dengan catatan "aktif setelah Fase 6".
- Login siswa pemilik / ortu terkait → tampilan publik + kartu kecil "Ini kendaraan Anda/anak Anda" + tautan ke dashboard.
- Token tidak ditemukan → halaman 404 kustom bertema (bukan error mentah) dan catat ke scan_attempts result not_found.
- Setiap akses dicatat ringan ke scan_attempts (result ok/not_found, ip, ua, scanned_by null).
- Meta tag og:title "Kendaraan terdaftar SSZ SMAN 1 Ciruas", tanpa data pribadi.
Test: tamu tidak melihat nama/NISN; admin melihat; siswa lain (bukan pemilik) tidak melihat data lengkap; throttle aktif.
```

### Fase 6 — Scanner petugas & ScanService (cek in / cek out)
```
Baca SPEC.md §4.2, §8, §10 (bagian antrean offline). Implementasikan:
1. ScanService::handle(token, officer, gate, mode='auto'|'in'|'out'|'manual', note=null): ScanResult DTO (status: ok|duplicate|denied_blocked|denied_expired|denied_revoked|not_found, type in/out, kind, log, student ringkas, message). Ikuti pseudo-kode SPEC §8 termasuk anti double-scan 120 detik, checkin_open_time, checkout_min_time → early_leave flag, pair_id untuk out.
2. Event VehicleScanned + listener NotifyParentOfScan (ShouldQueue) — untuk fase ini listener hanya log; notifikasi dibuat di Fase 8.
3. POST /api/scan (route web dengan CSRF, middleware auth + role:admin,petugas) menerima {token, mode, note, gate_id, client_scanned_at?, offline_id?} → JSON ScanResult. Bila client_scanned_at diisi dan offline_id belum pernah diproses, catat is_offline_sync=true dengan waktu klien (maks 12 jam mundur); idempoten berdasarkan offline_id.
4. Livewire Petugas\Scanner di /petugas/scan (layouts/mobile): integrasi html5-qrcode (kamera belakang, kotak scan 250px, torch toggle jika didukung), pilihan gerbang (dropdown dari gates, diingat di localStorage), mode Auto/In/Out. Hasil ditampilkan overlay penuh layar: HIJAU (foto, nama, kelas, plat, "CEK IN 06.52"), MERAH (DIBLOKIR + alasan + "Tidak dicatat"), KUNING (kedaluwarsa/dicabut/tidak dikenal), ABU (duplikat: "Sudah tercatat 06.51"). Getar (navigator.vibrate) + beep Web Audio berbeda per warna. Auto-tutup 3 detik, tombol "Scan lagi".
   Antrean offline: jika fetch gagal karena offline, simpan {offline_id uuid, token, mode, gate_id, client_scanned_at} di IndexedDB, tampilkan badge "N menunggu", kirim ulang saat online/event 'online' atau saat halaman dibuka; tampilkan hasil sinkron.
5. Livewire Petugas\Today: daftar hari ini (cari nama/plat/kelas), kolom jam masuk, jam keluar, status (Di sekolah / Sudah pulang / Ditolak), ringkasan angka di atas, wire:poll.20s.
6. Livewire Petugas\ManualScan: cari siswa by plat/nama/NISN → pilih → mode in/out → alasan wajib → ScanService mode manual.
7. Aktifkan tombol Cek In/Out di halaman secure.permit (Fase 5) memanggil endpoint yang sama.
8. Feature test alur: in→out→re_entry; blocked ditolak dan tercatat scan_attempts; expired ditolak; duplicate <120 detik; manual butuh note; offline_id idempoten.
```

### Fase 7 — Blokir, pelanggaran, pengajuan perubahan data
```
Baca SPEC.md §4.6, §5.2, §5.9, §5.10 (data_change_requests). Bangun:
1. Admin\Blocks: dari Students\Show tab Blokir → modal Blokir (alasan wajib, jenis, blocked_until opsional) → Student status blocked, semua permit active → suspended, audit log, kirim notifikasi StudentBlocked (buat kelas notifikasi database-only dulu; push ditambah Fase 8). Buka Blokir → kembalikan permit suspended → active (jika belum kedaluwarsa), StudentUnblocked. Halaman Admin\Blocks\Index: daftar siswa terblokir + tanggal berakhir; perintah ssz:auto-unblock harian membuka blokir yang blocked_until lewat.
2. Admin\Violations: CRUD pelanggaran (kategori enum SPEC §5.9, poin, tanggal, foto bukti opsional). Petugas hanya boleh menambah kategori ringan (helm, parkir, knalpot) dari halaman Petugas\Today (tombol "Catat pelanggaran" di baris siswa). Total poin tampil di Students\Show; bila ≥ violation_block_threshold tampilkan banner "Disarankan blokir" dengan tombol cepat.
3. Admin\DataRequests\Index: daftar pengajuan perubahan (dari siswa, Fase 9) dengan tombol Setujui (terapkan nilai ke students/parents) / Tolak (catatan) → DataChangeReviewed (database).
4. Admin\AuditLogs\Index: tabel audit dengan filter user/aksi/tanggal.
5. Test: blokir menonaktifkan scan; buka blokir memulihkan; threshold banner muncul.
```

### Fase 8 — Notifikasi in-app + Web Push, dan portal Orang Tua
```
Baca SPEC.md §4.5, §9, §10. Implementasikan:
1. Generate VAPID (php artisan webpush:vapid) → tulis ke .env & .env.example (kosong). Publish migration push_subscriptions jika belum.
2. Kelas notifikasi (database + WebPushChannel): StudentCheckedIn, StudentCheckedOut, StudentEarlyLeave, StudentNotArrived, StudentBlocked, StudentUnblocked, PermitExpiring, DataChangeReviewed, DailySummaryAdmin — teks Bahasa Indonesia sesuai contoh SPEC §9.2, payload push berisi title, body, icon (/icons/icon-192.png), badge, tag unik, data.url.
3. Listener NotifyParentOfScan: kirim ke semua ortu siswa (CheckedIn/CheckedOut/EarlyLeave sesuai flag). Jalankan sinkron (tanpa ShouldQueue) untuk CheckedIn/Out agar instan; yang lain ShouldQueue.
4. Command ssz:not-arrived-alerts dijadwalkan Senin–Jumat pukul late_alert_time (baca dari settings; scheduler tiap menit cek waktu) → kirim StudentNotArrived untuk siswa dengan permit aktif tanpa log in hari ini. Command ssz:permit-expiring harian. DailySummaryAdmin 15.30 hari sekolah.
5. public/sw.js: precache offline page/aset, network-first HTML, cache-first aset, handler push & notificationclick; manifest.webmanifest lengkap; ikon 192/512 + maskable dibuat dari public/images/logo-sman1ciruas.png (gunakan GD/intervention untuk membuat file ikon di public/icons). Registrasi SW di layout, banner "Pasang aplikasi" kustom.
6. Livewire Shared\NotificationBell (badge jumlah belum dibaca, dropdown 10 terbaru, tandai dibaca, wire:poll.30s) dipasang di semua layout.
7. Portal ortu (layouts/mobile): Ortu\Home — pemilih anak jika >1, kartu status hari ini ("Sudah di sekolah sejak 06.52" / "Sudah pulang 15.10" / "Belum cek in") wire:poll.15s, foto anak, kelas, plat & status stiker, tombol "Aktifkan Notifikasi" (alur subscribe → POST /push/subscribe) dengan status terpasang/tidak; Ortu\History — filter bulan, daftar per hari (masuk/keluar/durasi), unduh PDF bulanan (DomPDF sederhana); Ortu\Notifications — daftar semua notifikasi; Ortu\Profile — ubah HP alternatif, ganti password, kelola perangkat push (hapus subscription).
8. Test: scan in mengirim notifikasi database ke ortu terkait; ortu lain tidak menerima; endpoint subscribe menyimpan subscription untuk user login.
```

### Fase 9 — Portal Siswa
```
Baca SPEC.md §4.4. Bangun (layouts/mobile):
1. Siswa\Home: kartu status (Stiker aktif s.d. tanggal / Belum punya stiker / DIBLOKIR + alasan + tanggal berakhir), status hari ini (jam in/out), ringkasan 7 hari, pengumuman dari admin (opsional: tabel announcements sederhana — buat jika mudah).
2. Siswa\DigitalCard: tampilan kartu masuk digital identik dengan stiker (komponen Blade sama, QR sama) + kecerahan maksimum tip; tombol "Unduh gambar" (render ke PNG via html2canvas dari npm, opsional).
3. Siswa\History: riwayat 60 hari dengan filter, ekspor PDF.
4. Siswa\Profile: foto (crop persegi di klien dengan canvas → kompres ≤ 600px sebelum upload), ubah HP sendiri langsung; ubah alamat / HP ortu / nama ortu → membuat data_change_requests status pending (tampilkan status pengajuan); ganti password.
5. Siswa tidak bisa melihat siswa lain (policy) — test.
```

### Fase 10 — Laporan, ekspor, pengaturan, manajemen pengguna
```
Baca SPEC.md §3, §5.10, §11.3. Bangun:
1. Admin\Reports\Attendance: filter rentang tanggal, kelas, siswa, gerbang; tabel + ringkasan (hadir dengan kendaraan, rata-rata jam masuk, keluar lebih awal, ditolak); ekspor XLSX (maatwebsite/excel) & PDF; grafik tren.
   Admin\Reports\Vehicles: rekap kendaraan per kelas, stiker per status; Admin\Reports\Violations: rekap pelanggaran per kategori/kelas; Admin\Reports\ScanAttempts: scan gagal/ditolak per hari (deteksi penyalahgunaan).
2. Admin\Settings: form terkelompok — Identitas sekolah (nama, alamat, telepon, WA admin, nama kepala sekolah untuk stiker belakang), Jam & aturan (checkin_open_time, checkout_min_time, late_alert_time, auto_activate_on_print, violation_block_threshold), Privasi (public_show_emergency_phone, sticker_show_class), Stiker (sticker_footer_text, sticker_print_back), Tahun ajaran (CRUD + aktifkan), Gerbang (CRUD).
3. Admin\Users: daftar akun per role, buat akun petugas/admin, reset password (generate acak + tampilkan sekali), aktif/nonaktif, paksa ganti password; akun siswa/ortu ditautkan ke data masing-masing.
4. Command ssz:backup (mysqldump via PHP jika exec dinonaktifkan → fallback ekspor tabel via Eloquent ke SQL/JSON) ke storage/app/backups, jadwal mingguan; tombol unduh backup di Settings.
5. Halaman /privasi (kebijakan privasi singkat: data apa yang disimpan, siapa yang bisa melihat, kontak).
6. Test ekspor mengembalikan file xlsx.
```

### Fase 11 — Poles, aksesibilitas, performa, siap deploy
```
Baca SPEC.md §10, §11, §16. Lakukan audit dan perbaikan:
1. Lint & rapikan: php artisan pint, hapus kode mati, pastikan semua teks Bahasa Indonesia konsisten (Cek In/Cek Out, Stiker, Kartu Masuk).
2. Responsif: uji setiap halaman pada 360px, 768px, 1280px; bottom nav tidak menutupi konten; tombol ≥44px; kontras AA.
3. Performa: eager loading (cegah N+1 dengan Model::preventLazyLoading di lokal), index DB sesuai SPEC, cache settings, pagination 25.
4. Halaman error kustom 403/404/419/500 bertema.
5. Keamanan: header (X-Frame-Options, Referrer-Policy, CSP dasar), throttle login & /q, validasi upload (mime, ukuran ≤2MB), pastikan /media memerlukan auth.
6. PWA: uji Lighthouse PWA installable; offline page bekerja; SW tidak meng-cache /livewire/* dan /q/*.
7. Tulis README.md: fitur, cara install lokal, akun demo, cara deploy cPanel (ringkas dari SPEC §16), cara set cron, cara generate VAPID, FAQ.
8. Jalankan seluruh test; laporkan cakupan fitur yang sudah diuji dan daftar hal yang perlu dicoba manual di HP.
```

### Prompt tambahan (opsional, setelah Fase 11)
- **Integrasi WhatsApp**: "Tambahkan kanal notifikasi WhatsApp via Fonnte (token di settings) sebagai opsi tambahan untuk StudentCheckedIn/Out; buat WhatsAppChannel, antrean, dan toggle per ortu."
- **Kalender libur**: "Tambahkan tabel holidays dan CRUD di Settings; scheduler not-arrived-alerts melewati hari libur; label 'Hari libur' pada log."
- **Multi-gerbang & shift petugas**: "Tambahkan jadwal petugas per gerbang dan laporan kinerja petugas (jumlah scan per hari)."
- **Foto saat scan**: "Saat cek in, opsional ambil foto dari kamera petugas dan simpan ke attendance_logs.photo_path untuk bukti."

---

## 15. Checklist Uji Terima (per fase)

| Fase | Yang harus bisa dibuktikan |
|---|---|
| 0 | `/` tampil di HP & desktop, build tanpa error, tidak ada paket starter kit |
| 1 | `migrate:fresh --seed` sukses; data demo terlihat di DB; plat "b 1234 xyz" tersimpan `B1234XYZ` |
| 2 | Login 4 role diarahkan benar; siswa → `/admin` = 403; login pertama dipaksa ganti password |
| 3 | Buat siswa + foto → akun otomatis; impor CSV 3 baris; tambah kendaraan; foto tidak bisa dibuka tanpa login |
| 4 | Terbitkan stiker → nomor `SSZ-2627-0001`; PDF satuan 90×55 mm; PDF massal 8/lembar; QR terbaca kamera HP dan membuka `/q/{token}` |
| 5 | Scan QR dengan HP tanpa login → hanya plat + tombol darurat; login petugas → data lengkap |
| 6 | Scan pertama = CEK IN hijau; scan kedua <2 menit = duplikat; setelah 2 menit = CEK OUT; siswa diblokir = merah & tidak tercatat; mode pesawat → scan masuk antrean lalu terkirim saat online |
| 7 | Blokir → scan merah; buka blokir → hijau; pelanggaran menambah poin; banner saran blokir pada threshold |
| 8 | Ortu aktifkan notifikasi → cek in anak memunculkan push di HP ortu dalam ≤60 detik; lonceng in-app bertambah; alert "belum cek in" terkirim pada jam yang diatur |
| 9 | Siswa lihat kartu digital, riwayat, ajukan ubah HP ortu → muncul di admin → disetujui → data berubah |
| 10 | Ekspor XLSX & PDF laporan; ubah pengaturan jam berpengaruh pada scan; reset password akun |
| 11 | Lighthouse: PWA installable, Performance >80 mobile; semua test hijau; README lengkap |

---

## 16. Deploy ke cPanel (shared hosting)

1. **Persiapan lokal**: `npm run build`, pastikan `public/build` ikut di-upload (atau di-commit). Set `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://ssz.sman1ciruas.sch.id` (contoh).
2. **Struktur folder**: upload seluruh proyek ke `~/ssz-app/` (di luar `public_html`). Isi `public_html` (atau subdomain doc-root) dengan isi folder `public/`, lalu edit `index.php` di sana: ubah `require __DIR__.'/../vendor/autoload.php'` → `require __DIR__.'/../ssz-app/vendor/autoload.php'` dan `$app = require_once __DIR__.'/../ssz-app/bootstrap/app.php'`. Alternatif: jadikan doc-root subdomain langsung ke `~/ssz-app/public` bila cPanel mengizinkan.
3. **PHP**: pilih PHP 8.2/8.3 di *MultiPHP Manager*, aktifkan ekstensi `gd, mbstring, intl, zip, pdo_mysql, fileinfo, bcmath, openssl`. Naikkan `upload_max_filesize`/`post_max_size` ke 8M, `memory_limit` 256M (untuk DomPDF massal).
4. **Composer**: jalankan `composer install --no-dev --optimize-autoloader` via Terminal cPanel/SSH; jika tidak ada, upload folder `vendor` hasil lokal (PHP versi sama).
5. **Database**: buat DB & user di MySQL Databases, isi `.env`, lalu `php artisan migrate --force --seed` (seed hanya `RoleUserSeeder`, `SettingSeeder`, `AcademicYearSeeder` — jangan DemoSeeder di produksi) dan `php artisan storage:link` (jika gagal, route `/media` sudah menjadi fallback).
6. **Cron (Cron Jobs cPanel)** — dua baris, tiap menit:
   ```
   * * * * * cd /home/USER/ssz-app && /usr/local/bin/php artisan schedule:run >> /dev/null 2>&1
   * * * * * cd /home/USER/ssz-app && /usr/local/bin/php artisan queue:work --stop-when-empty --max-time=50 --tries=3 >> /dev/null 2>&1
   ```
7. **Optimasi**: `php artisan config:cache route:cache view:cache event:cache`. Ulangi setiap kali `.env` berubah.
8. **HTTPS wajib** (Web Push & kamera hanya jalan di HTTPS): aktifkan AutoSSL/Let's Encrypt di cPanel; paksa redirect HTTPS di `.htaccess`.
9. **VAPID**: jalankan `php artisan webpush:vapid --show` lokal, salin ke `.env` produksi.
10. **Uji pasca-deploy**: login admin, ganti password default, isi Pengaturan, cetak 1 stiker uji, scan dengan HP petugas, aktifkan push di HP ortu.
11. **Backup**: unduh berkas dari Settings → Backup mingguan; simpan juga folder `storage/app/public/students`.

---

## 17. Catatan Improvisasi (yang ditambahkan di luar permintaan awal)

- Nomor stiker berurutan per tahun ajaran & masa berlaku otomatis, plus perpanjangan tanpa ganti token.
- Anti double-scan, deteksi *re-entry*, dan label *keluar lebih awal* dengan notifikasi berbeda.
- Antrean scan offline di HP petugas (sinyal di gerbang sering buruk).
- Alert "belum cek in" ke ortu pada jam yang diatur, dan ringkasan harian ke admin.
- Kartu digital di akun siswa sebagai cadangan jika stiker rusak.
- Pengajuan perubahan data oleh siswa dengan persetujuan admin (data ortu tidak bisa diubah sepihak).
- Poin pelanggaran dengan saran blokir otomatis, blokir berjangka yang terbuka sendiri.
- `scan_attempts` + `audit_logs` untuk audit dan deteksi penyalahgunaan QR.
- Nama siswa sengaja **tidak** dicetak di stiker; kelas bisa dimatikan — meminimalkan risiko data pribadi di kendaraan yang terparkir di ruang publik.
- Halaman privasi & kebijakan tampilan publik yang bisa diatur (mis. sembunyikan nomor darurat dari layar tapi tetap bisa ditekan sebagai tombol telepon).

*Dokumen disusun 12 September 2026 untuk SMA Negeri 1 Ciruas, Kabupaten Serang, Banten.*

---

## 18. Catatan Implementasi (diisi saat pembangunan)

Bagian ini mencatat keputusan yang diambil saat spesifikasi ini diwujudkan menjadi kode,
terutama hal-hal yang berbeda atau lebih rinci dari rencana awal.

### 18.1 Versi framework & advisory keamanan
Composer 2.10 menolak memasang Laravel 11 karena rangkaian advisory yang tidak di-backport
(Laravel 11 sudah lewat masa dukungan keamanan). Karena §2 dan `CLAUDE.md` mengunci Laravel 11,
proyek menyetel `policy.advisories.block: false` pada `composer.json`.
**Rekomendasi:** naikkan ke Laravel 12 LTS sebelum aplikasi dipakai menangani data siswa sungguhan.

### 18.2 Tambahan di luar rencana awal
- Enum `ViolationCategory` (§5.9) dibuat sebagai enum PHP tersendiri, lengkap dengan poin bawaan
  per kategori dan daftar kategori ringan yang boleh dicatat petugas.
- `ScanResultType` mendapat nilai tambahan `too_early` untuk penolakan lembut sebelum
  `checkin_open_time`; nilai ini juga tersedia pada enum kolom `scan_attempts.result`.
- Kolom `attendance_logs.offline_id` (UUID unik) ditambahkan agar sinkronisasi antrean offline
  benar-benar idempoten, bukan sekadar ditandai `is_offline_sync`.
- Kolom `students.blocked_type` ditambahkan untuk menyimpan jenis blokir (pelanggaran/administrasi/lainnya).
- `BackupService` dipisahkan dari perintah `ssz:backup` agar dapat dipanggil juga dari tombol
  unduh cadangan di halaman Pengaturan.
- `AccountService` memusatkan aturan pembuatan akun otomatis siswa & orang tua (§5.10).
- `StudentBlockService` memusatkan alur blokir/buka blokir beserta penangguhan stiker dan notifikasi.

### 18.3 Keputusan teknis
- **QR pada PDF**: dirender sebagai SVG (error-correction H) dan disisipkan sebagai data URI.
  DomPDF menanganinya lewat `dompdf/php-svg-lib`, sehingga tidak memerlukan Imagick.
- **Logo pada stiker**: diperkecil ke 96 px dan di-cache di `storage/app/sticker-logos`
  sebelum disematkan sebagai base64; logo asli berukuran ratusan kilobita membengkakkan PDF.
- **html5-qrcode**: dibundel sebagai entri Vite terpisah (`resources/js/scanner.js`) dan hanya
  dimuat di halaman scan, agar halaman lain tidak menanggung ~335 KB.
- **Deteksi N+1**: `Model::preventLazyLoading` aktif di luar produksi, tetapi di lokal
  pelanggaran hanya dicatat ke log (tidak melempar error) agar halaman tetap terbuka;
  saat pengujian tetap dilempar sebagai error.
- **Mode manual** tetap melewati `ScanService` agar seluruh aturan dan pencatatan konsisten;
  jenis in/out ditentukan oleh pilihan petugas.

### 18.4 Yang tetap perlu diuji manual
Pemindaian QR fisik dari stiker cetak, izin kamera di HP, antrean offline (mode pesawat),
pengiriman Web Push ke HP orang tua, pemasangan PWA ke layar utama, dan ketepatan potongan
90×55 mm saat dicetak di kertas A4 tanpa penskalaan. Rincian ada di `README.md` §9.
