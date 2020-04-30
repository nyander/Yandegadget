<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Cart;
use App\Ship;
use App\ShippedProduct;
use App\ShipCompany;
use DB;
use App\User;
use App\Notifications\NewShipment;
use Carbon\Carbon;

class ShipmentConfirmationController extends Controller
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
        $companies = ShipCompany::all();
        $dt = Carbon::now();
        $today = $dt->toDateString();
        return view('confirmations.index')->with(['companies'=>$companies, 'today'=>$today]);
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

        $staffretrieval = DB::table('role_user')->where('role_id',2)->get();
        foreach ($staffretrieval as $staff){
            User::find($staff->user_id)->notify(new NewShipment);
    
        }
        
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

    
}
