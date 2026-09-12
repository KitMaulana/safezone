{{-- Kartu stiker 90 x 55 mm. Dipakai oleh PDF satuan, lembar massal, dan pratinjau HTML. --}}
<table class="card" cellpadding="0" cellspacing="0">
    {{-- Kepala: logo sekolah, judul, logo polres --}}
    <tr>
        <td class="card-head" colspan="2">
            <table cellpadding="0" cellspacing="0" width="100%">
                <tr>
                    <td width="15%" align="left" valign="middle">
                        @if ($card['logo_school'])
                            <img src="{{ $card['logo_school'] }}" class="logo" alt="">
                        @endif
                    </td>
                    <td width="70%" align="center" valign="middle">
                        <div class="title">KARTU MASUK</div>
                        <div class="subtitle">{{ strtoupper($card['school_name']) }}</div>
                        <div class="tagline">School Safe Zone &bull; TA {{ $card['academic_year'] }}</div>
                    </td>
                    <td width="15%" align="right" valign="middle">
                        @if ($card['logo_police'])
                            <img src="{{ $card['logo_police'] }}" class="logo" alt="">
                        @endif
                    </td>
                </tr>
            </table>
        </td>
    </tr>

    {{-- Badan: QR + data --}}
    <tr>
        <td class="card-qr" width="36%" valign="top">
            <img src="{{ $card['qr_svg'] }}" class="qr" alt="QR">
            <div class="qr-note">PINDAI VERIFIKASI</div>
        </td>
        <td class="card-info" width="64%" valign="top">
            <div class="label">NO. POLISI</div>
            <div class="plate">{{ $card['plate'] }}</div>
            <table cellpadding="0" cellspacing="0" width="100%" class="meta">
                <tr>
                    <td width="38%" class="meta-lbl">No. Stiker</td>
                    <td width="4%" class="meta-sep">:</td>
                    <td class="meta-val"><strong>{{ $card['permit_number'] }}</strong></td>
                </tr>
                <tr>
                    <td class="meta-lbl">Berlaku s.d.</td>
                    <td class="meta-sep">:</td>
                    <td class="meta-val"><strong>{{ $card['expires_at'] }}</strong></td>
                </tr>
                @if ($card['show_class'])
                    <tr>
                        <td class="meta-lbl">Kelas</td>
                        <td class="meta-sep">:</td>
                        <td class="meta-val"><strong>{{ $card['class_room'] }}</strong></td>
                    </tr>
                @endif
            </table>
        </td>
    </tr>

    {{-- Kaki: blok darurat & kepolisian --}}
    <tr>
        <td class="card-foot" colspan="2">
            <table cellpadding="0" cellspacing="0" width="100%" class="emergency">
                <tr>
                    <td class="emergency-text" valign="middle">
                        <div class="em-title">&#9888; BILA KEADAAN DARURAT HUBUNGI:</div>
                        <div class="em-val">
                            @if ($card['emergency_phone'])
                                <strong>{{ $card['emergency_phone'] }}</strong>@if ($card['emergency_name']) <span class="em-name">({{ $card['emergency_name'] }})</span>@endif
                            @else
                                <strong>{{ $card['school_phone'] }}</strong> <span class="em-name">(Sekolah)</span>
                            @endif
                        </div>
                    </td>
                    @if ($card['police_phone'])
                        <td class="police" valign="middle" align="center">
                            <div class="police-label">POLISI</div>
                            <div class="police-number">
                                <svg class="phone-ico" width="8" height="8" viewBox="0 0 24 24" style="vertical-align: -0.5px; display: inline-block;"><path fill="#ffffff" d="M6.62 10.79a15.053 15.053 0 006.59 6.59l2.2-2.2a1 1 0 011.02-.24c1.12.37 2.33.57 3.57.57a1 1 0 011 1V20a1 1 0 01-1 1A17 17 0 013 4a1 1 0 011-1h3.5a1 1 0 011 1c0 1.25.2 2.45.57 3.57a1 1 0 01-.25 1.02l-2.2 2.2z"/></svg>
                                {{ $card['police_phone'] }}
                            </div>
                        </td>
                    @endif
                </tr>
            </table>
            <div class="school-line">
                Sekolah: {{ $card['school_phone'] }}
                @if ($card['police_phone'])
                    &nbsp;&bull;&nbsp; Kepolisian RI: {{ $card['police_phone'] }}
                @endif
                &nbsp;&bull;&nbsp; {{ $card['footer_text'] }}
            </div>
        </td>
    </tr>
</table>
