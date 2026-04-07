<?php

use App\Http\Controllers\BookJsonController;
use Illuminate\Support\Facades\Route;

Route::prefix('XX_module_d')->group(function () {
    Route::get('/books.json', [BookJsonController::class, 'index']);
    Route::get('/books/{isbn}.json', [BookJsonController::class, 'show']);
});
