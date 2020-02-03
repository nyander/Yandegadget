<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Product;
use App\Supplier;
use App\Condition;
use DB;
use Illuminate\Support\Facades\Auth;


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
        $currency = '£';
        return view('products.create')->with(['suppliers'=>$suppliers, 'conditions'=> $conditions, 'categories'=> $categories, 'currency' => $currency]);    
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
            'cost' => 'required'
        ]);

         $product = new Product;
         $product->name = request("name");
         $product->user_id = $id;
         $product->cost = request("cost");
         $product->type = request("catselect");
         $product->supplier = request("supselect");
         $product->purchase_Date = request("purchasedate");
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
        $supplier = DB::table('suppliers')->where('id', $post->supplier)->value('name');
        $categories = DB::table('categories')->where('id',$post->type)->value('type');
        $condition = DB::table('conditions')->where('id',$post->condition)->value('details');
        $adminname= DB::table('users')->where('id',$post->user_id)->value('name');
        $currency = '£';        
        return view('products.show')->with(['adminname'=>$adminname,'product' => $post, 'supplier'=> $supplier, 'categories'=> $categories, 'currency'=>$currency, 'condition'=>$condition, 'user_id']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $product = Product::find($id);
        $suppliers = DB::table('suppliers')->select('id','name')->get();
        $conditions = DB::table('conditions')->select('id','details')->get();
        $categories = DB::table('categories')->select('id','type')->get();
        $conditionname = DB::table('conditions')->where('id',$product->condition)->value('details');
        $conditionid = DB::table('conditions')->where('id',$product->condition)->value('id');
        $categoriesname = DB::table('categories')->where('id',$product->type)->value('type');
        $suppliername = DB::table('suppliers')->where('id', $product->supplier)->value('name');
        $supplierid = DB::table('suppliers')->where('id', $product->supplier)->value('id');        
        return view('products.edit')->with(['product' => $product,
                                            'suppliers'=>$suppliers, 
                                            'conditions'=> $conditions, 
                                            'categories'=> $categories, 
                                            'categoriesname'=>$categoriesname, 
                                            'suppliername'=> $suppliername,
                                            'conditionname'=> $conditionname,
                                            'conditionid'=> $conditionid,
                                            'supplierid'=> $supplierid]);  
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
         $product = Product::find($id);
         $product->name = request("name");
         $product->cost = request("cost");
         $product->type = request("catselect");
         $product->supplier = request("supselect");
         $product->purchase_Date = request("purchasedate");
         $product->condition = request("conselect");
         $product->condition_Notes = request("condition_Notes");
         $product->selling_Price = request("price");
         $product->save();

         return redirect('/products')->with('success', 'Product Updated');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $product = Product::find($id);
        $product->delete();

        return redirect('/products')->with('success', 'The product has been deleted');
    }
}
