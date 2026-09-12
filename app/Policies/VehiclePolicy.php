<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Vehicle;

class VehiclePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isStaff();
    }

    public function view(User $user, Vehicle $vehicle): bool
    {
        $student = $vehicle->relationLoaded('student') ? $vehicle->student : $vehicle->student()->first();

        return $student ? app(StudentPolicy::class)->view($user, $student) : $user->isStaff();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Vehicle $vehicle): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Vehicle $vehicle): bool
    {
        return $user->isAdmin();
    }
}
