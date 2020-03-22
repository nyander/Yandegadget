@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-9">
            <div class="card">
                <div class="card-header">Shipments</div>
                
                

                <div class="card-body">
                     {{-- Table design based from : https://getbootstrap.com/docs/4.4/content/tables/ --}}
                     <table class="table">
                        <thead class="thead-dark">
                          <tr>
                            <th scope="col">#</th>
                            <th scope="col">Shipment Company</th>
                            <th scope="col">Date of Shipment</th>
                            <th scope="col">Package Recieved</th> 
                            <th scope="col">Items Recieved</th>                            
                            <th scope ="col">Action</th>                          
                                                        
                          </tr>
                        </thead>
                        <tbody>
                        @foreach ($ships as $ship)

                        <p hidden>
                           {{$amountrecieved = DB::table('shipped_product')->where('shipment_id', $ship->id)->where('recieved', 1)->count()}} 
                           {{$totalamount = DB::table('shipped_product')->where('shipment_id', $ship->id)->count()}}
                        </p>

                        @if($ship->recieved == 0)
                        <tr style="background-color:#FA8072;">
                          <th scope="row">{{$ship->id}}</th>
                          <td>{{$ship->shipment_company}}</td>
                          <td>{{$ship->shipment_date}}</td>
                          <td>No</td>
                          <td>{{$amountrecieved}}/{{$totalamount}}</td>                          
                          <td>                          
                            <a href="{{route('ships.show', $ship->id)}}"><button type="button" class="btn btn-primary float-left">View</button></a>
                            @can('staff-shipment')
                            <a href="{{route('ships.recieved', $ship->id)}}"><button type="button" class="btn btn-success float-left" style="margin-left: 5px;"> Recieved</button></a>                            
                            @endcan
                          </td>
                        </tr>

                        @else
                        @if($amountrecieved < $totalamount )
                        <tr style="background-color: #FFA500;">
                          <th scope="row">{{$ship->id}}</th>
                          <td>{{$ship->shipment_company}}</td>
                          <td>{{$ship->shipment_date}}</td>
                          <td>Yes</td>
                          <td>{{$amountrecieved}}/{{$totalamount}}</td>
                          <td>                          
                            <a href="{{route('ships.show', $ship->id)}}"><button type="button" class="btn btn-primary float-left">View</button></a>
                                                        
                          </td>
                        </tr>
                        @else
                        <tr style="background-color: #98FB98;">
                          <th scope="row">{{$ship->id}}</th>
                          <td>{{$ship->shipment_company}}</td>
                          <td>{{$ship->shipment_date}}</td>
                          <td>Yes</td>
                          <td>{{$amountrecieved}}/{{$totalamount}}</td>
                          <td>                          
                            <a href="{{route('ships.show', $ship->id)}}"><button type="button" class="btn btn-primary float-left">View</button></a>                                                        
                          </td>
                        </tr>
                        @endif
                        
                       
                        @endif
                            
                        @endforeach   
                                               
                        </tbody>
                      </table>                      
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
<script >
  document.addEventListener('DOMContentLoaded', function () {
  Echo.join(`chat`)
    .here((users) => {
        //
    })
    .joining((user) => {
        console.log(user.name);
    })
    .leaving((user) => {
        console.log(user.name);
    });
  });
</script>