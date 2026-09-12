<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class PhotoService
{
    public const MAX_DIMENSION = 600;

    /**
     * Simpan foto ke storage/app/public/{folder} setelah diperkecil maksimal 600 px.
     * Mengembalikan path relatif untuk disimpan di kolom *_path.
     */
    public function store(UploadedFile $file, string $folder, ?string $replacing = null): string
    {
        $manager = new ImageManager(new Driver);

        $image = $manager->read($file->getRealPath())
            ->scaleDown(self::MAX_DIMENSION, self::MAX_DIMENSION);

        $path = $folder.'/'.Str::uuid()->toString().'.jpg';

        Storage::disk('public')->put($path, (string) $image->toJpeg(82));

        if ($replacing) {
            $this->delete($replacing);
        }

        return $path;
    }

    public function delete(?string $path): void
    {
        if (filled($path) && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    /** URL aman yang melewati MediaController. */
    public function url(?string $path): ?string
    {
        return filled($path) ? route('media', ['path' => $path]) : null;
    }
}
