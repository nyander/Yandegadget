@extends('layouts.app')

@section('content')
    <a href="{{ URL::previous() }}" class="btn btn-md btn-outline-secondary mt-3 mb-3">Back</a>
    <div >
        <h5 >Shipment No: {{$ship->id}}</h5>        
    </div>
    <br>

    <div class="row">
        <div class="col-md-4 order-md-1 mr-auto">
            <div class="card">
                <h5 class="card-header bg-danger text-light">Shipment Details</h5>
                <div class="card-body">
                    <p><b>Shipped by:</b> {{DB::table('users')->where('id',$ship->user_id)->value('name')}}</p>
                    <p><b>Shipment Company:</b> {{DB::table('ship_companies')->where('id',$ship->shipment_company)->value('name')}}</p>
                    <p><b>Date of Shipment:</b> {{$ship->shipment_date}} 
                    <p><b>Cost of Shipment:</b> £{{$ship->shipment_cost}}</p>  
                    <p><b>Shipment received?:</b> @if($ship->received == 1) Yes @else No @endif</p>  
                    <p><b>Extra Information:</b></p>
                    <p>{{$ship->shipment_notes}}</p>
                    <br>
                </div>
            </div>
        </div>

        <div class="col-md-8 order-md-2 card ">    
            <h4 class="card-header bg-danger text-light">Product List</h4>
            <div class="sweat py-5 bg-light card-body">
                <div class="container">
                    <div class="row">
                        @foreach ($products1 as $product1)
                            <span hidden>{{$received = DB::table('products')->where('id', $product1->product_id)->value('received')}}</span>
                            <span hidden>{{$received2 = DB::table('shipped_product')->where('product_id', $product1->product_id)->value('received')}}</span>   
                            <span hidden>{{$thumbnail_path = DB::table('products')->where('id', $product1->product_id)->value('thumbnail_path')}}</span> 
                            <span hidden>{{$price = DB::table('products')->where('id', $product1->product_id)->value('selling_Price')}}</span> 
                            <span hidden>{{$href = DB::table('products')->where('id', $product1->product_id)->value('id')}}</span>  
                            <span hidden>{{$name = DB::table('products')->where('id', $product1->product_id)->value('name')}}</span>                            
                            <div class="col-md-6 mb-2" >
                                <div class="card mb-7 box-shadow" >
                                    <img class="card-img-top " src="/gallery/{{$thumbnail_path}}" style="max-height:15em; object-fit: contain;" >
                                    <div class="card-body">
                                        <h5 class="title"> <a href="/products/{{$href}}"> {{$name}}</a></h5>
                                        <p class="card-text">Price: <span class= "pricesmbl">£</span>{{$price}}</p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            
                                            @if($received == 0 and $received2 == 0)
                                            <div class="btn-group">
                                                <a href="{{route('products.received', $href)}}"><button type="button" class="btn btn-md btn-outline-success" style="margin-top: 1%;"> received</button></a>
                                            </div>
                                            @else
                                            <small class="text-muted">received</small>
                                            @endif  
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>


@endsection