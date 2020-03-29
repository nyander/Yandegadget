@extends('layouts.app')

@section('content')
    @if(Cart::count()>0)
        <h2> {{Cart::count()}} Item(s) for shipment </h2>
        <div class="shipment-table">
            @foreach (Cart::content() as $item)
                <div class="shipment-table-row">
                    <div class="shipment-table-row-left">
                        <a>image </a>
                        <div class="shipment-item-details">
                            <div class="shipment-product-name"><a href="/products/{{$item->id}}">{{ $item->model->name}}</a></div>
                            <div class="shipment-product-conditionnotes">{{$item->model->condition_Notes}}</div>
                            <div class="shipment-product-price">Potential Sale: £{{$item->model->selling_Price}}</div>
                        </div>
                    </div>
                    <div class="shipment-table-row-right">
                        <div class="shipment-table-actions">
                            <form action="{{route('shipments.destroy', $item->rowId)}}" method="POST" onsubmit="myButton.disabled = true; return true;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" name="myButton" class="cart-options">Remove</button>
                            </form>
                            
                            <form action="{{route('shipments.switchToSaveForLater', $item->rowId)}}" method="POST" onsubmit="myButton.disabled = true; return true;">
                                @csrf
                                <button type="submit" name="myButton" class="cart-options">Save For Later</button>
                            </form>
                        </div>
                    </div>
                </div>        
            @endforeach            
        </div>
        
        <div  class="shipment-totals-right">
            <div>
                SubTotal: <br>
                Tax: <br>                    
            </div>            
            <span class="shipment-totals-total">Potential Sale Total: £{{Cart::total()}}</span>                
        </div>
        <a href="/products" class="button">Add More Products</a>
        <a href="/confirmations" class="button-primary">Confirm Shipment</a>   
    @else
        <h3>No items for shipment</h3> 
        <div class="spacer"></div>
        <a href="{{route('products.index')}}" class="button">Back to shop</a>
        <div class="spacer"></div>
    @endif

    @if(Cart::instance('saveForLater')->count()>0)
        <h2>{{Cart::instance('saveForLater')->count()}} items(s) saved for later shipment </h2>
        <div class="save-for-later shipment-table">
            @foreach(Cart::instance('saveForLater')->content() as $item)
                <div class="shipment-table-row">
                    <div class="shipment-table-row-left">
                        <br>
                        <a href="/">Image Here</a>
                        <div class="shipment-item-details">
                            <div class="shipment-table-item">
                                <a href="/products/{{$item->id}}">{{$item->model->name}}</a>
                            </div>
                            <div class="shipment-table-conditionnotes">
                                <p>{{$item->model->condition_Notes}}</p>
                            </div>
                            <div class="shipment-table-price">
                                <p>Potential Sale £{{$item->model->selling_Price}}</p>
                            </div>
                        </div>
                    </div>
                    <div class="shipment-table-row-light">
                        <div class="shipment-table-actions">
                            <form action="{{route('saveForLater.destroy', $item->rowId)}}" method="POST" onsubmit="myButton.disabled = true; return true;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" name="myButton" class="cart-options">Remove</button>
                            </form>
                            
                            <form action="{{route('saveForLater.switchToShipment', $item->rowId)}}" method="POST" onsubmit="myButton.disabled = true; return true;">
                                @csrf
                                <button type="submit" name="myButton" class="cart-options">Switch To Cart</button>
                            </form>
                            
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <h2> You have no items saved for shipment </h2>
    @endif
@endsection
