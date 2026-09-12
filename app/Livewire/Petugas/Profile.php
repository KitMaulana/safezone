<?php

namespace App\Livewire\Petugas;

use App\Models\AttendanceLog;
use App\Services\AuditService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.mobile')]
#[Title('Profil Petugas')]
class Profile extends Component
{
    public string $name = '';

    public string $phone = '';

    public string $current_password = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function mount(): void
    {
        $this->name = auth()->user()->name;
        $this->phone = (string) auth()->user()->phone;
    }

    public function saveProfile(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
        ], attributes: ['name' => 'nama', 'phone' => 'nomor HP']);

        auth()->user()->update(['name' => $this->name, 'phone' => $this->phone ?: null]);

        $this->dispatch('toast', type: 'success', message: 'Profil diperbarui.');
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

    public function render()
    {
        $today = now('Asia/Jakarta')->toDateString();

        return view('livewire.petugas.profile', [
            'scanToday' => AttendanceLog::where('scanned_by', auth()->id())->whereDate('scanned_at', $today)->count(),
            'scanTotal' => AttendanceLog::where('scanned_by', auth()->id())->count(),
        ]);
    }
}
