<?php

use App\Http\Controllers\Api\V1\AlertController;
use App\Http\Controllers\Api\V1\ArticleController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ContentController;
use App\Http\Controllers\Api\V1\CropController;
use App\Http\Controllers\Api\V1\DistrictController;
use App\Http\Controllers\Api\V1\MarketController;
use App\Http\Controllers\Api\V1\MetaController;
use App\Http\Controllers\Api\V1\PriceController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\QuestionController;
use App\Http\Controllers\Api\V1\WeatherController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Mobile app API – version 1  (base URL: /api/v1)
|--------------------------------------------------------------------------
| Everything a guest needs (knowledge base, prices, weather, alerts) is public
| so the app is useful without an account. Only asking experts needs login.
| See docs/API.md for request/response examples.
*/

Route::prefix('v1')->middleware('throttle:api')->group(function () {
    // App bootstrap & reference data
    Route::get('meta', MetaController::class);
    Route::get('districts', [DistrictController::class, 'index']);

    // Knowledge base (offline bundle + individual endpoints)
    Route::get('content', ContentController::class);
    Route::get('crops', [CropController::class, 'index']);
    Route::get('crops/{slug}', [CropController::class, 'show']);
    Route::get('problems/{problem}', [CropController::class, 'problem']);
    Route::get('articles', [ArticleController::class, 'index']);
    Route::get('articles/{slug}', [ArticleController::class, 'show']);

    // Markets & prices
    Route::get('markets', [MarketController::class, 'index']);
    Route::get('commodities', [MarketController::class, 'commodities']);
    Route::get('prices', [PriceController::class, 'index']);
    Route::get('prices/history', [PriceController::class, 'history']);
    Route::get('fertilizer-prices', [PriceController::class, 'fertilizers']);

    // Weather & alerts
    Route::get('weather', WeatherController::class)->middleware('throttle:weather');
    Route::get('alerts', [AlertController::class, 'index']);

    // Community Q&A (answered questions an expert made public)
    Route::get('community/questions', [QuestionController::class, 'community']);

    // Authentication
    Route::middleware('throttle:auth')->group(function () {
        Route::post('auth/register', [AuthController::class, 'register']);
        Route::post('auth/login', [AuthController::class, 'login']);
    });

    Route::middleware(['auth:sanctum', 'active'])->group(function () {
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::get('me', [ProfileController::class, 'show']);
        Route::patch('me', [ProfileController::class, 'update']);
        Route::delete('me', [ProfileController::class, 'destroy'])->middleware('throttle:auth');

        Route::get('questions', [QuestionController::class, 'index']);
        Route::post('questions', [QuestionController::class, 'store'])->middleware('throttle:questions');
        Route::get('questions/{question}', [QuestionController::class, 'show']);
    });
});
