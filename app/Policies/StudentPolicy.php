<?php

namespace App\Policies;

use App\Models\Student;
use App\Models\User;

class StudentPolicy
{
    /** Admin & petugas boleh melihat semua data siswa. */
    public function viewAny(User $user): bool
    {
        return $user->isStaff();
    }

    public function view(User $user, Student $student): bool
    {
        if ($user->isStaff()) {
            return true;
        }

        if ($student->user_id === $user->id) {
            return true;
        }

        return $this->isGuardianOf($user, $student);
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Student $student): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Student $student): bool
    {
        return $user->isAdmin();
    }

    public function block(User $user, Student $student): bool
    {
        return $user->isAdmin();
    }

    private function isGuardianOf(User $user, Student $student): bool
    {
        $parent = $user->relationLoaded('parentGuardian') ? $user->parentGuardian : $user->parentGuardian()->first();

        if (! $parent) {
            return false;
        }

        return $parent->students()->whereKey($student->getKey())->exists();
    }
}
