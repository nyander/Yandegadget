<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Cart;
use App\Ship;
use App\ShippedProduct;
use App\ShipCompany;

class ShipmentConfirmationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $companies = ShipCompany::all();
        return view('confirmations.index')->with(['companies'=>$companies]);
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
        $this->addToOrdersTable($request);

        //when successful it will remove the items from the cart
        Cart::instance('default')->destroy();
        return redirect()->route('ships.index')->with('success', 'Shipment has been confirmed'); 
    }

    protected function addToOrdersTable($request)
    {
        //Insert ship into table
        $ship = Ship::create([
            'user_id' => auth()->user() ? auth()->user()->id : null,
            'shipped' => true,
            'shipment_company' => request("shipmentcompany"),
            'shipment_date' => request("shipmentdate"),
            'shipment_cost' => request("cost"),
            'shipment_notes' => request("shipment_Notes"),   
        ]);
        //insert shipped_products into table
        foreach (Cart::content() as $item) {
            ShippedProduct::create([
                'shipment_id' => $ship->id,
                'product_id' => $item->model->id ,
                            
            ]);             
        } 
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
