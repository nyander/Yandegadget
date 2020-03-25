@extends('layouts.app')

@section('content')
    <a href="/ships" class="btn btn-default">Back</a>
    <div >
        <h5 >Shipment No: {{$ship->id}}</h5>
        <p style="max-width:50em;" >Much did had call new drew that kept. Limits expect wonder law she. 
            Now has you views woman noisy match money rooms. 
            To up remark it eldest length oh passed. Off because yet mistake feeling has men. 
            Consulted disposing to moonlight ye extremity. Engage piqued in on coming. 
        </p>
    </div>
    <br>

    <div class="row">
        <div class="col-md-4 order-md-1 mr-auto">
            <div class="card">
                <h5 class="card-header bg-danger text-light">Shipment Details</h5>
                <div class="card-body">
                    <p><b>Shipped by:</b> {{$ship->user_id}}</p>
                    <p><b>Shipment Company:</b> {{$ship->shipment_company}}</p>
                    <p><b>Date of Shipment:</b> {{$ship->shipment_date}} 
                    <p><b>Cost of Shipment:</b> {{$ship->shipment_cost}}</p>  
                    <p><b>Shipment Recieved?:</b> @if($ship->recieved == 1) Yes @else No @endif</p>  
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
                            <span hidden>{{$recieved = DB::table('products')->where('id', $product1->product_id)->value('recieved')}}</span>
                            <span hidden>{{$recieved2 = DB::table('shipped_product')->where('product_id', $product1->product_id)->value('recieved')}}</span>   
                            <span hidden>{{$thumbnail_path = DB::table('products')->where('id', $product1->product_id)->value('thumbnail_path')}}</span> 
                            <span hidden>{{$price = DB::table('products')->where('id', $product1->product_id)->value('selling_Price')}}</span> 
                            <span hidden>{{$href = DB::table('products')->where('id', $product1->product_id)->value('id')}}</span>  
                            <span hidden>{{$name = DB::table('products')->where('id', $product1->product_id)->value('name')}}</span>                            
                            <div class="col-md-5" >
                                <div class="card mb-7 box-shadow" >
                                    <img class="card-img-top " src="/gallery/{{$thumbnail_path}}" style="max-height:10em; object-fit: cover;" >
                                    <div class="card-body">
                                        <h5 class="title"> <a href="/products/{{$href}}"> {{$name}}</a></h5>
                                        <p class="card-text">Price: <span class= "pricesmbl">£</span>{{$price}}</p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            
                                            @if($recieved == 0 and $recieved2 == 0)
                                            <div class="btn-group">
                                                <a href="{{route('products.recieved', $href)}}"><button type="button" class="btn btn-sm btn-outline-success" style="margin-top: 1%;"> Recieved</button></a>
                                            </div>
                                            @else
                                            <small class="text-muted">Recieved</small>
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