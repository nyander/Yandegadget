@extends('layouts.app')

@section('content')
    
    <div id="wrapper">
        <br>
            <h3>Update Products</h3>
        <div class="row">

            <div class="col-md-5 order-md-1 mr-5 mt-2">
                <div id="carouselExampleControls" class="carousel slide" data-ride="carousel">
                    <div class="carousel-inner">
                        <div class="carousel-item active">
                            <img class="d-block w-100" src="/gallery/{{$product->thumbnail_path}}" alt="First slide">
                        </div>
                        <p hidden>{{$images = DB::table('images')->where('product_id',$product->id)->get()}}
                            <p>
                                @foreach ( $images as $image)
                                <div class="carousel-item">
                                    <img class="d-block w-100" src="/gallery/{{$image->path}}" alt="First slide" style="max-height:80px; max-width:auto;">
                                    <div class="carousel-caption d-none d-md-block">
                                        <form action="{{ route('images.destroy', $image->id) }}" method="POST" onsubmit="myButton.disabled = true; return true;">
                                            @csrf @method('DELETE')
                                            <button type="submit" name="myButton" class="btn btn-danger">Delete</button>
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
                    <form action="/images/{{$product->id}}" method="POST" class="form-group" enctype="multipart/form-data" onsubmit="myButton.disabled = true; return true;">
                    @csrf
                    @method('PUT')
                        <input class="input" type="text" name="productId" id="productId" value="{{$product->id}}" hidden> 
                        
                        <div class="row mt-3">
                            {{-- Add & Update Gallery --}}
                            <div class="form-group col-md-6">
                                <label class="label" for="imagecollection"> Replace Product Thumbnail </label>
                                <input type="file" class="form-control" name="thumbnail" >
                            </div>

                            <div class="form-group col-md-6">
                                <label class="label" for="imagecollection"> Replace Product Images </label>
                                <input type="file" class="form-control" name="images[]" multiple >
                            </div>
                        </div>

                        <div class="field is-grouped">
                            <div class="control">
                                <button class="button is-link" name="myButton" type="submit">Update</button>
                            </div>
                        </div> 
                    </form>
                </div>
            </div>
       
        


            <div class="col-md-5 order-md-2">
                <form method="POST" action="/products/{{$product->id}}" enctype="multipart/form-data" onsubmit="myButton.disabled = true; return true;">
                    @csrf
                    @method('PUT')
                    {{-- Product Name field--}}
                    
                    
                    <div class="field row">
                        <label class="label col-md-5" for="name">Product Name</label>
                        <div class="control col-md-7">
                        <input class="input" type="text" name="name" id="name" value="{{$product->name}}" required> 
                        </div>
                    </div>    

                    {{-- Cost field--}}
                    <div class="field row">
                        <label class="label col-md-8" for="cost">Cost</label>
                        <div class="control col-md-4">
                            <input class="input" type="number" name="cost" id="cost" value="{{$product->cost}}" required> 
                        </div>
                    </div>    

                    {{-- Selling Price field--}}
                    <div class="field row">
                        <label class="label col-md-8" for="price">Price</label>
                        <div class="control col-md-4">
                            <input class="input" type="number" name="price" id="price" value="{{$product->selling_Price}}" required> 
                        </div>
                    </div>

                    <div class="field row">
                        <label class="label col-md-7" for="date">Purchase Date</label>
                        <div class="control">  
                            <input class="input col-md-12" type="date" name="purchasedate" max="{{$today}}" placeholder="Enter Date of Purchase" value={{$date}} required>                         
                        </div>
                    </div>  

                    <div class="row">
                        {{-- Category field --}}
                        <div class="form-group col-md-4">
                            <label class="label" for="date">Product Type</label>
                            <select name="type" id="type" class="form-control input-lg dynamic" data-dependent="labSubCat" required>
                                <option value="{{$product->type}}">{{$categoriesname}}</option>
                                    @foreach($categories as $ct)
                                        <option value="{{$ct->id}}">{{$ct->type}}</option>
                                    @endforeach
                            </select>
                        </div> 

                        {{-- Supplier Dropdown --}}
                        <div class="form-group col-md-4">
                            <label class="label" for="date">Supplier</label>
                            <select name="supselect" id="supselect" class="form-control input-lg dynamic" data-dependent="labSubCat" value="{{$product->supplier}}" required>
                            <option value="{{$supplierid}}">{{$suppliername}}</option>
                                @foreach($suppliers as $lb)
                                    <option value="{{$lb->id}}">{{$lb->name}}</option>
                                @endforeach
                            </select>
                        </div>   
                        
                        

                        {{-- Condititon Dropdown --}}
                        <div class="form-group col-md-4">
                            <label class="label" for="date">Condition</label>
                            <select name="conselect" id="condition" class="form-control input-lg dynamic" data-dependent="labSubCat" value="{{$product->name}}" required>
                            <option value="{{$conditionid}}">{{$conditionname}}</option>
                                @foreach($conditions as $cn)
                                    <option value="{{$cn->id}}">{{$cn->details}}</option>
                                @endforeach
                            </select>
                        </div>   
                    </div>             

                    {{-- Condition Notes --}}
                    <div class="field">
                        <label class="label" for="condition_Notes">Condition Notes</label>
                        <div class="control">
                            <textarea class="textarea" name="condition_Notes" id="condition_Notes" >{{$product->condition_Notes}}</textarea> 
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
                            <button class="button is-link" type="submit" name="myButton">Submit</button>
                        </div>
                    </div>                                
                </form>
            </div>
        </div>
    </div>    
@endsection

