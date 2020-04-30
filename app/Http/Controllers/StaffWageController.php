<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\StaffWage;

class StaffWageController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth','verified']);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $wages = StaffWage::all();
        return view('staffwages.index')->with(['wages'=>$wages]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $currency = '£';
        return view('staffwages.create')->with(['currency' => $currency]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $wages = new StaffWage;
        $wages->startDate = request("startdate");
        $wages->endDate = request("enddate");        
        $wages->wage = request("wages");
        
        $wages->save();

        return redirect('/staffwages')->with('success', 'Wage has been added');
    }
    
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $wage = StaffWage::find($id);
        $wage->delete();
        
        return redirect('/staffwages')->with('success', 'Wage has been deleted');
    }
}
