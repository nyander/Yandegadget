<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PagesController extends Controller
{
    //this public method/function is available and accessible anywhere, this function will return the index page as a view
    public function index() {
        //the variable is going to be the title of the  
        $title = 'Welcome To Yande Gadgets';
        return view('index')->with('title',$title);
    }
    //this public method/function is available and accessible anywhere, this function will return the index page as a view
    public function about() {
        $title = 'About Us';
        return view('about')->with('title',$title);
    }

    //this public method/function is available and accessible anywhere, this function will return the index page as a view
    public function products() {
        //as there is a range of fields that   
        $data = array (
            //the title is the products
            'title' => 'Products',
            //the product list is the enlisted products
            'productlist' => ['Phone','TV & Audio', 'Appliances', 'Computing', 'Gaming', 'Phones', 'Many more smart tech']
        );
        return view('products')->with($data);
    }
}
