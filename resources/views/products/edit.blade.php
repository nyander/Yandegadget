@extends('layouts.app')

@section('content')
    
    <div id="wrapper">
        <div id="createproduct" class="container">
            <h3>Update Products</h3>


        {{-- Edit Gallery --}}
            <div id="carouselExampleControls" class="carousel slide" data-ride="carousel">
                <div class="carousel-inner">
                    <div class="carousel-item active">
                        <img class="d-block w-100" src="/gallery/{{$product->thumbnail_path}}" alt="First slide">
                    </div>
                    <p hidden>{{$images = DB::table('images')->where('product_id',$product->id)->get()}}
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
       
        


            
            <form method="POST" action="/products/{{$product->id}}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                {{-- Product Name field--}}
                
                
                <div class="field">
                    <label class="label" for="name">Product Name</label>
                    <div class="control">
                    <input class="input" type="text" name="name" id="name" value="{{$product->name}}"> 
                    </div>
                </div>    

                {{-- Cost field--}}
                <div class="field">
                    <label class="label" for="cost">Cost</label>
                    <div class="control">
                        <input class="input" type="number" name="cost" id="cost" value="{{$product->cost}}"> 
                    </div>
                </div>    

                
                {{-- Category field --}}
                <div class="form-group">
                    <label class="label" for="date">Product Type</label>
                    <select name="type" id="type" class="form-control input-lg dynamic" data-dependent="labSubCat">
                        <option value="{{$categories}}">{{$categoriesname}}</option>
                            @foreach($categories as $ct)
                                <option value="{{$ct->id}}">{{$ct->type}}</option>
                            @endforeach
                    </select>
                </div> 

                {{-- Supplier Dropdown --}}
                <div class="form-group">
                    <label class="label" for="date">Supplier</label>
                    <select name="supselect" id="supselect" class="form-control input-lg dynamic" data-dependent="labSubCat" value="{{$product->supplier}}">
                    <option value="{{$supplierid}}">{{$suppliername}}</option>
                        @foreach($suppliers as $lb)
                            <option value="{{$lb->id}}">{{$lb->name}}</option>
                        @endforeach
                    </select>
                </div>   
                
                <div class="field">
                    <label class="label" for="date">Purchase Date</label>
                    <div class="control">
                        <input class="input" type="date" name="purchasedate" placeholder="Enter Date of Purchase" value="{{$product->purchase_Date}}"> 
                    </div>
                </div>  

                {{-- Condititon Dropdown --}}
                <div class="form-group">
                    <label class="label" for="date">Condition</label>
                    <select name="conselect" id="condition" class="form-control input-lg dynamic" data-dependent="labSubCat" value="{{$product->name}}">
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
                        <textarea class="textarea" name="condition_Notes" id="condition_Notes" >{{$product->condition_Notes}}</textarea> 
                    </div>
                </div>

                {{-- Selling Price field--}}
                <div class="field">
                    <label class="label" for="price">Price</label>
                    <div class="control">
                        <input class="input" type="number" name="price" id="price" value="{{$product->selling_Price}}"> 
                    </div>
                </div>
                
                {{-- Featured Product --}}
                <div class="field">
                    <label class="label" for="condition_Notes">Featured ?</label>
                    @if($product->featured = true)
                    Yes: {{Form::radio('featured', 'true', true)}}
                    No:  {{Form::radio('featured', 'false')}} 
                    @else 
                        Yes: {{Form::radio('featured', 'true')}}
                        No:  {{Form::radio('featured', 'false', true)}}  
                                 
                    @endif     
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

