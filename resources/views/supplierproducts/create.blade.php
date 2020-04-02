@extends('layouts.app')

@section('content')
    
    <h3 class="mt-5">Upload Products</h3>
    <div id="createproduct">
        <form method="POST" class="row" action="/supplierproducts" enctype="multipart/form-data" onsubmit="myButton.disabled = true; return true;" >
            <div class="col-md-5 order-md-1 my-3">
                <div class="container bg-info text-white p-3 mr-auto">
                    <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy 
                        text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It 
                        has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged.</p>
                </div>

                <div class="row mt-2">
                    <div class="form-group col-md-6">
                        <label class="label" for="imagecollection"> Product Thumbnail </label>
                        <input type="file" class="form-control" name="thumbnail" required>
                    </div>
        
                    <div class="form-group col-md-6">
                        <label class="label" for="imagecollection"> Product Images </label>
                        <input type="file" class="form-control" name="images[]" multiple required>
                    </div> 
                </div>
            </div>

            <div class="col-md-5 order-md-2">
                @csrf
                {{-- Product Name field--}}
                <div class="field row">
                    <label class="label col-md-6" for="name">Product Name</label>
                    <input class="input col-md-6" type="text" name="name" id="name" required> 
                </div>    

                {{-- Categories Dropdown --}}
                {{-- <select> is a dropdown --}}
                {{-- <option> is each option of a dropdown --}}
                <div class="form-group row">
                    <label class="label col-md-6" for="category">Product Type</label>
                    <select name="catselect" id="category" class="form-control input-lg dynamic col-md-6" data-dependent="labSubCat" required>
                        <option value="{{$categories}}">Select Type</option>
                            @foreach($categories as $ct)
                                <option value="{{$ct->id}}">{{$ct->type}}</option>
                            @endforeach
                    </select>
                </div> 

                {{-- Selling Price field--}}
                <div class="field row">
                    <label class="label col-md-6" for="price">Price ({{$currency}})</label>
                    <input class="input col-md-6" type="number" name="price" id="price" required> 
                </div>
                

                {{-- Condititon Dropdown --}}
                <div class="form-group row">
                    <label class="label col-md-6" for="date">Condition</label>
                    <select name="conselect" id="condition" class="form-control input-lg dynamic col-md-6" data-dependent="labSubCat" required>
                    <option value="{{$conditions}}">Select Condition</option>
                        @foreach($conditions as $cn)
                            <option value="{{$cn->id}}">{{$cn->details}}</option>
                        @endforeach
                    </select>
                </div>                

                {{-- Condition Notes --}}
                <div class="field">
                    <label class="label" for="condition_Notes">Condition Notes</label>
                    <div class="control">
                        <textarea class="textarea" name="condition_Notes" id="condition_Notes"></textarea> 
                    </div>
                </div>

                

                <div class="field is-grouped">
                    <div class="control">
                        <button class="btn btn-md btn-outline-primary" type="submit" name="myButton">Submit</button>
                    </div>
                </div>  
            </div>          
        </form>
    </div>
       
@endsection

