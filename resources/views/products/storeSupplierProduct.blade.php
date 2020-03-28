@extends('layouts.app')

@section('content')
    
    <div id="wrapper">
        <div id="createproduct" class="container">
            <h3>Store Products</h3>
            <form method="POST" action="/products"  enctype="multipart/form-data">       
                @csrf
                {{-- Product Name field--}}
                <div class="field">
                    <label class="label" for="name">Product Name</label>
                    <div class="control">
                    <input class="input" type="text" name="name" id="name" value="{{$product->name}}"> 
                    </div>
                </div>    

                {{-- Cost field--}}
                <div class="field">
                    <label class="label" for="cost">Cost ({{$currency}})</label>
                    <div class="control">
                        <input class="input" type="number" name="cost" id="cost" value="{{$product->selling_Price}}"> 
                    </div>
                </div>    

                {{-- Categories Dropdown --}}
                {{-- <select> is a dropdown --}}
                {{-- <option> is each option of a dropdown --}}
                <div class="form-group">
                    <label class="label" for="date">Product Type</label>
                    <select name="catselect" id="category" class="form-control input-lg dynamic" data-dependent="labSubCat">
                        <option value="{{$product->type}}">{{$categoriesname}}</option>
                            @foreach($categories as $ct)
                                <option value="{{$ct->id}}">{{$ct->type}}</option>
                            @endforeach
                    </select>
                </div> 

                {{-- Supplier Dropdown --}}
                <div class="form-group">
                    <label class="label" for="date">Supplier</label>
                    <select name="supselect" id="supplier" class="form-control input-lg dynamic" data-dependent="labSubCat">
                    <option value="{{$product->supplier_id}}">{{$suppliername}}</option>
                        @foreach($suppliers as $lb)
                            <option value="{{$lb->id}}">{{$lb->name}}</option>
                        @endforeach
                    </select>
                </div>   
                
                <div class="field">
                    <label class="label" for="date">Purchase Date</label>
                    <div class="control">
                        <input class="input" type="date"  max="{{$today}}" name="purchasedate" placeholder="Enter Date of Purchase"> 
                    </div>
                </div>  

                {{-- Condititon Dropdown --}}
                <div class="form-group">
                    <label class="label" for="date">Condition</label>
                    <select name="conselect" id="condition" class="form-control input-lg dynamic" data-dependent="labSubCat">
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
                        <input class="input" type="number" name="price" id="price"> 
                    </div>
                </div>

                {{-- Featured Product --}}
                <div class="field">
                    <label class="label" for="condition_Notes">Featured ?</label>
                    Yes: {{Form::radio('featured', 'true')}}
                    No:  {{Form::radio('featured', 'false')}}                   
                </div>

                <div class="form-group">
                    <label class="label" for="imagecollection"> Product Thumbnail </label>
                    <input type="file" class="form-control" name="thumbnail" required>
                </div>

                <div class="form-group">
                    <label class="label" for="imagecollection"> Product Images </label>
                    <input type="file" class="form-control" name="images[]" multiple required>
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

