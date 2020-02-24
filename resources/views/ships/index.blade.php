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
                            <th scope="col">Recieved ?</th>  
                            <th scope ="col"></th>                          
                                                        
                          </tr>
                        </thead>
                        <tbody>
                        @foreach ($ships as $ship)
                        @if($ship->recieved == 0)
                        <tr style="background-color:#FA8072;">
                          <th scope="row">{{$ship->id}}</th>
                          <td>{{$ship->shipment_company}}</td>
                          <td>{{$ship->shipment_date}}</td>
                          <td>{{$ship->recieved}}</td>
                          <td>                          
                            <a href="{{route('ships.show', $ship->id)}}"><button type="button" class="btn btn-primary float-left">View</button></a>
                            <a href="{{route('ships.recieved', $ship->id)}}"><button type="button" class="btn btn-success float-left" style="margin-left: 5px;"> Recieved</button></a>                            
                          </td>
                        </tr>
                        @else
                        <tr style="background-color: #98FB98;">
                          <th scope="row">{{$ship->id}}</th>
                          <td>{{$ship->shipment_company}}</td>
                          <td>{{$ship->shipment_date}}</td>
                          <td>{{$ship->recieved}}</td>
                          <td>                          
                            <a href="{{route('ships.show', $ship->id)}}"><button type="button" class="btn btn-primary float-left">View</button></a>
                                                        
                          </td>
                        </tr>
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
