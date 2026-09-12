<?php

use App\Enums\Role;
use App\Services\SettingService;
use App\Services\StickerPdfService;
use Barryvdh\DomPDF\Facade\Pdf;
use Tests\TestHelpers;

beforeEach(function () {
    TestHelpers::academicYear();
    $this->admin = TestHelpers::user(Role::Admin);
});

it('menghasilkan tepat 1 halaman untuk stiker satuan tanpa tata tertib belakang', function () {
    $permit = TestHelpers::activePermit(TestHelpers::student());
    $service = app(StickerPdfService::class);

    $cardData = $service->cardData($permit);

    $pdf = Pdf::loadView('pdf.sticker-single', [
        'card' => $cardData,
        'a4' => false,
        'showBack' => false,
        'settings' => app(SettingService::class)->all(),
    ]);

    $pdf->setPaper([0, 0, 255.12, 155.91], 'portrait');
    $pdf->setOption([
        'isRemoteEnabled' => false,
        'isHtml5ParserEnabled' => true,
        'defaultFont' => 'DejaVu Sans',
        'dpi' => 150,
    ]);

    $dompdf = $pdf->getDomPDF();
    $dompdf->render();

    $pageCount = $dompdf->getCanvas()->get_page_count();
    expect($pageCount)->toBe(1);
});

it('menghasilkan tepat 2 halaman untuk stiker satuan dengan tata tertib belakang', function () {
    $permit = TestHelpers::activePermit(TestHelpers::student());
    $service = app(StickerPdfService::class);

    $cardData = $service->cardData($permit);

    $pdf = Pdf::loadView('pdf.sticker-single', [
        'card' => $cardData,
        'a4' => false,
        'showBack' => true,
        'settings' => app(SettingService::class)->all(),
    ]);

    $pdf->setPaper([0, 0, 255.12, 155.91], 'portrait');
    $pdf->setOption([
        'isRemoteEnabled' => false,
        'isHtml5ParserEnabled' => true,
        'defaultFont' => 'DejaVu Sans',
        'dpi' => 150,
    ]);

    $dompdf = $pdf->getDomPDF();
    $dompdf->render();

    $pageCount = $dompdf->getCanvas()->get_page_count();
    expect($pageCount)->toBe(2);
});

it('menghasilkan tepat 1 halaman untuk cetak massal 8 stiker pada A4', function () {
    $service = app(StickerPdfService::class);
    $cards = [];
    for ($i = 0; $i < 8; $i++) {
        $permit = TestHelpers::activePermit(TestHelpers::student());
        $cards[] = $service->cardData($permit);
    }

    $pdf = Pdf::loadView('pdf.sticker-sheet', [
        'cards' => $cards,
        'settings' => app(SettingService::class)->all(),
    ]);

    $pdf->setPaper('a4', 'portrait');
    $pdf->setOption([
        'isRemoteEnabled' => false,
        'isHtml5ParserEnabled' => true,
        'defaultFont' => 'DejaVu Sans',
        'dpi' => 150,
    ]);

    $dompdf = $pdf->getDomPDF();
    $dompdf->render();

    $pageCount = $dompdf->getCanvas()->get_page_count();
    expect($pageCount)->toBe(1);
});

it('menghasilkan tepat 2 halaman untuk cetak massal 16 stiker pada A4', function () {
    $service = app(StickerPdfService::class);
    $cards = [];
    for ($i = 0; $i < 16; $i++) {
        $permit = TestHelpers::activePermit(TestHelpers::student());
        $cards[] = $service->cardData($permit);
    }

    $pdf = Pdf::loadView('pdf.sticker-sheet', [
        'cards' => $cards,
        'settings' => app(SettingService::class)->all(),
    ]);

    $pdf->setPaper('a4', 'portrait');
    $pdf->setOption([
        'isRemoteEnabled' => false,
        'isHtml5ParserEnabled' => true,
        'defaultFont' => 'DejaVu Sans',
        'dpi' => 150,
    ]);

    $dompdf = $pdf->getDomPDF();
    $dompdf->render();

    $pageCount = $dompdf->getCanvas()->get_page_count();
    expect($pageCount)->toBe(2);
});

it('menghasilkan tepat 1 halaman untuk stiker satuan pada A4 tanpa tata tertib belakang', function () {
    $permit = TestHelpers::activePermit(TestHelpers::student());
    $service = app(StickerPdfService::class);

    $cardData = $service->cardData($permit);

    $pdf = Pdf::loadView('pdf.sticker-single', [
        'card' => $cardData,
        'a4' => true,
        'showBack' => false,
        'settings' => app(SettingService::class)->all(),
    ]);

    $pdf->setPaper('a4', 'portrait');
    $pdf->setOption([
        'isRemoteEnabled' => false,
        'isHtml5ParserEnabled' => true,
        'defaultFont' => 'DejaVu Sans',
        'dpi' => 150,
    ]);

    $dompdf = $pdf->getDomPDF();
    $dompdf->render();

    $pageCount = $dompdf->getCanvas()->get_page_count();
    expect($pageCount)->toBe(1);
});
