@extends('layouts.app')

@section('content')


<div class="row mt-5">
    <div class="col-md-6 order-md-1">
        <div class="container bg-info text-white p-3">
            
            <p>Please record purchase of the particular product. </p>
            <p>  If customer purchasing product is: {{DB::table('users')->where('id',$product->requested_by)->value('name')}}</p>
            <p>They will only have to pay {{$ghanaconversion - (DB::table('conditions')->where('id',$product->condition)->value('deposit') * $rate)}} GHS  </p>
            <p>Then reord it as full price</p> 
            <p><b>Product cannot be sold at a price less than {{$result}}GHS</b></p>
        </div>
    </div>
    <div id="createproduct" class="col-md-4 order-md-2">
        <h3>Update Products</h3>
        <form method="POST" action="{{route('products.purchaseupdate',$product->id)}}" onsubmit="myButton.disabled = true; return true;">
            @csrf
            @method('PUT')
            <div class="field row">
                <label class="label col-md-6" for="price">Price (GHS)</label>                
                <input class="input col-md-6" type="number" name="price" id="price" value="{{$ghanaconversion}}" required>                 
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