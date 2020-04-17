@extends('layouts.app')

@section('content')
    <div class="row justify-content-md-center">
        <div class="col-md-5 order-md-1">
            <div class="control my-2">
                <a href="{{route('products.index')}}" class="btn btn-md btn-outline-secondary" type="button">Back To Products </a>
            </div>
                       
            <div class="container bg-info text-white p-3">
                <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. </p>
                    <p><b>Types</b></p>
                    @foreach($conditions as $ct)
                    <p><b>{{$ct->details}}</b> : {{$ct->explanation}}</p>
                    <p>Deposit: £{{$ct->deposit}}</p>
                    @endforeach
            </div>
        </div>
        <div id="createsupplier" class="col-md-5 order-md-2">
            <br>
            <h3>Request Product</h3>
            <form method="POST" action="/requests" onsubmit="myButton.disabled = true; return true;">
                @csrf
                {{-- Supplier Name field--}}
                <div class="field row">
                    <label class="label col-md-4 order-md-1" for="name">Product Name</label>
                    <div class="control col-md-8 order-md-2">
                        <input class="input" type="text" name="name" id="name" required> 
                    </div>
                </div>    
                
                {{-- Categories Dropdown --}}
                {{-- <select> is a dropdown --}}
                {{-- <option> is each option of a dropdown --}}
                    <div class="form-group row">
                        <label class="label col-md-4 order-md-1 " style="margin-right:1.5em;" for="date">Product Type</label>
                        <select name="type" id="type" class="form-control input-lg dynamic col-md-7 order-md-2" data-dependent="labSubCat" required>
                            <option >Select Type</option>
                                @foreach($categories as $ct)
                                    <option value="{{$ct->id}}">{{$ct->type}}</option>
                                @endforeach
                        </select>
                    </div> 

                    <div class="form-group row">
                        <label class="label col-md-4 order-md-1" for="type" style="margin-right:1.5em;" >Condition</label>
                        <select name="condition" id="condition" class=" form-control input-lg dynamic col-md-7 order-md-2" required>
                            <option >Select Type </option>
                            @foreach($conditions as $ct)
                                <option value="{{$ct->deposit}}">{{$ct->details}}</option>
                            @endforeach
                        </select>                       
                    </div>  
                    
                    <div class="field row">
                        <label class="label col-md-5" for="type" >Deposit</label>
                        <div class="col-md-7 row">
                            <span class="col-md-2">(£) </span> <input class="input col-md-10" type="text" name="deposit" id="deposit" onClick="checkPrice()" readonly>
                        </div>
                    </div>             

                

                <div class="field is-grouped">
                    <div class="control">

                        <div class="field is-grouped">
                            <div class="control">
                                <button class="btn btn-md btn-outline-primary" type="submit" name="myButton">Submit</button>
                            </div>

                            
                                              
                        </div>
                    </div>
                </div>                                
            </form>
            
        </div>
    </div>    
@endsection

@section('extra-js')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var select = document.getElementById('condition');
            var input = document.getElementById('deposit');
            select.onchange = function() {
            input.value = select.value;
        }
        });
        
    </script>
@endsection

