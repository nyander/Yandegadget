<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PagesController extends Controller
{
    //this public method/function is available and accessible anywhere
    public function index() {
        return 'THIS IS A TEST FOR INDEX FUNCTION ';
    }
}
