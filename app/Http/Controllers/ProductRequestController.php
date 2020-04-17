<?php

namespace App\Http\Controllers;

use Auth;
use DB;
use App\ProductRequest;
use Illuminate\Http\Request;


class ProductRequestController extends Controller
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
        $requests = ProductRequest::all();           
            
        return view('requests.index')->with(['requests'=>$requests]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $conditions = DB::table('conditions')->select('id','details','deposit','explanation')->get();
        $categories = DB::table('categories')->select('id','type')->get();
        return view('requests.create')->with(['conditions'=> $conditions, 'categories'=> $categories]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $id = Auth::id();
        $this->validate($request,[
            'name' => 'required'            
        ]);

         $productrequ = new ProductRequest;
         $productrequ->name = request("name");
         $productrequ->customer_id = $id;         
         $productrequ->type = request("type");         
         $productrequ->condition = $condition = DB::table('conditions')->where('deposit',request("condition"))->value('id');
         $productrequ->deposit_paid = false;  
         $productrequ->acquired = false;
         $productrequ->deposit= request("deposit");       
         $productrequ->save();

         return redirect('/requests')->with('success', 'Request has been made');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $requests = ProductRequest::find($id);
        $categories = DB::table('categories')->where('id',$requests->type)->value('type');
        $condition = DB::table('conditions')->where('id',$requests->condition)->value('details');
        return view('requests.show')->with(['requests'=> $requests, 'categories'=> $categories, 'condition'=>$condition ]);
    }

        /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $requests = ProductRequest::find($id);
        $conditions = DB::table('conditions')->select('id','details')->get();
        $categories = DB::table('categories')->select('id','type')->get();
        $conditionname = DB::table('conditions')->where('id',$requests->condition)->value('details');
        $conditionid = DB::table('conditions')->where('id',$requests->condition)->value('id');
        $categoriesname = DB::table('categories')->where('id',$requests->type)->value('type');
        $suppliername = DB::table('suppliers')->where('id', $requests->supplier)->value('name');
        return view('requests.edit')->with(['requests'=> $requests,
                                            'conditions'=> $conditions, 
                                            'categories'=> $categories, 
                                            'categoriesname'=>$categoriesname, 
                                            'suppliername'=> $suppliername,
                                            'conditionname'=> $conditionname,
                                            'conditionid'=> $conditionid,]);
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
        
        $productrequ = ProductRequest::find($id);
        $productrequ->name = request("name");
        $productrequ->customer_id = Auth::id();         
        $productrequ->type = request("type");         
        $productrequ->condition = request("condition");               
        $productrequ->save();

        return redirect('/requests')->with('success', 'Request has been updated');

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $productrequ = ProductRequest::find($id);
        $productrequ->delete();

        return redirect('/requests')->with('success', 'Request has been removed');
    }
    
}
