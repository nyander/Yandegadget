<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Supplier;
use Auth;
use DB;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $suppliers = DB::table('suppliers')->where('supplier_id', Auth::user()->id)->value('id');
        //this is to check whether the current user's ID does not exist in the table. 
        $supproduct = DB::table('supplier_products')->where('supplier_id',Auth::user()->id)->count();
        $soldsupproduct = DB::table('supplier_products')->where('supplier_id',Auth::user()->id)->where('purchased',1)->count();
        $checker = Supplier::where('supplier_id', Auth::user()->id)->doesntExist();
        return view('home')->with(['suppliers' => $suppliers, 'checker' => $checker,'supproduct'=> $supproduct, 'soldsupproduct'=> $soldsupproduct]);
    }

    
}
