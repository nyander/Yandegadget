<?php

namespace App\Http\Controllers;

use App\SupplierProduct;
use Illuminate\Http\Request;
use DB;
use Auth;
use App\Image;
use Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use App\ShippedProduct;
use App\Supplier;
use App\Notifications\NewSupplierProduct;
use App\User;


class SupplierProductController extends Controller
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
        
        if(Gate::denies('supproducts')){
            return redirect('/home');
        }
        if(Gate::denies('admin-role')){
            $products = SupplierProduct::where('supplier_id', Auth::id())->get();
            return view('supplierproducts.index')->with(['products'=> $products]);
        }
        $products = SupplierProduct::all();
        return view('supplierproducts.index')->with(['products'=> $products]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()    {
        if(Gate::denies('supplier-role')){
            return back();
        }

        $checker = Supplier::where('supplier_id', Auth::user()->id)->doesntExist();
        if ($checker){
            return redirect('/home')->with('error', 'Add Your Details to enable product management')->with(['checker'=> $checker]);
        }
        
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
            'catselect'=>'required',
            'price' => 'required',
            'conselect' => 'required',
            'thumbnail' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'images.*' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048'

        ]);

        if($request->hasfile('thumbnail'))
        {
            
            $thumb = $request->file('thumbnail');
            $name = pathinfo($thumb->getClientOriginalName(), PATHINFO_FILENAME);
            $filename =  $name.'-'.time().'.'.$thumb->getClientOriginalExtension();
            $location = public_path('./publc/photos/' . $filename);
            $thumb->move(public_path().'/gallery/',$filename);
        }    


         $product = new SupplierProduct;
         $product->supplier_id = $id;    
         $product->name = request("name");              
         $product->type = request("catselect");         
         $product->condition = request("conselect");
         $product->condition_Notes = request("condition_Notes");
         $product->selling_Price = request("price");
         $product->purchased = false; 
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
                $photo->supplierproduct_id = $product->id;
                $photo->path = $filename;
                $photo->save();  
            }
            
        }

        $staffretrieval = DB::table('role_user')->where('role_id',1)->get();
        foreach ($staffretrieval as $staff){
            User::find($staff->user_id)->notify(new NewSupplierProduct);
    
        }
        

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
        if(Gate::denies('supplier-role')){
            return back();
        }
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
        // $id = Auth::id();
        $this->validate($request,[
            'name' => 'required', 
            'catselect'=>'required',
            'price' => 'required',
            'conselect' => 'required',            

        ]);

         $product = SupplierProduct::find($id);         
         $product->name = request("name");              
         $product->type = request("catselect");         
         $product->condition = request("conselect");
         $product->condition_Notes = request("condition_Notes");
         $product->selling_Price = request("price");         
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
        if(Gate::denies('supproducts')){
            return back();
        }
        $product = SupplierProduct::find($id);
        File::delete(public_path('/gallery/'.$product->thumbnail_path));
        $product->delete();

        $image = DB::table('images')->where('supplierproduct_id', $id)->get();
        foreach($image as $im){            
            File::delete(public_path('/gallery/'.$im->path));
            $animage = Image::find($im->id);
            $animage->delete();   
        }
        $product->delete();
        
        return redirect('/supplierproducts')->with('success', 'The product has been deleted');
    }

    public function MarkRead($id){
        $user = \Auth::user();
        $notification = $user->notifications()->where('id',$id)->first();
        if ($notification)
        {
            $notification->delete();
            return back();
        }
        else
            return back()->withErrors('we could not found the specified notification');
    }    
}
