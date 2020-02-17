@extends('layouts.app')

@section('content')
<div class="wrapper">
    <div id="checkout" class="container">
        <h3>Checkout</h3>
        <form method="POST" action="/checkouts">
            @csrf            
            <h4> Billing Information</h4>

            <div class="form-group">
                <label for="email"> Email Address</label>
                <input type="email" class="form-control" id="email" name="email" value="">
            </div>
            <div class="form-group">
                <label for="name"> Name</label>
                <input type="text" class="form-control" id="name" name="name" value="">
            </div>
            <div class="form-group">
                <label for="address"> Address</label>
                <input type="text" class="form-control" id="address" name="address" value="">
            </div>

            <div class="half-form">
                <div class="form-group">
                    <label for="city">City</label>
                    <input type="text" class="form-control" id="city" name="city" value="">
                </div>
                
            </div>

            <div class="half-form">
                <div class="form-group">
                    <label for="postcode">Postcode</label>
                    <input type="text" class="form-control" id="postcode" name="postcode" value="">
                </div>
                <div class="form-group">
                    <label for="phone">Phone</label>
                    <input type="text" class="form-control" id="phone" name="phone" value="">
                </div>
            </div>

            <br>

            <h4> Payment Details </h4>
                <div class="form-group">
                    <label for="name_on_card">Name on Card</label>
                    <input type="text" class="form-control" id="name_on_card" name="name_on_card" value="">
                </div>
                <div class="form-group">
                    <label for="cc-number">Credit Card Number</label>
                    <input type="text" class="form-control" id="cc-number" name="cc-number" value="">
                </div>
                <div class="form-group">
                    <label for="expiry">Expiry</label>
                    <input type="text" class="form-control" id="cc-number" name="cc-number" value="">
                </div>
                <div class="form-group">
                    <label for="cvc">CVC Code</label>
                    <input type="text" class="form-control" id="cvc" name="cvc" value="">
                </div> 

                <div class="spaces"></div>

                <button type="submit" class="button-primary full width"> Complete Request </button>
        </form>
    </div>

    
</div>    
@endsection