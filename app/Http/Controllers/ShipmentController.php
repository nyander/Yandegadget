<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Shipment;
use Cart;
use Gate;

class ShipmentController extends Controller
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
        if(Gate::denies('admin-role'))
        {
            return redirect(route('products.index'));
        }
        return view('shipments.index');
    }

    
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if(Gate::denies('admin-role'))
        {
            return redirect(route('products.index'));
        }
        $duplicates = Cart::search(function($shipmentItem, $rowId) use($request){
            return $shipmentItem->id === $request->id;
        });

        if($duplicates->isNotEmpty()){
            return redirect()->route('shipments.index')->with('success', 'Product is already in your shipment list!');
        }

        Cart::add($request->id, $request->name ,1, $request->selling_Price)->associate('App\Product');
        return redirect()->route('shipments.index')->with('success','Product has been added for shipment');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if(Gate::denies('admin-role'))
        {
            return redirect(route('products.index'));
        }
        Cart::remove($id);
        return back()->with('success', 'product has been removed');
    }

    /**
     * Switch item for shopping cart to save for later
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function switchToSaveForLater($id)
    {
        $product = Cart::get($id);
        Cart::remove($id);

        $duplicates = Cart::instance('saveForLater')->search(function($shipmentItem, $rowId) use($id){
            return $rowId === $id;
        });

        if($duplicates->isNotEmpty()){
            return redirect()->route('shipments.index')->with('warning', 'Product is already saved for later');
        }
        
        Cart::instance('saveForLater')->add($product->id, $product->name ,1, $product->price)->associate('App\Product');
        return redirect()->route('shipments.index')->with('warning','Product saved for later shipment');
    }
}
