<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\PublicPageController;
use App\Http\Middleware\SetPublicPageLocale;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('welcome'))->name('home');

// Public pages linked from the app and the Google Play listing
Route::middleware(SetPublicPageLocale::class)->group(function () {
    Route::get('/privacy', [PublicPageController::class, 'privacy'])->name('privacy');
    Route::get('/account/delete', [PublicPageController::class, 'deleteAccountForm'])->name('account.delete');
    Route::post('/account/delete', [PublicPageController::class, 'deleteAccount'])->middleware('throttle:auth');
});

// Uploaded images (see App\Support\Media)
Route::get('/media/{path}', MediaController::class)->where('path', '.*')->name('media');

/*
|--------------------------------------------------------------------------
| Admin panel  (/admin)
|--------------------------------------------------------------------------
| Access is by role – see the permission table in AppServiceProvider.
*/
Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('login', [Admin\AuthController::class, 'showLogin'])->name('login');
        Route::post('login', [Admin\AuthController::class, 'login'])->middleware('throttle:admin-login');
    });

    Route::middleware(['auth', 'active', 'staff'])->group(function () {
        Route::post('logout', [Admin\AuthController::class, 'logout'])->name('logout');
        Route::get('/', Admin\DashboardController::class)->name('dashboard');

        Route::middleware('can:manage-content')->group(function () {
            Route::resource('crops', Admin\CropController::class)->except('show');
            Route::post('crops/{crop}/stages', [Admin\CropStageController::class, 'store'])->name('crops.stages.store');
            Route::get('stages/{stage}/edit', [Admin\CropStageController::class, 'edit'])->name('stages.edit');
            Route::put('stages/{stage}', [Admin\CropStageController::class, 'update'])->name('stages.update');
            Route::delete('stages/{stage}', [Admin\CropStageController::class, 'destroy'])->name('stages.destroy');
            Route::post('crops/{crop}/doses', [Admin\CropFertilizerDoseController::class, 'store'])->name('crops.doses.store');
            Route::delete('doses/{dose}', [Admin\CropFertilizerDoseController::class, 'destroy'])->name('doses.destroy');

            Route::resource('problems', Admin\ProblemController::class)->except('show');
            Route::resource('articles', Admin\ArticleController::class)->except('show');
        });

        Route::middleware('can:answer-questions')->group(function () {
            Route::get('questions', [Admin\QuestionController::class, 'index'])->name('questions.index');
            Route::get('questions/{question}', [Admin\QuestionController::class, 'show'])->name('questions.show');
            Route::post('questions/{question}/answers', [Admin\QuestionController::class, 'answer'])->name('questions.answer');
            Route::patch('questions/{question}', [Admin\QuestionController::class, 'update'])->name('questions.update');
            Route::get('ai-conversations', [Admin\AiConversationController::class, 'index'])->name('ai.index');
            Route::get('ai-conversations/{conversation}', [Admin\AiConversationController::class, 'show'])->name('ai.show');
        });

        Route::middleware('can:manage-prices')->group(function () {
            Route::get('prices', [Admin\PriceController::class, 'index'])->name('prices.index');
            Route::get('prices/entry', [Admin\PriceController::class, 'entry'])->name('prices.entry');
            Route::post('prices/entry', [Admin\PriceController::class, 'storeEntry'])->name('prices.entry.store');
            Route::get('prices/import', [Admin\PriceController::class, 'importForm'])->name('prices.import');
            Route::post('prices/import', [Admin\PriceController::class, 'import'])->name('prices.import.store');
            Route::get('prices/template.csv', [Admin\PriceController::class, 'template'])->name('prices.template');
            Route::delete('prices/{price}', [Admin\PriceController::class, 'destroy'])->name('prices.destroy');

            Route::resource('markets', Admin\MarketController::class)->except('show', 'destroy');
            Route::resource('commodities', Admin\CommodityController::class)->except('show', 'destroy');
            Route::resource('fertilizers', Admin\FertilizerController::class)->only('index', 'edit', 'update');
        });

        Route::middleware('can:manage-alerts')->group(function () {
            Route::resource('alerts', Admin\AlertController::class)->except('show');
        });

        Route::middleware('can:manage-users')->group(function () {
            Route::resource('users', Admin\UserController::class)->except('show');
        });
    });
});
