@extends('layouts.app')

@section('content')
    
    <div id="wrapper">
        <div id="createproduct" class="container">
            <h3>Store Products</h3>
            <form method="POST" action="/products"  enctype="multipart/form-data" onsubmit="myButton.disabled = true; return true;">       
                @csrf
                {{-- Product Name field--}}
                <div class="field">
                    <label class="label" for="name">Product Name</label>
                    <div class="control">
                    <input class="input" type="text" name="name" id="name" value="{{$product->name}}" required> 
                    </div>
                </div>    

                {{-- Requested By  --}}
                <div class="form-group">
                    <label class="label" for="requestedby">Requested By</label>
                    <select name="requestedby" id="requestedby" class="form-control input-lg dynamic" data-dependent="labSubCat" readonly>
                    <option value="{{$product->customer_id}}">{{$requestedname}}</option>                        
                    </select>
                </div> 
                

                {{-- Cost field--}}
                <div class="field">
                    <label class="label" for="cost">Cost ({{$currency}})</label>
                    <div class="control">
                        <input class="input" type="number" name="cost" id="cost" required> 
                    </div>
                </div>    

                {{-- Categories Dropdown --}}
                {{-- <select> is a dropdown --}}
                {{-- <option> is each option of a dropdown --}}
                <div class="form-group">
                    <label class="label" for="date">Product Type</label>
                    <select name="catselect" id="category" class="form-control input-lg dynamic" data-dependent="labSubCat" required>
                        <option value="{{$product->type}}">{{$categoriesname}}</option>
                            @foreach($categories as $ct)
                                <option value="{{$ct->id}}">{{$ct->type}}</option>
                            @endforeach
                    </select>
                </div> 

                {{-- Supplier Dropdown --}}
                <div class="form-group">
                    <label class="label" for="date">Supplier</label>
                    <select name="supselect" id="supplier" class="form-control input-lg dynamic" data-dependent="labSubCat" required>
                    <option value="{{$suppliers}}">Select Suppliers</option>
                        @foreach($suppliers as $lb)
                            <option value="{{$lb->id}}">{{$lb->name}}</option>
                        @endforeach
                    </select>
                </div>  
                
                <div class="field">
                    <label class="label" for="date">Purchase Date</label>
                    <div class="control">
                        <input class="input" type="date" max="{{$today}}" name="purchasedate" placeholder="Enter Date of Purchase" required> 
                    </div>
                </div>  

                {{-- Condititon Dropdown --}}
                <div class="form-group">
                    <label class="label" for="date">Condition</label>
                    <select name="conselect" id="condition" class="form-control input-lg dynamic" data-dependent="labSubCat" required>
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

                {{-- Selling Price field--}}
                <div class="field">
                    <label class="label" for="price">Price ({{$currency}})</label>
                    <div class="control">
                        <input class="input" type="number" name="price" id="price" required> 
                    </div>
                </div>

                 

                <div class="form-group">
                    <label class="label" for="imagecollection"> Product Thumbnail </label>
                    <input type="file" class="form-control" name="thumbnail" required>
                </div>

                <div class="form-group">
                    <label class="label" for="imagecollection"> Product Images </label>
                    <input type="file" class="form-control" name="images[]" multiple >
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
                        <button class="button is-link" name="myButton" type="submit">Submit</button>
                    </div>
                </div>    
                
                
            </form>
        </div>
    </div>    
@endsection

