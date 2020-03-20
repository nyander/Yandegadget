<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Product;
use App\Category;
use DB;
use App\User;
use App\Ship;
use App\Transaction;
use App\StaffWage;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
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
        $transactions = DB::table('transactions')->whereMonth('created_at', Carbon::now()->month)->orderBy('date', 'desc')->get(); 
        return view('reports.index')->with(compact('current_month_users','last_month_users','last_2_month_users',
                                                    'last_3_month_users','last_4_month_users','last_5_month_users','last_6_month_users',
                                                    
                                                    'current_month_sold_products', 'last_1_month_sold_products','last_2_month_sold_products',
                                                    'last_3_month_sold_products','last_4_month_sold_products','last_5_month_sold_products','last_6_month_sold_products',
                                                    'transactions'
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
            //Assets
                //Current Assets
                $cash = Transaction::where('type',1)->where('date','>=', $startdate )->where('date','<=', $enddate)->sum('amount') ;
                $account_Recievable = Transaction::where('type',2)->where('date','>=', $startdate )->where('date','<=', $enddate)->sum('amount') ;
                $stock  = Product::where('sold', false)->where('created_at','>=', $startdate )->where('created_at','<=', $enddate)->sum('selling_Price');
                $prepaid_Expense = Transaction::where('type',4)->where('date','>=', $startdate )->where('date','<=', $enddate)->sum('amount') ;
                $othercurrentassets = Transaction::where('type',5)->where('date','>=', $startdate )->where('date','<=', $enddate)->sum('amount') ;
                //Fixed Assets
                $land = Transaction::where('type',7)->where('date','>=', $startdate )->where('date','<=', $enddate)->sum('amount') ;
                $building = Transaction::where('type',8)->where('date','>=', $startdate )->where('date','<=', $enddate)->sum('amount') ;
                $vehicles = Transaction::where('type',9)->where('date','>=', $startdate )->where('date','<=', $enddate)->sum('amount') ;
                $machinery = Transaction::where('type',10)->where('date','>=', $startdate )->where('date','<=', $enddate)->sum('amount') ;
                $computer = Transaction::where('type',11)->where('date','>=', $startdate )->where('date','<=', $enddate)->sum('amount') ;
                $otherfixedassets = Transaction::where('type',12)->where('date','>=', $startdate )->where('date','<=', $enddate)->sum('amount') ;
                
            $totalassets = $cash + $account_Recievable + $stock + $prepaid_Expense  + $othercurrentassets + $land + $building + $vehicles + $machinery + $computer + $otherfixedassets;

            //Liabilties
                //Current Liabilities
                $account_payable  =  Transaction::where('type',13)->where('date','>=', $startdate )->where('date','<=', $enddate)->sum('amount');
                $rent = Transaction::where('type', 6)->where('date','>=', $startdate )->where('date','<=', $enddate)->sum('amount') ;                 
                $delivery_charge = Ship::where('shipment_date','>=', $startdate )->where('shipment_date','<=', $enddate)->sum('shipment_cost');
                $overdrafts = Transaction::where('type',14)->where('date','>=', $startdate )->where('date','<=', $enddate)->sum('amount');
                $currentLeasePayaple = Transaction::where('type',15)->where('date','>=', $startdate )->where('date','<=', $enddate)->sum('amount');
                $customerRepayment = Transaction::where('type',16)->where('date','>=', $startdate )->where('date','<=', $enddate)->sum('amount');
                $otherCurrentLiabilities = Transaction::where('type',17)->where('date','>=', $startdate )->where('date','<=', $enddate)->sum('amount');
                
                //START OF WAGES HERE
                $wages = StaffWage::all();
                $to = Carbon::createFromFormat('Y-m-d',$enddate );
                $from = Carbon::createFromFormat('Y-m-d',$startdate );
                $diff_in_days = $to->diffInDays($from);        
                $diff_in_days;     

                $sum = 0;

                foreach($wages as $wage){
                    if($startdate < $wage->startDate){
                        if($enddate > $wage->endDate){
                            $from = Carbon::createFromFormat('Y-m-d',$wage->startDate ); 
                            $to = Carbon::createFromFormat('Y-m-d',$wage->endDate );    
                            $diff_in_days = $to->diffInDays($from);
                            $diff_in_days;                            
                            $totalwage = $diff_in_days * $wage->wage;                                
                            $sum = $sum + $totalwage;
                            
                    }
                    elseif($enddate <= $wage->endDate && $enddate > $wage->startDate){
                        $from = Carbon::createFromFormat('Y-m-d',$wage->startDate ); 
                            $to = Carbon::createFromFormat('Y-m-d',$enddate );    
                            $diff_in_days = $to->diffInDays($from);
                            $diff_in_days;                            
                            $totalwage = $diff_in_days * $wage->wage;                                
                            $sum = $sum + $totalwage;
                    }
                }
                elseif($startdate >= $wage->startDate){
                    if($enddate > $wage->startDate && $enddate <= $wage->endDate){
                        $from = Carbon::createFromFormat('Y-m-d',$startdate ); 
                            $to = Carbon::createFromFormat('Y-m-d',$enddate );    
                            $diff_in_days = $to->diffInDays($from);
                            $diff_in_days;                            
                            $totalwage = $diff_in_days * $wage->wage;                                
                            $sum = $sum + $totalwage;
                    }
                    elseif($enddate > $wage->endDate && $startdate < $wage->endDate){
                        $from = Carbon::createFromFormat('Y-m-d',$startdate ); 
                            $to = Carbon::createFromFormat('Y-m-d',$wage->endDate );    
                            $diff_in_days = $to->diffInDays($from);
                            $diff_in_days;                            
                            $totalwage = $diff_in_days * $wage->wage;                                
                            $sum = $sum + $totalwage;
                }
            }
        };
            
                
                //END OF WAGES


                
                //Fixed liabilities
                $longTermDepts = Transaction::where('type',18)->where('date','>=', $startdate )->where('date','<=', $enddate)->sum('amount');
                $pensionFundLiability = Transaction::where('type',19)->where('date','>=', $startdate )->where('date','<=', $enddate)->sum('amount');
                $prefferedTaxedLiability = Transaction::where('type',20)->where('date','>=', $startdate )->where('date','<=', $enddate)->sum('amount');
                $otherLongTermLiability = Transaction::where('type',21)->where('date','>=', $startdate )->where('date','<=', $enddate)->sum('amount');

            $totalliability = $account_payable + $sum + $rent +  $delivery_charge +  $overdrafts +  $currentLeasePayaple +  $customerRepayment + $otherCurrentLiabilities + $longTermDepts + $pensionFundLiability + $prefferedTaxedLiability + $otherLongTermLiability;

            //Equity
            $retained_earnings = Product::where('sold', true)->where('sold_Date','>=', $startdate )->where('sold_Date','<=', $enddate)->sum('selling_Price');
            $treausryStock = Transaction::where('type',23)->where('date','>=', $startdate )->where('date','<=', $enddate)->sum('amount');
            $commonStock = Transaction::where('type',24)->where('date','>=', $startdate )->where('date','<=', $enddate)->sum('amount');
            $otherShareholderEquity = Transaction::where('type',25)->where('date','>=', $startdate )->where('date','<=', $enddate)->sum('amount');
            
            $totalequity = $retained_earnings + $treausryStock +  $commonStock + $otherShareholderEquity;   

           $totalequityliability =  $totalliability + $totalequity;
            //days between start and end date
            
            //staff earning
            $staff_payable = (5 * $diff_in_days); 

            //total liability
            $liability_total = ($delivery_charge + $staff_payable);

            
            
            
            //total assets
            $assets_total = ($retained_earnings + $stock);


            //Income Statement
            $product_cost = Product::where('sold', true)->where('sold_Date','>=', $startdate )->where('sold_Date','<=', $enddate)->sum('cost');
            $gross_profit = $retained_earnings - $product_cost;
            $totalexpenses = $delivery_charge + $sum + $account_payable + $rent + $overdrafts + $currentLeasePayaple + $customerRepayment;
            $other_Income = Transaction::where('type',26)->where('date','>=', $startdate )->where('date','<=', $enddate)->sum('amount');
            $income_provision = Transaction::where('type',27)->where('date','>=', $startdate )->where('date','<=', $enddate)->sum('amount');
            $provision_for_income_taxes = Transaction::where('type',28)->where('date','>=', $startdate )->where('date','<=', $enddate)->sum('amount');
            $totalotherincome = $other_Income + $income_provision + $provision_for_income_taxes;

            $categories = Category::all();  
        return view('reports.show')->with(compact('startdate','enddate','categories','sold_products_category_1',
                                                    'sold_products_category_2','sold_products_category_3',
                                                    'sold_products_category_4','sold_products_category_5',
                                                    'sold_products_category_6','sold_products_category_7',
                                                    'sold_products_condition_1','sold_products_condition_2',
                                                    'sold_products_condition_3', 'delivery_charge',
                                                    'staff_payable','retained_earnings','liability_total',
                                                    'stock','assets_total','cash','account_Recievable','stock','prepaid_Expense'
                                                    ,'othercurrentassets','land','building','vehicles'
                                                    ,'machinery','computer','otherfixedassets','account_payable','rent','delivery_charge','overdrafts', 
                                                    'currentLeasePayaple','customerRepayment','otherCurrentLiabilities','sum','longTermDepts','pensionFundLiability','totalassets',
                                                    'prefferedTaxedLiability','otherLongTermLiability','totalliability','retained_earnings','treausryStock','commonStock','otherShareholderEquity','totalequity','totalequityliability',
                                                    'gross_profit','product_cost','totalexpenses','other_Income', 'income_provision', 'provision_for_income_taxes',
                                                    'totalotherincome'));
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
