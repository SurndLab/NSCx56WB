<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\PublisherController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/XX_module_d/books');

Route::get('/XX_module_d/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/XX_module_d/login', [AuthController::class, 'login'])->name('login.submit');
Route::post('/XX_module_d/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/XX_module_d/isbn-validate', [PublicController::class, 'isbnValidationPage'])->name('public.isbn.validate');
Route::post('/XX_module_d/isbn-validate', [PublicController::class, 'isbnValidationSubmit'])->name('public.isbn.validate.submit');
Route::get('/XX_module_d/01/{isbn}', [PublicController::class, 'bookShow'])->name('public.book.show');
Route::get('/XX_module_d/publishers/{publisher}', [PublicController::class, 'publisherShow'])->name('public.publisher.show');

Route::middleware('auth')->prefix('XX_module_d')->group(function () {
    Route::get('/books', [BookController::class, 'index'])->name('books.index');
    Route::get('/books/new', [BookController::class, 'create'])->name('books.create');
    Route::post('/books', [BookController::class, 'store'])->name('books.store');
    Route::get('/books/{isbn}', [BookController::class, 'show'])->name('books.show');
    Route::put('/books/{book}', [BookController::class, 'update'])->name('books.update');
    Route::patch('/books/{book}/hide', [BookController::class, 'hide'])->name('books.hide');
    Route::patch('/books/{book}/show', [BookController::class, 'showBook'])->name('books.show-book');
    Route::delete('/books/{book}', [BookController::class, 'destroy'])->name('books.destroy');

    Route::middleware('super.admin')->group(function () {
        Route::get('/publishers', [PublisherController::class, 'index'])->name('publishers.index');
        Route::get('/publishers/inactive', [PublisherController::class, 'inactive'])->name('publishers.inactive');
        Route::post('/publishers', [PublisherController::class, 'store'])->name('publishers.store');
        Route::put('/publishers/{publisher}', [PublisherController::class, 'update'])->name('publishers.update');
        Route::patch('/publishers/{publisher}/disable', [PublisherController::class, 'disable'])->name('publishers.disable');
        Route::patch('/publishers/{publisher}/enable', [PublisherController::class, 'enable'])->name('publishers.enable');
    });
});
