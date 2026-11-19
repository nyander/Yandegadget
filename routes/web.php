<?php

use App\Enums\Permission;
use App\Http\Controllers\PaystackWebhookController;
use App\Http\Controllers\ProductRequestController;
use App\Http\Controllers\Shop\HomeController;
use App\Http\Controllers\Shop\ProductController;
use App\Http\Controllers\Staff\SaleController as StaffSaleController;
use App\Http\Controllers\Staff\ShipmentController as StaffShipmentController;
use App\Http\Controllers\Supplier\OfferController as SupplierOfferController;
use App\Http\Controllers\Supplier\ProfileController as SupplierProfileController;
use App\Http\Middleware\EnsureSupplierProfile;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::get('shop', [ProductController::class, 'index'])->name('shop.index');
Route::get('shop/{product:slug}', [ProductController::class, 'show'])->name('shop.show');

// Paystack sends customers back here after checkout, and posts signed webhooks.
Route::get('payments/callback', [ProductRequestController::class, 'callback'])->name('payments.callback');
Route::post('webhooks/paystack', PaystackWebhookController::class)->name('webhooks.paystack');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');

    Route::middleware('can:'.Permission::RequestProducts->value)->group(function () {
        Route::get('requests', [ProductRequestController::class, 'index'])->name('requests.index');
        Route::get('requests/new', [ProductRequestController::class, 'create'])->name('requests.create');
        Route::post('requests', [ProductRequestController::class, 'store'])->name('requests.store');
        Route::post('requests/{productRequest}/pay', [ProductRequestController::class, 'pay'])->name('requests.pay');
        Route::post('requests/{productRequest}/cancel', [ProductRequestController::class, 'cancel'])->name('requests.cancel');
    });

    Route::middleware('can:'.Permission::SubmitSupplierOffers->value)
        ->prefix('supplier')->name('supplier.')
        ->group(function () {
            Route::get('profile', [SupplierProfileController::class, 'edit'])->name('profile.edit');
            Route::put('profile', [SupplierProfileController::class, 'update'])->name('profile.update');

            Route::middleware(EnsureSupplierProfile::class)->group(function () {
                Route::get('offers', [SupplierOfferController::class, 'index'])->name('offers.index');
                Route::get('offers/new', [SupplierOfferController::class, 'create'])->name('offers.create');
                Route::post('offers', [SupplierOfferController::class, 'store'])->name('offers.store');
                Route::post('offers/{offer}/withdraw', [SupplierOfferController::class, 'withdraw'])->name('offers.withdraw');
            });
        });

    // Viewable by the supplier who made it and by the shop.
    Route::get('supplier/offers/{offer}', [SupplierOfferController::class, 'show'])->name('supplier.offers.show');

    // Viewable by the customer who made it and by the shop.
    Route::get('requests/{productRequest}', [ProductRequestController::class, 'show'])->name('requests.show');

    Route::middleware('can:'.Permission::ReceiveShipments->value)
        ->prefix('staff')->name('staff.')
        ->group(function () {
            Route::get('shipments', [StaffShipmentController::class, 'index'])->name('shipments.index');
            Route::get('shipments/{shipment}', [StaffShipmentController::class, 'show'])->name('shipments.show');
            Route::post('shipments/{shipment}/items/{item}/check-in', [StaffShipmentController::class, 'checkIn'])
                ->scopeBindings()
                ->name('shipments.items.check-in');
        });

    Route::middleware('can:'.Permission::RecordSales->value)
        ->prefix('staff')->name('staff.')
        ->group(function () {
            Route::get('sell', [StaffSaleController::class, 'index'])->name('sell.index');
            Route::get('sell/{unit}', [StaffSaleController::class, 'create'])->name('sell.create');
            Route::post('sell/{unit}', [StaffSaleController::class, 'store'])->name('sell.store');
            Route::get('sales/{sale}', [StaffSaleController::class, 'show'])->name('sales.show');
        });
});

require __DIR__.'/settings.php';
