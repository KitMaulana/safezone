<?php

namespace App\Livewire\Admin\Students;

use App\Models\Student;
use App\Services\StudentImportService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
#[Title('Impor Siswa')]
class Import extends Component
{
    use WithFileUploads;

    public $file = null;

    /** @var array<int, array<string, mixed>> */
    public array $rows = [];

    /** @var array<int, array<int, string>> */
    public array $rowErrors = [];

    public ?array $summary = null;

    public function mount(): void
    {
        $this->authorize('create', Student::class);
    }

    public function updatedFile(StudentImportService $importer): void
    {
        $this->validate([
            'file' => ['required', 'file', 'mimes:csv,txt,xlsx,xls', 'max:4096'],
        ], attributes: ['file' => 'berkas']);

        $this->summary = null;
        $this->rows = $importer->readRows($this->file->getRealPath());
        $this->rowErrors = [];

        foreach ($this->rows as $index => $row) {
            $errors = $importer->validateRow($row, $index);

            if ($errors) {
                $this->rowErrors[$index] = $errors;
            }
        }
    }

    public function import(StudentImportService $importer): void
    {
        $this->authorize('create', Student::class);

        abort_if(empty($this->rows), 422, 'Tidak ada baris yang bisa diimpor.');

        $this->summary = $importer->import($this->rows);
        $this->reset('rows', 'rowErrors', 'file');

        $this->dispatch('toast', type: 'success', message: 'Impor selesai: '
            .$this->summary['created'].' dibuat, '
            .$this->summary['updated'].' diperbarui, '
            .$this->summary['skipped'].' dilewati.');
    }

    public function downloadTemplate(StudentImportService $importer)
    {
        return response()->streamDownload(
            fn () => print ($importer->templateCsv()),
            'template-impor-siswa.csv',
            ['Content-Type' => 'text/csv']
        );
    }

    public function render()
    {
        return view('livewire.admin.students.import', [
            'columns' => StudentImportService::COLUMNS,
            'preview' => array_slice($this->rows, 0, 10, true),
            'validCount' => count($this->rows) - count($this->rowErrors),
        ]);
    }
}
