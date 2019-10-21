<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PagesController extends Controller
{
    //this public method/function is available and accessible anywhere, this function will return the index page as a view
    public function index() {
        return view('pages.index');
    }
    //this public method/function is available and accessible anywhere, this function will return the index page as a view
    public function about() {
        return view('pages.about');
    }

    //this public method/function is available and accessible anywhere, this function will return the index page as a view
    public function products() {
        return view('pages.products');
    }
}
