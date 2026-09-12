<?php

namespace App\Services;

use App\Enums\Role;
use App\Models\ParentGuardian;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

/**
 * Aturan akun otomatis (SPEC §5.10).
 * - Siswa : username = NISN, password awal = tanggal lahir ddmmyyyy (atau "sman1ciruas").
 * - Ortu  : username = nomor HP ternormalisasi, password awal = 6 digit terakhir nomor HP.
 */
class AccountService
{
    public const STUDENT_FALLBACK_PASSWORD = 'sman1ciruas';

    public function defaultStudentPassword(Student $student): string
    {
        return $student->birth_date?->format('dmY') ?: self::STUDENT_FALLBACK_PASSWORD;
    }

    public function defaultParentPassword(string $phone): string
    {
        $normalized = ParentGuardian::normalizePhone($phone) ?? '';

        return strlen($normalized) >= 6 ? substr($normalized, -6) : str_pad($normalized, 6, '0');
    }

    public function createForStudent(Student $student): User
    {
        $user = User::create([
            'name' => $student->name,
            'username' => $student->nisn,
            'phone' => $student->phone,
            'password' => Hash::make($this->defaultStudentPassword($student)),
            'role' => Role::Siswa,
            'is_active' => true,
            'must_change_password' => true,
        ]);

        $student->forceFill(['user_id' => $user->id])->save();

        return $user;
    }

    public function createForParent(ParentGuardian $parent): User
    {
        $user = User::create([
            'name' => $parent->name,
            'username' => $parent->phone,
            'phone' => $parent->phone,
            'password' => Hash::make($this->defaultParentPassword($parent->phone)),
            'role' => Role::OrangTua,
            'is_active' => true,
            'must_change_password' => true,
        ]);

        $parent->forceFill(['user_id' => $user->id])->save();

        return $user;
    }

    /** Buat akun bila belum ada; kembalikan akun yang sudah ada bila sudah dibuat. */
    public function ensureForStudent(Student $student): User
    {
        return $student->user ?? $this->createForStudent($student);
    }

    public function ensureForParent(ParentGuardian $parent): User
    {
        return $parent->user ?? $this->createForParent($parent);
    }
}
