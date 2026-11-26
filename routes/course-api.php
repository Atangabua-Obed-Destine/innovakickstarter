<?php

use App\Http\Controllers\CourseApi\NkwenController;
use Illuminate\Support\Facades\Route;

/*
| Public practice APIs used by Software Engineering lessons (no session, no CSRF;
| CORS comes from the framework's HandleCors for api/* paths).
*/
Route::prefix('api/course/nkwen')->middleware(['throttle:120,1,course-api'])->group(function () {
    Route::get('/', [NkwenController::class, 'index']);
    Route::get('/products', [NkwenController::class, 'products']);
    Route::get('/products/{id}', [NkwenController::class, 'product']);
    Route::get('/categories', [NkwenController::class, 'categories']);
    Route::get('/branches', [NkwenController::class, 'branches']);
    Route::get('/legacy/price-list', [NkwenController::class, 'legacyPriceList']);
    Route::post('/orders', [NkwenController::class, 'order'])->middleware('throttle:30,1,course-api-orders');
    Route::post('/login', [NkwenController::class, 'login'])->middleware('throttle:10,1,course-api-login');
    Route::get('/me', [NkwenController::class, 'me']);
    Route::get('/branches/{code}/stock', [NkwenController::class, 'branchStock']);
});
