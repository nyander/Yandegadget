@extends('layouts.app')

@section('content')

<div id="carouselExampleControls" class="carousel slide" data-ride="carousel">
    <div class="carousel-inner">
        <div class="carousel-item active">
            <img class="d-block w-100" src="/gallery/{{$product->thumbnail_path}}" alt="First slide">
        </div>
        <p hidden>{{$images = DB::table('images')->where('supplierproduct_id',$product->id)->get()}}
            <p>
                @foreach ( $images as $image)
                <div class="carousel-item">
                    <img class="d-block w-100" src="/gallery/{{$image->path}}" alt="First slide" width="50" height="50">   
                    <div class="carousel-caption d-none d-md-block">
                        <form action="{{ route('images.destroy', $image->id) }}" method="POST">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger">remove</button>
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
            <form action="{{ route('images.store', $product->id) }}" method="POST" class="form-group" enctype="multipart/form-data">
            @csrf
                <input class="input" type="text" name="productId" id="productId" value="{{$product->id}}" hidden> 
                {{-- Add & Update Gallery --}}
                <div class="form-group">
                    <label class="label" for="imagecollection"> Replace Product Thumbnail </label>
                    <input type="file" class="form-control" name="thumbnail" >
                </div>

                <div class="form-group">
                    <label class="label" for="imagecollection"> Replace Product Images </label>
                    <input type="file" class="form-control" name="images[]" multiple >
                </div>

                <div class="field is-grouped">
                    <div class="control">
                        <button class="button is-link" type="submit">Update</button>
                    </div>
                </div> 
            </form>
        </div>  

    
    <div id="wrapper">
        <div id="updateproduct" class="container">
            <h3>Upload Products</h3>
            <form method="POST" action="/supplierproducts/{{$product->id}}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                {{-- Product Name field--}}
                
                <div class="field">
                    <label class="label" for="name">Product Name</label>
                    <div class="control">
                        <input class="input" type="text" name="name" id="name" value="{{$product->name}}"> 
                    </div>
                </div>    

                {{-- Categories Dropdown --}}
                {{-- <select> is a dropdown --}}
                {{-- <option> is each option of a dropdown --}}
                <div class="form-group">
                    <label class="label" for="date">Product Type</label>
                    <select name="catselect" id="category" class="form-control input-lg dynamic" data-dependent="labSubCat">
                        <option value="{{$categoriesid}}">{{$categoriesname}}</option>
                            @foreach($categories as $ct)
                                <option value="{{$ct->id}}">{{$ct->type}}</option>
                            @endforeach
                    </select>
                </div> 
                

                {{-- Condititon Dropdown --}}
                <div class="form-group">
                    <label class="label" for="date">Condition</label>
                    <select name="conselect" id="condition" class="form-control input-lg dynamic" data-dependent="labSubCat">
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
                <div class="field">
                    <label class="label" for="price">Price ({{$currency}})</label>
                    <div class="control">
                        <input class="input" type="number" name="price" id="price" value="{{$product->selling_Price}}"> 
                    </div>
                </div>
                

                <div class="field is-grouped">
                    <div class="control">
                        <button class="button is-link" type="submit">Submit</button>
                    </div>
                </div>                                
            </form>
        </div>
    </div>    
@endsection

