<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Cart;

class SaveForLaterController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth','verified']);
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Cart::instance('saveForLater')->remove($id);
        return back()->with('success', 'product has been removed');
    }

    // THis will add the product back into the shipment list 
    public function switchToShipment($id)
    {
        // find the product within the 
        $product = Cart::instance('saveForLater')->get($id);
        Cart::instance('saveForLater')->remove($id);

        $duplicates = Cart::instance('default')->search(function($shipmentItem, $rowId) use($id){
            return $rowId === $id;
        });

        if($duplicates->isNotEmpty()){
            return redirect()->route('shipments.index')->with('warning', 'Product is already in your shipment');
        }
        
        Cart::instance('default')->add($product->id, $product->name ,1, $product->price)->associate('App\Product');
        return redirect()->route('shipments.index')->with('success','Product has been moved to be shipment');
    }
}
