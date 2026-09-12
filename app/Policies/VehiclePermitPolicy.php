<?php

namespace App\Policies;

use App\Models\User;
use App\Models\VehiclePermit;

class VehiclePermitPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isStaff();
    }

    public function view(User $user, VehiclePermit $permit): bool
    {
        $vehicle = $permit->relationLoaded('vehicle') ? $permit->vehicle : $permit->vehicle()->first();

        return $vehicle ? app(VehiclePolicy::class)->view($user, $vehicle) : $user->isStaff();
    }

    /** Hanya admin yang boleh menerbitkan, mencetak, menangguhkan, dan mencabut stiker. */
    public function manage(User $user): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, VehiclePermit $permit): bool
    {
        return $user->isAdmin();
    }

    public function print(User $user, VehiclePermit $permit): bool
    {
        return $user->isAdmin();
    }
}
