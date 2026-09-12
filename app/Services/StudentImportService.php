<?php

namespace App\Services;

use App\Enums\StudentStatus;
use App\Models\AcademicYear;
use App\Models\ParentGuardian;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Impor siswa dari CSV/XLSX.
 * Kolom: nisn, nis, nama, jk, tgl_lahir, kelas, alamat, hp_siswa, nama_ortu, hp_ortu, hubungan
 */
class StudentImportService
{
    public const COLUMNS = [
        'nisn', 'nis', 'nama', 'jk', 'tgl_lahir', 'kelas',
        'alamat', 'hp_siswa', 'nama_ortu', 'hp_ortu', 'hubungan',
    ];

    public function __construct(
        private readonly AccountService $accounts,
        private readonly AuditService $audit,
    ) {}

    /** Baca berkas menjadi larik asosiatif berdasarkan baris header. */
    public function readRows(string $absolutePath): array
    {
        $extension = strtolower(pathinfo($absolutePath, PATHINFO_EXTENSION));

        $raw = $extension === 'csv'
            ? $this->readCsv($absolutePath)
            : $this->readSpreadsheet($absolutePath);

        if (count($raw) < 2) {
            return [];
        }

        $header = array_map(
            fn ($h) => str_replace(' ', '_', strtolower(trim((string) $h))),
            array_shift($raw)
        );

        $rows = [];

        foreach ($raw as $line) {
            if (count(array_filter($line, fn ($v) => filled($v))) === 0) {
                continue;
            }

            $row = [];

            foreach ($header as $i => $key) {
                $row[$key] = isset($line[$i]) ? trim((string) $line[$i]) : null;
            }

            $rows[] = $row;
        }

        return $rows;
    }

    /** Validasi satu baris; mengembalikan daftar pesan kesalahan (kosong bila valid). */
    public function validateRow(array $row, int $index): array
    {
        $validator = Validator::make($row, [
            'nisn' => ['required', 'digits_between:6,10'],
            'nama' => ['required', 'string', 'max:255'],
            'jk' => ['required', 'in:L,P,l,p'],
            'tgl_lahir' => ['nullable', 'date'],
            'kelas' => ['required', 'string', 'max:50'],
            'hp_ortu' => ['nullable', 'string'],
            'nama_ortu' => ['nullable', 'string', 'max:255'],
            'hubungan' => ['nullable', 'in:ayah,ibu,wali'],
        ], [], [
            'nisn' => 'NISN', 'nama' => 'nama', 'jk' => 'jenis kelamin',
            'tgl_lahir' => 'tanggal lahir', 'kelas' => 'kelas',
            'hp_ortu' => 'HP orang tua', 'nama_ortu' => 'nama orang tua',
        ]);

        $errors = $validator->errors()->all();

        if (filled($row['nama_ortu'] ?? null) && blank($row['hp_ortu'] ?? null)) {
            $errors[] = 'Nomor HP orang tua wajib diisi bila nama orang tua diisi.';
        }

        return $errors;
    }

    /**
     * Jalankan impor. Mengembalikan ringkasan: dibuat, diperbarui, dilewati.
     *
     * @return array{created:int, updated:int, skipped:int, errors:array<int, string>}
     */
    public function import(array $rows): array
    {
        $year = AcademicYear::current();
        $summary = ['created' => 0, 'updated' => 0, 'skipped' => 0, 'errors' => []];

        foreach ($rows as $index => $row) {
            $errors = $this->validateRow($row, $index);

            if ($errors) {
                $summary['skipped']++;
                $summary['errors'][] = 'Baris '.($index + 2).': '.implode(' ', $errors);

                continue;
            }

            try {
                DB::transaction(function () use ($row, $year, &$summary) {
                    $existing = Student::where('nisn', $row['nisn'])->first();

                    $student = Student::updateOrCreate(
                        ['nisn' => $row['nisn']],
                        [
                            'nis' => $row['nis'] ?: null,
                            'name' => $row['nama'],
                            'gender' => strtoupper($row['jk']),
                            'birth_date' => $row['tgl_lahir'] ?: null,
                            'class_room' => $row['kelas'],
                            'academic_year_id' => $year?->id,
                            'address' => $row['alamat'] ?: null,
                            'phone' => $row['hp_siswa'] ?: null,
                            'status' => StudentStatus::Active,
                        ]
                    );

                    $this->accounts->ensureForStudent($student->fresh());

                    if (filled($row['hp_ortu'] ?? null)) {
                        $phone = ParentGuardian::normalizePhone($row['hp_ortu']);

                        $parent = ParentGuardian::firstOrNew(['phone' => $phone]);
                        $parent->name = $row['nama_ortu'] ?: ($parent->name ?: 'Orang Tua '.$student->name);
                        $parent->relationship = $row['hubungan'] ?: ($parent->relationship ?: 'wali');
                        $parent->address = $parent->address ?: ($row['alamat'] ?: null);
                        $parent->save();

                        $this->accounts->ensureForParent($parent->fresh());

                        $student->parents()->syncWithoutDetaching([$parent->id => ['is_primary' => true]]);
                    }

                    $existing ? $summary['updated']++ : $summary['created']++;
                });
            } catch (\Throwable $e) {
                $summary['skipped']++;
                $summary['errors'][] = 'Baris '.($index + 2).': '.$e->getMessage();
            }
        }

        $this->audit->log('student.import', null, null, [
            'created' => $summary['created'],
            'updated' => $summary['updated'],
            'skipped' => $summary['skipped'],
        ]);

        return $summary;
    }

    public function templateCsv(): string
    {
        $lines = [
            implode(',', self::COLUMNS),
            '0012345678,26270001,Ken Arya Pratama,L,2009-01-01,XII IPA 1,"Kp. Ciruas, Serang",085712345678,Siti Rahmawati,081234567890,ibu',
            '0023456781,26270002,Rizky Maulana,L,2009-03-12,XII IPA 1,"Kp. Pelawad, Serang",085798765432,Bambang Sugianto,081211122233,ayah',
        ];

        return implode("\n", $lines)."\n";
    }

    private function readCsv(string $path): array
    {
        $rows = [];
        $handle = fopen($path, 'r');

        if ($handle === false) {
            return [];
        }

        // Lewati BOM UTF-8 bila ada.
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        $delimiter = $this->detectDelimiter($path);

        while (($line = fgetcsv($handle, 0, $delimiter)) !== false) {
            $rows[] = $line;
        }

        fclose($handle);

        return $rows;
    }

    private function detectDelimiter(string $path): string
    {
        $firstLine = (string) fgets(fopen($path, 'r'));

        return substr_count($firstLine, ';') > substr_count($firstLine, ',') ? ';' : ',';
    }

    private function readSpreadsheet(string $path): array
    {
        $reader = IOFactory::createReaderForFile($path);
        $reader->setReadDataOnly(true);

        $sheet = $reader->load($path)->getActiveSheet();

        return $sheet->toArray(null, true, false, false);
    }
}
