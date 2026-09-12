<?php

namespace App\Livewire\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.public')]
#[Title('Lupa Kata Sandi')]
class ForgotPassword extends Component
{
    public string $username = '';

    public ?string $status = null;

    public ?string $noEmailNotice = null;

    public function send(): void
    {
        $this->validate(['username' => ['required', 'string']], attributes: ['username' => 'Username / NISN / No. HP']);

        $this->status = null;
        $this->noEmailNotice = null;

        $user = User::where('username', trim($this->username))->first();

        // Tanpa email terdaftar, pemulihan hanya bisa lewat admin sekolah.
        if (! $user || blank($user->email)) {
            $this->noEmailNotice = 'Akun ini tidak memiliki alamat email. Silakan hubungi admin sekolah untuk mengatur ulang kata sandi Anda.';

            return;
        }

        $result = Password::sendResetLink(['email' => $user->email]);

        $this->status = $result === Password::RESET_LINK_SENT
            ? __('passwords.sent')
            : __($result);
    }

    public function render()
    {
        return view('livewire.auth.forgot-password');
    }
}
