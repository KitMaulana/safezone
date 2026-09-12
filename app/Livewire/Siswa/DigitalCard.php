<?php

namespace App\Livewire\Siswa;

use App\Models\Student;
use App\Services\StickerPdfService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.mobile')]
#[Title('Kartu Digital')]
class DigitalCard extends Component
{
    public function render(StickerPdfService $stickers)
    {
        $student = Student::with(['vehicles.permits' => fn ($q) => $q->orderByDesc('id'), 'parents', 'academicYear'])
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $permit = $student->vehicles->flatMap->permits->first();

        return view('livewire.siswa.digital-card', [
            'student' => $student,
            'permit' => $permit,
            'card' => $permit ? $stickers->cardData($permit) : null,
        ]);
    }
}
