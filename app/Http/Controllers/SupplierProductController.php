<?php

namespace App\Http\Controllers;

use App\SupplierProduct;
use Illuminate\Http\Request;
use DB;
use Auth;


class SupplierProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $products = SupplierProduct::all();
        return view('supplierproducts.index')->with(['products'=> $products]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()    {
        
        $conditions = DB::table('conditions')->select('id','details')->get();
        $categories = DB::table('categories')->select('id','type')->get();
        $currency = '£';
        return view('supplierproducts.create')->with(['conditions'=> $conditions, 'categories'=> $categories, 'currency' => $currency]);;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $id = Auth::id();
        $this->validate($request,[
            'name' => 'required',            
        ]);

         $product = new SupplierProduct;
         $product->supplier_id = $id;    
         $product->name = request("name");              
         $product->type = request("catselect");         
         $product->condition = request("conselect");
         $product->condition_Notes = request("condition_Notes");
         $product->selling_Price = request("price");
         $product->interested = false;
         $product->purchased = false;         
         $product->save();

         return redirect('/supplierproducts')->with('success', 'Product Uploaded');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\SupplierProduct  $supplierProduct
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $post = SupplierProduct::find($id);
        $supplier = DB::table('users')->where('id', $post->supplier_id)->value('name');
        $categories = DB::table('categories')->where('id',$post->type)->value('type');
        $condition = DB::table('conditions')->where('id',$post->condition)->value('details');
        $currency = '£';        
        return view('supplierproducts.show')->with(['product' => $post, 'supplier'=> $supplier, 'categories'=> $categories, 'currency'=>$currency, 'condition'=>$condition, 'user_id']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\SupplierProduct  $supplierProduct
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $product = SupplierProduct::find($id);
        
        $conditions = DB::table('conditions')->select('id','details')->get();
        $categories = DB::table('categories')->select('id','type')->get();
        $conditionname = DB::table('conditions')->where('id',$product->condition)->value('details');
        $conditionid = DB::table('conditions')->where('id',$product->condition)->value('id');
        $categoriesname = DB::table('categories')->where('id',$product->type)->value('type');
        $categoriesid = DB::table('categories')->where('id',$product->type)->value('id');
        $currency = '£';      
        return view('supplierproducts.edit')->with(['product' => $product,                                            
                                            'conditions'=> $conditions, 
                                            'categories'=> $categories, 
                                            'categoriesname'=>$categoriesname,                                            
                                            'conditionname'=> $conditionname,
                                            'conditionid'=> $conditionid,
                                            'currency' => $currency,
                                            'categoriesid' => $categoriesid]); 
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\SupplierProduct  $supplierProduct
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $id = Auth::id();
        $this->validate($request,[
            'name' => 'required',            
        ]);

         $product = SupplierProduct::find($id) ;
         $product->supplier_id = $id;    
         $product->name = request("name");              
         $product->type = request("catselect");         
         $product->condition = request("conselect");
         $product->condition_Notes = request("condition_Notes");
         $product->selling_Price = request("price");
         $product->interested = false;
         $product->purchased = false;         
         $product->save();

         return redirect('/supplierproducts')->with('success', 'Product Updated');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\SupplierProduct  $supplierProduct
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $product = SupplierProduct::find($id);
        $product->delete();
        
        return redirect('/supplierproducts')->with('success', 'The product has been deleted');
    }
}
