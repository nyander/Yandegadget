@extends('layouts.app')

@section('content')

<div class="row my-5">
    <div class="col-md-6 order-md-1 my-3">
        <div id="carouselExampleControls" class="carousel slide" data-ride="carousel">
            <div class="carousel-inner">
                <div class="carousel-item active">
                    <img class="d-block w-100" src="/gallery/{{$product->thumbnail_path}}" alt="First slide" style="max-height:15em; object-fit: cover;" >
                </div>
                <p hidden>{{$images = DB::table('images')->where('supplierproduct_id',$product->id)->get()}}</p>
                    @foreach ( $images as $image)
                        <div class="carousel-item">
                            <img class="d-block w-100" src="/gallery/{{$image->path}}" alt="First slide" width="50" height="50" style="max-height:15em; object-fit: cover;">   
                            <div class="carousel-caption d-none d-md-block">
                                <form action="{{ route('images.destroy', $image->id) }}" method="POST" onsubmit="myButton.disabled = true; return true;">
                                    @csrf @method('DELETE')
                                    <button type="submit" name="myButton" class="btn btn-danger">remove</button>
                                </form>
                            </div>                     
                        </div>
                    @endforeach
                    <a class="carousel-control-prev" href="#carouselExampleControls" role="button" data-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="sr-only">Previous</span>
                    </a>
                    <a class="carousel-control-next" href="#carouselExampleControls" role="button" data-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="sr-only">Next</span>
                    </a>
            </div>
        </div>
    
        <div>
            <form action="/images/updatesuppliers/{{$product->id}}" method="POST" class="form-group" enctype="multipart/form-data">
            @csrf
            @method('PUT')
                <input class="input" type="text" name="productId" id="productId" value="{{$product->id}}" hidden> 
                {{-- Add & Update Gallery --}}
                <div class="row mt-2">
                    <div class="form-group col-md-6">
                        <label class="label" for="imagecollection"> Replace Product Thumbnail </label>
                        <input type="file" class="form-control" name="thumbnail" >
                    </div>

                    <div class="form-group col-md-6">
                        <label class="label" for="imagecollection"> Replace Product Images </label>
                        <input type="file" class="form-control" name="images[]" multiple >
                    </div>

                    <div class="field is-grouped" onsubmit="myButton.disabled = true; return true;">
                        <div class="control my-2">
                            <button class="btn btn-md btn-outline-primary" type="submit" name="myButton">Update</button>
                        </div>
                    </div> 
                </div>
            </form>
        </div>  
    </div>

    
    <div class="col-md-6 order-md-2 ">
        <div id="updateproduct" class="container">
            <h3>Upload Products</h3>
            <form method="POST" action="/supplierproducts/{{$product->id}}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                {{-- Product Name field--}}
                
                <div class="field row">
                    <label class="label col-md-6" for="name">Product Name</label>
                    <input class="input col-md-6" type="text" name="name" id="name" value="{{$product->name}}" required>
                </div>  

                <div class="field row">
                    <label class="label col-md-6" for="price">Price ({{$currency}})</label>                  
                        <input class="input col-md-6" type="number" name="price" id="price" value="{{$product->selling_Price}}" required> 
                </div>

                {{-- Categories Dropdown --}}
                {{-- <select> is a dropdown --}}
                {{-- <option> is each option of a dropdown --}}
                <div class="form-group row">
                    <label class="label col-md-6" for="producttype">Product Type</label>
                    <select name="catselect" id="category" class="form-control input-lg dynamic col-md-6" data-dependent="labSubCat" required>
                        <option value="{{$categoriesid}}">{{$categoriesname}}</option>
                            @foreach($categories as $ct)
                                <option value="{{$ct->id}}">{{$ct->type}}</option>
                            @endforeach
                    </select>
                </div> 
                

                {{-- Condititon Dropdown --}}
                <div class="form-group row">
                    <label class="label col-md-6" for="condition">Condition</label>
                    <select name="conselect" id="condition" class="form-control input-lg dynamic col-md-6" data-dependent="labSubCat" required>
                    <option value="{{$conditionid}}">{{$conditionname}}</option>
                        @foreach($conditions as $cn)
                            <option value="{{$cn->id}}">{{$cn->details}}</option>
                        @endforeach
                    </select>
                </div>                

                {{-- Condition Notes --}}
                <div class="field">
                    <label class="label" for="condition_Notes">Condition Notes</label>
                    <div class="control">
                        <textarea class="textarea" name="condition_Notes" id="condition_Notes"> {{$product->condition_Notes}} </textarea> 
                    </div>
                </div>

                {{-- Selling Price field--}}
                
                

                <div class="field is-grouped" onsubmit="myButton.disabled = true; return true;">
                    <div class="control">
                        <button class="btn btn-md btn-outline-primary" type="submit" name="myButton">Submit</button>
                    </div>
                </div>                                
            </form>
        </div>
    </div>

</div>    
@endsection

