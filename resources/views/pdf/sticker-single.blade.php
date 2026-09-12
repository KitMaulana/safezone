<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Stiker {{ $card['permit_number'] }}</title>
    @include('pdf._sticker-styles')
    <style>
        @page { margin: {{ $a4 ? '0' : '0' }}; }
        body { margin: 0; padding: 0; }
        .wrap-a4 { padding-top: 100mm; text-align: center; }
    </style>
</head>
<body>
    @if ($a4)
        <div class="wrap-a4">
            <table cellpadding="0" cellspacing="0" align="center"><tr><td>
                @include('pdf._sticker-card', ['card' => $card])
            </td></tr></table>
        </div>
    @else
        @include('pdf._sticker-card', ['card' => $card])
    @endif

    @if ($showBack)
        @if ($a4)
            <div style="page-break-before: always;"></div>
            <div class="wrap-a4">
                <table cellpadding="0" cellspacing="0" align="center"><tr><td>
                    <div class="back">
                        <h4>TATA TERTIB BERKENDARA PELAJAR</h4>
                        <ol>
                            <li>Wajib memakai helm SNI selama perjalanan.</li>
                            <li>Maksimal berboncengan dua orang.</li>
                            <li>Kendaraan standar pabrik, tanpa knalpot bising.</li>
                            <li>Parkir hanya di area yang ditentukan sekolah.</li>
                            <li>Patuhi rambu dan jangan mengebut di area sekolah.</li>
                        </ol>
                        <div class="sign">
                            Ciruas, {{ now('Asia/Jakarta')->translatedFormat('d F Y') }}<br>
                            Kepala Sekolah<br><br><br>
                            <strong>{{ $settings['headmaster_name'] ?: '..............................' }}</strong>
                        </div>
                    </div>
                </td></tr></table>
            </div>
        @else
            <div class="back" style="page-break-before: always;">
                <h4>TATA TERTIB BERKENDARA PELAJAR</h4>
                <ol>
                    <li>Wajib memakai helm SNI selama perjalanan.</li>
                    <li>Maksimal berboncengan dua orang.</li>
                    <li>Kendaraan standar pabrik, tanpa knalpot bising.</li>
                    <li>Parkir hanya di area yang ditentukan sekolah.</li>
                    <li>Patuhi rambu dan jangan mengebut di area sekolah.</li>
                </ol>
                <div class="sign">
                    Ciruas, {{ now('Asia/Jakarta')->translatedFormat('d F Y') }}<br>
                    Kepala Sekolah<br><br><br>
                    <strong>{{ $settings['headmaster_name'] ?: '..............................' }}</strong>
                </div>
            </div>
        @endif
    @endif
</body>
</html>
