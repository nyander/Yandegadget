@extends('layouts.app')

@section('content')
    <a href="/ships" class="btn btn-default">Back</a>
    <h3>{{$ship->id}}</h3>
    <div>
        <p>Shipped by: {{$ship->user_id}}</p>
        <p>Shipment Company: {{$ship->shipment_company}}</p>
        <p>Date of Shipment: {{$ship->shipment_date}} 
        <p>Cost of Shipment: {{$ship->shipment_cost}}</p>    
        <p>Extra Information: {{$ship->shipment_notes}}</p>
        <p>Shipment Recieved? : {{$ship->shipment_notes}}</p>
        <br>
    </div>

    
        <h4>Product List 2</h4>
        
        <ul>
            @foreach ($products1 as $product1)
                <li style="margin-bottom: 10px">
                    
                        <div class="panel-heading" >Product Id: {{ DB::table('products')->where('id', $product1->product_id)->value('id')}}</div>
                        <div class="panel-heading" >
                            <div>Product Name: {{ DB::table('products')->where('id', $product1->product_id)->value('name')}}</div>
                            <div>Product Type: {{ DB::table('products')->where('id', $product1->product_id)->value('type')}}</div>
                            <div>Product Condition: {{ DB::table('products')->where('id', $product1->product_id)->value('condition')}}</div>
                            <div>Product Selling Price: £ {{ DB::table('products')->where('id', $product1->product_id)->value('selling_Price')}}</div>
                            <div>Product Recieved: {{ DB::table('products')->where('id', $product1->product_id)->value('recieved')}}</div>                            
                            <a href="{{route('products.recieved', DB::table('products')->where('id', $product1->product_id)->value('id'))}}"><button type="button" class="btn btn-success" style="margin-top: 1%;"> Recieved</button></a>
                        </div>    
                </li> 
            @endforeach
        </ul>
    
        
        <a href="/ships/{{$ship->id}}/edit" class="btn btn-default"> Edit </a>
    
    

@endsection