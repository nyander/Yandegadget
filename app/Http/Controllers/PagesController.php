<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Category;

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
        $categories = Category::all();
        return view('about')->with(['title' => $title, 'categories' => $categories]);
    }

    //this public method/function is available and accessible anywhere, this function will return the index page as a view
    public function products() {
        //as there is a range of fields that   
        $data = array (
            //the title is the products
            'title' => 'Products',
            //the product list is the enlisted products
            'productslist' => ['Phone','TV & Audio', 'Appliances', 'Computing', 'Gaming', 'Phones', 'Many more smart tech']
        );
        //display the products page which passes in the data array
        return view('products')->with($data);
    }

    public function settings() {
        
        return view('settings');
    }
}
