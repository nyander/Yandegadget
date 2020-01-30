@extends('layouts.app')

@section('content')
    
    <div id="wrapper">
        <div id="createproduct" class="container">
            <h3>Update Products</h3>
            <form method="POST" action="/products/{{$product->id}}">
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

                {{-- Categories Dropdown --}}
                {{-- <select> is a dropdown --}}
                {{-- <option> is each option of a dropdown --}}
                <div class="form-group">
                    <label class="label" for="date">Product Type</label>
                    <select name="catselect" id="category" class="form-control input-lg dynamic" data-dependent="labSubCat" >
                        <option value="{{$categories}}">Select Categories</option>
                            @foreach($categories as $ct)
                                <option value="{{$ct->id}}">{{$ct->type}}</option>
                            @endforeach
                    </select>
                </div> 

                {{-- Supplier Dropdown --}}
                <div class="form-group">
                    <label class="label" for="date">Supplier</label>
                    <select name="supselect" id="supplier" class="form-control input-lg dynamic" data-dependent="labSubCat" value="{{$product->supplier}}">
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

                <div class="field is-grouped">
                    <div class="control">
                        <button class="button is-link" type="submit">Submit</button>
                    </div>
                </div>                                
            </form>
        </div>
    </div>    
@endsection

