<?php

use App\Enums\Permission;
use App\Http\Controllers\Shop\HomeController;
use App\Http\Controllers\Shop\ProductController;
use App\Http\Controllers\Staff\ShipmentController as StaffShipmentController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::get('shop', [ProductController::class, 'index'])->name('shop.index');
Route::get('shop/{product:slug}', [ProductController::class, 'show'])->name('shop.show');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');

    Route::middleware('can:'.Permission::ReceiveShipments->value)
        ->prefix('staff')->name('staff.')
        ->group(function () {
            Route::get('shipments', [StaffShipmentController::class, 'index'])->name('shipments.index');
            Route::get('shipments/{shipment}', [StaffShipmentController::class, 'show'])->name('shipments.show');
            Route::post('shipments/{shipment}/items/{item}/check-in', [StaffShipmentController::class, 'checkIn'])
                ->scopeBindings()
                ->name('shipments.items.check-in');
        });
});

require __DIR__.'/settings.php';
