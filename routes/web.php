<?php

use App\Enums\Permission;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LegalController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PaystackWebhookController;
use App\Http\Controllers\ProductRequestController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\Shop\HomeController;
use App\Http\Controllers\Shop\ProductController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\Staff\SaleController as StaffSaleController;
use App\Http\Controllers\Staff\ShipmentController as StaffShipmentController;
use App\Http\Controllers\Supplier\OfferController as SupplierOfferController;
use App\Http\Controllers\Supplier\ProfileController as SupplierProfileController;
use App\Http\Middleware\EnsureSupplierProfile;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::get('shop', [ProductController::class, 'index'])->name('shop.index');
Route::get('shop/{product:slug}', [ProductController::class, 'show'])->name('shop.show');

Route::get('privacy', [LegalController::class, 'privacy'])->name('legal.privacy');
Route::get('terms', [LegalController::class, 'terms'])->name('legal.terms');
Route::get('sitemap.xml', [SitemapController::class, 'sitemap'])->name('sitemap');
Route::get('robots.txt', [SitemapController::class, 'robots'])->name('robots');
Route::post('language', LocaleController::class)->name('locale.update');

// Paystack sends customers back here after checkout, and posts signed webhooks.
Route::get('payments/callback', [PaymentController::class, 'callback'])->name('payments.callback');
Route::post('webhooks/paystack', PaystackWebhookController::class)->name('webhooks.paystack');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    // Local test mobile money checkout (FakeGateway only; 404 with Paystack).
    Route::get('payments/test/{payment:reference}', [PaymentController::class, 'testCheckout'])->name('payments.test');
    Route::post('payments/test/{payment:reference}', [PaymentController::class, 'testDecide'])->name('payments.test.decide');

    // Reserving an item in the store and paying online.
    Route::middleware('can:'.Permission::RequestProducts->value)->group(function () {
        Route::get('reservations', [ReservationController::class, 'index'])->name('reservations.index');
        Route::get('shop/{product:slug}/reserve/{reference}', [ReservationController::class, 'create'])->name('reservations.create');
        Route::post('shop/{product:slug}/reserve/{reference}', [ReservationController::class, 'store'])->name('reservations.store');
        Route::post('reservations/{reservation}/pay', [ReservationController::class, 'pay'])->name('reservations.pay');
        Route::post('reservations/{reservation}/cancel', [ReservationController::class, 'cancel'])->name('reservations.cancel');
    });
    Route::get('reservations/{reservation}', [ReservationController::class, 'show'])->name('reservations.show');

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
            Route::post('shipments/{shipment}/batches/{batch}/check-in', [StaffShipmentController::class, 'checkInBatch'])
                ->name('shipments.batches.check-in');
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
