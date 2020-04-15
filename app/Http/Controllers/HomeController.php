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
        // admin dashboard
        $requestedProducts =DB::table('product_requests')->where('acquired',0)->where('deposit_paid',1)->count();
        $purchasedProducts =DB::table('products')->where('sold',1)->count();  
        $supplierProducts =DB::table('supplier_products')->count();  
        $uploadedProducts =DB::table('products')->where('sold',0)->count();  
        $signedCustomers =DB::table('role_user')->where('role_id',3)->count();  
        $signedSuppliers =DB::table('role_user')->where('role_id',4)->count();  
        $suppliersAmount =DB::table('suppliers')->count();  
        $transactionCount =DB::table('transactions')->count(); 

        return view('home')->with(['suppliers' => $suppliers, 'checker' => $checker,
                                    'supproduct'=> $supproduct, 'soldsupproduct'=> $soldsupproduct,
                                    'requestedProducts' => $requestedProducts, 'purchasedProducts'=>$purchasedProducts,
                                    'supplierProducts'=>$supplierProducts,'uploadedProducts'=>$uploadedProducts,
                                    'signedCustomers'=>$signedCustomers,'suppliersAmount'=>$suppliersAmount,
                                    'transactionCount'=>$transactionCount,'signedSuppliers'=>$signedSuppliers]);
    }

    
}
