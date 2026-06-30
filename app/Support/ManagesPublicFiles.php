<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

trait ManagesPublicFiles
{
    protected function storePublicUploadedFile(UploadedFile $file, string $directory): string
    {
        $directory = trim($directory, '/\\');
        $targetPath = public_path($directory);

        if (! File::exists($targetPath)) {
            File::makeDirectory($targetPath, 0755, true);
        }

        $filename = (string) Str::uuid() . '.' . $file->getClientOriginalExtension();
        $file->move($targetPath, $filename);

        return $directory . '/' . $filename;
    }

    protected function deletePublicFile(?string $path): void
    {
        if (! $path) {
            return;
        }

        $fullPath = public_path($path);

        if (File::exists($fullPath)) {
            File::delete($fullPath);
        }
    }
}
