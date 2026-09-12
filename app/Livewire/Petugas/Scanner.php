<?php

namespace App\Livewire\Petugas;

use App\Models\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.mobile')]
#[Title('Scan QR')]
class Scanner extends Component
{
    public function render()
    {
        return view('livewire.petugas.scanner', [
            'gates' => Gate::where('is_active', true)->get(['id', 'name']),
        ]);
    }
}
