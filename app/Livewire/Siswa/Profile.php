<?php

namespace App\Livewire\Siswa;

use App\Models\DataChangeRequest;
use App\Models\Student;
use App\Services\AuditService;
use App\Services\PhotoService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.mobile')]
#[Title('Profil Siswa')]
class Profile extends Component
{
    use WithFileUploads;

    public Student $student;

    public string $phone = '';

    public $photo = null;

    // Pengajuan perubahan data yang butuh persetujuan admin.
    public string $requestField = 'address';

    public string $requestValue = '';

    // Ganti kata sandi
    public string $current_password = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function mount(): void
    {
        $this->student = Student::where('user_id', auth()->id())->firstOrFail();
        $this->phone = (string) $this->student->phone;
    }

    /** Nomor HP sendiri boleh diubah langsung. */
    public function savePhone(AuditService $audit): void
    {
        $this->validate([
            'phone' => ['required', 'string', 'max:20'],
        ], attributes: ['phone' => 'nomor HP']);

        $before = ['phone' => $this->student->phone];

        $this->student->update(['phone' => $this->phone]);
        auth()->user()->forceFill(['phone' => $this->phone])->save();

        $audit->log('student.self_update_phone', $this->student, $before, ['phone' => $this->phone]);

        $this->dispatch('toast', type: 'success', message: 'Nomor HP diperbarui.');
    }

    public function savePhoto(PhotoService $photos): void
    {
        $this->validate([
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], attributes: ['photo' => 'foto']);

        $this->student->update([
            'photo_path' => $photos->store($this->photo, 'students', $this->student->photo_path),
        ]);

        $this->reset('photo');
        $this->student->refresh();

        $this->dispatch('toast', type: 'success', message: 'Foto diperbarui.');
    }

    /** Alamat & data orang tua hanya berubah setelah disetujui admin. */
    public function submitRequest(): void
    {
        $this->validate([
            'requestField' => ['required', 'in:address,parent_phone,parent_name'],
            'requestValue' => ['required', 'string', 'min:3', 'max:255'],
        ], attributes: ['requestField' => 'data', 'requestValue' => 'nilai baru']);

        $primaryParent = $this->student->primaryParent();

        $oldValue = match ($this->requestField) {
            'address' => $this->student->address,
            'parent_phone' => $primaryParent?->phone,
            'parent_name' => $primaryParent?->name,
        };

        DataChangeRequest::create([
            'student_id' => $this->student->id,
            'field' => $this->requestField,
            'old_value' => $oldValue,
            'new_value' => $this->requestValue,
            'status' => 'pending',
        ]);

        $this->reset('requestValue');

        $this->dispatch('toast', type: 'success', message: 'Pengajuan dikirim. Menunggu persetujuan admin.');
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
        $this->student->loadMissing('parents');

        return view('livewire.siswa.profile', [
            'requests' => DataChangeRequest::where('student_id', $this->student->id)
                ->latest()
                ->limit(10)
                ->get(),
            'primaryParent' => $this->student->primaryParent(),
        ]);
    }
}
