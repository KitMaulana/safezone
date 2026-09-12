@extends('layouts.public')

@section('title', 'Kebijakan Privasi')

@section('content')
    <h1 class="text-xl font-bold text-slate-900">Kebijakan Privasi</h1>
    <p class="mt-1 text-sm text-slate-500">School Safe Zone SMA Negeri 1 Ciruas</p>

    <div class="mt-5 space-y-4 text-sm leading-relaxed text-slate-700">
        <section>
            <h2 class="font-semibold text-slate-900">Data yang kami simpan</h2>
            <p class="mt-1">Identitas siswa (NISN, nama, kelas, tanggal lahir, alamat, nomor HP), data orang tua/wali (nama, nomor HP, hubungan), data kendaraan (nomor polisi, merek, tipe, warna, kelengkapan surat), foto siswa, serta catatan cek in/cek out dan pelanggaran.</p>
        </section>

        <section>
            <h2 class="font-semibold text-slate-900">Siapa yang dapat melihat</h2>
            <ul class="mt-1 list-disc space-y-1 pl-5">
                <li><strong>Publik</strong> (memindai QR pada stiker): hanya nomor polisi, status stiker, dan tombol hubungi kontak darurat. Nama, NISN, foto, kelas, dan alamat <em>tidak</em> ditampilkan.</li>
                <li><strong>Petugas &amp; Admin sekolah</strong>: data lengkap siswa untuk keperluan verifikasi di gerbang.</li>
                <li><strong>Siswa</strong>: data dirinya sendiri.</li>
                <li><strong>Orang tua/wali</strong>: data anaknya sendiri.</li>
            </ul>
        </section>

        <section>
            <h2 class="font-semibold text-slate-900">Perlindungan data</h2>
            <p class="mt-1">Foto dan berkas siswa hanya dapat dibuka melalui akun yang berwenang. QR pada stiker tidak memuat data pribadi — hanya token acak. Setiap pemindaian dan setiap perubahan data sensitif dicatat dalam log audit.</p>
        </section>

        <section>
            <h2 class="font-semibold text-slate-900">Nama siswa tidak dicetak di stiker</h2>
            <p class="mt-1">Stiker yang tertempel di kendaraan sengaja tidak memuat nama siswa untuk mengurangi risiko penyalahgunaan data saat kendaraan terparkir di ruang publik.</p>
        </section>

        <section>
            <h2 class="font-semibold text-slate-900">Kontak</h2>
            <p class="mt-1">Pertanyaan atau permintaan koreksi data dapat disampaikan ke bagian Kesiswaan SMA Negeri 1 Ciruas.</p>
        </section>
    </div>

    <div class="mt-6">
        <x-ui.button variant="secondary" :href="url('/')" class="w-full">
            <x-icon.arrow-left class="h-5 w-5" />
            Kembali
        </x-ui.button>
    </div>
@endsection
