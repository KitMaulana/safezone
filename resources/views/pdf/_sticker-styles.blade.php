{{-- Gaya stiker: dipakai bersama oleh PDF satuan, lembar massal, dan pratinjau HTML. --}}
<style>
    * { font-family: "DejaVu Sans", sans-serif; }

    /*
     * Ukuran akhir stiker tetap 90 x 55 mm.
     * DomPDF memakai model kotak content-box, sehingga border 0.4mm di tiap sisi
     * ditambahkan DI LUAR lebar/tinggi. Nilai di bawah sudah dikurangi 0.8mm
     * agar kartu pas mengisi halaman PDF 90 x 55 mm tanpa meluber ke halaman baru.
     */
    .card {
        width: 89.2mm;
        height: 54.2mm;
        border: 0.5mm solid #1E3A8A;
        border-collapse: collapse;
        background: #ffffff;
    }

    .card-head {
        background: #1E3A8A;
        color: #ffffff;
        padding: 0.6mm 2mm;
        border-bottom: 0.7mm solid #FACC15;
    }

    .logo { height: 6.8mm; width: 6.8mm; }

    .title {
        font-size: 9.5pt;
        font-weight: bold;
        letter-spacing: 0.6mm;
        line-height: 1.05;
        color: #ffffff;
    }

    .subtitle {
        font-size: 7.2pt;
        font-weight: bold;
        letter-spacing: 0.2mm;
        line-height: 1.1;
        color: #F8FAFC;
    }

    .tagline {
        font-size: 4.8pt;
        font-weight: bold;
        color: #FACC15;
        line-height: 1.15;
        letter-spacing: 0.2mm;
    }

    .card-qr {
        padding: 0.7mm 1mm 0.2mm 2.2mm;
        text-align: center;
        vertical-align: middle;
    }

    .qr { width: 25.5mm; height: 25.5mm; }

    .qr-note {
        font-size: 4pt;
        font-weight: bold;
        color: #475569;
        letter-spacing: 0.3mm;
        padding-top: 0.2mm;
        line-height: 1;
    }

    .card-info {
        padding: 0.6mm 2.5mm 0.2mm 1.5mm;
        vertical-align: middle;
    }

    .label {
        font-size: 4.8pt;
        font-weight: bold;
        color: #2563EB;
        letter-spacing: 0.4mm;
        line-height: 1.1;
    }

    .plate {
        font-size: 20pt;
        font-weight: bold;
        color: #0F172A;
        letter-spacing: 0.7mm;
        line-height: 1.05;
        padding: 0.2mm 0 0.4mm 0;
    }

    .meta {
        font-size: 6.5pt;
        color: #334155;
    }
    .meta td { padding: 0.15mm 0; }
    .meta-lbl { color: #64748B; font-weight: normal; }
    .meta-sep { color: #94A3B8; }
    .meta-val { color: #0F172A; }

    .card-foot {
        padding: 0;
        border-top: 0.3mm solid #E2E8F0;
    }

    .emergency {
        background: #FEF2F2;
        border-collapse: collapse;
    }

    .emergency-text {
        color: #991B1B;
        padding: 0.5mm 1.5mm 0.5mm 2.2mm;
        border-left: 0.8mm solid #EF4444;
    }

    .em-title {
        font-size: 4.5pt;
        font-weight: bold;
        color: #DC2626;
        letter-spacing: 0.2mm;
        line-height: 1.1;
    }

    .em-val {
        font-size: 6.2pt;
        color: #991B1B;
        line-height: 1.15;
    }

    .em-name {
        font-size: 5.5pt;
        color: #B91C1C;
        font-weight: normal;
    }

    /* Nomor darurat Kepolisian RI — dibuat kontras dengan icon telepon */
    .police {
        width: 14mm;
        background: #DC2626;
        color: #ffffff;
        padding: 0.4mm 1mm;
    }

    .police-label {
        font-size: 4pt;
        font-weight: bold;
        letter-spacing: 0.3mm;
        line-height: 1;
        color: #FEE2E2;
    }

    .police-number {
        font-size: 9.5pt;
        font-weight: bold;
        line-height: 1.1;
        color: #ffffff;
    }

    .phone-ico {
        vertical-align: -0.5px;
    }

    .school-line {
        font-size: 4.2pt;
        color: #475569;
        padding: 0.25mm 2mm;
        text-align: center;
        line-height: 1.1;
        background: #F8FAFC;
        border-top: 0.2mm solid #E2E8F0;
    }

    /* Halaman belakang (tata tertib) */
    .back {
        width: 89.2mm;
        height: 49.4mm;
        border: 0.5mm solid #1E3A8A;
        padding: 2mm 2.5mm;
        background: #ffffff;
    }

    .back h4 {
        font-size: 8pt;
        font-weight: bold;
        color: #1E3A8A;
        margin: 0 0 1.2mm 0;
        text-align: center;
        letter-spacing: 0.2mm;
    }

    .back ol {
        font-size: 5.8pt;
        color: #334155;
        margin: 0;
        padding-left: 3.5mm;
        line-height: 1.4;
    }

    .sign {
        font-size: 5.2pt;
        color: #475569;
        text-align: right;
        padding-top: 1mm;
    }
</style>
