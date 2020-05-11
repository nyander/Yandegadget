<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Shipment;
// Required CART - external repository
use Cart;
use Gate;

class ShipmentController extends Controller
{
    //  this will authenticate users
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
        // only the admin can access the shipments page. 
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
     * This function will store the products into the shipment (cart)
     */
    public function store(Request $request)
    {
        // only admin can run this function
        if(Gate::denies('admin-role'))
        {
            return redirect(route('products.index'));
        }
        // this will check for duplicates, which searches for the product ID within the shipment (cart) list  
        $duplicates = Cart::search(function($shipmentItem, $rowId) use($request){
            return $shipmentItem->id === $request->id;
        });

        //if the duplicates return with items, it will 
        if($duplicates->isNotEmpty()){
            return redirect()->route('shipments.index')->with('error', 'Product is already in your shipment list!');
        }
        //this will run if there is no duplicates \, it will add the produt's ID, name, and selling price
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
        //remove the product from the shipment list (cart)
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
        //  First remove the product from the shipment list (Cart) 
        $product = Cart::get($id);
        Cart::remove($id);

        // this is searching for the product in the  an instance called saveForLater.
        $duplicates = Cart::instance('saveForLater')->search(function($shipmentItem, $rowId) use($id){
            return $rowId === $id;
        });
        // if it is not empty, it means the product has already been saved for later 
        if($duplicates->isNotEmpty()){
            return redirect()->route('shipments.index')->with('warning', 'Product is already saved for later');
        }
        
        // this will add the product to the saveForLater instance
        Cart::instance('saveForLater')->add($product->id, $product->name ,1, $product->price)->associate('App\Product');
        return redirect()->route('shipments.index')->with('warning','Product saved for later shipment');
    }
}
