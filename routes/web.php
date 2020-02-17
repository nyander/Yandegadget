<?php

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

//this route should display the index page of the website 
Route::get('/', 'PagesController@index');


//this route should ensure that when the end user types in website link/about - it should open the about page in the pages folder 
Route::get('/about', 'PagesController@about');

//Route::get('/products', 'PagesController@products');
Route::get('/welcome', function () {
    return view('welcome'); 
});

//authentication related
Auth::routes();
Route::get('/home', 'HomeController@index')->name('home');

Route::resource('/products', 'ProductsController');
Route::resource('/requests','ProductRequestController');
Route::resource('/conditions', 'ConditionsController');
Route::resource('/categories', 'CategoriesController');
Route::resource('/suppliers', 'SuppliersController');
Route::resource('/confirmations','ShipmentConfirmationController');
Route::resource('/checkouts', 'CheckoutController');
Route::resource('/deposits', 'DepositsController');

//shipment routes
Route::resource('/shipments', 'ShipmentController'); 
Route::post('/shipments/switchToSaveForLater/{product}', 'ShipmentController@switchToSaveForLater')->name('shipments.switchToSaveForLater');

Route::resource('/saveForLater', 'SaveForLaterController'); 
Route::post('/saveForLater/switchToShipment/{product}', 'SaveForLaterController@switchToShipment')->name('saveForLater.switchToShipment');

Route::get('empty', function(){
    Cart::instance('saveForLater')->destroy();
});





// adding a prefix adds admin to the start of the URL and the name adds the admin. to the routes entered. it applies middleware so when the user logs in it checks whther it is a staff or an admin 
Route::namespace('Admin')->prefix('admin')->name('admin.')->middleware('can:manage-users')->group(function(){
    Route::resource('/users', 'UsersController',['except'=>['show','create','store']]);
    

});