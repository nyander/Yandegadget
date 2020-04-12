@extends('layouts.app')
@section('content')

    <div class="field is-grouped mt-4">
        <div class="control">
            <a href="/shipments" class="btn btn-md btn-outline-secondary">Back to Cart</a>
        </div>
    </div> 
    <div class="row mt-2">
        <div id="createproduct" class="col-md-5 order-md-1">
            <h3>Upload Products</h3>        
            <form action="{{route('confirmations.store')}}" method="POST" onsubmit="myButton.disabled = true; return true;">
                @csrf   
                
                {{-- Shipment Company Dropdown --}}
                {{-- <select> is a dropdown --}}
                {{-- <option> is each option of a dropdown --}}
                <div class="form-group row">
                    <label class="label col-md-6" for="date">Shipment Company</label>
                    <select name="shipselect" id="shipmentcompany" class="form-control col-md-6 " data-dependent="labSubCat" required>
                        <option value="">Select Company</option>
                            @foreach($companies as $company)
                                <option value="{{$company->id}}">{{$company->name}}</option>
                            @endforeach 
                    </select>
                </div> 
                

                {{-- This is the Date of the Shipment --}}
                <div class="field row">
                    <label class="label col-md-6" for="date">Date of Shipment</label>                    
                    <input class="input col-md-6" type="date" max="{{$today}}" name="shipmentdate" placeholder="Enter Date of Purchase" required>                    
                </div>  

                {{-- Cost field--}}
                <div class="field row">
                    <label class="label col-md-6" for="cost">Shipment Cost</label>
                    <input class="input col-md-6" type="number" name="cost" id="cost" required> 
                </div> 

                
                {{-- Further Notes --}}
                <div class="field">
                    <label class="label" for="shipment_Notes">Shipment Notes</label>
                    <div class="control">
                        <textarea class="textarea" name="shipment_Notes" id="shipment_Notes"></textarea> 
                    </div>
                </div>

                
                <div class="field is-grouped">
                    <div class="control">
                        <button type="submit" name="myButton" class="btn btn-md btn-outline-success"> Confirm</button>
                    </div>
                </div> 
            </form>
        </div>


        <div class="col-md-7 order-md-2">
            <h4> Products to be Shipped </h4>
            <table class="table ">
                <thead class="thead-dark">
                <tr>
                    <th scope="col">{{Cart::count()}} Item(s) for shipment</th>
                    <th scope="col">Details</th>                          
                </tr>
                </thead>
                <tbody>
                    @foreach (Cart::content() as $item)            
                        <tr>                    
                            <td><img class="card-img-top" src="/gallery/{{$item->model->thumbnail_path}}" style="max-width: 20em; max-height: 10em; object-fit: contain;"></td>
                            <td>                        
                                <p><b>Product:</b> <a href="/products/{{$item->id}}" class="text-dark">{{ $item->model->name}}</a></p>                        
                                <p><b>Cost:</b> £{{$item->model->cost}}</p>
                                <p><b>Selling Price:</b> £{{$item->model->selling_Price}}</p>
                            </td>                            
                        </tr>       
                    @endforeach  
                </tbody>
            </table>  
            <div class="row">
                <div class="col-md-10" style="margin-right:5em;" ></div>
                <div  class="col-md-12 text-right">     
                    <hr>                   
                    <span class="shipment-totals-total"><b>Sales Total:</b> £{{Cart::total()}}</span>  
                </div>
            </div>
        </div>
    </div> 
       
@endsection