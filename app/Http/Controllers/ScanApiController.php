<?php

namespace App\Http\Controllers;

use App\Models\Gate;
use App\Services\ScanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ScanApiController extends Controller
{
    public function __construct(
        private readonly ScanService $scans,
    ) {}

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'token' => ['required', 'string', 'max:64'],
            'mode' => ['nullable', 'in:auto,in,out,manual'],
            'note' => ['nullable', 'string', 'max:500'],
            'gate_id' => ['nullable', 'exists:gates,id'],
            'client_scanned_at' => ['nullable', 'date'],
            'offline_id' => ['nullable', 'uuid'],
        ], attributes: ['token' => 'kode QR', 'note' => 'catatan']);

        $mode = $data['mode'] ?? 'auto';

        if ($mode === 'manual' && blank($data['note'] ?? null)) {
            return response()->json([
                'message' => 'Alasan wajib diisi untuk pencatatan manual.',
                'errors' => ['note' => ['Alasan wajib diisi untuk pencatatan manual.']],
            ], 422);
        }

        $result = $this->scans->handle(
            token: $data['token'],
            officer: $request->user(),
            gate: isset($data['gate_id']) ? Gate::find($data['gate_id']) : null,
            mode: $mode,
            note: $data['note'] ?? null,
            clientScannedAt: isset($data['client_scanned_at']) ? Carbon::parse($data['client_scanned_at']) : null,
            offlineId: $data['offline_id'] ?? null,
            deviceInfo: $this->shortUserAgent($request),
        );

        return response()->json($result->toArray());
    }

    private function shortUserAgent(Request $request): string
    {
        $ua = (string) $request->userAgent();

        $platform = match (true) {
            str_contains($ua, 'Android') => 'Android',
            str_contains($ua, 'iPhone') || str_contains($ua, 'iPad') => 'iOS',
            str_contains($ua, 'Windows') => 'Windows',
            default => 'Lainnya',
        };

        $browser = match (true) {
            str_contains($ua, 'Edg/') => 'Edge',
            str_contains($ua, 'Chrome') => 'Chrome',
            str_contains($ua, 'Firefox') => 'Firefox',
            str_contains($ua, 'Safari') => 'Safari',
            default => 'Browser',
        };

        return $platform.' · '.$browser;
    }
}
