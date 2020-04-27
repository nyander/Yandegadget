@extends('layouts.app')

@section('content')


<div class="row mt-5">
    <div class="col-md-6 order-md-1">
        <div class="container bg-info text-white p-3">
            <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy 
                text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It 
                has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged.</p>
        </div>
    </div>
    <div id="createproduct" class="col-md-4 order-md-2">
        <h3>Update Products</h3>
        <form method="POST" action="{{route('products.purchase',$product->id)}}" onsubmit="myButton.disabled = true; return true;">
            @csrf
            @method('PUT')
            <div class="field row">
                <label class="label col-md-6" for="price">Price (£)</label>                
                <input class="input col-md-6" type="number" name="price" id="price" value="{{$product->selling_Price}}" required>                 
            </div>

            <div class="field row">
                <label class="label col-md-6" for="date">Sold Date</label>                
                <input class="input col-md-6" type="date" max="{{$today}}" name="soldDate" placeholder="Enter Date of Purchase" required> 
            </div>

            <div class="field is-grouped">
                <div class="control">
                    <button class=" btn btn-sm btn-outline-primary float-right" name="myButton" type="submit">Confirm Purchase</button>
                </div>
            </div>
                                      
        </form>
    </div>
</div>

@endsection