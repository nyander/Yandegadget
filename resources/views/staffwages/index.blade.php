@extends('layouts.settingside')

@section('settings')

            <div class="card">
                <div class="card-header">wages</div>
                <div class="card-body">
                     {{-- Table design based from : https://getbootstrap.com/docs/4.4/content/tables/ --}}
                     <table class="table">
                        <thead class="thead-dark">
                          <tr>
                            <th scope="col">#</th>
                            <th scope="col">Pay</th>
                            <th scope="col">Start Date</th>
                            <th scope="col">End Date</th>                            
                            <th scope="col"><a href="{{route('staffwages.create')}}"><button type="button" class="btn btn-success" >Add</button></a></th>                            
                          </tr>
                        </thead>
                        <tbody>
                        @foreach ($wages as $wage)
                        <tr>
                            <th scope="row">{{$wage->id}}</th>
                            <td>£ {{$wage->wage}}</a></td>
                            <td>{{$wage->startDate}}</td>
                            <td>{{$wage->endDate}}</td>
                            <td>                          
                                
                                
                                
                                <form action="{{route('staffwages.destroy', $wage->id)}}" method="POST" onsubmit="myButton.disabled = true; return true;">
                                  @csrf
                                  {{method_field('DELETE')}}
                                  <button type="submit" class="btn btn-sm btn-outline-danger" name="myButton">Delete</button>
                                </form> 
                               
                                
                            </td>
                        </tr>    
                        @endforeach   
                                               
                        </tbody>
                      </table>                      
                </div>
            </div>
@endsection
