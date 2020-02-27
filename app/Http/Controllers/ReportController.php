<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Product;
use App\Category;
use DB;
use App\User;
use App\Ship;

class ReportController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //users report
        $current_month_users = User::whereMonth('created_at', Carbon::now()->month)->count(); 
        $last_month_users = User::whereMonth('created_at', Carbon::now()->subMonth(1))->count();      
        $last_2_month_users = User::whereMonth('created_at', Carbon::now()->subMonth(2))->count();
        $last_3_month_users = User::whereMonth('created_at', Carbon::now()->subMonth(3))->count();
        $last_4_month_users = User::whereMonth('created_at', Carbon::now()->subMonth(4))->count();
        $last_5_month_users = User::whereMonth('created_at', Carbon::now()->subMonth(5))->count();  
        $last_6_month_users = User::whereMonth('created_at', Carbon::now()->subMonth(6))->count();                                                               
                                        


        $current_month_sold_products = Product::where('sold', true)->whereMonth('sold_Date', Carbon::now()->month)->count();
        $last_1_month_sold_products = Product::where('sold', true)->whereMonth('sold_Date', Carbon::now()->subMonth(1))->count();        
        $last_2_month_sold_products = Product::where('sold', true)->whereMonth('sold_Date', Carbon::now()->subMonth(2))->count();  
        $last_3_month_sold_products = Product::where('sold', true)->whereMonth('sold_Date', Carbon::now()->subMonth(3))->count();
        $last_4_month_sold_products = Product::where('sold', true)->whereMonth('sold_Date', Carbon::now()->subMonth(4))->count();         
        $last_5_month_sold_products = Product::where('sold', true)->whereMonth('sold_Date', Carbon::now()->subMonth(5))->count(); 
        $last_6_month_sold_products = Product::where('sold', true)->whereMonth('sold_Date', Carbon::now()->subMonth(6))->count();  

        $categories = Category::all()->count();   
        $currentDate = Carbon::today()->toDateString(); 
        $previousDate = Carbon::now()->startOfMonth()->subMonth()->toDateString();       
        return view('reports.index')->with(compact('current_month_users','last_month_users','last_2_month_users',
                                                    'last_3_month_users','last_4_month_users','last_5_month_users','last_6_month_users',
                                                    
                                                    'current_month_sold_products', 'last_1_month_sold_products','last_2_month_sold_products',
                                                    'last_3_month_sold_products','last_4_month_sold_products','last_5_month_sold_products','last_6_month_sold_products'
                                                    )) ;
    
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
         
        return view('reports.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $startdate = request('startdate');
        $enddate = request('enddate');
        
        //Sales Based on Type from {{$startdate}} to {{$enddate}}
        $sold_products_category_1  = Product::where('sold', true)->where('sold_Date','>=', $startdate )->where('sold_Date','<=', $enddate)->where('type','1')->count();
        $sold_products_category_2  = Product::where('sold', true)->where('sold_Date','>=', $startdate )->where('sold_Date','<=', $enddate)->where('type','2')->count();
        $sold_products_category_3  = Product::where('sold', true)->where('sold_Date','>=', $startdate )->where('sold_Date','<=', $enddate)->where('type','3')->count();
        $sold_products_category_4  = Product::where('sold', true)->where('sold_Date','>=', $startdate )->where('sold_Date','<=', $enddate)->where('type','4')->count();
        $sold_products_category_5  = Product::where('sold', true)->where('sold_Date','>=', $startdate )->where('sold_Date','<=', $enddate)->where('type','5')->count();
        $sold_products_category_6  = Product::where('sold', true)->where('sold_Date','>=', $startdate )->where('sold_Date','<=', $enddate)->where('type','6')->count();
        $sold_products_category_7  = Product::where('sold', true)->where('sold_Date','>=', $startdate )->where('sold_Date','<=', $enddate)->where('type','7')->count();

        //Sales Based on Condition from {{$startdate}} to {{$enddate}}
        $sold_products_condition_1  = Product::where('sold', true)->where('sold_Date','>=', $startdate )->where('sold_Date','<=', $enddate)->where('condition','1')->count();
        $sold_products_condition_2  = Product::where('sold', true)->where('sold_Date','>=', $startdate )->where('sold_Date','<=', $enddate)->where('condition','2')->count();
        $sold_products_condition_3  = Product::where('sold', true)->where('sold_Date','>=', $startdate )->where('sold_Date','<=', $enddate)->where('condition','3')->count();


        //Financial Balance Sheet
            //stock
            $stock  = Product::where('sold', false)->where('created_at','>=', $startdate )->where('created_at','<=', $enddate)->sum('selling_Price');
            //Delivery Charges
            $delivery_charge = Ship::where('shipment_date','>=', $startdate )->where('shipment_date','<=', $enddate)->sum('shipment_cost');

            //days between start and end date
            $to = Carbon::createFromFormat('Y-m-d',$enddate );
            $from = Carbon::createFromFormat('Y-m-d',$startdate );
            $diff_in_days = $to->diffInDays($from);        
            $diff_in_days;        

            //staff earning
            $staff_payable = (5 * $diff_in_days); 

            //total liability
            $liability_total = ($delivery_charge + $staff_payable);

            
            //sales made during period
            $retained_earnings = Product::where('sold', true)->where('sold_Date','>=', $startdate )->where('sold_Date','<=', $enddate)->sum('selling_Price');
            
            //total assets
            $assets_total = ($retained_earnings + $stock);
        $categories = Category::all();  
        return view('reports.show')->with(compact('startdate','enddate','categories','sold_products_category_1',
                                                    'sold_products_category_2','sold_products_category_3',
                                                    'sold_products_category_4','sold_products_category_5',
                                                    'sold_products_category_6','sold_products_category_7',
                                                    'sold_products_condition_1','sold_products_condition_2',
                                                    'sold_products_condition_3', 'delivery_charge',
                                                    'staff_payable','retained_earnings','liability_total',
                                                    'stock','assets_total' ));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
