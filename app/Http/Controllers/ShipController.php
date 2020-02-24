<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Ship;
use App\ShippedProduct;
use App\Product;

class ShipController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $ships = Ship::all();
        return view('ships.index')->with(['ships'=> $ships]);
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $ship = Ship::find($id);
        $products1 = ShippedProduct::where('shipment_id', $ship->id)->get();
        // $products = ShippedProduct::where('shipment_id', $ship->id)->get();
        return view('ships.show')->with(['ship'=>$ship, 'products1'=> $products1]);
    }

    public function recieved($id){
        // when called, it will set the specific shipment's recieved to true
        $ship = Ship::find($id);
        $ship->recieved = true;
        $ship->save();
        return redirect()->route('ships.index')->with('success','A Shipment has been recieved');
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
