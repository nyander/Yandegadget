<?php

use App\Http\Controllers\Shop\HomeController;
use App\Http\Controllers\Shop\ProductController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::get('shop', [ProductController::class, 'index'])->name('shop.index');
Route::get('shop/{product:slug}', [ProductController::class, 'show'])->name('shop.show');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';
