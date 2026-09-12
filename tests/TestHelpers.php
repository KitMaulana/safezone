<?php

namespace Tests;

use App\Enums\PermitStatus;
use App\Enums\Role;
use App\Models\AcademicYear;
use App\Models\Gate;
use App\Models\ParentGuardian;
use App\Models\Student;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehiclePermit;
use App\Services\AccountService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/** Pembantu pembuatan data untuk pengujian. */
class TestHelpers
{
    public static function academicYear(): AcademicYear
    {
        return AcademicYear::firstOrCreate(
            ['code' => '2627'],
            [
                'name' => '2026/2027',
                'start_date' => '2026-07-14',
                'end_date' => now()->addYear()->toDateString(),
                'is_active' => true,
            ]
        );
    }

    public static function gate(): Gate
    {
        return Gate::firstOrCreate(['name' => 'Gerbang Utama'], ['is_active' => true]);
    }

    public static function user(Role $role, array $attributes = []): User
    {
        return User::create(array_merge([
            'name' => 'Pengguna '.$role->value,
            'username' => $role->value.'-'.Str::random(6),
            'password' => Hash::make('password'),
            'role' => $role,
            'is_active' => true,
            'must_change_password' => false,
        ], $attributes));
    }

    public static function student(array $attributes = []): Student
    {
        $student = Student::create(array_merge([
            'nisn' => (string) random_int(1000000000, 9999999999),
            'name' => 'Siswa Uji',
            'gender' => 'L',
            'birth_date' => '2009-01-01',
            'class_room' => 'XII IPA 1',
            'academic_year_id' => self::academicYear()->id,
            'address' => 'Ciruas',
            'phone' => '081200000000',
        ], $attributes));

        app(AccountService::class)->ensureForStudent($student->fresh());

        return $student->fresh();
    }

    public static function parentFor(Student $student, array $attributes = []): ParentGuardian
    {
        $parent = ParentGuardian::create(array_merge([
            'name' => 'Orang Tua Uji',
            'phone' => '0812'.random_int(10000000, 99999999),
            'relationship' => 'ibu',
        ], $attributes));

        app(AccountService::class)->ensureForParent($parent->fresh());

        $student->parents()->syncWithoutDetaching([$parent->id => ['is_primary' => true]]);

        return $parent->fresh();
    }

    public static function vehicle(Student $student, array $attributes = []): Vehicle
    {
        return Vehicle::create(array_merge([
            'student_id' => $student->id,
            'plate_number' => 'A'.random_int(1000, 9999).'XY',
            'brand' => 'Honda',
            'model' => 'Beat',
            'color' => 'Hitam',
            'year' => 2020,
        ], $attributes));
    }

    /** Kendaraan + stiker aktif siap dipindai. */
    public static function activePermit(Student $student, array $permitAttributes = []): VehiclePermit
    {
        $vehicle = self::vehicle($student);

        return VehiclePermit::create(array_merge([
            'vehicle_id' => $vehicle->id,
            'permit_number' => 'SSZ-2627-'.str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT),
            'qr_token' => Str::random(32),
            'status' => PermitStatus::Active,
            'issued_at' => now(),
            'expires_at' => now()->addMonths(6)->toDateString(),
        ], $permitAttributes));
    }
}
