<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Supplier;
use Auth;
use DB;
use Carbon\Carbon;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(['auth','verified']);
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $dt = Carbon::now()->startOfMonth();
        $dt1 = Carbon::now()->endOfMonth();
        $today = $dt->toDateString();
        $endmonth = $dt1->toDateString();

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
        $unpaidrequest = DB::table('product_requests')->where('deposit_paid',0)->count();

        // Customer dashboard
        $productsavailable = DB::table('products')->where('received',1)->where('sold',0)->count();
        $requestedprod = DB::table('product_requests')->where('customer_id',Auth::user()->id)->count();
        $acquiredrequ = DB::table('product_requests')->where('customer_id',Auth::user()->id)->where('acquired',1)->count();
        $userunpaidrequest = DB::table('product_requests')->where('customer_id',Auth::user()->id)->where('deposit_paid',0)->count();

        // staff dashboard
        $monthsales = DB::table('products')->where('sold',1)->where('sold_Date','>=', $today)->where('sold_Date','<=', $endmonth)->count();
        $notrecieved = DB::table('ships')->where('received',0)->count();
        $notrecievedprod = DB::table('shipped_product')->where('received',0)->count();        
        
        return view('home')->with(['suppliers' => $suppliers, 'checker' => $checker,
                                    'supproduct'=> $supproduct, 'soldsupproduct'=> $soldsupproduct,
                                    'requestedProducts' => $requestedProducts, 'purchasedProducts'=>$purchasedProducts,
                                    'supplierProducts'=>$supplierProducts,'uploadedProducts'=>$uploadedProducts,
                                    'signedCustomers'=>$signedCustomers,'suppliersAmount'=>$suppliersAmount,
                                    'transactionCount'=>$transactionCount,'signedSuppliers'=>$signedSuppliers,
                                    'productsavailable'=>$productsavailable,'requestedprod'=>$requestedprod,
                                    'acquiredrequ'=>$acquiredrequ, 'userunpaidrequest'=>$userunpaidrequest,
                                    'monthsales'=>$monthsales,'today'=>$today,'endmonth'=>$endmonth,
                                    'notrecieved'=>$notrecieved, 'notrecievedprod'=>$notrecievedprod,'unpaidrequest'=>$unpaidrequest]);
    }

    
}
