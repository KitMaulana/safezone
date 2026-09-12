<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Vehicle;
use App\Models\Violation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Menyajikan berkas dari storage/app/public dengan otorisasi (SPEC §2.1, §6.4).
 * Tidak memakai asset('storage/...') agar foto siswa tidak bisa ditebak URL-nya.
 */
class MediaController extends Controller
{
    public function show(Request $request, string $path): BinaryFileResponse|Response
    {
        $path = ltrim(str_replace('\\', '/', $path), '/');

        // Cegah path traversal.
        if (str_contains($path, '..')) {
            abort(404);
        }

        $disk = Storage::disk('public');

        if (! $disk->exists($path)) {
            abort(404);
        }

        $this->authorizeAccess($request, $path);

        return response()->file($disk->path($path), [
            'Cache-Control' => 'private, max-age=3600',
        ]);
    }

    private function authorizeAccess(Request $request, string $path): void
    {
        $user = $request->user();

        if (! $user) {
            abort(403);
        }

        if ($user->isStaff()) {
            return;
        }

        $owner = $this->resolveOwner($path);

        if (! $owner) {
            abort(403, 'Anda tidak berhak membuka berkas ini.');
        }

        if ($request->user()->cannot('view', $owner)) {
            abort(403, 'Anda tidak berhak membuka berkas ini.');
        }
    }

    /** Cari siswa pemilik berkas berdasarkan lokasi penyimpanannya. */
    private function resolveOwner(string $path): ?Student
    {
        if (str_starts_with($path, 'students/')) {
            return Student::where('photo_path', $path)->first();
        }

        if (str_starts_with($path, 'stnk/')) {
            return Vehicle::where('stnk_photo_path', $path)->first()?->student;
        }

        if (str_starts_with($path, 'violations/')) {
            return Violation::where('evidence_photo_path', $path)->first()?->student;
        }

        return null;
    }
}
