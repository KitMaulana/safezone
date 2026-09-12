<?php

namespace App\Livewire\Admin;

use App\Models\AcademicYear;
use App\Models\Gate;
use App\Services\AuditService;
use App\Services\SettingService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Pengaturan')]
class Settings extends Component
{
    /** @var array<string, string> */
    public array $form = [];

    // Tahun ajaran
    public ?int $yearId = null;

    public string $yearName = '';

    public string $yearCode = '';

    public string $yearStart = '';

    public string $yearEnd = '';

    // Gerbang
    public ?int $gateId = null;

    public string $gateName = '';

    public function mount(SettingService $settings): void
    {
        foreach (SettingService::DEFAULTS as $key => $default) {
            $this->form[$key] = (string) $settings->get($key, $default);
        }
    }

    public function save(SettingService $settings, AuditService $audit): void
    {
        $this->validate([
            'form.school_name' => ['required', 'string', 'max:255'],
            'form.school_address' => ['nullable', 'string', 'max:500'],
            'form.school_phone' => ['nullable', 'string', 'max:50'],
            'form.police_phone' => ['nullable', 'string', 'max:20'],
            'form.headmaster_name' => ['nullable', 'string', 'max:255'],
            'form.whatsapp_admin' => ['nullable', 'string', 'max:30'],
            'form.late_alert_time' => ['required', 'date_format:H:i'],
            'form.checkin_open_time' => ['required', 'date_format:H:i'],
            'form.checkout_min_time' => ['required', 'date_format:H:i'],
            'form.violation_block_threshold' => ['required', 'integer', 'min:1', 'max:100'],
            'form.sticker_footer_text' => ['nullable', 'string', 'max:120'],
        ], [], [
            'form.school_name' => 'nama sekolah',
            'form.late_alert_time' => 'jam pengingat belum cek in',
            'form.checkin_open_time' => 'jam gerbang dibuka',
            'form.checkout_min_time' => 'jam minimal cek out',
            'form.violation_block_threshold' => 'ambang poin blokir',
        ]);

        // Checkbox dikirim sebagai boolean; normalkan ke "1"/"0".
        foreach (['auto_activate_on_print', 'public_show_emergency_phone', 'sticker_show_class', 'sticker_print_back'] as $key) {
            $this->form[$key] = filter_var($this->form[$key] ?? false, FILTER_VALIDATE_BOOLEAN) ? '1' : '0';
        }

        $settings->setMany($this->form);
        $audit->log('settings.update', null, null, $this->form);

        $this->dispatch('toast', type: 'success', message: 'Pengaturan disimpan.');
    }

    public function editYear(int $id): void
    {
        $year = AcademicYear::findOrFail($id);

        $this->yearId = $year->id;
        $this->yearName = $year->name;
        $this->yearCode = $year->code;
        $this->yearStart = $year->start_date->format('Y-m-d');
        $this->yearEnd = $year->end_date->format('Y-m-d');

        $this->dispatch('open-modal', 'tahun');
    }

    public function newYear(): void
    {
        $this->reset('yearId', 'yearName', 'yearCode', 'yearStart', 'yearEnd');
        $this->resetValidation();
        $this->dispatch('open-modal', 'tahun');
    }

    public function saveYear(): void
    {
        $this->validate([
            'yearName' => ['required', 'string', 'max:20'],
            'yearCode' => ['required', 'string', 'size:4'],
            'yearStart' => ['required', 'date'],
            'yearEnd' => ['required', 'date', 'after:yearStart'],
        ], attributes: [
            'yearName' => 'nama tahun ajaran', 'yearCode' => 'kode',
            'yearStart' => 'tanggal mulai', 'yearEnd' => 'tanggal selesai',
        ]);

        $payload = [
            'name' => $this->yearName,
            'code' => $this->yearCode,
            'start_date' => $this->yearStart,
            'end_date' => $this->yearEnd,
        ];

        $this->yearId
            ? AcademicYear::findOrFail($this->yearId)->update($payload)
            : AcademicYear::create($payload);

        $this->dispatch('close-modal');
        $this->dispatch('toast', type: 'success', message: 'Tahun ajaran disimpan.');
    }

    public function activateYear(int $id, AuditService $audit): void
    {
        AcademicYear::query()->update(['is_active' => false]);
        $year = AcademicYear::findOrFail($id);
        $year->update(['is_active' => true]);

        $audit->log('academic_year.activate', $year);

        $this->dispatch('toast', type: 'success', message: 'Tahun ajaran '.$year->name.' diaktifkan.');
    }

    public function saveGate(): void
    {
        $this->validate([
            'gateName' => ['required', 'string', 'max:100'],
        ], attributes: ['gateName' => 'nama gerbang']);

        $this->gateId
            ? Gate::findOrFail($this->gateId)->update(['name' => $this->gateName])
            : Gate::create(['name' => $this->gateName]);

        $this->reset('gateId', 'gateName');
        $this->dispatch('close-modal');
        $this->dispatch('toast', type: 'success', message: 'Gerbang disimpan.');
    }

    public function editGate(int $id): void
    {
        $gate = Gate::findOrFail($id);

        $this->gateId = $gate->id;
        $this->gateName = $gate->name;

        $this->dispatch('open-modal', 'gerbang');
    }

    public function newGate(): void
    {
        $this->reset('gateId', 'gateName');
        $this->resetValidation();
        $this->dispatch('open-modal', 'gerbang');
    }

    public function toggleGate(int $id): void
    {
        $gate = Gate::findOrFail($id);
        $gate->update(['is_active' => ! $gate->is_active]);

        $this->dispatch('toast', type: 'success', message: 'Status gerbang diperbarui.');
    }

    public function render()
    {
        return view('livewire.admin.settings', [
            'years' => AcademicYear::orderByDesc('start_date')->get(),
            'gates' => Gate::orderBy('name')->get(),
        ]);
    }
}
