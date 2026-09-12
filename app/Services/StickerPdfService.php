<?php

namespace App\Services;

use App\Models\VehiclePermit;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Symfony\Component\HttpFoundation\Response;

/**
 * Membuat PDF stiker "KARTU MASUK SMAN 1 Ciruas" (SPEC §7).
 * Ukuran per stiker 90 x 55 mm; lembar massal A4 2 kolom x 4 baris.
 */
class StickerPdfService
{
    /** 90 x 55 mm dalam satuan point (1 mm = 2.8346 pt). */
    private const CARD_SIZE = [0, 0, 255.12, 155.91];

    public function __construct(
        private readonly SettingService $settings,
        private readonly PermitService $permits,
    ) {}

    /** Stiker satuan. $a4 = true menghasilkan A4 dengan satu stiker di tengah. */
    public function single(VehiclePermit $permit, bool $a4 = false): Response
    {
        $permit->loadMissing(['vehicle.student.parents', 'vehicle.student.academicYear']);

        $pdf = Pdf::loadView('pdf.sticker-single', [
            'card' => $this->cardData($permit),
            'a4' => $a4,
            'showBack' => $this->settings->bool('sticker_print_back'),
            'settings' => $this->settings->all(),
        ]);

        $pdf->setPaper($a4 ? 'a4' : self::CARD_SIZE, 'portrait');
        $this->applyOptions($pdf);

        $this->permits->markPrinted($permit, $this->settings->bool('auto_activate_on_print', true));

        return $pdf->stream('stiker-'.$permit->permit_number.'.pdf');
    }

    /** Lembar massal A4: 8 stiker per lembar dengan garis potong. */
    public function batch(Collection $permits): Response
    {
        $permits->loadMissing(['vehicle.student.parents', 'vehicle.student.academicYear']);

        $cards = $permits->map(fn (VehiclePermit $permit) => $this->cardData($permit))->all();

        $pdf = Pdf::loadView('pdf.sticker-sheet', [
            'cards' => $cards,
            'settings' => $this->settings->all(),
        ]);

        $pdf->setPaper('a4', 'portrait');
        $this->applyOptions($pdf);

        $autoActivate = $this->settings->bool('auto_activate_on_print', true);

        foreach ($permits as $permit) {
            $this->permits->markPrinted($permit, $autoActivate);
        }

        return $pdf->stream('stiker-massal-'.now()->format('Ymd-His').'.pdf');
    }

    /** Data satu kartu; dipakai PDF maupun pratinjau HTML & kartu digital siswa. */
    public function cardData(VehiclePermit $permit): array
    {
        $vehicle = $permit->vehicle;
        $student = $vehicle->student;
        $parent = $student->primaryParent();

        return [
            'permit' => $permit,
            'permit_number' => $permit->permit_number,
            'plate' => $vehicle->formatted_plate,
            'expires_at' => $permit->expires_at->format('d M Y'),
            'class_room' => $student->class_room,
            'show_class' => $this->settings->bool('sticker_show_class', true),
            'qr_svg' => $this->qrDataUri($permit->publicUrl()),
            'academic_year' => optional($student->academicYear)->name ?? $this->academicYearName($permit),
            'emergency_name' => $parent?->name,
            'emergency_phone' => $parent?->phone,
            'school_name' => $this->settings->get('school_name'),
            'school_phone' => $this->settings->get('school_phone'),
            'police_phone' => $this->settings->get('police_phone'),
            'footer_text' => $this->settings->get('sticker_footer_text'),
            'logo_school' => $this->logoDataUri('logo-sman1ciruas.png'),
            'logo_police' => $this->logoDataUri('logo-polres-serang.png'),
        ];
    }

    /** QR sebagai data URI SVG, error-correction H agar tetap terbaca meski tergores. */
    public function qrDataUri(string $url): string
    {
        $svg = (string) QrCode::format('svg')
            ->errorCorrection('H')
            ->size(300)
            ->margin(0)
            ->generate($url);

        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }

    /**
     * Logo disematkan sebagai base64 supaya DomPDF tidak perlu akses HTTP.
     * Diperkecil lebih dulu ke 96 px — pada stiker logo hanya 8 mm, dan logo asli
     * berukuran ratusan kilobita akan membengkakkan berkas PDF.
     */
    private function logoDataUri(string $filename): ?string
    {
        return Cache::remember('ssz.logo.'.$filename, 3600, function () use ($filename) {
            $path = public_path('images/'.$filename);

            if (! is_file($path)) {
                return null;
            }

            $cachedPath = storage_path('app/sticker-logos/'.$filename);

            if (! is_file($cachedPath)) {
                $this->shrinkLogo($path, $cachedPath, 96);
            }

            $source = is_file($cachedPath) ? $cachedPath : $path;

            return 'data:image/png;base64,'.base64_encode((string) file_get_contents($source));
        });
    }

    private function shrinkLogo(string $source, string $destination, int $size): void
    {
        if (! function_exists('imagecreatefrompng')) {
            return;
        }

        @mkdir(dirname($destination), 0775, true);

        $image = @imagecreatefrompng($source);

        if (! $image) {
            return;
        }

        $canvas = imagecreatetruecolor($size, $size);
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        imagefilledrectangle($canvas, 0, 0, $size, $size, imagecolorallocatealpha($canvas, 0, 0, 0, 127));
        imagealphablending($canvas, true);

        $scale = min($size / imagesx($image), $size / imagesy($image));
        $width = (int) (imagesx($image) * $scale);
        $height = (int) (imagesy($image) * $scale);

        imagecopyresampled(
            $canvas, $image,
            (int) (($size - $width) / 2), (int) (($size - $height) / 2), 0, 0,
            $width, $height, imagesx($image), imagesy($image)
        );

        imagepng($canvas, $destination, 9);
        imagedestroy($canvas);
        imagedestroy($image);
    }

    private function academicYearName(VehiclePermit $permit): string
    {
        // Kode tahun ajaran ada di nomor stiker: SSZ-2627-0001 -> 2026/2027.
        $parts = explode('-', $permit->permit_number);
        $code = $parts[1] ?? '';

        if (strlen($code) === 4) {
            return '20'.substr($code, 0, 2).'/20'.substr($code, 2, 2);
        }

        return '-';
    }

    private function applyOptions($pdf): void
    {
        $pdf->setOption([
            'isRemoteEnabled' => false,
            'isHtml5ParserEnabled' => true,
            'defaultFont' => 'DejaVu Sans',
            'dpi' => 150,
        ]);
    }
}
