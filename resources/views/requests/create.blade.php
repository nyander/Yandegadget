@extends('layouts.app')

@section('content')
    
    <div id="wrapper">
        <div id="createsupplier" class="container">
            <h3>Request Product</h3>
            <form method="POST" action="/requests">
                @csrf
                {{-- Supplier Name field--}}
                <div class="field">
                    <label class="label" for="name">Product Name</label>
                    <div class="control">
                        <input class="input" type="text" name="name" id="name"> 
                    </div>
                </div>    
                
                {{-- Categories Dropdown --}}
                {{-- <select> is a dropdown --}}
                {{-- <option> is each option of a dropdown --}}
                    <div class="form-group">
                        <label class="label" for="date">Product Type</label>
                        <select name="type" id="type" class="form-control input-lg dynamic" data-dependent="labSubCat">
                            <option value="{{$categories}}">Select Type</option>
                                @foreach($categories as $ct)
                                    <option value="{{$ct->id}}">{{$ct->type}}</option>
                                @endforeach
                        </select>
                    </div> 
                    
                {{-- Condititon Dropdown --}}
                <div class="form-group">
                    <label class="label" for="date">Condition</label>
                    <select name="condition" id="condition" class="form-control input-lg dynamic" data-dependent="labSubCat">
                    <option value="{{$conditions}}">Select Condition</option>
                        @foreach($conditions as $cn)
                            <option value="{{$cn->id}}">{{$cn->details}}</option>
                        @endforeach
                    </select>
                </div>     
                
                {{-- Price--}}
                <div class="field">                    
                    <div class="control">
                        
                        <span>Deposit:</span><input class="input" type="text" name="deposit_amount" id="name" value="50" readonly>
                    
                    </div>
                </div> 

                <h4> Price will be: CREATE FUNCTION TO CHECK IF CONDITION IS A - £50, B- £40 , C - £30<h4>
                <div class="field is-grouped">
                    <div class="control">
                        <a href="{{route('products.index')}}" class="button">Back</a>
                        <a href="{{route('checkouts.index')}}" class="button">Proceed to Checkout</a>
                    </div>
                </div>                                
            </form>
            
        </div>
    </div>    
@endsection

