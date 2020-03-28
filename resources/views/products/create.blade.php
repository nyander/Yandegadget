@extends('layouts.app')

@section('content')
    
    <div id="wrapper">
        <div class="mt-5"></div>
        
        <div class="row">
            <div class="col-md-5 order-md-1 modal-dialog-centered mr-5">
                <div class="container bg-info text-white p-3">
                    <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy 
                        text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It 
                        has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged.</p>
                </div>
            </div>
            <div class="col-md-5 order-md-2">
                <div id="createproduct" class="container">
                    <h3>Upload Products</h3>
                    <form method="POST" action="/products"  enctype="multipart/form-data">       
                        @csrf

                        
                        
                        {{-- Product Name field--}}
                        <div class="field row">
                            <label class="label col-md-5" for="name">Product Name</label>
                            <div class="control col-md-7">
                                <input class="input" type="text" name="name" id="name"> 
                            </div>
                        </div>    

                        {{-- Cost field--}}
                        <div class="field row">
                            <label class="label col-md-8" for="cost">Cost ({{$currency}})</label>
                            <div class="control col-md-4">
                                <input class="input" type="number" name="cost" id="cost"> 
                            </div>
                        </div>  

                        {{-- Selling Price field--}}
                        <div class="field row">
                            <label class="label col-md-8" for="price">Price ({{$currency}})</label>
                            <div class="control col-md-4">
                                <input class="input" type="number" name="price" id="price"> 
                            </div>
                        </div>
                        
                        <div class="field row">
                            <label class="label col-md-6" for="date">Purchase Date</label>
                            <div class="control">
                                <input class="input col-md-12" type="date" name="purchasedate" max="{{$today}}" placeholder="Enter Date of Purchase"> 
                            </div>
                        </div>  
                        <div class="row">
                            {{-- Categories Dropdown --}}
                            {{-- <select> is a dropdown --}}
                            {{-- <option> is each option of a dropdown --}}
                            <div class="form-group col-md-4">
                                <label class="label " for="date">Product Type</label>
                                <select name="catselect" id="category" class="form-control input-lg dynamic " data-dependent="labSubCat">
                                    <option value="{{$categories}}">Type</option>
                                        @foreach($categories as $ct)
                                            <option value="{{$ct->id}}">{{$ct->type}}</option>
                                        @endforeach
                                </select>
                            </div> 

                            {{-- Supplier Dropdown --}}
                            <div class="form-group col-md-4">
                                <label class="label" for="date">Supplier</label>
                                <select name="supselect" id="supplier" class="form-control input-lg dynamic" data-dependent="labSubCat">
                                    <option value="{{$suppliers}}">Suppliers</option>
                                    @foreach($suppliers as $lb)
                                        <option value="{{$lb->id}}">{{$lb->name}}</option>
                                    @endforeach
                                </select>
                            </div> 

                            {{-- Condititon Dropdown --}}
                            <div class="form-group col-md-4">
                                <label class="label" for="date">Condition</label>
                                <select name="conselect" id="condition" class="form-control input-lg dynamic" data-dependent="labSubCat">
                                    <option value="{{$conditions}}">Condition</option>
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
                                <textarea class="textarea" name="condition_Notes" id="condition_Notes"></textarea> 
                            </div>
                        </div>

                        <div class="row">

                            <div class="form-group col-md-6">
                                <label class="label" for="imagecollection"> Product Thumbnail </label>
                                <input type="file" class="form-control" name="thumbnail" required>
                            </div>

                            <div class="form-group col-md-6">
                                <label class="label" for="imagecollection"> Product Images </label>
                                <input type="file" class="form-control" name="images[]" multiple required>
                            </div>
                        </div>
                        
                        {{-- Featured Product --}}
                        <div class="field">
                            <label class="label" for="condition_Notes">Featured ?</label>
                            Yes: {{Form::radio('featured', 'true')}}
                            No:  {{Form::radio('featured', 'false')}}                   
                        </div>

                        <div class="field is-grouped">
                            <div class="control">
                                <button class="button is-link" type="submit">Submit</button>
                            </div>
                        </div>                                
                    </form>
                </div>
            </div>
        </div> 
    </div>   
@endsection

