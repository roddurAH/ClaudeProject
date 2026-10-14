<?php

use App\Ai\AiUnavailableException;
use App\Ai\ModelClient;
use App\Http\Resources\DistrictResource;
use App\Models\AiMessage;
use App\Models\District;
use App\Services\AiDoctorService;
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

/*
 * Try the AI crop doctor with one photo (checks the API key and the prompt):
 *   php artisan krishi:ai-check path/to/leaf.jpg --text="পাতায় দাগ কেন?"
 */
Artisan::command('krishi:ai-check {photo? : Path to a plant photo (jpg, png or webp)} {--text= : Question text} {--lang=bn : bn or en}', function (AiDoctorService $doctor, ModelClient $model, ?string $photo = null) {
    if (! $model->isConfigured()) {
        $this->error('No AI configured: set ANTHROPIC_API_KEY in .env (or KRISHI_AI_DRIVER=demo).');

        return 1;
    }

    $content = [];
    if ($photo !== null) {
        $mediaType = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp'][strtolower(pathinfo($photo, PATHINFO_EXTENSION))] ?? null;
        if (! is_file($photo) || $mediaType === null) {
            $this->error('Photo not found or not jpg/png/webp: '.$photo);

            return 1;
        }
        $content[] = ['type' => 'image', 'source' => ['type' => 'base64', 'mediaType' => $mediaType, 'data' => base64_encode(file_get_contents($photo))]];
    }
    $content[] = ['type' => 'text', 'text' => '[language: '.$this->option('lang').' | month: '.now()->format('F')."]\n".($this->option('text') ?: '(No text – please look at the photo.)')];

    $this->info('Asking '.config('krishi.ai.model').' …');
    $started = microtime(true);
    try {
        $reply = $model->diagnose($doctor->systemPrompt(), [['role' => 'user', 'content' => $content]]);
    } catch (AiUnavailableException $e) {
        $this->error('Failed ('.$e->reason.'): '.($e->getPrevious()?->getMessage() ?? $e->getMessage()));

        return 1;
    }

    $this->line(json_encode($reply->diagnosis, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?: '(the model declined to answer)');
    $cost = (new AiMessage([
        'model' => $reply->model, 'input_tokens' => $reply->inputTokens, 'output_tokens' => $reply->outputTokens,
        'cache_read_tokens' => $reply->cacheReadTokens, 'cache_write_tokens' => $reply->cacheWriteTokens,
    ]))->estimatedCost();
    $this->info(sprintf('%s · %.1f s · tokens in %d / out %d / cache read %d / cache write %d · ≈ $%.4f',
        $reply->model, microtime(true) - $started, $reply->inputTokens, $reply->outputTokens, $reply->cacheReadTokens, $reply->cacheWriteTokens, $cost));

    return 0;
})->purpose('Send one test photo to the AI crop doctor');
