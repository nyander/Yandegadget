<?php

namespace App\Http\Controllers;

use App\Deposit;
use DB;
use Illuminate\Http\Request;

class DepositsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $deposits = Deposit::all();  
        $currency = '£';         
            
        return view('deposits.index')->with(['deposits'=>$deposits]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $categories = DB::table('categories')->select('id','type')->get();   
        $currency = '£';     
        return view('deposits.create')->with(['categories'=> $categories, 'currency' => $currency]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        
        $this->validate($request,[
            'catselect' => 'required',
            'condition_a' => 'required',
            'condition_b' => 'required',
            'condition_c' => 'required',
        ]);

         $deposits = new Deposit;
         $deposits->type = request("catselect");
         $deposits->condition_a = request("condition_a");
         $deposits->condition_b = request("condition_b");
         $deposits->condition_c = request("condition_c");
         $deposits->save();

         return redirect('/deposits')->with('success', 'Deposit Has been set');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
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
        $deposits = Deposit::find($id);        
        $categories = DB::table('categories')->select('id','type')->get();
        $categoriesname = DB::table('categories')->where('id',$deposits->type)->value('type');    
        $currency = '£';         
        return view('deposits.edit')->with(['categories'=> $categories, 
                                            'categoriesname'=>$categoriesname,
                                            'currency' => $currency,
                                            'deposits'=> $deposits]);
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
         $deposits = Deposit::find($id);
         $deposits->condition_a = request("condition_a");
         $deposits->condition_b = request("condition_b");
         $deposits->condition_c = request("condition_c");
         $deposits->save();

         return redirect('/deposits')->with('success', 'Deposit Has been updated');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $deposits = Deposit::find($id);
        $deposits->delete();
        
        return redirect('/deposits')->with('success', 'The Deposit has been removed');
    }
}
