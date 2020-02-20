<?php

namespace App\Http\Controllers;

use Cart;
use Stripe;
use DB;
use Illuminate\Http\Request;
use App\ProductRequest;
use Cartalyst\Stripe\Exception\CardErrorException;	

class CheckoutController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //return view('checkouts.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
       $request = ProductRequest::find($id);
       $conditionname = DB::table('conditions')->where('id',$request->condition)->value('details');
       $categoriesname = DB::table('categories')->where('id',$request->type)->value('type'); 
       return   view('checkouts.index')->with(['request'=> $request, 'conditionname'=> $conditionname, 'categoriesname'=> $categoriesname]);        
    }

    public function proceed($id)
    {
        
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
        $productreq = ProductRequest::find($id);
        $productreq->deposit_paid = 1;        
        $productreq->save();   
        
        
        $this->validate($request,[
            'name' => 'required',
            'email' => 'required|email',
            'address' => 'required',
            'city' => 'required',
            'postcode' => 'required',
            'phone' => 'required',
            'name_on_card' => 'required',            
            
        ]);
        // dd($request->all());
        //this is what is parsed into the stripe system. this is collected from the index form.
        // use die dumb method to see what information is being passed in
        try{
            
            $charge = Stripe::charges()->create([
                
                'amount' => $request->charge,
                'currency'=> 'GBP',
                'source' => $request->stripeToken,               
                'description'=> 'Order',
                'receipt_email' => $request->email,
                'metadata' => [
                    'Requested Product' => $request->productname,
                    'Product Type' => $request->type,
                    'Product condition' => $request->condition,                    
                ],
            ]);
            
            //this is going to set the deposit paid field to true           
            //successful
                          
            return redirect()->route('requests.index')->with('success', 'Thank you! Your Payment has been successful');
        } catch (CardErrorException $e) {
            //if the card payment comes back or detects an error, 
            //it should go back to the checkouts page and dispay what is the error 
            return back()->withErrors('Error! ' . $e->getMessage());
        }
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
