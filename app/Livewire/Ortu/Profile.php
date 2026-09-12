<?php

namespace App\Livewire\Ortu;

use App\Models\ParentGuardian;
use App\Services\AuditService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.mobile')]
#[Title('Profil Orang Tua')]
class Profile extends Component
{
    public ParentGuardian $parent;

    public string $phone_alt = '';

    public string $current_password = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function mount(): void
    {
        $this->parent = ParentGuardian::where('user_id', auth()->id())->firstOrFail();
        $this->phone_alt = (string) $this->parent->phone_alt;
    }

    public function saveAltPhone(): void
    {
        $this->validate([
            'phone_alt' => ['nullable', 'string', 'max:20'],
        ], attributes: ['phone_alt' => 'nomor HP alternatif']);

        $this->parent->update(['phone_alt' => $this->phone_alt ?: null]);

        $this->dispatch('toast', type: 'success', message: 'Nomor HP alternatif disimpan.');
    }

    public function changePassword(AuditService $audit): void
    {
        $this->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        if (! Hash::check($this->current_password, auth()->user()->password)) {
            throw ValidationException::withMessages(['current_password' => 'Kata sandi saat ini salah.']);
        }

        auth()->user()->forceFill([
            'password' => Hash::make($this->password),
            'must_change_password' => false,
        ])->save();

        $audit->log('user.password_changed', auth()->user());

        $this->reset('current_password', 'password', 'password_confirmation');

        $this->dispatch('toast', type: 'success', message: 'Kata sandi diperbarui.');
    }

    /** Hapus satu perangkat yang berlangganan notifikasi push. */
    public function removeDevice(int $id): void
    {
        auth()->user()->pushSubscriptions()->whereKey($id)->delete();

        $this->dispatch('toast', type: 'success', message: 'Perangkat dihapus dari daftar notifikasi.');
    }

    public function render()
    {
        return view('livewire.ortu.profile', [
            'children' => $this->parent->students()->get(['students.id', 'students.name', 'students.class_room']),
            'devices' => auth()->user()->pushSubscriptions()->get(),
        ]);
    }
}
