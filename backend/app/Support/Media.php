<?php

namespace App\Support;

/**
 * Uploaded images live on the "public" disk (storage/app/public) and are served
 * through the /media/{path} route instead of the storage symlink. That avoids
 * the `storage:link` symlink problems on Windows/XAMPP.
 */
final class Media
{
    public static function url(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        return route('media', ['path' => $path]);
    }
}
