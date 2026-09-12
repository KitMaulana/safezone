<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Cetak Stiker Massal</title>
    @include('pdf._sticker-styles')
    <style>
        @page { margin: 8mm 6mm; }
        body { margin: 0; padding: 0; }

        .sheet { border-collapse: collapse; width: 100%; margin: 0 auto; }
        .cell {
            width: 95mm;
            height: 67mm;
            padding: 0;
            border-right: 0.2mm dashed #CBD5E1;
            border-bottom: 0.2mm dashed #CBD5E1;
            vertical-align: middle;
            text-align: center;
        }
        .cell-last-col { border-right: 0; }
    </style>

</head>
<body>
    @foreach (array_chunk($cards, 8) as $page)
        @if (! $loop->first)
            <div style="page-break-before: always;"></div>
        @endif

        <table class="sheet" cellpadding="0" cellspacing="0">
            @foreach (array_chunk($page, 2) as $row)
                <tr>
                    @foreach ($row as $i => $card)
                        <td class="cell {{ $i === 1 ? 'cell-last-col' : '' }}">
                            @include('pdf._sticker-card', ['card' => $card])
                        </td>
                    @endforeach

                    @if (count($row) === 1)
                        <td class="cell cell-last-col"></td>
                    @endif
                </tr>
            @endforeach
        </table>
    @endforeach
</body>
</html>
