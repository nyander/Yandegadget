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
                            <th scope="col">Package received</th> 
                            <th scope="col">Items received</th>                            
                            <th scope ="col">Action</th>                          
                                                        
                          </tr>
                        </thead>
                        <tbody>
                        @foreach ($ships as $ship)

                        <p hidden>
                           {{$amountreceived = DB::table('shipped_product')->where('shipment_id', $ship->id)->where('received', 1)->count()}} 
                           {{$totalamount = DB::table('shipped_product')->where('shipment_id', $ship->id)->count()}}
                        </p>

                        @if($ship->received == 0)
                        <tr style="background-color:#FA8072;">
                          <th scope="row">{{$ship->id}}</th>
                          <td>{{$ship->shipment_company}}</td>
                          <td>{{$ship->shipment_date}}</td>
                          <td>No</td>
                          <td>{{$amountreceived}}/{{$totalamount}}</td>                          
                          <td>                          
                            <a href="{{route('ships.show', $ship->id)}}"><button type="button" class="btn btn-md btn-outline-primary">View</button></a>
                            @can('staff-role')
                            <a href="{{route('ships.received', $ship->id)}}"><button type="button" class="btn btn-md btn-outline-success" style="margin-left: 5px;"> received</button></a>                            
                            @endcan
                          </td>
                        </tr>

                        @else
                        @if($amountreceived < $totalamount )
                        <tr style="background-color: #ffcb6b;">
                          <th scope="row">{{$ship->id}}</th>
                          <td>{{$ship->shipment_company}}</td>
                          <td>{{$ship->shipment_date}}</td>
                          <td>Yes</td>
                          <td>{{$amountreceived}}/{{$totalamount}}</td>
                          <td>                          
                            <a href="{{route('ships.show', $ship->id)}}"><button type="button" class="btn btn-md btn-outline-primary">View</button></a>
                                                        
                          </td>
                        </tr>
                        @else
                        <tr style="background-color: #98FB98;">
                          <th scope="row">{{$ship->id}}</th>
                          <td>{{$ship->shipment_company}}</td>
                          <td>{{$ship->shipment_date}}</td>
                          <td>Yes</td>
                          <td>{{$amountreceived}}/{{$totalamount}}</td>
                          <td>                          
                            <a href="{{route('ships.show', $ship->id)}}"><button type="button" class="btn btn-md btn-outline-success">View</button></a>                                                        
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