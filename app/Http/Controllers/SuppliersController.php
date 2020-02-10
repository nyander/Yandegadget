<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Supplier;
use Gate;

class SuppliersController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if(Gate::denies('supplier-management')){
            return redirect(route('products.index'));
        }
        $suppliers = Supplier::all();
        return view('suppliers.index')->with('suppliers', $suppliers);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if(Gate::denies('supplier-management')){
            return redirect(route('products.index'));
        }
        return view('suppliers.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request,[
            'name' => 'required',
            'address' => 'required',            
            'contact' => 'required',
            'email' => 'required'
        ]);

        $supplier = new Supplier;
        $supplier->name = request("name");
        $supplier->address = request("address");
        $supplier->contact = request("contact");
        $supplier->email = request("email");
        $supplier->save();
        
        return redirect('/suppliers')->with('success', 'Supplier has been added');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        
        if(Gate::denies('supplier-management')){
            return redirect(route('products.index'));
        }
        $supplier = Supplier::find($id);
        return view('suppliers.show')->with(['supplier'=> $supplier]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if(Gate::denies('supplier-management')){
            return redirect(route('products.index'));
        }
        $supplier = Supplier::find($id);
        return view('suppliers.edit')->with('supplier',$supplier);
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
        $supplier = Supplier::find($id);
        $supplier->name = request("name");
        $supplier->address = request("address");
        $supplier->contact = request("contact");
        $supplier->email = request("email");
        $supplier->save();
        
        return redirect('/suppliers')->with('success', 'Supplier has been updated');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $supplier = Supplier::find($id);
        $supplier->delete();
        
        return redirect('/suppliers')->with('success', 'The supplier has been deleted');
    }

    /*public function list($company_name,$id)
    {
        //
    }*/
}
