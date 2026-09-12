<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RoleUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Administrator Sekolah',
                'email' => 'admin@sman1ciruas.sch.id',
                'password' => Hash::make('password'),
                'role' => Role::Admin,
                'is_active' => true,
                'must_change_password' => false,
            ]
        );

        User::updateOrCreate(
            ['username' => 'petugas1'],
            [
                'name' => 'Petugas Gerbang 1',
                'password' => Hash::make('password'),
                'role' => Role::Petugas,
                'is_active' => true,
                'must_change_password' => false,
            ]
        );
    }
}
