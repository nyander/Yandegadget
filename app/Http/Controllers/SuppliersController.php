<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Supplier;
use Gate;
use Auth;
use DB;

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
        // if(Gate::denies('manage-suppliers')){
        //     return redirect(route('products.index'));
        // }
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
        
        // if(Gate::denies('manage-suppliers')){
        //     return redirect(route('products.index'));
        // }
        $suppliersrole = DB::table('role_user')->where('user_id',4)->get();
        //this will check if the current user is a supplier or not
        $userIsSupplier =    DB::table('role_user')->where('user_id',Auth::user()->id)->where('role_id', 4)->exists();
         $currentUserid  =  DB::table('users')->where('id', Auth::user()->id)->value('id');
        $currentUsername  =  DB::table('users')->where('id', Auth::user()->id)->value('name');
         
        return view('suppliers.create')->with(['suppliersrole'=>$suppliersrole, 'userIsSupplier' => $userIsSupplier, 'currentUserid' => $currentUserid, 'currentUsername' => $currentUsername ]);
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
        ]);

        

        $supplier = new Supplier;
        $supplier->name = request("name");
        $supplier->address = request("address");
        $supplier->contact = request("contact");
        $supplier->supplier_id = request("supplier_id");
        $supplier->save();
        if(Gate::denies('manage-suppliers')){
            return redirect(route('home'));
        }
        else {
            return redirect('/suppliers')->with('success', 'Supplier has been added');
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
        
        // if(Gate::denies('manage-suppliers')){
        //     return redirect(route('products.index'));
        // }
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
        // if(Gate::denies('manage-suppliers')){
        //     return redirect(route('products.index'));
        // }
        $suppliersrole = DB::table('role_user')->where('user_id',4)->get();
        //this will check if the current user is a supplier or not
        $userIsSupplier =    DB::table('role_user')->where('user_id',Auth::user()->id)->where('role_id', 4)->exists();
        $currentUserid  =  DB::table('users')->where('id', Auth::user()->id)->value('id');
        $currentUsername  =  DB::table('users')->where('id', Auth::user()->id)->value('name');
        $supplier = Supplier::find($id);
        $user_supplierid = DB::table('users')->where('id',$supplier->supplier_id)->value('id');
        $user_suppliername = DB::table('users')->where('id',$supplier->supplier_id)->value('name');
        return view('suppliers.edit')->with(['supplier' => $supplier, 'suppliersrole'=>$suppliersrole, 'userIsSupplier' => $userIsSupplier, 'currentUserid' => $currentUserid
                                            , 'currentUsername' => $currentUsername
                                            , 'user_supplierid' => $user_supplierid
                                            , 'user_suppliername' => $user_suppliername ]);
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
        $supplier->supplier_id = request("supplier_id");
        $supplier->save();
        
        if(Gate::denies('manage-suppliers')){
            return redirect(route('home'))->with('success', 'Your Details has been updated');
        }
        else {
        return redirect('/suppliers')->with('success', 'Supplier has been updated');
        }
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
