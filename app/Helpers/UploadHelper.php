<?php

namespace App\Helpers;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class UploadHelper
{
    public static function uploadFile(UploadedFile $file, string $path)
    {
        if (!$file->isValid()) {
            throw new \Exception('Invalid file upload');
        }
        $destination = public_path($path);

        if (!file_exists($destination)) {
            mkdir($destination, 0755, true);
        }

        $filename = Str::uuid()->toString() . '.' . $file->getClientOriginalExtension();

        $file->move($destination, $filename);

        return $path . '/' . $filename;
    }

    public static function deleteFile(string $filePath)
    {
        $fullPath = public_path($filePath);
        if (file_exists($fullPath)) {
            return unlink($fullPath);
        }

        return false;
    }

    public static function getFileUrl(string $filePath): string
    {
        return asset($filePath);
    }
}
