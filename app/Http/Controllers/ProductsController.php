<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Product;
use App\Supplier;
use App\Condition;
use App\Image;
use App\Category;
use Gate;
use DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use App\ShippedProduct;


class ProductsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $pagination = 9;
        $categories = Category::all();
        //categories 
        if(request()->category) {
            //this will return the products based on the requested category            
            $products = Product::where('type', request()->category);
            $categories ;
            $categoryname = optional($categories->where('id', request()->category)->first())->type;
        } else
        {        //Pagination
        $products = Product::where('featured', 'true');
        $categories;        
        $categoryname = 'Featured';
        }
        
        //sort development
        if(request()->sort == 'low_high'){
            $products = $products->OrderBy('selling_Price', 'asc')->paginate($pagination);

        }elseif (request()->sort == 'high_low'){
            $products = $products->OrderBy('selling_Price', 'desc')->paginate($pagination);
        } else{
            $products = $products->paginate($pagination);
        }
        return view('products.index')->with(['products' => $products, 'categories'=> $categories, 'categoryname'=>$categoryname]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if(Gate::denies('manage-products')){
            return redirect(route('products.index'));
        }
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
        if($request->hasfile('thumbnail'))
        {
            $thumb = $request->file('thumbnail');
            $name = pathinfo($thumb->getClientOriginalName(), PATHINFO_FILENAME);
            $filename =  $name.'-'.time().'.'.$thumb->getClientOriginalExtension();
            $location = public_path('./publc/photos/' . $filename);
            $thumb->move(public_path().'/gallery/',$filename);
        }

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
         $product->featured = request("featured");
         $product->thumbnail_path = $filename;
         $product->save();

        if($request->hasfile('images'))
        {
            foreach($request->file('images') as $image) {

                $name = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);

                $filename =  $name.'-'.time().'.'.$image->getClientOriginalExtension();
                $location = public_path('./publc/photos/' . $filename);
                $image->move(public_path().'/gallery/',$filename);
                // width - height
                // Images::make($image)->resize(640, 480)->save($location);

                $photo = new Image;
                $photo->product_id = $product->id;
                $photo->path = $filename;
                $photo->save();  
            }
    
        }

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
        if(Gate::denies('manage-products')){
        return redirect(route('products.index'));
        }
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

    public function recieved($id){
        // when called, it will set the specific shipment's recieved to true
        $product = Product::find($id);
        $product->recieved = true;
        $product->save();
        
        $recievedshipprod = ShippedProduct::where('product_id', $id)->get();
        foreach($recievedshipprod as $prod){
        $prod->recieved =true;
        $prod->save(); 
        }
        return redirect()->back()->with('success','A Product has been recieved');
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
         $product->type = request("type");
         $product->supplier = request("supselect");
         $product->purchase_Date = request("purchasedate");
         $product->condition = request("conselect");
         $product->condition_Notes = request("condition_Notes");
         $product->selling_Price = request("price");
         $product->featured = request("featured");
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
        if(Gate::denies('manage-products')){
            return redirect(route('products.index'));
        }
        $product = Product::find($id);
        File::delete(public_path('/gallery/'.$product->thumbnail_path));
        

        $image = DB::table('images')->where('product_id', $id)->get();
        foreach($image as $im){            
            File::delete(public_path('/gallery/'.$im->path));
            $animage = Image::find($im->id);
            $animage->delete();   
        }
        $product->delete();
        

        return redirect('/products')->with('success', 'The product has been deleted');
    }

    public function purchase($id){
        // when called, it will set the specific shipment's recieved to true
        $product = Product::find($id); 
        return view('products.purchase')->with(['product'=>$product]);
    }

    public function purchaseupdate(Request $request, $id)
    {
         $product = Product::find($id);         
         $product->selling_Price = request("price");
         $product->sold_Date = request("soldDate");
         $product->sold = true;
         $product->save();

         return redirect('/products')->with('success', 'Product Purchased');
    }
}
