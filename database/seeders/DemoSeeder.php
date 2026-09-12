<?php

namespace Database\Seeders;

use App\Enums\PermitStatus;
use App\Enums\StudentStatus;
use App\Enums\ViolationCategory;
use App\Models\AcademicYear;
use App\Models\AttendanceLog;
use App\Models\Gate;
use App\Models\ParentGuardian;
use App\Models\Student;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehiclePermit;
use App\Models\Violation;
use App\Services\AccountService;
use App\Services\PermitService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DemoSeeder extends Seeder
{
    /** @var array<int, array{nisn:string, name:string, gender:string, birth:string, class:string, parent:string, parent_phone:string, rel:string}> */
    private array $rows = [
        ['nisn' => '0012345678', 'name' => 'Ken Arya Pratama', 'gender' => 'L', 'birth' => '2009-01-01', 'class' => 'XII IPA 1', 'parent' => 'Siti Rahmawati', 'parent_phone' => '081234567890', 'rel' => 'ibu'],
        ['nisn' => '0023456781', 'name' => 'Rizky Maulana', 'gender' => 'L', 'birth' => '2009-03-12', 'class' => 'XII IPA 1', 'parent' => 'Bambang Sugianto', 'parent_phone' => '081211122233', 'rel' => 'ayah'],
        ['nisn' => '0034567812', 'name' => 'Nabila Putri Anggraini', 'gender' => 'P', 'birth' => '2009-05-20', 'class' => 'XII IPA 2', 'parent' => 'Dewi Lestari', 'parent_phone' => '081233344455', 'rel' => 'ibu'],
        ['nisn' => '0045678123', 'name' => 'Fajar Nugroho', 'gender' => 'L', 'birth' => '2008-11-02', 'class' => 'XII IPA 2', 'parent' => 'Agus Setiawan', 'parent_phone' => '081255566677', 'rel' => 'ayah'],
        ['nisn' => '0056781234', 'name' => 'Salsabila Ramadhani', 'gender' => 'P', 'birth' => '2009-07-18', 'class' => 'XII IPS 1', 'parent' => 'Yuliana Sari', 'parent_phone' => '081277788899', 'rel' => 'ibu'],
        ['nisn' => '0067812345', 'name' => 'Dimas Adi Saputra', 'gender' => 'L', 'birth' => '2009-02-25', 'class' => 'XII IPS 1', 'parent' => 'Haryanto', 'parent_phone' => '081299900011', 'rel' => 'ayah'],
        ['nisn' => '0078123456', 'name' => 'Alya Zahra Fauziah', 'gender' => 'P', 'birth' => '2009-09-09', 'class' => 'XII IPS 2', 'parent' => 'Ratna Kurniasih', 'parent_phone' => '081322233344', 'rel' => 'ibu'],
        ['nisn' => '0081234567', 'name' => 'Bayu Kurniawan', 'gender' => 'L', 'birth' => '2008-12-30', 'class' => 'XII IPS 2', 'parent' => 'Sukirman', 'parent_phone' => '081344455566', 'rel' => 'ayah'],
        ['nisn' => '0091234568', 'name' => 'Intan Permatasari', 'gender' => 'P', 'birth' => '2009-04-04', 'class' => 'XII IPA 3', 'parent' => 'Endang Wahyuni', 'parent_phone' => '081366677788', 'rel' => 'ibu'],
        ['nisn' => '0102345679', 'name' => 'Galih Prasetyo', 'gender' => 'L', 'birth' => '2009-06-15', 'class' => 'XII IPA 3', 'parent' => 'Tono Hartono', 'parent_phone' => '081388899900', 'rel' => 'ayah'],
        ['nisn' => '0113456780', 'name' => 'Mutiara Hasanah', 'gender' => 'P', 'birth' => '2009-08-08', 'class' => 'XII IPS 3', 'parent' => 'Nur Aisyah', 'parent_phone' => '081311122234', 'rel' => 'wali'],
        ['nisn' => '0124567891', 'name' => 'Arif Rahman Hakim', 'gender' => 'L', 'birth' => '2009-10-21', 'class' => 'XII IPS 3', 'parent' => 'Slamet Riyadi', 'parent_phone' => '081355566678', 'rel' => 'ayah'],
    ];

    /** @var array<int, array{plate:string, brand:string, model:string, color:string, year:int}> */
    private array $bikes = [
        ['plate' => 'A1234XY', 'brand' => 'Honda', 'model' => 'Beat', 'color' => 'Hitam', 'year' => 2019],
        ['plate' => 'A2345BC', 'brand' => 'Yamaha', 'model' => 'Mio M3', 'color' => 'Biru', 'year' => 2018],
        ['plate' => 'A3456DE', 'brand' => 'Honda', 'model' => 'Vario 125', 'color' => 'Merah', 'year' => 2020],
        ['plate' => 'A4567FG', 'brand' => 'Suzuki', 'model' => 'Address', 'color' => 'Putih', 'year' => 2017],
        ['plate' => 'A5678HI', 'brand' => 'Honda', 'model' => 'Scoopy', 'color' => 'Krem', 'year' => 2021],
        ['plate' => 'A6789JK', 'brand' => 'Yamaha', 'model' => 'Fino', 'color' => 'Hijau', 'year' => 2019],
        ['plate' => 'A7891LM', 'brand' => 'Honda', 'model' => 'Supra X', 'color' => 'Hitam', 'year' => 2016],
        ['plate' => 'A8912NO', 'brand' => 'Yamaha', 'model' => 'Jupiter Z', 'color' => 'Biru', 'year' => 2015],
    ];

    public function run(): void
    {
        $year = AcademicYear::current();
        $admin = User::where('username', 'admin')->firstOrFail();
        $petugas = User::where('username', 'petugas1')->firstOrFail();
        $gate = Gate::first();

        $accounts = app(AccountService::class);
        $permits = app(PermitService::class);

        $students = collect();

        foreach ($this->rows as $row) {
            $student = Student::updateOrCreate(
                ['nisn' => $row['nisn']],
                [
                    'nis' => '2627'.substr($row['nisn'], -4),
                    'name' => $row['name'],
                    'gender' => $row['gender'],
                    'birth_date' => $row['birth'],
                    'class_room' => $row['class'],
                    'academic_year_id' => $year?->id,
                    'address' => 'Kp. Ciruas, Kec. Ciruas, Kab. Serang',
                    'phone' => '0857'.substr($row['nisn'], -8),
                    'status' => StudentStatus::Active,
                ]
            );

            $accounts->ensureForStudent($student->fresh());

            $parent = ParentGuardian::updateOrCreate(
                ['phone' => $row['parent_phone']],
                [
                    'name' => $row['parent'],
                    'relationship' => $row['rel'],
                    'address' => 'Kp. Ciruas, Kec. Ciruas, Kab. Serang',
                ]
            );

            $accounts->ensureForParent($parent->fresh());

            $student->parents()->syncWithoutDetaching([$parent->id => ['is_primary' => true]]);

            $students->push($student->fresh());
        }

        // 8 kendaraan dengan stiker aktif.
        foreach ($this->bikes as $index => $bike) {
            $student = $students[$index];

            $vehicle = Vehicle::updateOrCreate(
                ['plate_number' => $bike['plate']],
                [
                    'student_id' => $student->id,
                    'brand' => $bike['brand'],
                    'model' => $bike['model'],
                    'color' => $bike['color'],
                    'year' => $bike['year'],
                    'stnk_owner_name' => $student->name,
                    'sim_type' => $index % 3 === 0 ? 'C' : 'tidak_ada',
                    'sim_number' => $index % 3 === 0 ? '3604'.str_pad((string) $index, 8, '0', STR_PAD_LEFT) : null,
                    'requirements' => [
                        'surat_izin_ortu' => true,
                        'fotokopi_stnk' => true,
                        'fotokopi_sim' => $index % 3 === 0,
                        'pernyataan_tata_tertib' => true,
                    ],
                    'is_active' => true,
                ]
            );

            if (! $permits->hasOpenPermit($vehicle)) {
                $permit = $permits->issue($vehicle, $admin, $year);
                $permit->update([
                    'status' => PermitStatus::Active,
                    'printed_at' => now()->subDays(20),
                    'activated_at' => now()->subDays(20),
                    'print_count' => 1,
                ]);
            }
        }

        $this->seedAttendance($students, $petugas, $gate);
        $this->seedBlocked($students, $admin);
        $this->seedViolations($students, $petugas);
    }

    /** 30 hari log kehadiran acak untuk siswa yang punya kendaraan. */
    private function seedAttendance($students, User $petugas, ?Gate $gate): void
    {
        AttendanceLog::query()->delete();

        $withVehicle = $students->take(count($this->bikes));

        for ($day = 30; $day >= 1; $day--) {
            $date = Carbon::now('Asia/Jakarta')->subDays($day);

            if ($date->isWeekend()) {
                continue;
            }

            foreach ($withVehicle as $student) {
                if (random_int(1, 10) === 1) {
                    continue; // sesekali tidak membawa motor
                }

                $vehicle = $student->vehicles()->first();
                $permit = $vehicle?->permits()->latest('id')->first();

                if (! $vehicle || ! $permit) {
                    continue;
                }

                $in = $date->copy()->setTime(6, random_int(30, 59));

                $inLog = AttendanceLog::create([
                    'student_id' => $student->id,
                    'vehicle_id' => $vehicle->id,
                    'permit_id' => $permit->id,
                    'type' => 'in',
                    'kind' => 'normal',
                    'scanned_at' => $in->copy()->utc(),
                    'scanned_by' => $petugas->id,
                    'gate_id' => $gate?->id,
                    'device_info' => 'Android · Chrome',
                ]);

                $earlyLeave = random_int(1, 12) === 1;
                $out = $earlyLeave
                    ? $date->copy()->setTime(9, random_int(0, 59))
                    : $date->copy()->setTime(15, random_int(0, 40));

                AttendanceLog::create([
                    'student_id' => $student->id,
                    'vehicle_id' => $vehicle->id,
                    'permit_id' => $permit->id,
                    'type' => 'out',
                    'kind' => 'normal',
                    'scanned_at' => $out->copy()->utc(),
                    'scanned_by' => $petugas->id,
                    'gate_id' => $gate?->id,
                    'device_info' => 'Android · Chrome',
                    'pair_id' => $inLog->id,
                    'is_early_leave' => $earlyLeave,
                ]);
            }
        }
    }

    /**
     * Dua siswa diblokir sebagai contoh layar MERAH saat scan.
     * Salah satunya sengaja siswa yang punya kendaraan berstiker, agar penolakan
     * scan benar-benar bisa dicoba dari HP petugas.
     */
    private function seedBlocked($students, User $admin): void
    {
        $cases = [
            [$students[7], 'Berulang kali tidak memakai helm di area sekolah.', 'pelanggaran', now()->addDays(7)->toDateString()],
            [$students[11], 'Belum melengkapi surat izin orang tua dan fotokopi STNK.', 'administrasi', null],
        ];

        foreach ($cases as [$student, $reason, $type, $until]) {
            $student->update([
                'status' => StudentStatus::Blocked,
                'blocked_reason' => $reason,
                'blocked_type' => $type,
                'blocked_at' => now()->subDays(3),
                'blocked_until' => $until,
                'blocked_by' => $admin->id,
            ]);

            // Stiker aktif miliknya ikut ditangguhkan, sama seperti alur blokir di panel admin.
            VehiclePermit::whereIn('vehicle_id', $student->vehicles()->pluck('id'))
                ->where('status', PermitStatus::Active->value)
                ->update(['status' => PermitStatus::Suspended->value]);
        }
    }

    private function seedViolations($students, User $petugas): void
    {
        Violation::query()->delete();

        $samples = [
            [$students[0], ViolationCategory::TidakPakaiHelm, 'Tidak memakai helm saat masuk gerbang.'],
            [$students[1], ViolationCategory::ParkirSembarangan, 'Parkir di luar area yang ditentukan.'],
            [$students[10], ViolationCategory::KnalpotBising, 'Knalpot tidak standar, mengganggu KBM.'],
        ];

        foreach ($samples as [$student, $category, $description]) {
            Violation::create([
                'student_id' => $student->id,
                'reported_by' => $petugas->id,
                'category' => $category,
                'description' => $description,
                'points' => $category->defaultPoints(),
                'occurred_at' => now()->subDays(random_int(1, 14)),
            ]);
        }
    }
}
