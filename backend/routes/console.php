<?php

use App\Http\Resources\DistrictResource;
use App\Models\District;
use App\Services\ContentBundle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

/*
 * Copy the current knowledge base into the mobile app as a bundled asset:
 *   php artisan krishi:export-content
 * Run it before building a release so a fresh install works fully offline.
 * Image URLs inside the bundle point at APP_URL – set it to the production URL.
 */
Artisan::command('krishi:export-content {path=../mobile/assets/seed/content.json}', function (ContentBundle $bundle, string $path) {
    $request = Request::create(config('app.url'));
    app()->instance('request', $request);

    $json = json_encode($bundle->build($request), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $target = str_starts_with($path, '/') ? $path : base_path($path);

    if (! is_dir(dirname($target))) {
        $this->error('Directory does not exist: '.dirname($target));

        return 1;
    }

    file_put_contents($target, $json);
    $this->info(sprintf('Exported knowledge base (%s KB) to %s', number_format(strlen($json) / 1024, 1), realpath($target)));

    // Districts are needed during onboarding, before the first network call.
    $districts = DistrictResource::collection(District::with('division')->orderBy('name_en')->get())->resolve($request);
    $districtsPath = dirname($target).'/districts.json';
    file_put_contents($districtsPath, json_encode(['data' => $districts], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    $this->info('Exported '.count($districts).' districts to '.realpath($districtsPath));

    return 0;
})->purpose('Export the offline knowledge base for bundling inside the mobile app');
