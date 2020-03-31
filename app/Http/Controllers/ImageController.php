<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Image;
use App\Product;
use App\SupplierProduct;
use Illuminate\Support\Facades\File;

class ImageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        
        
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
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
        $this->validate($request,[       
            'thumbnail' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ]);

        if($request->hasfile('thumbnail'))
        {
            $thumb = $request->file('thumbnail');
            $name = pathinfo($thumb->getClientOriginalName(), PATHINFO_FILENAME);
            $filename =  $name.'-'.time().'.'.$thumb->getClientOriginalExtension();
            $location = public_path('./public/photos/' . $filename);
            $thumb->move(public_path().'/gallery/',$filename);
        }

        
        $product = Product::find($request->productId);
        if($request->hasfile('thumbnail')){
            File::delete(public_path('/gallery/'.$product->thumbnail_path));
            $product->thumbnail_path = $filename;
            $product->save();
        }
        

        if($request->hasfile('images'))
        {
            foreach($request->file('images') as $image) {

                $name = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);

                $filename =  $name.'-'.time().'.'.$image->getClientOriginalExtension();
                $location = public_path('./publc/photos/' . $filename);
                $image->move(public_path().'/gallery/',$filename);

                $photo = new Image;
                $photo->product_id = $product->id;
                $photo->path = $filename;
                $photo->save();  
            }
    
        }

        return back()->with('success', 'Images has been updated'); 
    }

    public function updatesuppliers(Request $request, $id)
    {
        if($request->hasfile('thumbnail'))
        {
            $thumb = $request->file('thumbnail');
            $name = pathinfo($thumb->getClientOriginalName(), PATHINFO_FILENAME);
            $filename =  $name.'-'.time().'.'.$thumb->getClientOriginalExtension();
            $location = public_path('./public/photos/' . $filename);
            $thumb->move(public_path().'/gallery/',$filename);
        }

        
        $product = SupplierProduct::find($request->productId);
        if($request->hasfile('thumbnail')){
            File::delete(public_path('/gallery/'.$product->thumbnail_path));
            $product->thumbnail_path = $filename;
            $product->save();
        }
        

        if($request->hasfile('images'))
        {
            foreach($request->file('images') as $image) {

                $name = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);

                $filename =  $name.'-'.time().'.'.$image->getClientOriginalExtension();
                $location = public_path('./publc/photos/' . $filename);
                $image->move(public_path().'/gallery/',$filename);

                $photo = new Image;
                $photo->supplierproduct_id = $product->id;
                $photo->path = $filename;
                $photo->save();  
            }
    
        }

        return back()->with('success', 'Images has been updated'); 
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $image = Image::find($id);

        File::delete(public_path('/gallery/'.$image->path));            
        $image->delete();  
        
        return back()->with('success', 'Image Has been removed'); 
    }
}
