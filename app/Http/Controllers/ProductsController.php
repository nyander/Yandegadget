<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Product;
use App\Supplier;
use App\Condition;
use DB;


class ProductsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        
        //Pagination
        $products = Product::OrderBy('created_at', 'desc')->paginate(12);
        return view('products.index')->with('products', $products);
        
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $suppliers = DB::table('suppliers')->select('id','name')->get();
        $conditions = DB::table('conditions')->select('id','details')->get();
        $categories = DB::table('categories')->select('id','type')->get();
        return view('products.create')->with(['suppliers'=>$suppliers, 'conditions'=> $conditions, 'categories'=> $categories]);    
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
            'cost' => 'required'
        ]);

         $product = new Product;
         $product->name = request("name");
         $product->cost = request("cost");
         $product->type = request("catselect");
         $product->supplier = request("supselect");
        //  $product->purchase_Date = request("date");
         $product->condition = request("conselect");
         $product->condition_Notes = request("condition_Notes");
         $product->selling_Price = request("price");
         $product->save();

         return redirect('/products')->with('success', 'Product Uploaded');
        
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $post = Product::find($id);
        $supplier = Supplier::find($id);
        return view('products.show')->with(['product' => $post, 'supplier'=> $supplier]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
