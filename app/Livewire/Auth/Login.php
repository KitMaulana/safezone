<?php

namespace App\Livewire\Auth;

use App\Models\ParentGuardian;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.public')]
#[Title('Masuk — School Safe Zone')]
class Login extends Component
{
    public string $username = '';

    public string $password = '';

    public bool $remember = false;

    public function mount(): void
    {
        if (Auth::check()) {
            $this->redirectRoute(Auth::user()->role->homeRoute(), navigate: false);
        }
    }

    public function login(): void
    {
        $this->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], attributes: [
            'username' => 'Username / NISN / No. HP',
            'password' => 'kata sandi',
        ]);

        $this->ensureIsNotRateLimited();

        $user = $this->findUser($this->username);

        if (! $user || ! Auth::attempt(['username' => $user->username, 'password' => $this->password], $this->remember)) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages(['username' => __('auth.failed')]);
        }

        if (! $user->is_active) {
            Auth::logout();

            throw ValidationException::withMessages(['username' => __('auth.inactive')]);
        }

        RateLimiter::clear($this->throttleKey());
        session()->regenerate();

        $user->forceFill(['last_login_at' => now()])->save();

        $this->redirectRoute(
            $user->must_change_password ? 'password.change' : $user->role->homeRoute(),
            navigate: false
        );
    }

    /** Terima username admin/petugas, NISN siswa, atau nomor HP orang tua (dinormalisasi). */
    private function findUser(string $input): ?User
    {
        $input = trim($input);

        $user = User::where('username', $input)->first();

        if ($user) {
            return $user;
        }

        $phone = ParentGuardian::normalizePhone($input);

        return $phone ? User::where('username', $phone)->first() : null;
    }

    private function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        throw ValidationException::withMessages([
            'username' => __('auth.throttle', ['seconds' => RateLimiter::availableIn($this->throttleKey())]),
        ]);
    }

    private function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->username).'|'.request()->ip());
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}
