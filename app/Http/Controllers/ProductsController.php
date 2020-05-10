<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Product;
use App\Supplier;
use App\SupplierProduct;
use App\ProductRequest;
use App\Condition;
use App\Image;
use App\Category;
use Gate;
use App\User;
use DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use App\ShippedProduct;
use App\Notifications\ProductAcquired;
use Carbon\Carbon;
use App\Conversion;

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
        //if a request for a category has been made 
        if(request()->category) {
            //this will return the products based on the requested category            
            $products = Product::where('type', request()->category);
            $categories;
            $categoryname = optional($categories->where('id', request()->category)->first())->type;
        } 
        else
        {        
            $products = Product::where('featured', 'true');
            $categories;        
            $categoryname = 'Featured';
        }
        
        //if the request made is by low to high or vice versa
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
        // only the admin can create products
        if(Gate::denies('admin-role')){
            return redirect(route('products.index'));
        }

        // in the following tables, select the following fields
        $suppliers = DB::table('suppliers')->select('id','name')->get();
        $conditions = DB::table('conditions')->select('id','details','explanation')->get();
        $categories = DB::table('categories')->select('id','type')->get();
        $currency = '£';
        // This returns current date. Carbon is an external framework for dates 
        $dt = Carbon::now();
        $today = $dt->toDateString();
        return view('products.create')->with(['suppliers'=>$suppliers, 'conditions'=> $conditions, 
                                              'categories'=> $categories, 'currency' => $currency,
                                              'today'=> $today]);    
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // THis will validate the submitted form before storing into the tables.   
        $id = Auth::id();
        $this->validate($request,[
            'name' => 'required',
            'cost' => 'required',
            'catselect' => 'required',
            'purchasedate' => 'required',
            // 'condition' => 'required',
            'price' => 'required',  
            'featured' => 'required',        
            'thumbnail' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'images.*' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ]);

        // if the is a file within the thumbnail,   
        if($request->hasfile('thumbnail'))
        {
            // Create image name using through the filename, date followed by the extension 
            $thumb = $request->file('thumbnail');
            $name = pathinfo($thumb->getClientOriginalName(), PATHINFO_FILENAME);
            $filename =  $name.'-'.time().'.'.$thumb->getClientOriginalExtension();

            // store the file into the gallery image within public folder
            $location = public_path('./publc/photos/' . $filename);
            $thumb->move(public_path().'/gallery/',$filename);
        }
        

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

         // if a field with a requestedby field has been passed in, then add it to the requested_by field
         if($request->requestedby){
           $product->requested_by = request("requestedby");
         }
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

                //create an image record, product ID is used to refer to product  
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
        $product = Product::find($id);

        //if the product has not been recieved and they dont have an admin role     
        if ($product->received == false){
            if(Gate::denies('admin-role')){
                return redirect(route('products.index'));   
            }          
        } 
        
        $supplier = DB::table('suppliers')->where('id', $product->supplier)->value('name');
        $categories = DB::table('categories')->where('id',$product->type)->value('type');
        $condition = DB::table('conditions')->where('id',$product->condition)->value('details');
        $adminname= DB::table('users')->where('id',$product->user_id)->value('name');
        $currency = '£';        
        return view('products.show')->with(['adminname'=>$adminname,'product' => $product, 'supplier'=> $supplier, 'categories'=> $categories, 'currency'=>$currency, 'condition'=>$condition, 'user_id']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {   
        // only admin can edit products
        if(Gate::denies('admin-role')){
        return redirect(route('products.index'));
        }
        $dt = Carbon::now();
        $today = $dt->toDateString();
        $product = Product::find($id);
        $suppliers = DB::table('suppliers')->select('id','name')->get();
        $conditions = DB::table('conditions')->select('id','details')->get();
        $categories = DB::table('categories')->select('id','type')->get();
        $conditionname = DB::table('conditions')->where('id',$product->condition)->value('details');
        $conditionid = DB::table('conditions')->where('id',$product->condition)->value('id');
        $categoriesname = DB::table('categories')->where('id',$product->type)->value('type');
        $suppliername = DB::table('suppliers')->where('id', $product->supplier)->value('name');
        $supplierid = DB::table('suppliers')->where('id', $product->supplier)->value('id');  
        $date = Carbon::createFromFormat('d/m/Y', $product->purchase_Date)->format('Y-m-d');      
        return view('products.edit')->with(['product' => $product,
                                            'suppliers'=>$suppliers, 
                                            'conditions'=> $conditions, 
                                            'categories'=> $categories, 
                                            'categoriesname'=>$categoriesname, 
                                            'suppliername'=> $suppliername,
                                            'conditionname'=> $conditionname,
                                            'conditionid'=> $conditionid,
                                            'supplierid'=> $supplierid,
                                            'date' => $date,
                                            'today' => $today ]);  
    }

    // This function will run when the staff member confirms the products have been recieved.  
    public function received($id){
        // when called, it will set the specific shipment's received to true
        $product = Product::find($id);
        $product->received = true;
        $product->save();
        
        $receivedproduct = ShippedProduct::where('product_id', $id)->get();
        foreach($receivedproduct as $prod)
        {
            $prod->received =true;
            $prod->save(); 
        }
        return redirect()->back()->with('success','A Product has been received');
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
        $this->validate($request,[
            'name' => 'required',
            'cost' => 'required',
            'purchasedate' => 'required',
            'price' => 'required',  
            'images.*' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ]);

        $date = Carbon::createFromFormat('Y-m-d', request("purchasedate"))->format('d/m/Y'); 
         $product = Product::find($id);
         $product->name = request("name");
         $product->cost = request("cost");
         $product->type = request("type");
         $product->supplier = request("supselect");
         $product->purchase_Date = $date;
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
        // only admin can delete products
        if(Gate::denies('admin-role')){
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
        // when called, it will set the specific shipment's received to true
        $dt = Carbon::now();
        $today = $dt->toDateString();
        $product = Product::find($id);         
        $conversionrate = Conversion::find(1);
        $ghanaconversion = $product->selling_Price * $conversionrate->rate;
        $result = $ghanaconversion - ($ghanaconversion * 0.3); 
        return view('products.purchase')->with(['product'=>$product, 'today' => $today, 'result'=>$result, 'ghanaconversion'=>$ghanaconversion]);
    }

    public function purchaseupdate(Request $request, $id)
    {
        
         $product = Product::find($id);  
        //  get the conversion rate 
         $conversionrate = Conversion::find(1);
        //  to get GHS , multiply the GBS selling price by the conversion rate 
         $ghanaconversion = $product->selling_Price * $conversionrate->rate;
        //  The result cannot be lower than the 30% of the GHS
         $result = $ghanaconversion - ($ghanaconversion * 0.3); 
         $price = request("price");
        //  if the purchase amount is lower than the result, send a warning 
         if($price<$result){
            return back()->withErrors('sold price cannot be less than GHS'.$result);
         }  
        //  Otherwise, divide the requested price by the conversion rate to get the GBP
         $bps = $price/$conversionrate->rate;
         $product->selling_Price = $bps;
         $product->sold_Date = request("soldDate");
         $product->sold = true;
         $product->save();

         return redirect('/products')->with('success', 'Product Purchased');
    }

    public function storesupproduct($id)
    {        
        $product = SupplierProduct::find($id);
        $dt = Carbon::now();
        $today = $dt->toDateString();
        $product->purchased = true;
        $product->save();
        $users = DB::table('role_user')->where('role_id',3)->get();
        $suppliers = DB::table('suppliers')->select('id','name')->get();
        $conditions = DB::table('conditions')->select('id','details','explanation')->get();
        $categories = DB::table('categories')->select('id','type')->get();
        $categoriesname = DB::table('categories')->where('id',$product->type)->value('type');
        $suppliername = DB::table('suppliers')->where('id', $product->supplier_id)->value('name');
        $conditionname = DB::table('conditions')->where('id',$product->condition)->value('details');
        $currency = '£';
        return view('products.storeSupplierProduct')->with(['product'=>$product, 'suppliers'=>$suppliers, 
                                                            'conditions'=> $conditions, 'categories'=> $categories, 
                                                            'currency' => $currency, 'categoriesname'=> $categoriesname,
                                                            'suppliername' => $suppliername, 'conditionname' => $conditionname, 
                                                            'today'=>$today, 'users'=>$users ]);
    }
    
    public function storereqproduct($id)
    {
        
        $dt = Carbon::now();
        $today = $dt->toDateString();
        $product = ProductRequest::find($id);
        $product->acquired = true;
        $product->save();
        User::find($product->customer_id)->notify(new ProductAcquired);
        $suppliers = DB::table('suppliers')->select('id','name')->get();
        $conditions = DB::table('conditions')->select('id','details','explanation')->get();
        $categories = DB::table('categories')->select('id','type')->get();
        $categoriesname = DB::table('categories')->where('id',$product->type)->value('type');
        $conditionname = DB::table('conditions')->where('id',$product->condition)->value('details');
        $requestedname = DB::table('users')->where('id',$product->customer_id)->value('name');
        $currency = '£';
        return view('products.storeRequestedProduct')->with(['product'=>$product, 'suppliers'=>$suppliers, 
                                                            'conditions'=> $conditions, 'categories'=> $categories, 
                                                            'currency' => $currency, 'categoriesname'=> $categoriesname,
                                                             'conditionname' => $conditionname, 'requestedname'=> $requestedname,
                                                             'today'=>$today]);
    }

    // Function to markk the product as read as an admin
    public function MarkRead($id){
        $user = \Auth::user();
        $notification = $user->notifications()->where('id',$id)->first();
        if ($notification)
        {
            $notification->markAsRead();
            return back();
        }
        else
            return back()->withErrors('we could not found the specified notification');
    }    
}
