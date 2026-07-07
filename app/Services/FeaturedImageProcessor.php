<?php

namespace App\Services;

use GdImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class FeaturedImageProcessor
{
    private const TARGET_WIDTH = 1280;

    private const TARGET_HEIGHT = 720;

    public function store(UploadedFile $file, string $directory = 'posts'): string
    {
        $image = $this->loadImage($file);

        $sourceWidth = imagesx($image);
        $sourceHeight = imagesy($image);
        $scale = min(
            1,
            self::TARGET_WIDTH / $sourceWidth,
            self::TARGET_HEIGHT / $sourceHeight
        );
        $targetWidth = max(1, (int) round($sourceWidth * $scale));
        $targetHeight = max(1, (int) round($sourceHeight * $scale));

        $resized = imagecreatetruecolor($targetWidth, $targetHeight);

        if ($resized === false) {
            imagedestroy($image);

            throw new RuntimeException('Unable to process image.');
        }

        imagecopyresampled(
            $resized,
            $image,
            0,
            0,
            0,
            0,
            $targetWidth,
            $targetHeight,
            $sourceWidth,
            $sourceHeight,
        );

        imagedestroy($image);

        ob_start();
        imagejpeg($resized, null, 85);
        $contents = ob_get_clean();

        imagedestroy($resized);

        if ($contents === false) {
            throw new RuntimeException('Unable to encode image.');
        }

        $filename = $directory.'/'.Str::random(40).'.jpg';

        Storage::disk('public')->put($filename, $contents);

        return $filename;
    }

    private function loadImage(UploadedFile $file): GdImage
    {
        $path = $file->getRealPath();

        if ($path === false) {
            throw new RuntimeException('Unable to read image.');
        }

        $image = match ($file->getMimeType()) {
            'image/jpeg', 'image/jpg' => imagecreatefromjpeg($path),
            'image/png' => imagecreatefrompng($path),
            'image/webp' => imagecreatefromwebp($path),
            default => throw new RuntimeException('Unsupported image type.'),
        };

        if ($image === false) {
            throw new RuntimeException('Unable to read image.');
        }

        return $image;
    }
}
