<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\BookJsonController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\PublisherAdminController;
use App\Http\Controllers\PublisherController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/XX_module_d/books');

// JSON API (no auth required)
Route::get('/XX_module_d/books.json', [BookJsonController::class, 'index']);
Route::get('/XX_module_d/books/{isbn}.json', [BookJsonController::class, 'show']);

Route::get('/XX_module_d/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/XX_module_d/login', [AuthController::class, 'login'])->name('login.submit');
Route::post('/XX_module_d/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/XX_module_d/isbn-validate', [PublicController::class, 'isbnValidationPage'])->name('public.isbn.validate');
Route::post('/XX_module_d/isbn-validate', [PublicController::class, 'isbnValidationSubmit'])->name('public.isbn.validate.submit');
Route::get('/XX_module_d/01/{isbn}', [PublicController::class, 'bookShow'])->name('public.book.show');
Route::get('/XX_module_d/publishers/{publisher}', [PublicController::class, 'publisherShow'])
    ->name('public.publisher.show')
    ->where('publisher', '[0-9]+');

Route::middleware('auth')->prefix('XX_module_d')->group(function () {
    Route::get('/books', [BookController::class, 'index'])->name('books.index');
    Route::get('/books/new', [BookController::class, 'create'])->name('books.create');
    Route::post('/books', [BookController::class, 'store'])->name('books.store');
    Route::get('/books/{isbn}/edit', [BookController::class, 'edit'])->name('books.edit');
    Route::get('/books/{isbn}', [BookController::class, 'show'])->name('books.show');
    Route::put('/books/{book}', [BookController::class, 'update'])->name('books.update');
    Route::patch('/books/{book}/hide', [BookController::class, 'hide'])->name('books.hide');
    Route::patch('/books/{book}/show', [BookController::class, 'showBook'])->name('books.show-book');
    Route::delete('/books/{book}', [BookController::class, 'destroy'])->name('books.destroy');

    Route::middleware('super.admin')->group(function () {
        Route::get('/publishers', [PublisherController::class, 'index'])->name('publishers.index');
        Route::get('/publishers/inactive', [PublisherController::class, 'inactive'])->name('publishers.inactive');
        Route::get('/publishers/new', [PublisherController::class, 'create'])->name('publishers.create');
        Route::post('/publishers', [PublisherController::class, 'store'])->name('publishers.store');
        Route::get('/publishers/{publisher}/detail', [PublisherController::class, 'show'])->name('publishers.show');
        Route::get('/publishers/{publisher}/edit', [PublisherController::class, 'edit'])->name('publishers.edit');
        Route::put('/publishers/{publisher}', [PublisherController::class, 'update'])->name('publishers.update');
        Route::patch('/publishers/{publisher}/disable', [PublisherController::class, 'disable'])->name('publishers.disable');
        Route::patch('/publishers/{publisher}/enable', [PublisherController::class, 'enable'])->name('publishers.enable');

        // Publisher admin CRUD
        Route::get('/publishers/{publisher}/admins/new', [PublisherAdminController::class, 'create'])->name('publisher-admins.create');
        Route::post('/publishers/{publisher}/admins', [PublisherAdminController::class, 'store'])->name('publisher-admins.store');
        Route::get('/publishers/{publisher}/admins/{admin}/edit', [PublisherAdminController::class, 'edit'])->name('publisher-admins.edit');
        Route::put('/publishers/{publisher}/admins/{admin}', [PublisherAdminController::class, 'update'])->name('publisher-admins.update');
        Route::delete('/publishers/{publisher}/admins/{admin}', [PublisherAdminController::class, 'destroy'])->name('publisher-admins.destroy');
    });
});
