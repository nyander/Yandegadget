<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Condition;
use App\Product;
use App\Supplier;
use DB;
use Illuminate\Support\Facades\Auth;

class ConditionsController extends Controller
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
        // $conditions = Condition::OrderBy('created_at', 'desc');
        $conditions = Condition::all();
        return view('conditions.index')->with('conditions', $conditions);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('conditions.create');
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
            'details' => 'required',
            'explanation' => 'required'
        ]);

         $condition = new Condition;
         $condition->details = request("details");
         $condition->explanation = request("explanation");
         $condition->deposit = request("deposit");
         $condition->save();

         return redirect('/conditions')->with('success', 'Condition Uploaded');
        
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
        $condition = Condition::find($id);
        return view('conditions.edit')->with('condition',$condition);
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
        $condition = Condition::find($id);
        $condition->details = request("details");
        $condition->explanation = request("explanation");
        $condition->deposit = request("deposit");
        $condition->save();

         return redirect('/conditions')->with('success', 'Conditions Updated');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $condition = Condition::find($id);
        $condition->delete();
        
        return redirect('/conditions')->with('success', 'The condition has been deleted');
    }
}
