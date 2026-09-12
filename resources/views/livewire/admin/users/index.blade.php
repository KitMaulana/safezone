<div class="space-y-4">
    @section('header', 'Manajemen Pengguna')

    <div class="flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-slate-500">{{ $users->total() }} akun</p>
        <x-ui.button wire:click="create">
            <x-icon.plus class="h-5 w-5" /> Tambah Akun
        </x-ui.button>
    </div>

    <x-ui.card padding="p-4">
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
            <x-ui.input wire:model.live.debounce.400ms="search" label="Cari" placeholder="Nama atau username…" />
            <x-ui.select wire:model.live="role" label="Role">
                <option value="">Semua role</option>
                @foreach ($roles as $case)
                    <option value="{{ $case->value }}">{{ $case->label() }}</option>
                @endforeach
            </x-ui.select>
        </div>
    </x-ui.card>

    <x-ui.table :headers="['Nama', 'Username', 'Role', 'Status', 'Login terakhir', 'Aksi']">
        @foreach ($users as $user)
            <tr class="hover:bg-slate-50">
                <td class="px-4 py-3 font-medium text-slate-900">{{ $user->name }}</td>
                <td class="whitespace-nowrap px-4 py-3 font-mono text-xs text-slate-600">{{ $user->username }}</td>
                <td class="whitespace-nowrap px-4 py-3 text-slate-600">{{ $user->role->label() }}</td>
                <td class="px-4 py-3">
                    <x-ui.badge :color="$user->is_active ? 'success' : 'slate'">{{ $user->is_active ? 'Aktif' : 'Nonaktif' }}</x-ui.badge>
                    @if ($user->must_change_password)
                        <x-ui.badge color="warning" size="sm" class="ml-1">Ganti sandi</x-ui.badge>
                    @endif
                </td>
                <td class="whitespace-nowrap px-4 py-3 text-xs text-slate-500">
                    {{ $user->last_login_at?->timezone('Asia/Jakarta')->format('d M Y · H.i') ?? 'Belum pernah' }}
                </td>
                <td class="whitespace-nowrap px-4 py-3">
                    <div class="flex flex-wrap items-center gap-1">
                        <button type="button" wire:click="edit({{ $user->id }})" class="rounded p-1.5 text-slate-400 hover:bg-slate-100 hover:text-brand-600" title="Ubah">
                            <x-icon.pencil class="h-4 w-4" />
                        </button>
                        <button type="button" wire:click="resetPassword({{ $user->id }})" wire:confirm="Atur ulang kata sandi akun ini?" class="rounded px-2 py-1 text-xs font-semibold text-brand-600 hover:bg-brand-50">
                            Reset sandi
                        </button>
                        <button type="button" wire:click="toggleActive({{ $user->id }})" class="rounded px-2 py-1 text-xs font-semibold text-slate-600 hover:bg-slate-100">
                            {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                        </button>
                    </div>
                </td>
            </tr>
        @endforeach
    </x-ui.table>

    <div>{{ $users->links() }}</div>

    <x-ui.modal name="pengguna" :title="$editingId ? 'Ubah Akun' : 'Tambah Akun'">
        <form wire:submit="save" class="space-y-4">
            @if ($generatedPassword)
                <x-ui.alert type="success" title="Kata sandi awal">
                    <code class="text-base font-bold">{{ $generatedPassword }}</code>
                    <p class="mt-1 text-xs">Catat sekarang — kata sandi ini hanya ditampilkan satu kali.</p>
                </x-ui.alert>
            @endif

            <x-ui.input wire:model="name" name="name" label="Nama lengkap" required />
            <x-ui.input wire:model="username" name="username" label="Username" required />
            <x-ui.input wire:model="email" name="email" type="email" label="Email (opsional)" hint="Diperlukan untuk fitur lupa kata sandi." />
            <x-ui.input wire:model="phone" name="phone" label="No. HP" />

            <x-ui.select wire:model="form_role" name="form_role" label="Role" required>
                @foreach ($roles as $case)
                    <option value="{{ $case->value }}">{{ $case->label() }}</option>
                @endforeach
            </x-ui.select>

            <label class="flex items-center gap-2 text-sm text-slate-700">
                <input type="checkbox" wire:model="is_active" class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                Akun aktif
            </label>

            <div class="flex justify-end gap-2">
                <x-ui.button variant="secondary" x-on:click="$dispatch('close-modal')">Tutup</x-ui.button>
                <x-ui.button type="submit">Simpan</x-ui.button>
            </div>
        </form>
    </x-ui.modal>

    <x-ui.modal name="sandi" title="Kata sandi baru">
        @if ($generatedPassword)
            <x-ui.alert type="success">
                <code class="text-lg font-bold">{{ $generatedPassword }}</code>
                <p class="mt-1 text-xs">Sampaikan kepada pengguna. Kata sandi ini hanya ditampilkan satu kali.</p>
            </x-ui.alert>
        @endif

        <div class="mt-4 flex justify-end">
            <x-ui.button variant="secondary" x-on:click="$dispatch('close-modal')">Tutup</x-ui.button>
        </div>
    </x-ui.modal>
</div>
