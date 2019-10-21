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
/*Route::get('/', 'PagesController@index');


//this route should ensure that when the end user types in website link/about - it should open the about page in the pages folder 
Route::get('/about', 'PagesController@about');


Route::get('/products', 'PagesController@products');
Route::get('/welcome', function () {
    return view('welcome'); 
});
*/
Route::get('/', function(){
    return view('pages.index');
});

Route::get('/testing' , function(){
    return view('tester'); 
});

