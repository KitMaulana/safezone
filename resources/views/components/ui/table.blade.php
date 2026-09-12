@props(['headers' => []])

<div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
    <table {{ $attributes->merge(['class' => 'min-w-full divide-y divide-slate-200 text-sm']) }}>
        @if (count($headers))
            <thead class="bg-slate-50">
                <tr>
                    @foreach ($headers as $header)
                        <th scope="col" class="whitespace-nowrap px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            {{ $header }}
                        </th>
                    @endforeach
                </tr>
            </thead>
        @endif
        <tbody class="divide-y divide-slate-100 bg-white">
            {{ $slot }}
        </tbody>
    </table>
</div>
