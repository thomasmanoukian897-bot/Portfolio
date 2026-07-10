<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FeaturedVideoProcessor
{
    /**
     * @var list<string>
     */
    private const ALLOWED_EXTENSIONS = ['mp4', 'webm', 'mov'];

    public function store(UploadedFile $file, string $directory = 'posts/videos'): string
    {
        $extension = strtolower($file->getClientOriginalExtension() ?: 'mp4');

        if (! in_array($extension, self::ALLOWED_EXTENSIONS, true)) {
            $extension = 'mp4';
        }

        $filename = $directory.'/'.Str::random(40).'.'.$extension;

        Storage::disk('public')->putFileAs(
            dirname($filename),
            $file,
            basename($filename),
        );

        return $filename;
    }
}
