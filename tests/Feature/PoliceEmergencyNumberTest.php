<?php

use App\Enums\Role;
use App\Services\SettingService;
use App\Services\StickerPdfService;
use Tests\TestHelpers;

beforeEach(function () {
    $this->student = TestHelpers::student(['name' => 'Ken Arya Pratama']);
    $this->parent = TestHelpers::parentFor($this->student, ['phone' => '081234567890']);
    $this->permit = TestHelpers::activePermit($this->student);
});

it('menampilkan nomor darurat Kepolisian RI kepada tamu di halaman QR', function () {
    $this->get('/q/'.$this->permit->qr_token)
        ->assertOk()
        ->assertSee('Kepolisian RI')
        ->assertSee('110')
        ->assertSee('href="tel:110"', false);
});

it('menampilkan nomor Kepolisian RI juga pada tampilan petugas', function () {
    $this->actingAs(TestHelpers::user(Role::Petugas))
        ->get('/q/'.$this->permit->qr_token)
        ->assertOk()
        ->assertSee('Kepolisian RI')
        ->assertSee('href="tel:110"', false);
});

it('mencantumkan nomor Kepolisian RI pada data kartu stiker', function () {
    $card = app(StickerPdfService::class)->cardData($this->permit);

    expect($card['police_phone'])->toBe('110');
});

it('mencetak nomor Kepolisian RI pada pratinjau stiker', function () {
    $this->actingAs(TestHelpers::user(Role::Admin))
        ->get('/admin/stiker/'.$this->permit->id.'/pratinjau')
        ->assertOk()
        ->assertSee('POLISI')
        ->assertSee('Kepolisian RI: 110');
});

it('menyembunyikan blok kepolisian bila nomornya dikosongkan admin', function () {
    $settings = app(SettingService::class);
    $settings->set('police_phone', '');

    $this->get('/q/'.$this->permit->qr_token)
        ->assertOk()
        ->assertDontSee('Kepolisian RI');

    $card = app(StickerPdfService::class)->cardData($this->permit->fresh());

    expect($card['police_phone'])->toBe('');
});

it('memakai nomor lain bila admin menggantinya', function () {
    app(SettingService::class)->set('police_phone', '112');

    $this->get('/q/'.$this->permit->qr_token)
        ->assertOk()
        ->assertSee('href="tel:112"', false);
});
