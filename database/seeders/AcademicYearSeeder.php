<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Gate;
use Illuminate\Database\Seeder;

class AcademicYearSeeder extends Seeder
{
    public function run(): void
    {
        AcademicYear::updateOrCreate(
            ['code' => '2627'],
            [
                'name' => '2026/2027',
                'start_date' => '2026-07-14',
                'end_date' => '2027-06-30',
                'is_active' => true,
            ]
        );

        Gate::firstOrCreate(['name' => 'Gerbang Utama'], ['is_active' => true]);
        Gate::firstOrCreate(['name' => 'Gerbang Belakang'], ['is_active' => true]);
    }
}
