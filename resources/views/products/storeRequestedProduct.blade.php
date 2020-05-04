@extends('layouts.app')

@section('content')
    
    <div class="row my-5">
        <div class="col-md-5 order-md-1 mr-5">
            <div class="container bg-info text-white p-3">
                <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy 
                    text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It 
                    has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged.
                </p>
                <a href="/products"><button class="btn btn-md btn-outline-light">Already Recorded</button></a>
            </div>
            <br>
            <div class="container bg-success text-white p-3">
                <h5>Conditions</h5>
                @foreach($conditions as $cn)
                    <p>{{$cn->details}} : {{$cn->explanation}}</p>
                @endforeach
            </div>
        </div>
        <div class="col-md-5 order-md-2">
            <h3>Store Products</h3>
            <form method="POST" action="/products"  enctype="multipart/form-data" onsubmit="myButton.disabled = true; return true;">       
                @csrf
                {{-- Product Name field--}}
                <div class="field row">
                    <label class="label col-md-6" for="name">Product Name</label>
                    <input class="input col-md-6" type="text" name="name" id="name" value="{{$product->name}}" required>
                </div>    

                {{-- Requested By  --}}
                <div class="form-group row">
                    <label class="label col-md-6" for="requestedby">Requested By</label>
                    <select name="requestedby" id="requestedby" class="form-control input-lg dynamic col-md-6" data-dependent="labSubCat" readonly>
                    <option value="{{$product->customer_id}}">{{$requestedname}}</option>                        
                    </select>
                </div> 
                

                {{-- Cost field--}}
                <div class="field row">
                    <label class="label col-md-6" for="cost">Cost ({{$currency}})</label>
                    <input class="input col-md-6" type="number" name="cost" id="cost" required>
                </div>    

                {{-- Selling Price field--}}
                <div class="field row">
                    <label class="label col-md-6" for="price">Price ({{$currency}})</label>                    
                    <input class="input col-md-6" type="number" name="price" id="price" required>
                </div>

                {{-- Categories Dropdown --}}
                {{-- <select> is a dropdown --}}
                {{-- <option> is each option of a dropdown --}}
                <div class="form-group row">
                    <label class="label col-md-6" for="date">Product Type</label>
                    <select name="catselect" id="category" class="form-control input-lg dynamic col-md-6" data-dependent="labSubCat" required>
                        <option value="{{$product->type}}">{{$categoriesname}}</option>
                            @foreach($categories as $ct)
                                <option value="{{$ct->id}}">{{$ct->type}}</option>
                            @endforeach
                    </select>
                </div> 

                {{-- Supplier Dropdown --}}
                <div class="form-group row">
                    <label class="label col-md-6" for="date">Supplier</label>
                    <select name="supselect" id="supplier" class="form-control input-lg dynamic col-md-6" data-dependent="labSubCat" required>
                    <option value="">Select Suppliers</option>
                        @foreach($suppliers as $lb)
                            <option value="{{$lb->id}}">{{$lb->name}}</option>
                        @endforeach
                    </select>
                </div>  
                
                <div class="field row">
                    <label class="label col-md-6" for="date">Purchase Date</label>                    
                    <input class="input col-md-6" type="date" max="{{$today}}" name="purchasedate" placeholder="Enter Date of Purchase" required> 
                </div>  

                {{-- Condititon Dropdown --}}
                <div class="form-group row">
                    <label class="label col-md-6" for="date">Condition</label>
                    <select name="conselect" id="condition" class="form-control col-md-6 input-lg dynamic" data-dependent="labSubCat" required>
                    <option value="{{$product->condition}}">{{$conditionname}}</option>
                        @foreach($conditions as $cn)
                            <option value="{{$cn->id}}">{{$cn->details}}</option>
                        @endforeach
                    </select>
                </div>                

                {{-- Condition Notes --}}
                <div class="field">
                    <label class="label" for="condition_Notes">Condition Notes</label>
                    <div class="control">
                        <textarea class="textarea" name="condition_Notes" id="condition_Notes">{{$product->condition_Notes}}</textarea> 
                    </div>
                </div>

                

                 
                <div class="row">
                    <div class="form-group col-md-6">
                        <label class="label" for="imagecollection"> Product Thumbnail </label>
                        <input type="file" class="form-control" name="thumbnail" required>
                    </div>

                    <div class="form-group col-md-6">
                        <label class="label" for="imagecollection"> Product Images </label>
                        <input type="file" class="form-control" name="images[]" multiple >
                    </div>
                </div>

                    {{-- Featured Product --}}
                    <div class="form-group">
                        <label class="label" for="featured">Featured ?</label>
                        <div class="radio" required>
                            <label><input type="radio" name="featured" value="true">true </label>
                            <label><input type="radio" name="featured" value="false">false</label>
                        </div>
                    </div> 
                
                
                <div class="field is-grouped">
                    <div class="control">
                        <button class="btn btn-md btn-outline-primary" name="myButton" type="submit">Submit</button>
                    </div>
                </div>    
                
                
            </form>
        </div>
    </div>    
@endsection

