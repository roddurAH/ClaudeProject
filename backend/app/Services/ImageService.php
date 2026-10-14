<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Stores uploaded photos on the public disk, shrinking them first so that
 * farmers on 2G/3G connections do not have to download 5 MB camera images.
 * Uses the GD extension that ships with XAMPP; falls back to storing the
 * original file if GD cannot read it.
 */
class ImageService
{
    public function store(UploadedFile $file, string $directory): string
    {
        $directory = trim($directory, '/').'/'.now()->format('Y/m');
        $maxDimension = (int) config('krishi.uploads.max_dimension');
        $quality = (int) config('krishi.uploads.jpeg_quality');

        $resized = $this->resizeToJpeg($file->getRealPath(), $maxDimension, $quality);

        if ($resized === null) {
            return $file->store($directory, 'public');
        }

        $path = $directory.'/'.Str::uuid()->toString().'.jpg';
        Storage::disk('public')->put($path, $resized);

        return $path;
    }

    public function delete(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }

    private function resizeToJpeg(string $sourcePath, int $maxDimension, int $quality): ?string
    {
        if (! function_exists('imagecreatefromstring')) {
            return null;
        }

        $contents = @file_get_contents($sourcePath);
        $source = $contents !== false ? @imagecreatefromstring($contents) : false;
        if ($source === false) {
            return null;
        }

        $width = imagesx($source);
        $height = imagesy($source);
        $scale = min(1, $maxDimension / max($width, $height));
        $newWidth = max(1, (int) round($width * $scale));
        $newHeight = max(1, (int) round($height * $scale));

        $canvas = imagecreatetruecolor($newWidth, $newHeight);
        // White background so transparent PNGs do not turn black as JPEG.
        imagefill($canvas, 0, 0, imagecolorallocate($canvas, 255, 255, 255));
        imagecopyresampled($canvas, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        ob_start();
        imagejpeg($canvas, null, $quality);
        $jpeg = ob_get_clean();

        imagedestroy($source);
        imagedestroy($canvas);

        return $jpeg === false ? null : $jpeg;
    }
}
