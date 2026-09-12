<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Cadangan basis data.
 * Memakai mysqldump bila tersedia; bila exec() dinonaktifkan (umum di shared hosting),
 * jatuh ke ekspor per tabel lewat query biasa.
 */
class BackupService
{
    /** Membuat berkas cadangan dan mengembalikan path relatif pada disk lokal. */
    public function create(): string
    {
        $filename = 'backups/ssz-'.now('Asia/Jakarta')->format('Ymd-His').'.sql';

        Storage::disk('local')->makeDirectory('backups');
        Storage::disk('local')->put($filename, $this->tryMysqldump() ?? $this->exportWithQueries());

        return $filename;
    }

    private function tryMysqldump(): ?string
    {
        if (! function_exists('exec') || in_array('exec', array_map('trim', explode(',', (string) ini_get('disable_functions'))), true)) {
            return null;
        }

        $config = config('database.connections.mysql');

        $command = sprintf(
            'mysqldump --host=%s --port=%s --user=%s %s %s 2>&1',
            escapeshellarg((string) $config['host']),
            escapeshellarg((string) $config['port']),
            escapeshellarg((string) $config['username']),
            $config['password'] ? '--password='.escapeshellarg((string) $config['password']) : '',
            escapeshellarg((string) $config['database'])
        );

        $output = [];
        $status = 0;

        @exec($command, $output, $status);

        if ($status !== 0 || empty($output)) {
            return null;
        }

        return implode("\n", $output);
    }

    private function exportWithQueries(): string
    {
        $lines = [
            '-- School Safe Zone backup',
            '-- Basis data: '.config('database.connections.mysql.database'),
            '-- Dibuat: '.now('Asia/Jakarta')->format('Y-m-d H:i:s').' WIB',
            'SET FOREIGN_KEY_CHECKS=0;',
            '',
        ];

        $pdo = DB::connection()->getPdo();

        foreach (DB::select('SHOW TABLES') as $row) {
            $table = array_values((array) $row)[0];

            $lines[] = '-- Tabel '.$table;
            $lines[] = 'TRUNCATE TABLE `'.$table.'`;';

            foreach (DB::table($table)->cursor() as $record) {
                $data = (array) $record;

                $values = array_map(
                    fn ($value) => $value === null ? 'NULL' : $pdo->quote((string) $value),
                    array_values($data)
                );

                $lines[] = 'INSERT INTO `'.$table.'` (`'.implode('`,`', array_keys($data)).'`) VALUES ('.implode(',', $values).');';
            }

            $lines[] = '';
        }

        $lines[] = 'SET FOREIGN_KEY_CHECKS=1;';

        return implode("\n", $lines);
    }
}
