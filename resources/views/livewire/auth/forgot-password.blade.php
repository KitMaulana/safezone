<div>
    <div class="text-center">
        <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-brand-50 text-brand-600">
            <x-icon.key class="h-7 w-7" />
        </span>
        <h1 class="mt-4 text-xl font-bold text-slate-900">Lupa Kata Sandi</h1>
        <p class="mt-1 text-sm text-slate-500">Masukkan username, NISN, atau nomor HP Anda.</p>
    </div>

    <x-ui.card class="mt-6">
        @if ($status)
            <x-ui.alert type="success" class="mb-4">{{ $status }}</x-ui.alert>
        @endif

        @if ($noEmailNotice)
            <x-ui.alert type="warning" class="mb-4">{{ $noEmailNotice }}</x-ui.alert>
        @endif

        <form wire:submit="send" class="space-y-4">
            <x-ui.input wire:model="username" name="username" label="Username / NISN / No. HP" required autofocus />
            <x-ui.button type="submit" size="lg" class="w-full">Kirim tautan</x-ui.button>
        </form>
    </x-ui.card>

    <p class="mt-4 text-center text-sm">
        <a href="{{ route('login') }}" class="font-medium text-brand-600 hover:text-brand-700">Kembali ke halaman masuk</a>
    </p>
</div>
