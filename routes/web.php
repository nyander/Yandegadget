<?php
    use App\Notifications\NewShipment;	
    use App\Ship;	
    use App\User;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

/*this is a test to see if the website displays
Route::get('/richard', function () {
    return 'I can confirm that the routes are working'; 
});
*/

//authentication related
Auth::routes(['verify' => true]);

//PageControllers
//this route should display the index page of the website 
Route::get('/', 'PagesController@index');
Route::get('/about', 'PagesController@about');
Route::get('/settings', 'PagesController@settings');

//HomeController
Route::get('/home', 'HomeController@index')->name('home');

//ProductsController
// GET|PUSH|PUT|etc
Route::resource('/products', 'ProductsController');
// Mark Products as read function
Route::get('products/notifications/{id}','ProductsController@MarkRead');
// Confirm product as been received function
Route::get('/products/received/{id}', 'ProductsController@received')->name('products.received');
// Redirects to page to confirm purchase
Route::get('/products/{id}/purchase', 'ProductsController@purchase')->name('products.purchase');
// Cofirm Product has been purchased function 
Route::put('/products/{id}/purchase', 'ProductsController@purchaseupdate')->name('products.purchaseupdate');
// Function for supplier to upload product
Route::get('/supplierproducts/{id}/addproduct', 'ProductsController@storesupproduct')->name('products.storesupproduct');
//Function for customer to request product
Route::get('/requester/{id}/addproduct', 'ProductsController@storereqproduct')->name('products.storereqproduct');

//Image Controller
Route::resource('/images', 'ImageController');

// update suppliers images
Route::put('/images/updatesuppliers/{id}', 'ImageController@updatesuppliers')->name('updatesuppliers');

//SupplierProduct Controller
Route::resource('/supplierproducts', 'SupplierProductController');
//marks notification as read 
Route::get('supproduct/notifications/{id}','SupplierProductController@MarkRead');

// Requested Product Controller
Route::resource('/requests','ProductRequestController');

// Staff Wage controller
Route::resource('/staffwages', 'StaffWageController');

// Conditions controller
Route::resource('/conditions', 'ConditionsController');

// Categories controller
Route::resource('/categories', 'CategoriesController');

// Suppliers controller
Route::resource('/suppliers', 'SuppliersController');

// Shipment Comapny Controller
Route::resource('/shipcompanies', 'ShipCompanyController');

// Shipment Confirmation Controller
Route::resource('/confirmations','ShipmentConfirmationController');

// Requested Product Checkout
Route::resource('/checkouts', 'CheckoutController');

// Shipped Product Controler
Route::resource('/ships', 'ShipController');

// Shipped Product has been received function
Route::get('/ships/received/{id}', 'ShipController@received')->name('ships.received');

//Charts and Reports controller 
Route::resource('/reports', 'ReportController');

// Admin recording Business Transactions
Route::resource('/transactions', 'TransactionController');

//shipment routes
Route::resource('/shipments', 'ShipmentController'); 

// place Products for save for later shipment 
Route::post('/shipments/switchToSaveForLater/{product}', 'ShipmentController@switchToSaveForLater')->name('shipments.switchToSaveForLater');


Route::resource('/saveForLater', 'SaveForLaterController'); 
Route::post('/saveForLater/switchToShipment/{product}', 'SaveForLaterController@switchToShipment')->name('saveForLater.switchToShipment');

Route::get('empty', function(){
    Cart::instance('saveForLater')->destroy();
});



// adding a prefix adds admin to the start of the URL and the name adds the admin. to the routes entered. it applies middleware so when the user logs in it checks whther it is a staff or an admin 
Route::namespace('Admin')->prefix('admin')->name('admin.')->middleware('can:admin-role')->group(function(){
    Route::resource('/users', 'UsersController',['except'=>['show','create','store']]);
    
});



