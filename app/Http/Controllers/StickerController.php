<?php

namespace App\Http\Controllers;

use App\Models\VehiclePermit;
use App\Services\StickerPdfService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StickerController extends Controller
{
    public function __construct(
        private readonly StickerPdfService $stickers,
    ) {}

    public function single(Request $request, VehiclePermit $permit): Response
    {
        $this->authorize('print', $permit);

        return $this->stickers->single($permit, $request->boolean('a4'));
    }

    public function batch(Request $request): Response
    {
        $this->authorize('manage', VehiclePermit::class);

        $data = $request->validate([
            'permits' => ['required', 'array', 'min:1', 'max:200'],
            'permits.*' => ['integer', 'exists:vehicle_permits,id'],
        ], attributes: ['permits' => 'stiker']);

        $permits = VehiclePermit::with('vehicle.student')
            ->whereIn('id', $data['permits'])
            ->orderBy('permit_number')
            ->get();

        return $this->stickers->batch($permits);
    }

    /** Pratinjau HTML dengan komponen Blade yang sama dengan PDF. */
    public function preview(VehiclePermit $permit)
    {
        $this->authorize('view', $permit);

        $permit->load(['vehicle.student.parents', 'vehicle.student.academicYear']);

        return view('pdf.sticker-preview', [
            'card' => $this->stickers->cardData($permit),
            'permit' => $permit,
        ]);
    }
}
