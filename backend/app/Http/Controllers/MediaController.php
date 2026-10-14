<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

/** Serves uploaded images from storage/app/public with long-lived caching. */
class MediaController extends Controller
{
    public function __invoke(string $path): Response
    {
        abort_if(str_contains($path, '..') || str_starts_with($path, '/'), 404);

        $disk = Storage::disk('public');
        abort_unless($disk->exists($path), 404);

        return $disk->response($path, null, [
            'Cache-Control' => 'public, max-age=2592000, immutable', // 30 days – file names are unique
        ]);
    }
}
