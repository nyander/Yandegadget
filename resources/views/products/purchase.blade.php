@extends('layouts.app')

@section('content')


<div id="wrapper">
    <div id="createproduct" class="container">
        <h3>Update Products</h3>
        <form method="POST" action="{{route('products.purchase',$product->id)}}">
            @csrf
            @method('PUT')
            <div class="field">
                <label class="label" for="price">Price (£)</label>
                <div class="control">
                    <input class="input" type="number" name="price" id="price" value="{{$product->selling_Price}}"> 
                </div>
            </div>

            <div class="field">
                <label class="label" for="date">Sold Date</label>
                <div class="control">
                    <input class="input" type="date" max="{{$today}}" name="soldDate" placeholder="Enter Date of Purchase" > 
                </div>
            </div>

            <div class="field is-grouped">
                <div class="control">
                    <button class="button is-link" type="submit">Confirm Purchase</button>
            </div>
                                      
        </form>
    </div>
</div>

@endsection