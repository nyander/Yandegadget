<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Ship;
use App\ShippedProduct;
use App\Product;
use App\User;
use Gate;
use DB; 
use App\Notifications\ShipmentReceived;

class ShipController extends Controller
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
        if(Gate::denies(['manage-shipped-products']))
        {
                return redirect(route('products.index'));
        }
        $ships = Ship::all();
        return view('ships.index')->with(['ships'=> $ships]);
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if(Gate::denies(['manage-shipped-products']))
        {
                return redirect(route('products.index'));     
        }
        $ship = Ship::find($id);
        $products1 = ShippedProduct::where('shipment_id', $ship->id)->get();
        // $products = ShippedProduct::where('shipment_id', $ship->id)->get();
        return view('ships.show')->with(['ship'=>$ship, 'products1'=> $products1]);
    }

    public function received($id){
        // when called, it will set the specific shipment's received to true
        $ship = Ship::find($id);
        $ship->received = true;
        $ship->save();
        
        $id1 = auth()->user()->unreadNotifications[0]->id;
        auth()->user()->unreadNotifications->where('id', $id1)->markAsRead(); 

        $adminretrieval = DB::table('role_user')->where('role_id',1)->get();
            foreach ($adminretrieval as $admin)
            {
                User::find($admin->user_id)->notify(new ShipmentReceived);        
            }  
        return redirect()->route('ships.index')->with('success','A Shipment has been received');
    }
  }
