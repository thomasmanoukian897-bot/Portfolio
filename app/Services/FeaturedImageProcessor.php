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

    private const ASPECT_RATIO = 16 / 9;

    public function store(UploadedFile $file, string $directory = 'posts'): string
    {
        $image = $this->loadImage($file);

        $sourceWidth = imagesx($image);
        $sourceHeight = imagesy($image);
        $sourceAspect = $sourceWidth / $sourceHeight;

        if ($sourceAspect > self::ASPECT_RATIO) {
            $cropHeight = $sourceHeight;
            $cropWidth = (int) round($sourceHeight * self::ASPECT_RATIO);
            $cropX = (int) round(($sourceWidth - $cropWidth) / 2);
            $cropY = 0;
        } else {
            $cropWidth = $sourceWidth;
            $cropHeight = (int) round($sourceWidth / self::ASPECT_RATIO);
            $cropX = 0;
            $cropY = (int) round(($sourceHeight - $cropHeight) / 2);
        }

        $cropped = imagecreatetruecolor(self::TARGET_WIDTH, self::TARGET_HEIGHT);

        if ($cropped === false) {
            imagedestroy($image);

            throw new RuntimeException('Unable to process image.');
        }

        imagecopyresampled(
            $cropped,
            $image,
            0,
            0,
            $cropX,
            $cropY,
            self::TARGET_WIDTH,
            self::TARGET_HEIGHT,
            $cropWidth,
            $cropHeight,
        );

        imagedestroy($image);

        ob_start();
        imagejpeg($cropped, null, 85);
        $contents = ob_get_clean();

        imagedestroy($cropped);

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
