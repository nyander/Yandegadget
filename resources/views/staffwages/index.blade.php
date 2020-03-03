@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-9">
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
                                
                                <a href="{{route('staffwages.edit', $wage->id)}}"><button type="button" class="btn btn-primary float-left">Edit</button></a>
                                
                                
                                <form action="{{route('staffwages.destroy', $wage->id)}}" method="POST" class="float-left">
                                @csrf
                                {{method_field('DELETE')}}
                                <button type="submit" class="btn btn-danger">Delete</button>
                                </form> 
                               
                                
                            </td>
                        </tr>    
                        @endforeach   
                                               
                        </tbody>
                      </table>                      
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
