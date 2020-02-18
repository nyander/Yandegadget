@extends('layouts.app')



@section('content')
<div id="wrapper">
    <div id="createproduct" class="container">
        <h3>Upload Products</h3>
        <form method="POST" action="/confirmations">
            @csrf            

            {{-- Shipment Company Dropdown --}}
            {{-- <select> is a dropdown --}}
            {{-- <option> is each option of a dropdown --}}
            <div class="form-group">
                <label class="label" for="date">Shipment Company Dropdown</label>
                <select name="shipselect" id="shipmentcompany" class="form-control input-lg dynamic" data-dependent="labSubCat">
                    <option value="">Select Shipment Company</option>
                        {{-- @foreach($companies as $company)
                            <option value="{{$company->id}}">{{company->name}}</option>
                        @endforeach --}}
                </select>
            </div> 

            
            <div class="field">
                <label class="label" for="date">Date of Shipment</label>
                <div class="control">
                    <input class="input" type="date" name="purchasedate" placeholder="Enter Date of Purchase"> 
                </div>
            </div>  

            {{-- Cost field--}}
            <div class="field">
                <label class="label" for="cost">Shipment Cost</label>
                <div class="control">
                   <input class="input" type="number" name="cost" id="cost"> 
                </div>
            </div> 

            
            {{-- Further Notes --}}
            <div class="field">
                <label class="label" for="condition_Notes">Condition Notes</label>
                <div class="control">
                    <textarea class="textarea" name="condition_Notes" id="condition_Notes"></textarea> 
                </div>
            </div>

            <div class="field is-grouped">
                <div class="control">
                    <a href="/shipments" class="button">Back to Cart</a>
                </div>
            </div>                                
        </form>
    </div>

    <div class="confirmation-table-container">
        <h4> Products to be Shipped </h4>
            <div class="confirmation-table">
                @foreach (Cart::content() as $item)
                <div class="confirmation-table-row">
                    <img src="" alt="product" class="confirmation-table-image">
                    <div class="confirmation-product-details">
                    <div class="shipment-table-item"> <a href="/products/{{$item->id}}">{{$item->model->name}}</a></div>
                    <div class="shipment-table-conditionnotes">{{$item->model->condition_Notes}}</div>
                     <div class="shipment-table-price">£{{$item->model->selling_Price}}</div>
                </div>
                    <br>
            </div>           
            @endforeach
            <div class="confirmation-totals">
                <div class ="checkout-totals-right">
                    <span class="confirm-totals-total">Total</span>
                </div>

                <div class="confirmation-totals-right">
                    <span class="confirmation-totals-total">£ {{Cart::total()}}</span>
                </div>
                
</div>    
@endsection