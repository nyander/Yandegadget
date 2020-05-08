<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Conversion;
use Gate;

class ConversionController extends Controller
{
    public function index()
    {
        if(Gate::denies(['admin-role']))
        {
                return redirect(route('products.index'));
        }
       
        $conversions = Conversion::all();
        return view('conversions.index')->with(['conversions'=> $conversions]);
    }
    /**
    * Show the form for creating a new resource.
    *
    * @return \Illuminate\Http\Response
    */
    public function edit($id)
    {
        $currency = Conversion::find($id);
        return view('conversions.edit')->with(['currency'=> $currency]);
    }
    

    public function update(Request $request, $id)
    {
        $currency = Conversion::find($id);         
        $currency->rate = request("rate");  
        $currency->save();

        return redirect('/conversions')->with('success', 'Rate Updated');
    }
}
