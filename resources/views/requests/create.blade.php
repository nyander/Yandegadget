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
                    
                    {{-- <div class="form-group">
                    <label class="label" for="type">Condition</label>
                        <select name="condition" id="cmbitems">
                            <option value="">Select Type</option>
                            @foreach($conditions as $ct)
                                    <option value="{{$ct->deposit}}">{{$ct->type}}</option>
                            @endforeach
                        </select>
                        <input type="text" name="charge" id="charge" onClick="checkPrice()" readonly>
                    </div>  --}}
                    <div class="form-group">
                        <label class="label" for="type">Condition</label>
                        <select name="condition" id="condition">
                            <option value="">Select Type </option>
                            @foreach($conditions as $ct)
                                <option value="{{$ct->deposit}}">{{$ct->details}}</option>
                            @endforeach
                        </select>
                        £<input type="text" name="charge" id="charge" onClick="checkPrice()" readonly>
                    </div> 
                    
                {{-- Condititon Dropdown
                <div class="form-group">
                    <label class="label" for="date">Condition</label>
                    <select name="condition" id="condition" class="form-control input-lg dynamic" data-dependent="labSubCat">
                    <option value="{{$conditions}}">Select Condition</option>
                        @foreach($conditions as $cn)
                            <option value="{{$cn->id}}">{{$cn->details}}</option>
                        @endforeach
                    </select>
                </div>     
                
                {{-- Price
                <div class="field">                    
                    <div class="control">
                        
                        <span>Deposit:</span><input class="input" type="text" name="charge" id="charge" value="50" readonly>
                    
                    </div>
                </div>                 --}}

                

                

                <div class="field is-grouped">
                    <div class="control">

                        <div class="field is-grouped">
                            <div class="control">
                                <button class="button is-link" type="submit">Submit</button>
                            </div>

                            <div class="control">
                                <a href="{{route('products.index')}}" class="button btn-secondary" type="button">Back To Products </a>
                            </div>
                                              
                    </div>
                </div>                                
            </form>
            
        </div>
    </div>    
@endsection

@section('extra-js')
    <script>
        
            var select = document.getElementById('condition');
            var input = document.getElementById('charge');
            select.onchange = function() {
            input.value = select.value;
        }
        
    </script>
@endsection

