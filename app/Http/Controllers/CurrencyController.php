<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Currency;
use Gate;

class CurrencyController extends Controller
{
    public function index()
    {
        if(Gate::denies(['admin-role']))
        {
                return redirect(route('products.index'));
        }
       
        $currencies = Currency::all();
        return view('currencies.index')->with(['currencies'=> $currencies]);
    }
    /**
    * Show the form for creating a new resource.
    *
    * @return \Illuminate\Http\Response
    */
    public function edit($id)
    {
        $currency = Currency::find($id);
        return view('currencies.edit')->with(['currency'=> $currency]);
    }
    

    public function update(Request $request, $id)
    {
        $currency = Currency::find($id);         
        $currency->rate = request("rate");  
        $currency->save();

        return redirect('/currencies')->with('success', 'Rate Updated');
    }
}
