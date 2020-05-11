@extends('layouts.app')

@section('content')
    @if(Cart::count()>0)
       
        <div class="shipment-table mt-5">
            <div class="row">
                <table class="table col-md-9">
                    <thead class="thead-dark">
                    <tr>
                        <th scope="col">{{Cart::count()}} Item(s) for shipment</th>
                        <th scope="col">Details</th>
                        <th scope="col">Action</th>                            
                    </tr>
                    </thead>
                    <tbody>
                        @foreach (Cart::content() as $item)            
                            <tr>                    
                                <td><img class="card-img-top" src="/gallery/{{$item->model->thumbnail_path}}" style="max-width: 20em; max-height: 10em;object-fit: contain;"></td>
                                <td>                        
                                    <p><b>Product:</b> <a href="/products/{{$item->id}}" class="text-dark">{{ $item->model->name}}</a></p>                        
                                    <p><b>Cost:</b> £{{$item->model->cost}}</p>
                                    <p><b>Selling Price:</b> £{{$item->model->selling_Price}}</p>
                                </td>
                                <td>
                                    <div class="shipment-table-row-right ">
                                        <form action="{{route('shipments.switchToSaveForLater', $item->rowId)}}" method="POST" onsubmit="myButton.disabled = true; return true;">
                                            @csrf
                                            <button type="submit" name="myButton" class="btn btn-md btn-outline-success mb-1">Save</button>
                                        </form>
                                        <form action="{{route('shipments.destroy', $item->rowId)}}" method="POST" onsubmit="myButton.disabled = true; return true;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" name="myButton" class="btn btn-md btn-outline-danger">Remove</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>       
                        @endforeach  
                    </tbody>
                </table>  
            </div>         
        </div>
        
        <div class="row">
            <div class="col-md-4" style="margin-right:5em;" ></div>
            <div  class="col-md-3">     
                <hr>                   
                <span class="shipment-totals-total"><b>Sales Total:</b> £{{Cart::total()}}</span>  
            </div>
        </div>

        <br>
        <a href="/products" class="btn  btn-outline-primary">Add More Products</a>
        <a href="/confirmations" class="btn btn-outline-success">Confirm Shipment</a>   
    @else
        <h3 class="d-flex justify-content-center mt-5">No items for shipment</h3> 
        <div class="spacer"></div>
       <div class="d-flex justify-content-center mx-auto">
           <a href="{{route('products.index')}}" class="btn  btn-outline-primary ">Products</a>
        </div> 
        <div class="spacer"></div>
    @endif




    @if(Cart::instance('saveForLater')->count()>0)
        
        <div class="save-for-later shipment-table mt-4">
            <table class="table col-md-9">
                <thead class="thead-dark">
                    <tr>
                        <th scope="col">{{Cart::instance('saveForLater')->count()}} items(s) saved for later shipment </th>
                        <th scope="col">Details</th>
                        <th scope="col">Action</th>                            
                    </tr>
                </thead>
                <tbody>
                    @foreach(Cart::instance('saveForLater')->content() as $item)
                        <tr>
                            <td><img class="card-img-top" src="/gallery/{{$item->model->thumbnail_path}}" style="max-width: 20em; max-height: 10em; object-fit: contain;"></td>
                            <td>                        
                                <p><b>Product:</b> <a href="/products/{{$item->id}}" class="text-dark">{{ $item->model->name}}</a></p>                        
                                <p><b>Cost:</b> £{{$item->model->cost}}</p>
                                <p><b>Selling Price:</b> £{{$item->model->selling_Price}}</p>
                            </td>
                            <td>
                                <div class="shipment-table-row-right ">
                                    <form action="{{route('saveForLater.switchToShipment', $item->rowId)}}" method="POST" onsubmit="myButton.disabled = true; return true;">
                                        @csrf
                                        <button type="submit" name="myButton" class="btn btn-md btn-outline-success mb-1">Switch</button>
                                    </form>

                                    <form action="{{route('saveForLater.destroy', $item->rowId)}}" method="POST" onsubmit="myButton.disabled = true; return true;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" name="myButton" class="btn btn-md btn-outline-danger">Remove</button>
                                    </form>
                                    
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>  
        </div>
    @else
        <h2 class="d-flex justify-content-center"> You have no items saved for later shipment</h2>
    @endif
@endsection
