<?php

namespace App\Livewire\Admin\Users;

use App\Enums\Role;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Manajemen Pengguna')]
class Index extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $role = '';

    public ?int $editingId = null;

    public string $name = '';

    public string $username = '';

    public string $email = '';

    public string $phone = '';

    public string $form_role = 'petugas';

    public bool $is_active = true;

    public ?string $generatedPassword = null;

    public function updated($property): void
    {
        if (in_array($property, ['search', 'role'], true)) {
            $this->resetPage();
        }
    }

    public function create(): void
    {
        $this->reset('editingId', 'name', 'username', 'email', 'phone', 'generatedPassword');
        $this->form_role = 'petugas';
        $this->is_active = true;
        $this->resetValidation();
        $this->dispatch('open-modal', 'pengguna');
    }

    public function edit(int $id): void
    {
        $user = User::findOrFail($id);

        $this->editingId = $user->id;
        $this->name = $user->name;
        $this->username = $user->username;
        $this->email = (string) $user->email;
        $this->phone = (string) $user->phone;
        $this->form_role = $user->role->value;
        $this->is_active = $user->is_active;
        $this->generatedPassword = null;

        $this->resetValidation();
        $this->dispatch('open-modal', 'pengguna');
    }

    public function save(AuditService $audit): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:50', Rule::unique('users', 'username')->ignore($this->editingId)->whereNull('deleted_at')],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->editingId)->whereNull('deleted_at')],
            'phone' => ['nullable', 'string', 'max:20'],
            'form_role' => ['required', 'in:admin,petugas,siswa,orangtua'],
        ], attributes: ['name' => 'nama', 'form_role' => 'role']);

        $isNew = ! $this->editingId;
        $password = null;

        $payload = [
            'name' => $this->name,
            'username' => $this->username,
            'email' => $this->email ?: null,
            'phone' => $this->phone ?: null,
            'role' => $this->form_role,
            'is_active' => $this->is_active,
        ];

        if ($isNew) {
            $password = Str::password(10, true, true, false);
            $payload['password'] = Hash::make($password);
            $payload['must_change_password'] = true;
        }

        $user = $this->editingId
            ? tap(User::findOrFail($this->editingId))->update($payload)
            : User::create($payload);

        $audit->log($isNew ? 'user.create' : 'user.update', $user, null, $user->only(['username', 'role', 'is_active']));

        $this->generatedPassword = $password;

        if (! $isNew) {
            $this->dispatch('close-modal');
        }

        $this->dispatch('toast', type: 'success', message: $isNew ? 'Akun dibuat.' : 'Akun diperbarui.');
    }

    /** Kata sandi acak ditampilkan satu kali saja. */
    public function resetPassword(int $id, AuditService $audit): void
    {
        $user = User::findOrFail($id);
        $password = Str::password(10, true, true, false);

        $user->forceFill([
            'password' => Hash::make($password),
            'must_change_password' => true,
        ])->save();

        $audit->log('user.reset_password', $user);

        $this->editingId = $user->id;
        $this->generatedPassword = $password;

        $this->dispatch('open-modal', 'sandi');
    }

    public function toggleActive(int $id, AuditService $audit): void
    {
        $user = User::findOrFail($id);

        if ($user->id === auth()->id()) {
            $this->dispatch('toast', type: 'danger', message: 'Anda tidak dapat menonaktifkan akun sendiri.');

            return;
        }

        $user->update(['is_active' => ! $user->is_active]);

        $audit->log('user.toggle_active', $user, null, ['is_active' => $user->is_active]);

        $this->dispatch('toast', type: 'success', message: $user->is_active ? 'Akun diaktifkan.' : 'Akun dinonaktifkan.');
    }

    public function forcePasswordChange(int $id): void
    {
        User::findOrFail($id)->update(['must_change_password' => true]);

        $this->dispatch('toast', type: 'success', message: 'Pengguna akan diminta mengganti kata sandi saat login berikutnya.');
    }

    public function render()
    {
        return view('livewire.admin.users.index', [
            'users' => User::query()
                ->when($this->role, fn ($q) => $q->where('role', $this->role))
                ->when($this->search, function ($q) {
                    $term = '%'.$this->search.'%';
                    $q->where(fn ($sub) => $sub->where('name', 'like', $term)->orWhere('username', 'like', $term));
                })
                ->orderBy('role')
                ->orderBy('name')
                ->paginate(25),
            'roles' => Role::cases(),
        ]);
    }
}
